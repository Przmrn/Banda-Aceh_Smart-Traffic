import cv2
import onnxruntime as ort
import numpy as np
import requests
import time
import os
import math
import logging
import signal
import sys
from datetime import datetime

# --- SETUP LOGGING ---
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('traffic_worker.log'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

# --- CONFIGURATION ---
class Config:
    # Stream settings
    STREAMS = [
        {
            'id': 'stream-1',
            'name': 'Simpang Lima',
            'url': 'https://cctv-stream.bandaacehkota.info/memfs/097d5dcf-eda2-4b29-9661-fcc035d7770f_output_0.m3u8',
            'enabled': True,  # Can be toggled
            'frame_skip': 5,  # Process every 5th frame
            'max_width': 1280  # Downscale if wider than this
        },
        {
            'id': 'stream-2',
            'name': 'Simpang Emat',
            'url': 'https://cctv-stream.bandaacehkota.info/memfs/f9444904-ad31-4401-9643-aee6e33b85c7_output_0.m3u8',
            'enabled': True,
            'frame_skip': 5,
            'max_width': 1280
        }
    ]
    
    # Model settings
    MODEL_PATH = 'yolov8n.onnx'
    SCORE_THRESHOLD = 0.25
    IOU_THRESHOLD = 0.4
    MODEL_HEIGHT = 640
    MODEL_WIDTH = 640
    
    # Processing settings
    UPDATE_INTERVAL = 5  # seconds between updates
    MAX_RETRIES = 3     # Max retries for failed requests
    REQUEST_TIMEOUT = 10  # seconds
    
    # Enhancement settings
    APPLY_CLAHE = True
    APPLY_GAMMA = True
    GAMMA_VALUE = 1.2

# --- GLOBALS ---
running = True

# --- SIGNAL HANDLER ---
def signal_handler(sig, frame):
    global running
    logger.info("Shutting down gracefully...")
    running = False
    sys.exit(0)

# --- ENHANCEMENT FUNCTIONS ---
def apply_clahe(frame):
    """Apply CLAHE for local contrast enhancement"""
    if not Config.APPLY_CLAHE:
        return frame
    
    try:
        lab = cv2.cvtColor(frame, cv2.COLOR_BGR2LAB)
        l, a, b = cv2.split(lab)
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
        l = clahe.apply(l)
        enhanced = cv2.merge([l, a, b])
        return cv2.cvtColor(enhanced, cv2.COLOR_LAB2BGR)
    except Exception as e:
        logger.error(f"Error in CLAHE: {e}")
        return frame

def apply_gamma_correction(frame, gamma=1.2):
    """Apply gamma correction for brightness adjustment"""
    if not Config.APPLY_GAMMA:
        return frame
    
    try:
        inv_gamma = 1.0 / gamma
        table = np.array([((i / 255.0) ** inv_gamma) * 255
                         for i in np.arange(0, 256)]).astype("uint8")
        return cv2.LUT(frame, table)
    except Exception as e:
        logger.error(f"Error in gamma correction: {e}")
        return frame

def enhance_frame(frame):
    """Enhance frame with gamma correction and CLAHE"""
    try:
        enhanced = apply_gamma_correction(frame, Config.GAMMA_VALUE)
        enhanced = apply_clahe(enhanced)
        return enhanced
    except Exception as e:
        logger.error(f"Error enhancing frame: {e}")
        return frame

def resize_frame(frame, max_width):
    """Resize frame while maintaining aspect ratio"""
    if frame is None:
        return None
        
    height, width = frame.shape[:2]
    if width > max_width:
        ratio = max_width / width
        new_height = int(height * ratio)
        return cv2.resize(frame, (max_width, new_height))
    return frame

# --- DETECTION FUNCTIONS ---
def preprocess_frame(frame, target_width, target_height):
    """Preprocess frame for model input"""
    if frame is None:
        return None
        
    frame_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
    input_tensor = cv2.resize(frame_rgb, (target_width, target_height))
    input_tensor = input_tensor.transpose(2, 0, 1)  # HWC to CHW
    input_tensor = np.expand_dims(input_tensor, axis=0).astype(np.float32)
    input_tensor /= 255.0  # Normalize to [0, 1]
    return input_tensor

def run_detection(session, input_name, frame):
    """Run object detection on frame"""
    if frame is None:
        return []
        
    # Preprocess
    input_tensor = preprocess_frame(frame, Config.MODEL_WIDTH, Config.MODEL_HEIGHT)
    if input_tensor is None:
        return []
    
    # Run inference
    try:
        outputs = session.run(None, {input_name: input_tensor})
        return process_detections(outputs[0], frame.shape[1], frame.shape[0])
    except Exception as e:
        logger.error(f"Error during detection: {e}")
        return []

def process_detections(output, original_width, original_height):
    """Process model output and filter detections"""
    detections = []
    
    # Get the boxes, scores, and class IDs
    boxes = output[0]  # [batch, num_boxes, 4]
    scores = output[1]  # [batch, num_boxes]
    class_ids = output[2]  # [batch, num_boxes]
    
    # Process each detection
    for i in range(len(scores[0])):
        score = scores[0][i]
        if score < Config.SCORE_THRESHOLD:
            continue
            
        class_id = int(class_ids[0][i])
        box = boxes[0][i]
        
        # Convert box from [x1, y1, x2, y2] to [x, y, width, height]
        x1, y1, x2, y2 = box
        width = x2 - x1
        height = y2 - y1
        
        # Scale to original image dimensions
        x1 = int(x1 * original_width / Config.MODEL_WIDTH)
        y1 = int(y1 * original_height / Config.MODEL_HEIGHT)
        width = int(width * original_width / Config.MODEL_WIDTH)
        height = int(height * original_height / Config.MODEL_HEIGHT)
        
        detections.append({
            'class_id': class_id,
            'score': float(score),
            'box': [x1, y1, width, height]
        })
    
    return detections

# --- STREAM PROCESSING ---
def process_stream(stream, session, input_name):
    """Process a single video stream"""
    if not stream.get('enabled', True):
        logger.info(f"Stream {stream['name']} is disabled, skipping...")
        return None
    
    cap = None
    frame_count = 0
    last_update = time.time()
    
    try:
        logger.info(f"Connecting to {stream['name']} at {stream['url']}")
        cap = cv2.VideoCapture(stream['url'])
        
        if not cap.isOpened():
            logger.error(f"Failed to open stream: {stream['name']}")
            return None
        
        # Read first frame to get properties
        ret, frame = cap.read()
        if not ret:
            logger.error(f"Failed to read from stream: {stream['name']}")
            return None
            
        # Resize frame if needed
        frame = resize_frame(frame, stream.get('max_width', 1280))
        
        # Run detection
        detections = run_detection(session, input_name, frame)
        
        # Count vehicles (class 2: car, 3: motorbike, 5: bus, 7: truck)
        vehicle_count = sum(1 for d in detections if d['class_id'] in [2, 3, 5, 7])
        
        # Log results
        logger.info(f"{stream['name']} - Detected {vehicle_count} vehicles")
        
        return {
            'id': stream['id'],
            'name': stream['name'],
            'vehicle_count': vehicle_count,
            'timestamp': datetime.now().isoformat(),
            'detection_count': len(detections)
        }
        
    except Exception as e:
        logger.error(f"Error processing {stream['name']}: {str(e)}")
        return None
        
    finally:
        if cap is not None:
            cap.release()

# --- UPDATE HANDLING ---
def send_update(stream_data):
    """Send update to Laravel backend"""
    if not stream_data:
        return False
        
    url = "http://127.0.0.1:8000/api/traffic-update"
    headers = {'Content-Type': 'application/json'}
    
    try:
        response = requests.post(
            url,
            json=stream_data,
            headers=headers,
            timeout=Config.REQUEST_TIMEOUT
        )
        response.raise_for_status()
        logger.info(f"Update sent successfully for {stream_data['name']}")
        return True
    except requests.exceptions.RequestException as e:
        logger.error(f"Failed to send update for {stream_data['name']}: {str(e)}")
        return False

# --- MAIN WORKER ---
def start_worker():
    """Main worker function"""
    global running
    
    # Set up signal handler for graceful shutdown
    signal.signal(signal.SIGINT, signal_handler)
    signal.signal(signal.SIGTERM, signal_handler)
    
    logger.info("Starting traffic monitoring worker...")
    
    # Initialize ONNX Runtime session
    try:
        session = ort.InferenceSession(Config.MODEL_PATH)
        input_name = session.get_inputs()[0].name
        logger.info(f"Model loaded: {Config.MODEL_PATH}")
    except Exception as e:
        logger.error(f"Failed to load model: {str(e)}")
        return
    
    # Main loop
    while running:
        start_time = time.time()
        
        # Process each stream
        for stream in Config.STREAMS:
            if not running:
                break
                
            # Process stream
            result = process_stream(stream, session, input_name)
            
            # Send update if successful
            if result:
                send_update(result)
            
            # Small delay between streams
            time.sleep(1)
        
        # Calculate sleep time to maintain update interval
        elapsed = time.time() - start_time
        sleep_time = max(0, Config.UPDATE_INTERVAL - elapsed)
        
        if sleep_time > 0 and running:
            logger.debug(f"Sleeping for {sleep_time:.2f} seconds...")
            time.sleep(sleep_time)
    
    logger.info("Worker stopped")

if __name__ == "__main__":
    start_worker()
