import cv2
import requests
import time
import os
import onnxruntime as ort
import numpy as np
import mysql.connector # NEW: Import MySQL Connector

# --- KONFIGURASI DATABASE ---
# Ganti dengan user/pass database lokalmu
DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'laravel' # Pastikan nama DB sesuai dengan .env Laravel
}

# --- KONFIGURASI PATH ---
# Lokasi folder tempat Laravel menyimpan upload
# NOTE: Pastikan path ini mengarah ke folder 'public/uploads' di project Laravelmu
VIDEO_STORAGE_PATH = "D:/Laravel/smart-traffic/public/uploads/"

LARAVEL_WEBHOOK_URL = "http://127.0.0.1:8000/api/traffic-update"

# Load Model YOLO (ONNX)
MODEL_PATH = "yolov8n.onnx"
session = ort.InferenceSession(MODEL_PATH, providers=['CPUExecutionProvider'])
input_name = session.get_inputs()[0].name
MODEL_WIDTH, MODEL_HEIGHT = 640, 640

# --- FUNGSI PREPROCESS & DETEKSI (TETAP SAMA) ---
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
        # Class: 2=car, 3=motorcycle, 5=bus, 7=truck
        if class_id in [2, 3, 5, 7]:
            car_count += 1
    return car_count

# --- FUNGSI DATABASE ---
def connect_db():
    return mysql.connector.connect(**DB_CONFIG)

# --- MAIN WORKER LOOP ---
def start_worker():
    print(" [SYSTEM] STATIC WORKER STARTED. WAITING FOR UPLOADS...")

    while True:
        try:
            conn = connect_db()
            cursor = conn.cursor(dictionary=True)

            # 1. Cari job yang statusnya 'pending'
            cursor.execute("SELECT * FROM analysis_jobs WHERE status = 'pending' LIMIT 1")
            job = cursor.fetchone()

            if job:
                filename = job['filename']
                job_id = job['id']

                print(f" [JOB FOUND] Processing: {filename}")

                # 2. Update status jadi 'processing'
                cursor.execute("UPDATE analysis_jobs SET status = 'processing' WHERE id = %s", (job_id,))
                conn.commit()

                # Cek apakah file ada
                full_path = os.path.join(VIDEO_STORAGE_PATH, filename)
                if not os.path.exists(full_path):
                    print(f" [ERROR] File not found: {full_path}")
                    cursor.execute("UPDATE analysis_jobs SET status = 'failed' WHERE id = %s", (job_id,))
                    conn.commit()
                    conn.close()
                    continue

                # 3. Proses Video
                cap = cv2.VideoCapture(full_path)

                while cap.isOpened():
                    ret, frame = cap.read()
                    if not ret:
                        break

                    # Inference ONNX
                    input_data = preprocess_frame(frame, MODEL_WIDTH, MODEL_HEIGHT)
                    result = session.run(None, {input_name: input_data})

                    h, w = frame.shape[:2]
                    car_count = run_detection_and_get_stats(result, w, h, MODEL_WIDTH, MODEL_HEIGHT)

                    # KIRIM KE LARAVEL (Updated Payload)
                    # Penting: 'source_id' harus sama dengan filename agar Web Socket tahu video mana yang diupdate
                    payload = {
                        'car_count': car_count,
                        'mode': 'static',      # Identitas mode
                        'source_id': filename  # Identitas file
                    }

                    try:
                        requests.post(LARAVEL_WEBHOOK_URL, json=payload, timeout=0.5)
                        # print(f"Processing: {car_count} cars", end='\r') # Optional log
                    except:
                        pass

                    # Delay simulasi agar sinkron dengan video player di browser
                    # Sesuaikan angka ini (0.04 ~ 25 FPS)
                    time.sleep(0.04)

                cap.release()

                # 4. Selesai -> Update status jadi 'completed'
                cursor.execute("UPDATE analysis_jobs SET status = 'completed' WHERE id = %s", (job_id,))
                conn.commit()
                print(f"\n [JOB COMPLETE] Finished: {filename}")

            else:
                # Tidak ada job, tunggu 2 detik sebelum cek lagi
                # print(" [IDLE] Waiting...", end='\r')
                time.sleep(2)

            cursor.close()
            conn.close()

        except Exception as e:
            print(f" [ERROR] Database Connection Failed: {e}")
            time.sleep(5)

if __name__ == "__main__":
    start_worker()
