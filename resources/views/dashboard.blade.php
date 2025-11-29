@extends('layouts.user_type.auth')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6>Live Stream CCTV</h6>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="container-fluid">
                            <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
                            <div class="row">
                                <!-- Stream 1 -->
                                <div class="col-xl-6 col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header p-3 pt-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Ulee Lheu</h6>
                                                <span class="badge bg-gradient-primary" id="car-count-1">0 kendaraan</span>
                                            </div>
                                        </div>
                                        <div class="card-body p-0">
                                            <div style="position: relative; padding-top: 56.25%; background: #000; border-radius: 0 0 0.5rem 0.5rem; overflow: hidden;">
                                                <video id="cctv-player-1" class="w-100 h-100" style="position: absolute; top: 0; left: 0;" controls autoplay muted playsinline></video>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stream 2 -->
                                <div class="col-xl-6 col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header p-3 pt-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Simpang Dharma</h6>
                                                <span class="badge bg-gradient-primary" id="car-count-2">0 kendaraan</span>
                                            </div>
                                        </div>
                                        <div class="card-body p-0">
                                            <div style="position: relative; padding-top: 56.25%; background: #000; border-radius: 0 0 0.5rem 0.5rem; overflow: hidden;">
                                                <video id="cctv-player-2" class="w-100 h-100" style="position: absolute; top: 0; left: 0;" controls autoplay muted playsinline></video>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6>Analisis Real-Time</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card mb-3">
                                    <div class="card-header p-3 pt-2">
                                        <h6 class="mb-0">Statistik Lalu Lintas</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="timeline timeline-one-side">
                                            <div class="timeline-block mb-3">
                                            <span class="timeline-step">
                                                <i class="ni ni-pin-3 text-primary text-gradient"></i>
                                            </span>
                                                <div class="timeline-content">
                                                    <h6 class="text-dark text-sm font-weight-bold mb-0">Ulee Lheu</h6>
                                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                                        <span>Kendaraan Terdeteksi:</span>
                                                        <span class="badge bg-gradient-primary" id="car-count-1-stats">0</span>
                                                    </div>
                                                    <div class="progress mt-2">
                                                        <div id="density-meter-1" class="progress-bar bg-gradient-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="timeline-block">
                                            <span class="timeline-step">
                                                <i class="ni ni-pin-3 text-success text-gradient"></i>
                                            </span>
                                                <div class="timeline-content">
                                                    <h6 class="text-dark text-sm font-weight-bold mb-0">Simpang Dharma</h6>
                                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                                        <span>Kendaraan Terdeteksi:</span>
                                                        <span class="badge bg-gradient-success" id="car-count-2-stats">0</span>
                                                    </div>
                                                    <div class="progress mt-2">
                                                        <div id="density-meter-2" class="progress-bar bg-gradient-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header p-3 pt-2">
                                        <h6 class="mb-0">Status Sistem</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 ps-0 pt-0">
                                                <span>Status Koneksi</span>
                                                <span id="realtime-status" class="badge bg-gradient-secondary">Menghubungkan...</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 ps-0">
                                                <span>Total Kendaraan</span>
                                                <span id="total-vehicles" class="badge bg-gradient-primary">0</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 ps-0">
                                                <span>Status Analisis</span>
                                                <span id="analysis-status" class="badge bg-gradient-success">Aktif</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 ps-0 pb-0">
                                                <span>Pembaruan Terakhir</span>
                                                <span id="last-update" class="text-xs text-secondary">-</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Stream configurations
                const streams = [
                    {
                        id: 'cctv-player-1',
                        url: 'https://cctv-stream.bandaacehkota.info/memfs/097d5dcf-eda2-4b29-9661-fcc035d7770f_output_0.m3u8?session=YfkSxaizRZbFzmGAk4arpy',
                        location: 'Ulee Lheu 1',
                        carCount: 0,
                        element: null,
                        hls: null
                    },
                    {
                        id: 'cctv-player-2',
                        url: 'https://cctv-stream.bandaacehkota.info/memfs/7eec00d1-9025-4c1c-9007-7a742cac3dd8_output_0.m3u8?session=RpRPECagkXWFiSPtU2WPWz',
                        location: 'Ulee Lheu 2',
                        carCount: 0,
                        element: null,
                        hls: null
                    }
                ];

                // Initialize video players
                function initializeVideoPlayers() {
                    streams.forEach(stream => {
                        const video = document.getElementById(stream.id);
                        if (!video) return;

                        stream.element = video;

                        if (Hls.isSupported()) {
                            const hls = new Hls({
                                enableWorker: true,
                                lowLatencyMode: true,
                                backBufferLength: 30
                            });

                            hls.loadSource(stream.url);
                            hls.attachMedia(video);
                            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                                video.play().catch(e => {
                                    console.error(`Error playing ${stream.location}:`, e);
                                });
                            });

                            hls.on(Hls.Events.ERROR, (event, data) => {
                                console.error(`HLS Error for ${stream.location}:`, data);
                                if (data.fatal) {
                                    switch(data.type) {
                                        case Hls.ErrorTypes.NETWORK_ERROR:
                                            console.log('Fatal network error, trying to recover...');
                                            hls.startLoad();
                                            break;
                                        case Hls.ErrorTypes.MEDIA_ERROR:
                                            console.log('Fatal media error, recovering...');
                                            hls.recoverMediaError();
                                            break;
                                        default:
                                            console.log('Unrecoverable error, destroying...');
                                            hls.destroy();
                                            break;
                                    }
                                }
                            });

                            stream.hls = hls;
                        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                            // For Safari
                            video.src = stream.url;
                            video.addEventListener('loadedmetadata', () => {
                                video.play().catch(e => {
                                    console.error(`Error playing ${stream.location} on Safari:`, e);
                                });
                            });
                        }
                    });
                }

                // Initialize WebSocket connection
                function initializeWebSocket() {
                    if (typeof window.Echo === 'undefined') {
                        console.error('Laravel Echo is not available');
                        updateStatus('Echo Gagal Dimuat', 'danger');
                        return;
                    }

                    const statusElement = document.getElementById('realtime-status');

                    // Pusher connection events
                    const pusher = window.Echo.connector.pusher;

                    pusher.connection.bind('connected', () => {
                        console.log('Connected to WebSocket');
                        updateStatus('Terhubung', 'success');

                        // Subscribe to the traffic channel
                        window.Echo.channel('traffic-channel')
                            .listen('TrafficDataUpdated', (data) => {
                                console.log('Received update:', data);
                                updateUI(data);
                            });
                    });

                    pusher.connection.bind('disconnected', () => {
                        console.log('Disconnected from WebSocket');
                        updateStatus('Terputus', 'danger');
                    });

                    pusher.connection.bind('failed', () => {
                        console.error('Failed to connect to WebSocket');
                        updateStatus('Koneksi Gagal', 'danger');
                    });
                }

                // Update UI with new data
                function updateUI(data) {
                    console.log('updateUI FULL DATA:', JSON.stringify(data, null, 2));

                    // CRITICAL FIX: Unwrap the 'statistics' wrapper if it exists
                    const actualData = data.statistics || data;

                    const totalVehicles = actualData.total_vehicles || actualData.totalVehicles || 0;
                    const streams = actualData.streams || [];

                    console.log('Extracted - totalVehicles:', totalVehicles, 'streams:', streams);

                    // Update total vehicles
                    const totalElement = document.getElementById('total-vehicles');
                    if (totalElement) {
                        totalElement.textContent = totalVehicles;
                        console.log('Updated total-vehicles to:', totalVehicles);
                    }

                    // Update individual streams
                    if (Array.isArray(streams) && streams.length > 0) {
                        console.log('Processing', streams.length, 'streams');

                        streams.forEach((streamData, index) => {
                            console.log(`Stream ${index}:`, JSON.stringify(streamData));

                            let elementNumber = null;
                            if (streamData.id === 'stream-1') {
                                elementNumber = '1';
                            } else if (streamData.id === 'stream-2') {
                                elementNumber = '2';
                            }

                            console.log(`Stream ID: ${streamData.id} -> Element: ${elementNumber}`);

                            if (elementNumber) {
                                const carCount = streamData.car_count || streamData.carCount || 0;
                                console.log(`Car count for stream ${elementNumber}:`, carCount);

                                // Update car count badge (shows "X kendaraan")
                                const countElement = document.getElementById(`car-count-${elementNumber}`);
                                if (countElement) {
                                    countElement.textContent = `${carCount} kendaraan`;
                                    console.log(`✓ Updated car-count-${elementNumber} to "${carCount} kendaraan"`);
                                } else {
                                    console.error(`✗ Element car-count-${elementNumber} NOT FOUND`);
                                }

                                // Update statistics count
                                const statsElement = document.getElementById(`car-count-${elementNumber}-stats`);
                                if (statsElement) {
                                    statsElement.textContent = carCount;
                                    console.log(`✓ Updated car-count-${elementNumber}-stats to ${carCount}`);
                                } else {
                                    console.error(`✗ Element car-count-${elementNumber}-stats NOT FOUND`);
                                }

                                // Update progress bar
                                const progressElement = document.getElementById(`density-meter-${elementNumber}`);
                                if (progressElement) {
                                    const percentage = Math.min(carCount * 5, 100);
                                    progressElement.style.width = `${percentage}%`;
                                    progressElement.setAttribute('aria-valuenow', percentage);

                                    if (carCount > 15) {
                                        progressElement.className = 'progress-bar bg-gradient-danger';
                                    } else if (carCount > 8) {
                                        progressElement.className = 'progress-bar bg-gradient-warning';
                                    } else {
                                        progressElement.className = 'progress-bar bg-gradient-success';
                                    }
                                    console.log(`✓ Updated progress bar ${elementNumber} to ${percentage}%`);
                                }
                            }
                        });
                    } else {
                        console.error('❌ No streams array found. Data:', actualData);
                    }

                    // Update last update time
                    const now = new Date();
                    const lastUpdateElement = document.getElementById('last-update');
                    if (lastUpdateElement) {
                        lastUpdateElement.textContent = now.toLocaleTimeString();
                    }
                }

                // Update connection status
                function updateStatus(message, type) {
                    const statusElement = document.getElementById('realtime-status');
                    if (statusElement) {
                        statusElement.textContent = message;
                        statusElement.className = `badge bg-gradient-${type}`;
                    }
                }

                // Initialize everything when the page loads
                initializeVideoPlayers();
                initializeWebSocket();
            });
        </script>
    @endpush
@endsection
