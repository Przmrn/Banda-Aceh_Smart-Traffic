import cv2
import requests
import time
import os
import onnxruntime as ort
import numpy as np
import mysql.connector

# --- CONFIGURATION ---
DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'laravel' # CHECK THIS MATCHES YOUR DB NAME
}

# IMPORTANT: Ensure this path ends with a slash '/'
VIDEO_STORAGE_PATH = "D:/Laravel/smart-traffic/public/uploads/"
LARAVEL_WEBHOOK_URL = "http://127.0.0.1:8000/api/traffic-update"

# Load Model
print(" [INIT] Loading YOLO Model...")
MODEL_PATH = "yolov8n.onnx"
session = ort.InferenceSession(MODEL_PATH, providers=['CPUExecutionProvider'])
input_name = session.get_inputs()[0].name
MODEL_WIDTH, MODEL_HEIGHT = 640, 640
print(" [INIT] Model Loaded.")

def preprocess_frame(frame, w, h):
    image = cv2.resize(frame, (w, h))
    image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
    image = image.transpose((2, 0, 1))
    image = np.expand_dims(image, axis=0)
    image = image.astype(np.float32) / 255.0
    return image

def run_detection_and_get_stats(results, frame_w, frame_h, model_w, model_h):
    output = results[0][0]
    output = output.transpose()
    car_count = 0
    for row in output:
        prob = row[4:].max()
        if prob < 0.5: continue
        class_id = row[4:].argmax()
        if class_id in [2, 3, 5, 7]: # Car, Motorcycle, Bus, Truck
            car_count += 1
    return car_count

def connect_db():
    return mysql.connector.connect(**DB_CONFIG)

def start_worker():
    print(" [SYSTEM] DEBUG WORKER STARTED. WAITING FOR UPLOADS...")

    while True:
        try:
            conn = connect_db()
            cursor = conn.cursor(dictionary=True)

            # 1. Check for Pending Jobs
            cursor.execute("SELECT * FROM analysis_jobs WHERE status = 'pending' LIMIT 1")
            job = cursor.fetchone()

            if job:
                filename = job['filename']
                job_id = job['id']

                print(f"\n [JOB DETECTED] ID: {job_id} | File: {filename}")

                # Update Status
                cursor.execute("UPDATE analysis_jobs SET status = 'processing' WHERE id = %s", (job_id,))
                conn.commit()

                # Verify Path
                full_path = os.path.join(VIDEO_STORAGE_PATH, filename)
                print(f" [DEBUG] Looking for file at: {full_path}")

                if not os.path.exists(full_path):
                    print(f" [ERROR] File NOT found at path!")
                    cursor.execute("UPDATE analysis_jobs SET status = 'failed' WHERE id = %s", (job_id,))
                    conn.commit()
                    conn.close()
                    continue

                print(f" [DEBUG] File found. Opening video capture...")
                cap = cv2.VideoCapture(full_path)

                if not cap.isOpened():
                    print(" [ERROR] cv2.VideoCapture failed to open file.")
                    cursor.execute("UPDATE analysis_jobs SET status = 'failed' WHERE id = %s", (job_id,))
                    conn.commit()
                    continue

                print(" [DEBUG] Video opened successfully. Starting processing loop...")

                frame_count = 0
                while cap.isOpened():
                    ret, frame = cap.read()
                    if not ret:
                        print(" [DEBUG] End of video stream reached.")
                        break

                    frame_count += 1

                    # Inference
                    input_data = preprocess_frame(frame, MODEL_WIDTH, MODEL_HEIGHT)
                    result = session.run(None, {input_name: input_data})

                    h, w = frame.shape[:2]
                    car_count = run_detection_and_get_stats(result, w, h, MODEL_WIDTH, MODEL_HEIGHT)

                    # Send Data
                    payload = {
                        'car_count': car_count,
                        'mode': 'static',
                        'source_id': filename
                    }

                    try:
                        response = requests.post(LARAVEL_WEBHOOK_URL, json=payload, timeout=2.0)
                        # Print progress every 10 frames to avoid spamming, but show activity
                        if frame_count % 5 == 0:
                            print(f" -> Frame {frame_count}: {car_count} objects detected (Status: {response.status_code})")
                    except Exception as e:
                        print(f" [NET ERROR] Could not connect to Laravel: {e}")

                    # Speed Control
                    time.sleep(0.04)

                cap.release()
                cursor.execute("UPDATE analysis_jobs SET status = 'completed' WHERE id = %s", (job_id,))
                conn.commit()
                print(f" [JOB COMPLETE] Finished processing {filename}")

            else:
                # No jobs, silent wait
                time.sleep(2)

            cursor.close()
            conn.close()

        except Exception as e:
            print(f" [CRITICAL ERROR] {e}")
            time.sleep(5)

if __name__ == "__main__":
    start_worker()
