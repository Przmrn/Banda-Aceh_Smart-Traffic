import cv2
import onnxruntime as ort
import numpy as np
import requests
import time
import os
import math

# --- PENGATURAN ---
STREAMS = [
    {
        'id': 'stream-1',
        'name': 'Ulee Lheu 1',
        'url': 'https://cctv-stream.bandaacehkota.info/memfs/097d5dcf-eda2-4b29-9661-fcc035d7770f_output_0.m3u8?session=YfkSxaizRZbFzmGAk4arpy',
        'car_count': 0,
        'last_update': time.time()
    },
    {
        'id': 'stream-2',
        'name': 'Ulee Lheu 2',
        'url': 'https://cctv-stream.bandaacehkota.info/memfs/7eec00d1-9025-4c1c-9007-7a742cac3dd8_output_0.m3u8?session=RpRPECagkXWFiSPtU2WPWz',
        'car_count': 0,
        'last_update': time.time()
    }
]

LARAVEL_WEBHOOK_URL = "http://127.0.0.1:8000/api/traffic-update"
UPDATE_INTERVAL = 5  # seconds between updates

# Pengaturan Model
MODEL_PATH = 'yolov8m.onnx'
SCORE_THRESHOLD = 0.15  # Lowered for night-time detection
IOU_THRESHOLD = 0.6     # More lenient overlap for night-time
MODEL_HEIGHT = 640
MODEL_WIDTH = 640
CLASS_NAMES = ['person', 'bicycle', 'car', 'motorbike', 'aeroplane', 'bus', 'train', 'truck', 'boat', 'traffic light', 'fire hydrant', 'stop sign', 'parking meter', 'bench', 'bird', 'cat', 'dog', 'horse', 'sheep', 'cow', 'elephant', 'bear', 'zebra', 'giraffe', 'backpack', 'umbrella', 'handbag', 'tie', 'suitcase', 'frisbee', 'skis', 'snowboard', 'sports ball', 'kite', 'baseball bat', 'baseball glove', 'skateboard', 'surfboard', 'tennis racket', 'bottle', 'wine glass', 'cup', 'fork', 'knife', 'spoon', 'bowl', 'banana', 'apple', 'sandwich', 'orange', 'broccoli', 'carrot', 'hot dog', 'pizza', 'donut', 'cake', 'chair', 'sofa', 'pottedplant', 'bed', 'diningtable', 'toilet', 'tvmonitor', 'laptop', 'mouse', 'remote', 'keyboard', 'cell phone', 'microwave', 'oven', 'toaster', 'sink', 'refrigerator', 'book', 'clock', 'vase', 'scissors', 'teddy bear', 'hair drier', 'toothbrush']

# Pengaturan Enhancement
APPLY_CLAHE = True  # Set False untuk disable CLAHE
APPLY_GAMMA = True  # Set False untuk disable gamma correction
SAVE_DEBUG_IMAGES = False  # Set True to enable debug image saving

# --- FUNGSI ENHANCEMENT ---
def apply_clahe(frame):
    """CLAHE untuk meningkatkan kontras lokal"""
    lab = cv2.cvtColor(frame, cv2.COLOR_BGR2LAB)
    l, a, b = cv2.split(lab)
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    l = clahe.apply(l)
    enhanced = cv2.merge([l, a, b])
    return cv2.cvtColor(enhanced, cv2.COLOR_LAB2BGR)

def apply_gamma_correction(frame, gamma=1.2):
    """Gamma correction untuk brightness adjustment"""
    inv_gamma = 1.0 / gamma
    table = np.array([((i / 255.0) ** inv_gamma) * 255 for i in range(256)]).astype("uint8")
    return cv2.LUT(frame, table)

def enhance_frame(frame, gamma_value=1.2):
    """Pipeline enhancement: gamma dulu, baru CLAHE"""
    enhanced = frame.copy()

    if APPLY_GAMMA:
        enhanced = apply_gamma_correction(enhanced, gamma_value)

    if APPLY_CLAHE:
        enhanced = apply_clahe(enhanced)

    return enhanced

# --- FUNGSI DETEKSI ---
def preprocess_frame(frame, target_width, target_height):
    img = cv2.resize(frame, (target_width, target_height))
    img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    img = img.astype(np.float32) / 255.0
    img = np.transpose(img, (2, 0, 1))
    return np.expand_dims(img, axis=0)

def run_detection_and_get_stats(output_from_model, original_width, original_height, model_width, model_height, stream_name="Unknown", frame=None):
    outputs = np.transpose(np.squeeze(output_from_model[0]))
    boxes, scores, class_ids = [], [], []
    x_factor, y_factor = original_width / model_width, original_height / model_height

    # TEMPORARY: Disable ROI to see ALL detections
    roi_y_start = 0  # CHANGED: was int(original_height * 0.4)
    roi_y_end = original_height
    roi_x_start = 0  # CHANGED: was int(original_width * 0.1)
    roi_x_end = original_width

    total_detections = 0
    vehicle_detections = 0
    roi_filtered = 0

    for row in outputs:
        class_scores = row[4:]
        max_score, class_id = np.max(class_scores), np.argmax(class_scores)

        # Check ALL objects first
        if max_score > SCORE_THRESHOLD:
            total_detections += 1

            # Only consider vehicle classes
            if CLASS_NAMES[class_id] in ['car', 'bus', 'truck', 'motorbike']:
                vehicle_detections += 1
                scores.append(max_score)
                class_ids.append(class_id)
                cx, cy, w, h = row[:4]
                x, y = int((cx - w/2) * x_factor), int((cy - h/2) * y_factor)
                w, h = int(w * x_factor), int(h * y_factor)

                # Check if the center of the box is within ROI
                box_center_x = x + w//2
                box_center_y = y + h//2
                if (roi_x_start <= box_center_x <= roi_x_end and
                    roi_y_start <= box_center_y <= roi_y_end):
                    boxes.append([x, y, w, h])
                else:
                    roi_filtered += 1

    # Apply NMS
    indices = cv2.dnn.NMSBoxes(boxes, scores, SCORE_THRESHOLD, IOU_THRESHOLD) if boxes else []

    # DEBUG PRINT with stream name
    print(f"[{stream_name}] DEBUG: Total detections={total_detections}, Vehicles={vehicle_detections}, "
          f"ROI filtered={roi_filtered}, After NMS={len(indices)}")

    # Visualization code (same as before)
    if frame is not None:
        cv2.rectangle(frame, (roi_x_start, roi_y_start), (roi_x_end, roi_y_end), (0, 0, 255), 2)

        if len(indices) > 0:
            for i in indices.flatten():
                x, y, w, h = boxes[i]
                cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 255, 0), 2)
                label = f"{CLASS_NAMES[class_ids[i]].upper()}: {scores[i]:.2f}"
                (label_w, label_h), _ = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.5, 1)
                cv2.rectangle(frame, (x, y - 20), (x + label_w, y), (0, 255, 0), -1)
                cv2.putText(frame, label, (x, y - 5),
                           cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 0, 0), 1, cv2.LINE_AA)

    return len(indices)

def process_stream(stream, session, input_name):
    print(f"Processing stream: {stream['name']}")

    # Set gamma value per stream
    if stream['id'] == 'stream-1':
        gamma_value = 1.2
    elif stream['id'] == 'stream-2':
        gamma_value = 1.5  # Brighter for darker camera
    else:
        gamma_value = 1.2

    print(f"  → Using GAMMA={gamma_value} for {stream['name']}")

    cap = cv2.VideoCapture(stream['url'])

    if not cap.isOpened():
        print(f"\n{'='*50}")
        print(f"ERROR: Could not open video stream for {stream['name']}")
        print(f"URL: {stream['url']}")
        print("Possible causes:")
        print("1. The session token in the URL has expired.")
        print("2. The stream is offline.")
        print("3. Network connectivity issues.")
        print(f"{'='*50}\n")
        return

    frame_count = 0
    detection_history = []  # Track recent detections
    detection_interval = 1  # Process every frame
    last_debug_save = time.time()

    while True:
        ret, frame = cap.read()
        if not ret:
            print(f"Error reading frame from {stream['name']}. Reconnecting...")
            cap.release()
            time.sleep(5)  # Wait before reconnecting
            cap = cv2.VideoCapture(stream['url'])
            continue

        frame_count += 1
        if frame_count % detection_interval != 0:
            continue

        try:
            # Create a copy for display
            display_frame = frame.copy()

            # Enhance frame with stream-specific gamma
            enhanced_frame = enhance_frame(frame, gamma_value)

            # Resize and preprocess for model
            input_tensor = preprocess_frame(enhanced_frame, MODEL_WIDTH, MODEL_HEIGHT)

            # Run inference
            start_time = time.time()
            outputs = session.run(None, {input_name: input_tensor})
            inference_time = time.time() - start_time

            # Get detections with visualization
            car_count = run_detection_and_get_stats(
                outputs, frame.shape[1], frame.shape[0],
                MODEL_WIDTH, MODEL_HEIGHT, stream['name'], display_frame
            )

            # Add to detection history
            detection_history.append(car_count)
            if len(detection_history) > 20:  # Keep last 20 frames
                detection_history.pop(0)

            # Use maximum from recent history
            max_count = max(detection_history) if detection_history else 0

            # Update stream data
            stream['car_count'] = max_count
            stream['last_update'] = time.time()

            # ADDED: Debug print
            if frame_count % 30 == 0:  # Print every 30 frames to avoid spam
                print(f"[{stream['name']}] Current={car_count}, Max={max_count}, History={detection_history[-5:]}")

            # For debugging: Save frame with detections every 10 seconds
            if SAVE_DEBUG_IMAGES:
                current_time = time.time()
                if current_time - last_debug_save > 10:
                    debug_filename = f"debug_{stream['id']}_{int(current_time)}.jpg"
                    cv2.imwrite(debug_filename, display_frame)
                    print(f"Saved debug image: {debug_filename}")
                    last_debug_save = current_time

        except Exception as e:
            print(f"Error processing frame from {stream['name']}: {str(e)}")
            import traceback
            traceback.print_exc()
            continue

        # Small delay to prevent high CPU usage
        time.sleep(0.05)

    cap.release()

def send_updates(streams):
    while True:
        try:
            total_vehicles = sum(stream['car_count'] for stream in streams)

            # Prepare data for each stream
            streams_data = [
                {
                    'id': stream['id'],
                    'name': stream['name'],
                    'car_count': stream['car_count'],
                    'timestamp': time.time()
                } for stream in streams
            ]

            # Send to Laravel
            data = {
                'total_vehicles': int(total_vehicles),
                'streams': streams_data,
                'timestamp': time.time()
            }

            print(f"Sending update to Laravel: Total Vehicles={total_vehicles}")
            # print(f"Payload: {data}") # Uncomment for verbose debug

            try:
                response = requests.post(LARAVEL_WEBHOOK_URL, json=data, timeout=5)
                if response.status_code == 200:
                    print(f"Update sent successfully: {data}")
                else:
                    print(f"Failed to send update: {response.status_code} - {response.text}")
            except requests.exceptions.RequestException as e:
                print(f"Error sending update: {e}")

            time.sleep(UPDATE_INTERVAL)

        except Exception as e:
            print(f"Error in update thread: {e}")
            time.sleep(5)  # Wait before retrying

def start_worker():
    print(f"Memuat model {MODEL_PATH}...")
    print(f"Enhancement: CLAHE={APPLY_CLAHE}, Gamma={APPLY_GAMMA}")

    try:
        providers = ['CUDAExecutionProvider', 'CPUExecutionProvider']
        session = ort.InferenceSession(MODEL_PATH, providers=providers)
        print(f"Model dimuat, menggunakan: {session.get_providers()[0]}")
    except Exception as e:
        print(f"Gagal memuat model: {e}")
        return

    input_name = session.get_inputs()[0].name
    import threading

    # Start update thread
    update_thread = threading.Thread(target=send_updates, args=(STREAMS,), daemon=True)
    update_thread.start()

    # Start processing each stream in a separate thread
    threads = []
    for stream in STREAMS:
        t = threading.Thread(target=process_stream, args=(stream, session, input_name), daemon=True)
        t.start()
        threads.append(t)
        time.sleep(1)  # Stagger thread starts

    try:
        # Keep main thread alive
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        print("\nMenghentikan worker...")

    # Wait for all threads to finish
    for t in threads:
        t.join()
    update_thread.join()

if __name__ == "__main__":
    start_worker()
