# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview
This is a **Smart Traffic Analysis System** built with a **Laravel 11** backend and a **Python (Flask + YOLOv8)** service for computer vision. The frontend uses the Soft UI Dashboard (Bootstrap 5).

### Key Components
1.  **Laravel App**: Handles user authentication, dashboard UI, and orchestrates the video analysis process.
2.  **Python Service (`python/`)**: A Flask API that runs YOLOv8 inference on video files to detect vehicles and calculate traffic density.
3.  **Frontend**: Bootstrap 5 with Laravel Mix/Vite.

## Common Commands

### Setup & Installation
```bash
# 1. Backend Setup
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link  # Critical for serving processed videos

# 2. Frontend Setup
npm install
npm run dev

# 3. Python Service Setup
cd python
python -m venv venv
# Windows
.\venv\Scripts\activate
# Linux/Mac
# source venv/bin/activate
pip install -r requirements.txt
```

### Running the Application
You need to run three processes simultaneously:

1.  **Laravel Server**:
    ```bash
    php artisan serve
    ```

2.  **Python Analysis Service**:
    ```bash
    cd python
    # Ensure venv is activated
    python app.py
    ```
    *Note: The service runs on `http://127.0.0.1:5000` by default.*

3.  **Frontend Watcher** (Optional, for UI dev):
    ```bash
    npm run watch
    ```

### Testing & Quality
- **PHP Tests**:
  ```bash
  php artisan test
  ```

## Architecture & Data Flow

### Video Analysis Workflow
1.  **Upload**: User uploads a video via the Laravel Dashboard (`TrafficController@showDashboard`).
2.  **Hand-off**: `TrafficController` saves the video temporarily and POSTs it to the Python service (`http://127.0.0.1:5000/analyze-video`).
3.  **Processing (`python/app.py`)**:
    - The Python script loads the `yolov8n.onnx` model.
    - Processes the video frame-by-frame (skipping frames for performance).
    - Detects vehicles and draws bounding boxes using OpenCV.
    - **FFmpeg Dependency**: Converts the raw output using FFmpeg (hardcoded path: `C:\ffmpeg\bin\ffmpeg.exe` - *verify this path exists or update `app.py`*).
4.  **Result Handling**:
    - Python returns JSON stats and the filename of the processed video.
    - Laravel moves the processed video from the `python/` directory to `storage/app/public/analyzed_videos`.
    - **Important**: The Laravel app assumes it has direct filesystem access to the `python/` directory to retrieve the output file.

### Configuration Notes
- **FFmpeg**: The Python service requires FFmpeg. The path is defined in `python/app.py` (`FFMPEG_EXE_PATH`).
- **Model**: The YOLOv8 model is located at `python/yolov8n.onnx`.
- **Service URL**: The Python API URL is currently hardcoded in `App\Http\Controllers\TrafficController.php` as `http://127.0.0.1:5000/analyze-video`.

### Directory Structure
- `app/`: Core Laravel code (Controllers, Models).
- `python/`: Python Flask service, YOLO model, and scripts.
- `resources/`: Frontend assets (Blade views, SCSS, JS).
- `storage/`: Stores uploads and processed videos.
