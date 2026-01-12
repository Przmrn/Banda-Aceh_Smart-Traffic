import cv2
import requests
import time
import os
import onnxruntime as ort
import numpy as np

# --- KONFIGURASI ---
# Lokasi folder tempat Laravel menyimpan upload
# Biasanya di: [Folder Laravel]/storage/app/public/videos/
VIDEO_STORAGE_PATH = "D:/Laravel/smart-traffic/storage/app/public/videos/"

LARAVEL_WEBHOOK_URL = "http://127.0.0.1:8000/api/traffic-update"

# Load Model YOLO (sama seperti sebelumnya)
MODEL_PATH = "yolov8n.onnx"
session = ort.InferenceSession(MODEL_PATH, providers=['CPUExecutionProvider'])
input_name = session.get_inputs()[0].name
MODEL_WIDTH, MODEL_HEIGHT = 640, 640

# --- FUNGSI PREPROCESS & DETEKSI (Copy paste dari realtime_worker.py) ---
# (Pastikan Anda menyalin fungsi preprocess_frame dan run_detection_and_get_stats di sini)
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
        # Anggap kelas 2=car, 3=motorcycle, 5=bus, 7=truck (COCO dataset)
        if class_id in [2, 3, 5, 7]:
            car_count += 1
    return car_count

# --- MAIN LOOP ---
def process_static_video():
    print("--- WORKER VIDEO STATIS ---")
    filename = input("Masukkan nama file video (contoh: 173556_video.mp4): ")

    full_path = os.path.join(VIDEO_STORAGE_PATH, filename)

    if not os.path.exists(full_path):
        print(f"Error: File tidak ditemukan di {full_path}")
        return

    cap = cv2.VideoCapture(full_path)
    print(f"Mulai memproses: {filename}")

    while cap.isOpened():
        ret, frame = cap.read()
        if not ret:
            print("Video selesai.")
            break

        # 1. Proses
        input_data = preprocess_frame(frame, MODEL_WIDTH, MODEL_HEIGHT)
        result = session.run(None, {input_name: input_data})

        h, w = frame.shape[:2]
        car_count = run_detection_and_get_stats(result, w, h, MODEL_WIDTH, MODEL_HEIGHT)

        # 2. Kirim Data ke Laravel
        payload = {'car_count': car_count}
        try:
            requests.post(LARAVEL_WEBHOOK_URL, json=payload, timeout=0.5)
            print(f"Frame diproses. Mobil: {car_count}")
        except:
            pass

        # 3. Kontrol Kecepatan (PENTING)
        # Karena ini file lokal, Python akan memproses secepat kilat (100+ FPS).
        # Kita beri delay agar terlihat seperti 'real-time' di dashboard.
        time.sleep(0.1)

    cap.release()

if __name__ == "__main__":
    process_static_video()
