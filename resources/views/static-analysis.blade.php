@extends('layouts.user_type.auth')

@section('content')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DotGothic16&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <style>
        :root {
            --nd-bg: #050505;
            --nd-card: #000000;
            --nd-border: #333333;
            --nd-border-light: #555555;
            --nd-red: #D71921;
            --nd-white: #E6E6E6;
            --nd-dot-font: 'DotGothic16', sans-serif;
            --nd-mono-font: 'Space Mono', monospace;
        }

        /* GLOBAL */
        body, .g-sidenav-show, .main-content {
            background-color: var(--nd-bg) !important;
            color: var(--nd-white) !important;
            font-family: var(--nd-mono-font);
        }

        .navbar-main { display: none !important; }

        /* TYPOGRAPHY */
        .font-dot { font-family: var(--nd-dot-font) !important; }
        .text-red { color: var(--nd-red) !important; }
        .fs-7 { font-size: 0.75rem; }

        /* BUTTONS */
        .btn-nd {
            background: transparent;
            border: 1px solid var(--nd-border);
            color: var(--nd-border-light);
            font-family: var(--nd-dot-font);
            border-radius: 0;
            padding: 8px 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }
        .btn-nd:hover {
            border-color: var(--nd-white);
            color: var(--nd-white);
        }
        .btn-nd-active {
            background: var(--nd-red);
            color: black;
            border: 1px solid var(--nd-red);
        }
        .btn-nd-active:hover {
            background: #b01016;
            color: black;
        }

        /* CARD STRUCTURE */
        .nd-card {
            background-color: var(--nd-card) !important;
            border: 1px solid var(--nd-border);
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 250px;
        }

        /* CORNERS */
        .corner-tl { position: absolute; top: 0; left: 0; width: 8px; height: 8px; border-top: 2px solid white; border-left: 2px solid white; z-index: 5; }
        .corner-tr { position: absolute; top: 0; right: 0; width: 8px; height: 8px; border-top: 2px solid white; border-right: 2px solid white; z-index: 5; }
        .corner-bl { position: absolute; bottom: 0; left: 0; width: 8px; height: 8px; border-bottom: 2px solid white; border-left: 2px solid white; z-index: 5; }
        .corner-br { position: absolute; bottom: 0; right: 0; width: 8px; height: 8px; border-bottom: 2px solid white; border-right: 2px solid white; z-index: 5; }

        /* COMPONENTS */
        .nd-header {
            padding: 15px 20px;
            border-bottom: 1px dashed var(--nd-border);
            background: rgba(255,255,255,0.02);
            flex-shrink: 0;
        }
        .nd-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .video-container {
            width: 100%;
            aspect-ratio: 16/9;
            border: 1px solid var(--nd-border);
            background: #080808;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-bottom: 15px;
        }
        .video-container video { width: 100%; height: 100%; object-fit: contain; }

        .stat-box-big {
            background: var(--nd-red);
            color: black;
            padding: 20px;
            margin-bottom: 20px;
        }

        .nd-footer {
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px dashed var(--nd-border);
            text-align: center;
            opacity: 0.5;
            font-size: 0.7rem;
            font-family: var(--nd-dot-font);
            letter-spacing: 1px;
        }

        .bg-grid-dots {
            background-image: radial-gradient(#333 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .file-upload-wrapper {
            border: 2px dashed var(--nd-border);
            padding: 40px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        .file-upload-wrapper:hover {
            border-color: var(--nd-red);
            background: rgba(215, 25, 33, 0.05);
        }
        input[type="file"] {
            display: none;
        }
    </style>

    <div class="container-fluid pt-4 pb-4 bg-grid-dots min-vh-100 d-flex flex-column">

        <div class="row align-items-center pb-4 mb-4 border-bottom" style="border-color: #222 !important;">
            <div class="col-md-6">
                <h4 class="font-dot text-white mb-0" style="letter-spacing: 2px;">
                    BANDA ACEH SMART <span class="text-red">TRAFFIC</span>
                </h4>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group">
                    <a href="{{ route('dashboard') }}" class="btn btn-nd">
                        REAL-TIME
                    </a>
                    <a href="{{ route('static.index') }}" class="btn btn-nd btn-nd-active">
                        STATIC ANALYSIS
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-5 align-items-end">
            <div class="col-8">
                <h1 class="display-3 fw-bold font-dot mb-0 text-white" style="line-height: 0.9;">
                    STATIC <span class="text-red">ANALYSIS</span>
                </h1>
                <div class="d-flex align-items-center mt-2">
                    <span class="badge border border-secondary text-secondary rounded-0 font-dot me-3">OFFLINE_MODE</span>
                    <small class="text-muted font-dot fs-7">// UPLOAD VIDEO FOR DETECTION</small>
                </div>
            </div>
            <div class="col-4 text-end">
                <small class="d-block text-muted font-dot fs-7">SYSTEM TIME</small>
                <span id="system-clock" class="font-dot fs-3 text-white">00:00</span>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="nd-card mb-4">
                    <div class="corner-tl"></div><div class="corner-tr"></div><div class="corner-bl"></div><div class="corner-br"></div>
                    <div class="nd-header">
                        <span class="font-dot text-uppercase">STEP 1: INPUT SOURCE</span>
                    </div>
                    <div class="nd-body">
                        <form action="{{ route('static.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <label for="video_file" class="file-upload-wrapper w-100 d-block">
                                        <i class="fas fa-file-upload fa-2x mb-2 text-muted"></i>
                                        <div class="font-dot text-white">CLICK TO SELECT FILE</div>
                                        <div class="fs-7 text-muted mt-1">SUPPORTED: MP4, AVI (MAX 50MB)</div>
                                    </label>
                                    <input type="file" id="video_file" name="video_file" accept="video/*" onchange="document.querySelector('.file-upload-wrapper div').innerText = this.files[0].name" required>
                                </div>
                                <div class="col-md-4 mt-3 mt-md-0">
                                    <button class="btn btn-nd btn-nd-active w-100 py-4" type="submit">
                                        <i class="fas fa-play me-2"></i> INITIATE
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @if(isset($video_path))
                    <div class="nd-card">
                        <div class="corner-tl"></div><div class="corner-tr"></div><div class="corner-bl"></div><div class="corner-br"></div>
                        <div class="nd-header d-flex justify-content-between align-items-center">
                            <span class="font-dot text-uppercase">PLAYBACK: {{ $filename }}</span>
                            <i class="fas fa-film text-red"></i>
                        </div>
                        <div class="nd-body">
                            <div class="video-container">
                                <video id="static-player" controls autoplay>
                                    <source src="{{ asset($video_path) }}" type="video/mp4">
                                    Browser does not support video.
                                </video>
                                <div class="position-absolute top-0 start-0 m-2 px-2 py-1 bg-black text-white font-dot fs-7 border border-dark">ANALYZING</div>
                            </div>
                            <div class="mt-auto d-flex justify-content-between align-items-end">
                                <div>
                                    <small class="text-muted font-dot d-block fs-7">STATUS</small>
                                    <span class="font-dot text-success" id="static-status-text">PROCESSING STREAM...</span>
                                </div>
                                <span class="text-red font-dot fs-7">BUFFERING</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if(isset($video_path))
                <div class="col-lg-4">
                    <div class="nd-card h-100">
                        <div class="corner-tl"></div><div class="corner-tr"></div><div class="corner-bl"></div><div class="corner-br"></div>
                        <div class="nd-header">
                            <span class="font-dot text-uppercase">ANALYSIS RESULTS</span>
                        </div>
                        <div class="nd-body">
                            <div class="stat-box-big">
                                <span class="font-dot fw-bold d-block fs-7 mb-1">OBJECTS DETECTED</span>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h1 class="display-3 font-dot fw-bold mb-0" id="static-car-count">0</h1>
                                    <i class="fas fa-car-side fa-2x opacity-25"></i>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="font-dot text-muted fs-7">TRAFFIC DENSITY</span>
                                    <span class="font-dot text-red fs-7" id="density-percent">0%</span>
                                </div>
                                <div style="height: 6px; background: #222; border: 1px solid #444;">
                                    <div id="density-bar" style="height: 100%; width: 0%; background: var(--nd-red); transition: width 0.5s;"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between py-2 border-bottom border-dark">
                                    <span class="text-muted font-dot fs-7">SOURCE ID</span>
                                    <span class="font-dot fs-7 text-uppercase">{{ substr($filename, 0, 15) }}...</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-bottom border-dark">
                                    <span class="text-muted font-dot fs-7">MODEL</span>
                                    <span class="font-dot text-white fs-7">YOLOv8n.onnx</span>
                                </div>
                            </div>

                            <div class="nd-footer">
                                STATIC PROCESSING MODULE V1.0
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <script>
        // --- 2. Configure Echo Manually ---
        // This takes the values directly from your Blade template
        window.Pusher = Pusher;

        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: "{{ env('REVERB_APP_KEY') }}",
            wsHost: "{{ env('REVERB_HOST', 'localhost') }}",
            wsPort: {{ env('REVERB_PORT', 8080) }},
            wssPort: {{ env('REVERB_PORT', 8080) }},
            forceTLS: false, // Set to false for local development (http)
            enabledTransports: ['ws', 'wss'],
        });

        // --- 3. Clock Function ---
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const clockEl = document.getElementById('system-clock');
            if(clockEl) clockEl.innerText = `${hours}:${minutes}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // --- 4. Traffic Logic ---
        window.addEventListener('load', function() {
            const countEl = document.getElementById('static-car-count');
            const densityBar = document.getElementById('density-bar');
            const videoPlayer = document.getElementById('static-player');
            const currentFilename = "{{ $filename ?? '' }}";

            console.log("--- SYSTEM READY (CDN MODE) ---");
            console.log("Listening for file:", currentFilename);

            // Debug Connection
            window.Echo.connector.pusher.connection.bind('connected', () => {
                console.log("✅ CONNECTED to Reverb Server!");
            });

            // Listen
            window.Echo.channel('traffic-channel')
                .listen('TrafficDataUpdated', (e) => {

                    // console.log("Event:", e); // Uncomment to see raw data

                    // STRICT CHECK: Only update if filename matches
                    // We use weak comparison (==) to handle potential string/int differences
                    if (e.source_type == 'static' && e.source_id == currentFilename) {

                        // 1. Update UI
                        const count = e.statistics.car_count;
                        if(countEl) countEl.innerText = count;

                        // 2. Move Progress Bar
                        let percentage = Math.min((count / 20) * 100, 100);
                        if(densityBar) densityBar.style.width = percentage + '%';

                        // 3. Auto-Play Video (Sync)
                        if (videoPlayer && videoPlayer.paused) {
                            console.log("▶️ Data received - Starting Video");
                            videoPlayer.play();
                        }
                    }
                });
        });
    </script>
@endpush
