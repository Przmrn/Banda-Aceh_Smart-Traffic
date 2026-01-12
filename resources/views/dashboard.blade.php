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
            --fixed-height: 520px;
        }

        /* GLOBAL */
        body, .g-sidenav-show, .main-content {
            background-color: var(--nd-bg) !important;
            color: var(--nd-white) !important;
            font-family: var(--nd-mono-font);
        }

        /* Sembunyikan Navbar Bawaan Template (Kita buat sendiri di bawah) */
        .navbar-main { display: none !important; }

        /* TYPOGRAPHY */
        .font-dot { font-family: var(--nd-dot-font) !important; }
        .text-red { color: var(--nd-red) !important; }
        .fs-7 { font-size: 0.75rem; }

        /* TOMBOL NAVIGASI CUSTOM */
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

        /* CARDS STRUCTURE */
        .nd-card {
            height: var(--fixed-height) !important;
            background-color: var(--nd-card) !important;
            border: 1px solid var(--nd-border);
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* DEKORASI SUDUT */
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
            height: 65%;
            border: 1px solid var(--nd-border);
            background: #080808;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-bottom: 15px;
        }
        .video-container video { width: 100%; height: 100%; object-fit: cover; }
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
                    <a href="{{ route('dashboard') }}" class="btn btn-nd btn-nd-active">
                        REAL-TIME
                    </a>
                    <a href="{{ route('static.index') }}" class="btn btn-nd">
                        STATIC ANALYSIS
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-5 align-items-end">
            <div class="col-8">
                <h1 class="display-3 fw-bold font-dot mb-0 text-white" style="line-height: 0.9;">
                    LIVE <span class="text-red">MONITORING</span>
                </h1>
                <div class="d-flex align-items-center mt-2">
                    <span class="badge border border-secondary text-secondary rounded-0 font-dot me-3">NETWORK_ONLINE</span>
                    <small class="text-muted font-dot fs-7">// REAL-TIME DATA FEED</small>
                </div>
            </div>
            <div class="col-4 text-end">
                <small class="d-block text-muted font-dot fs-7">SYSTEM TIME</small>
                <span id="system-clock" class="font-dot fs-3 text-white">00:00</span>
            </div>
        </div>

        <div class="row g-4">

            <div class="col-lg-8">
                <div class="row g-4">

                    <div class="col-md-6">
                        <div class="nd-card">
                            <div class="corner-tl"></div><div class="corner-tr"></div><div class="corner-bl"></div><div class="corner-br"></div>
                            <div class="nd-header d-flex justify-content-between align-items-center">
                                <span class="font-dot text-uppercase">CAM_01: ULEE LHEU</span>
                                <i class="fas fa-video text-red"></i>
                            </div>
                            <div class="nd-body">
                                <div class="video-container">
                                    <video id="video-ulee-lheu" muted autoplay loop playsinline>
                                        <source src="https://cctv-stream.bandaacehkota.info/memfs/dcba24ce-e625-4e77-8d8b-e0529725cc13_output_0.m3u8?session=SGZ9EMztkjveUYiyDdWZub" type="application/x-mpegURL">
                                    </video>
                                    <div class="position-absolute top-0 start-0 m-2 px-2 py-1 bg-black text-white font-dot fs-7 border border-dark">LIVE</div>
                                </div>
                                <div class="mt-auto d-flex justify-content-between align-items-end">
                                    <div>
                                        <small class="text-muted font-dot d-block fs-7">DETECTED</small>
                                        <span class="font-dot fs-2" id="count-ulee-lheu">0</span>
                                    </div>
                                    <span class="text-red font-dot fs-7">RECORDING</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="nd-card">
                            <div class="corner-tl"></div><div class="corner-tr"></div><div class="corner-bl"></div><div class="corner-br"></div>
                            <div class="nd-header d-flex justify-content-between align-items-center">
                                <span class="font-dot text-uppercase">CAM_02: SIMPANG DHARMA</span>
                                <i class="fas fa-video-slash text-muted"></i>
                            </div>
                            <div class="nd-body">
                                <div class="video-container">
                                    <div class="text-center">
                                        <h5 class="font-dot text-muted mb-0">NO SIGNAL</h5>
                                        <small class="text-muted font-dot fs-7">CHECK_CABLE_02</small>
                                    </div>
                                </div>
                                <div class="mt-auto d-flex justify-content-between align-items-end">
                                    <div>
                                        <small class="text-muted font-dot d-block fs-7">DETECTED</small>
                                        <span class="font-dot fs-2" id="count-dharma">0</span>
                                    </div>
                                    <span class="text-muted font-dot fs-7">OFFLINE</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="nd-card">
                    <div class="corner-tl"></div><div class="corner-tr"></div><div class="corner-bl"></div><div class="corner-br"></div>
                    <div class="nd-header">
                        <span class="font-dot text-uppercase">REAL-TIME DATA</span>
                    </div>
                    <div class="nd-body">
                        <div class="stat-box-big">
                            <span class="font-dot fw-bold d-block fs-7 mb-1">TOTAL TRAFFIC</span>
                            <div class="d-flex align-items-center justify-content-between">
                                <h1 class="display-3 font-dot fw-bold mb-0">128</h1>
                                <i class="fas fa-car fa-2x opacity-25"></i>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="font-dot text-muted fs-7">CONGESTION</span>
                                <span class="font-dot text-red fs-7">78%</span>
                            </div>
                            <div style="height: 6px; background: #222; border: 1px solid #444;">
                                <div style="height: 100%; width: 78%; background: var(--nd-red);"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between py-2 border-bottom border-dark">
                                <span class="text-muted font-dot fs-7">ACTIVE CAMS</span>
                                <span class="font-dot fs-7">1 / 4</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom border-dark">
                                <span class="text-muted font-dot fs-7">LATENCY</span>
                                <span class="font-dot text-success fs-7">24ms</span>
                            </div>
                        </div>
                        <div class="nd-footer">
                            POWERED BY YOLOv8 & LARAVEL REVERB
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        window.addEventListener('load', function() {
            const countUleeLheu = document.getElementById('count-ulee-lheu');
            const countDharma = document.getElementById('count-dharma');

            if (typeof window.Echo !== 'undefined') {
                window.Echo.channel('traffic-channel')
                    .listen('TrafficDataUpdated', (e) => {
                        if (e.statistics.camera_id === 'simpang_surabaya' || e.statistics.camera_id === 'ulee_lheu') {
                            if(countUleeLheu) countUleeLheu.innerText = e.statistics.car_count;
                        }
                    });
            }
        });
    </script>

    <script>
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const clockEl = document.getElementById('system-clock');
            if(clockEl) {
                clockEl.innerText = `${hours}:${minutes}`;
            }
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
@endpush
