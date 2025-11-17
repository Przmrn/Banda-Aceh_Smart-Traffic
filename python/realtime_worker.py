import cv2
import onnxruntime as ort
import numpy as np
import requests # Library untuk mengirim data ke Laravel
import time
import os

# --- PENGATURAN ---
# URL Stream CCTV Anda (ganti ini)
STREAM_URL = "https://atcs-dishub.bandung.go.id:1990/PaskalPTZ2/stream.m3u8"

# Endpoint Webhook Laravel (yang akan kita buat di Langkah 5)
LARAVEL_WEBHOOK_URL = "http://127.0.0.1:8000/api/traffic-update"

# Pengaturan Model (salin dari app.py)
MODEL_PATH = 'yolov8n.onnx'
SCORE_THRESHOLD = 0.5
IOU_THRESHOLD = 0.5
MODEL_HEIGHT = 640
MODEL_WIDTH = 640
CLASS_NAMES = ['person', 'bicycle', 'car', 'motorbike', 'aeroplane', 'bus', 'train', 'truck', 'boat', 'traffic light', 'fire hydrant', 'stop sign', 'parking meter', 'bench', 'bird', 'cat', 'dog', 'horse', 'sheep', 'cow', 'elephant', 'bear', 'zebra', 'giraffe', 'backpack', 'umbrella', 'handbag', 'tie', 'suitcase', 'frisbee', 'skis', 'snowboard', 'sports ball', 'kite', 'baseball bat', 'baseball glove', 'skateboard', 'surfboard', 'tennis racket', 'bottle', 'wine glass', 'cup', 'fork', 'knife', 'spoon', 'bowl', 'banana', 'apple', 'sandwich', 'orange', 'broccoli', 'carrot', 'hot dog', 'pizza', 'donut', 'cake', 'chair', 'sofa', 'pottedplant', 'bed', 'diningtable', 'toilet', 'tvmonitor', 'laptop', 'mouse', 'remote', 'keyboard', 'cell phone', 'microwave', 'oven', 'toaster', 'sink', 'refrigerator', 'book', 'clock', 'vase', 'scissors', 'teddy bear', 'hair drier', 'toothbrush']

# --- FUNGSI DETEKSI (salin dari app.py) ---
def preprocess_frame(frame, target_width, target_height):
    img = cv2.resize(frame, (target_width, target_height))
    img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    img = img.astype(np.float32) / 255.0
    img = np.transpose(img, (2, 0, 1))
    return np.expand_dims(img, axis=0)

def run_detection_and_get_stats(output_from_model, original_width, original_height, model_width, model_height):
    outputs = np.transpose(np.squeeze(output_from_model[0]))
    boxes, scores, class_ids = [], [], []
    x_factor, y_factor = original_width / model_width, original_height / model_height

    for row in outputs:
        class_scores = row[4:]
        max_score, class_id = np.max(class_scores), np.argmax(class_scores)
        if max_score > SCORE_THRESHOLD:
            scores.append(max_score)
            class_ids.append(class_id)
            cx, cy, w, h = row[:4]
            boxes.append([int((cx - w / 2) * x_factor), int((cy - h / 2) * y_factor), int(w * x_factor), int(h * y_factor)])

    indices = cv2.dnn.NMSBoxes(boxes, np.array(scores), SCORE_THRESHOLD, IOU_THRESHOLD)
    car_count = 0
    if len(indices) > 0:
        for i in indices.flatten():
            class_name = CLASS_NAMES[class_ids[i]]
            if class_name in ['car', 'bus', 'truck', 'motorbike']:
                car_count += 1
    return car_count

# --- FUNGSI UTAMA ---
def start_worker():
    print(f"Memuat model {MODEL_PATH}...")
    try:
        # Gunakan GPU jika ada, jika tidak, gunakan CPU
        providers = ['CUDAExecutionProvider', 'CPUExecutionProvider']
        session = ort.InferenceSession(MODEL_PATH, providers=providers)
        print(f"Model dimuat, menggunakan: {session.get_providers()[0]}")
    except Exception as e:
        print(f"Gagal memuat model: {e}")
        return

    input_name = session.get_inputs()[0].name

    while True:
        try:
            print(f"Mencoba terhubung ke stream: {STREAM_URL}...")
            cap = cv2.VideoCapture(STREAM_URL)
            if not cap.isOpened():
                print("Gagal membuka stream. Mencoba lagi dalam 10 detik...")
                time.sleep(10)
                continue

            print("Berhasil terhubung ke stream. Memulai analisis...")
            while cap.isOpened():
                ret, frame = cap.read()
                if not ret:
                    print("Stream terputus. Menghubungkan ulang...")
                    break # Keluar dari loop dalam untuk reconnect

                # 1. Pre-process
                input_data = preprocess_frame(frame, MODEL_WIDTH, MODEL_HEIGHT)

                # 2. Deteksi
                result = session.run(None, {input_name: input_data})

                # 3. Dapatkan Statistik
                w, h = int(cap.get(3)), int(cap.get(4))
                car_count = run_detection_and_get_stats(result, w, h, MODEL_WIDTH, MODEL_HEIGHT)

                stats = {'car_count': car_count}
                print(f"Kirim data ke Laravel: {stats}")

                try:
                    # 4. Kirim ke Laravel
                    requests.post(LARAVEL_WEBHOOK_URL, json=stats, timeout=2)
                except requests.exceptions.RequestException as e:
                    print(f"Gagal mengirim ke Laravel: {e}")

                # 5. Tunggu 1 detik sebelum memproses frame berikutnya
                # Ini setara dengan 1 FPS, yang cukup untuk statistik
                time.sleep(1)

        except KeyboardInterrupt:
            print("Worker dihentikan.")
            break
        except Exception as e:
            print(f"Error di loop utama: {e}. Restart dalam 10 detik.")
            time.sleep(10)

if __name__ == "__main__":
    start_worker()
