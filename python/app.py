from flask import Flask, request, jsonify
import onnxruntime as ort
import numpy as np
import cv2
import os
import traceback
import subprocess

app = Flask(__name__)

# --- PENGATURAN KONFIGURASI ---
MODEL_PATH = 'yolov8n.onnx'
SCORE_THRESHOLD = 0.5
IOU_THRESHOLD = 0.5
FRAMES_TO_SKIP = 5 # Analisis 1 dari setiap 10 frame
FFMPEG_EXE_PATH = r"C:\ffmpeg\bin\ffmpeg.exe" # Path FFMPEG Anda

# --- PENGATURAN MODEL ONNX ---
try:
    session = ort.InferenceSession(MODEL_PATH)
    model_inputs = session.get_inputs()
    model_outputs = session.get_outputs()
    input_name, output_name = model_inputs[0].name, model_outputs[0].name
    input_shape = model_inputs[0].shape
    MODEL_HEIGHT, MODEL_WIDTH = input_shape[2], input_shape[3]
    CLASS_NAMES = ['person', 'bicycle', 'car', 'motorbike', 'aeroplane', 'bus', 'train', 'truck', 'boat', 'traffic light', 'fire hydrant', 'stop sign', 'parking meter', 'bench', 'bird', 'cat', 'dog', 'horse', 'sheep', 'cow', 'elephant', 'bear', 'zebra', 'giraffe', 'backpack', 'umbrella', 'handbag', 'tie', 'suitcase', 'frisbee', 'skis', 'snowboard', 'sports ball', 'kite', 'baseball bat', 'baseball glove', 'skateboard', 'surfboard', 'tennis racket', 'bottle', 'wine glass', 'cup', 'fork', 'knife', 'spoon', 'bowl', 'banana', 'apple', 'sandwich', 'orange', 'broccoli', 'carrot', 'hot dog', 'pizza', 'donut', 'cake', 'chair', 'sofa', 'pottedplant', 'bed', 'diningtable', 'toilet', 'tvmonitor', 'laptop', 'mouse', 'remote', 'keyboard', 'cell phone', 'microwave', 'oven', 'toaster', 'sink', 'refrigerator', 'book', 'clock', 'vase', 'scissors', 'teddy bear', 'hair drier', 'toothbrush']
    COLORS = np.random.uniform(0, 255, size=(len(CLASS_NAMES), 3))
    print(f"Model '{MODEL_PATH}' berhasil dimuat.")
except Exception as e:
    print(f"ERROR: Gagal memuat model ONNX: {e}")
    session = None

# --- FUNGSI PRE-PROCESSING (Tidak berubah) ---
def preprocess_frame(frame, target_width, target_height):
    img = cv2.resize(frame, (target_width, target_height))
    img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    img = img.astype(np.float32) / 255.0
    img = np.transpose(img, (2, 0, 1))
    return np.expand_dims(img, axis=0)

# --- FUNGSI DIPISAH: Bagian 1 - Mendeteksi Kotak ---
def run_detection_and_get_boxes(output_from_model, original_width, original_height, model_width, model_height):
    """
    HANYA memproses output model dan mengembalikan daftar kotak.
    """
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

    final_boxes_to_draw = []
    car_count = 0

    if len(indices) > 0:
        for i in indices.flatten(): # .flatten() untuk mengatasi error jika indices kosong
            box = boxes[i]
            score = scores[i]
            class_id = class_ids[i]

            # Simpan semua info yang dibutuhkan untuk menggambar nanti
            final_boxes_to_draw.append({'box': box, 'score': score, 'class_id': class_id})

            class_name = CLASS_NAMES[class_id]
            if class_name in ['car', 'bus', 'truck', 'motorbike']:
                car_count += 1

    return final_boxes_to_draw, car_count

# --- FUNGSI DIPISAH: Bagian 2 - Menggambar Kotak ---
def draw_boxes_on_frame(frame, boxes_to_draw):
    """
    Menggambar daftar kotak yang sudah diproses ke frame.
    """
    for item in boxes_to_draw:
        box = item['box']
        score = item['score']
        class_id = item['class_id']

        left, top, width, height = box
        color = COLORS[class_id]
        cv2.rectangle(frame, (left, top), (left + width, top + height), color, 2)

        class_name = CLASS_NAMES[class_id]
        label = f"{class_name}: {score:.2f}"
        cv2.putText(frame, label, (left, top - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.7, color, 2)
    return frame

# --- ENDPOINT ANALISIS VIDEO (Logika Loop Diperbarui) ---
@app.route('/analyze-video', methods=['POST'])
def analyze_video():
    if session is None: return jsonify({'error': 'Model tidak dapat dimuat.'}), 500
    if 'video_file' not in request.files: return jsonify({'error': 'Tidak ada file video.'}), 400

    video_file = request.files['video_file']
    temp_input_path = "temp_input.mp4"
    temp_raw_output_path = "temp_raw_output.mp4"
    final_output_path = "final_output.mp4"
    video_file.save(temp_input_path)

    all_densities, frame_count_processed, frame_count_total = [], 0, 0
    cap_input, video_writer = None, None

    # Variabel baru untuk "menahan" deteksi terakhir
    last_known_boxes = []
    last_known_car_count = 0

    try:
        cap_input = cv2.VideoCapture(temp_input_path)
        if not cap_input.isOpened(): return jsonify({'error': 'Gagal membuka video input.'}), 400

        w, h, fps = int(cap_input.get(3)), int(cap_input.get(4)), cap_input.get(5) or 30

        fourcc = cv2.VideoWriter_fourcc(*'mp4v')
        video_writer = cv2.VideoWriter(temp_raw_output_path, fourcc, fps, (w, h))
        if not video_writer.isOpened(): return jsonify({'error': 'Gagal inisialisasi VideoWriter.'}), 500

        while cap_input.isOpened():
            ret, frame = cap_input.read()
            if not ret: break

            frame_count_total += 1

            # Cek apakah ini frame yang harus diproses
            if frame_count_total % FRAMES_TO_SKIP == 0:
                # Ya, proses frame ini
                input_data = preprocess_frame(frame, MODEL_WIDTH, MODEL_HEIGHT)
                result = session.run([output_name], {input_name: input_data})

                # Dapatkan kotak dan hitungan baru, lalu simpan
                last_known_boxes, last_known_car_count = run_detection_and_get_boxes(result, w, h, MODEL_WIDTH, MODEL_HEIGHT)

                frame_count_processed += 1
                print(f"Memproses frame ke-{frame_count_processed} (Total: {frame_count_total}) | Kendaraan: {last_known_car_count}")

            # (Tidak ada 'else')
            # Gambar kotak di SETIAP frame (menggunakan 'last_known_boxes')
            frame_with_boxes = draw_boxes_on_frame(frame.copy(), last_known_boxes)

            # Tulis frame yang sudah digambar ke video
            video_writer.write(frame_with_boxes)

            # Catat statistik kepadatan untuk SETIAP frame (menggunakan 'last_known_car_count')
            all_densities.append(last_known_car_count)

    except Exception as e:
        traceback.print_exc()
        return jsonify({'error': f'Error saat pemrosesan OpenCV: {str(e)}'}), 500
    finally:
        if cap_input: cap_input.release()
        if video_writer: video_writer.release()

    # --- PROSES FFMPEG (Tidak ada perubahan) ---
    try:
        print("Pemrosesan OpenCV selesai. Memulai konversi FFMPEG...")
        ffmpeg_command = [
            FFMPEG_EXE_PATH, '-i', temp_raw_output_path, '-c:v', 'libx264',
            '-preset', 'veryfast', '-crf', '23', '-y', final_output_path
        ]
        subprocess.run(ffmpeg_command, check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        print("Konversi FFMPEG berhasil.")
    except FileNotFoundError:
        print(f"ERROR: FFMPEG_EXE_PATH tidak ditemukan di '{FFMPEG_EXE_PATH}'")
        return jsonify({'error': "FFMPEG tidak ditemukan di server. Periksa path di app.py."}), 500
    except subprocess.CalledProcessError as e:
        print(f"ERROR: FFMPEG gagal dengan error: {e.stderr.decode()}")
        return jsonify({'error': f"FFMPEG gagal mengkonversi video."}), 500
    finally:
        if os.path.exists(temp_input_path): os.remove(temp_input_path)
        if os.path.exists(temp_raw_output_path): os.remove(temp_raw_output_path)

    # --- Perhitungan Statistik (Diperbarui) ---
    if frame_count_total > 0: # Gunakan total frame sebagai basis
        # 'all_densities' sekarang terisi penuh (tidak hanya frame yg diproses)
        avg_density = np.mean(all_densities) if all_densities else 0
        max_density = np.max(all_densities) if all_densities else 0

        # Hitungan durasi sekarang lebih akurat
        high_density_frames = sum(1 for d in all_densities if d > 5)
        duration_high_density_sec = high_density_frames / fps

        stats = {
            'rata_rata_kepadatan': float(avg_density),
            'kepadatan_maksimum': float(max_density),
            'total_frame': frame_count_total,
            'durasi_kepadatan_tinggi': round(duration_high_density_sec, 2)
        }

        return jsonify({'statistics': stats, 'output_video_path': final_output_path})
    else:
        return jsonify({'error': 'Tidak ada frame yang diproses.'}), 400

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
