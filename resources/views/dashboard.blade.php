@extends('layouts.app')

@section('content')
<div class="row">
    <!-- Left Column: CCTV Streams -->
    <div class="col-xl-9 col-lg-8">
        <div class="card overflow-hidden">
            <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">LIVE MONITORING</h3>
                    <p class="text-xs text-secondary font-weight-bold mb-0">BANDA ACEH TRAFFIC NETWORK</p>
                </div>
                <div class="d-flex align-items-center">
                    <span id="realtime-status" class="badge bg-light text-dark border me-3">CONNECTING...</span>
                    <div class="spinner-grow spinner-grow-sm text-primary" role="status" id="status-spinner"></div>
                </div>
            </div>
            <div class="card-body p-4">
                <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
                <div class="row g-4">
                    <!-- Stream 1 -->
                    <div class="col-xl-6">
                        <div class="position-relative overflow-hidden border-radius-2xl shadow-lg bg-dark group">
                            <div class="position-absolute top-0 start-0 w-100 p-4 d-flex justify-content-between align-items-start z-index-2">
                                <h5 class="text-white mb-0">ULEE LHEU</h5>
                                <span class="badge bg-white text-dark shadow-sm" id="car-count-1">0 VEHICLES</span>
                            </div>
                            <div style="padding-top: 56.25%;">
                                <video id="cctv-player-1" class="position-absolute top-0 start-0 w-100 h-100" autoplay muted playsinline style="object-fit: cover;"></video>
                            </div>
                        </div>
                    </div>
                    <!-- Stream 2 -->
                    <div class="col-xl-6">
                        <div class="position-relative overflow-hidden border-radius-2xl shadow-lg bg-dark">
                            <div class="position-absolute top-0 start-0 w-100 p-4 d-flex justify-content-between align-items-start z-index-2">
                                <h5 class="text-white mb-0">SIMPANG DHARMA</h5>
                                <span class="badge bg-white text-dark shadow-sm" id="car-count-2">0 VEHICLES</span>
                            </div>
                            <div style="padding-top: 56.25%;">
                                <video id="cctv-player-2" class="position-absolute top-0 start-0 w-100 h-100" autoplay muted playsinline style="object-fit: cover;"></video>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Stats -->
    <div class="col-xl-3 col-lg-4">
        <div class="card h-100">
            <div class="card-header pb-0 border-0">
                <h5 class="mb-0">ANALYTICS</h5>
            </div>
            <div class="card-body p-4">
                <div class="p-4 border-radius-xl bg-dark mb-4 shadow-dark">
                    <span class="text-white text-xxs font-weight-bold opacity-6 d-block mb-1">TOTAL VEHICLES</span>
                    <h1 class="text-white mb-0 display-4" id="total-vehicles">0</h1>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-xs font-weight-bold">ULEE LHEU</span>
                        <span class="text-xs font-weight-bold" id="car-count-1-stats">0</span>
                    </div>
                    <div class="progress progress-md mb-1" style="height: 8px;">
                        <div id="density-meter-1" class="progress-bar bg-gradient-primary" role="progressbar" style="width: 0%"></div>
                    </div>
                    <span class="text-xxs font-weight-bold" id="density-label-1">STATUS: NORMAL</span>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-xs font-weight-bold">SIMPANG DHARMA</span>
                        <span class="text-xs font-weight-bold" id="car-count-2-stats">0</span>
                    </div>
                    <div class="progress progress-md mb-1" style="height: 8px;">
                        <div id="density-meter-2" class="progress-bar bg-gradient-primary" role="progressbar" style="width: 0%"></div>
                    </div>
                    <span class="text-xxs font-weight-bold" id="density-label-2">STATUS: NORMAL</span>
                </div>

                <div class="mt-5 pt-4 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-xxs font-weight-bold text-secondary">SYSTEM HEALTH</span>
                        <span class="text-xxs font-weight-bold text-success">OPTIMAL</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-xxs font-weight-bold text-secondary">LAST SYNC</span>
                        <span class="text-xxs font-weight-bold" id="last-update">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const streams = [
            { id: 'cctv-player-1', url: 'https://cctv-stream.bandaacehkota.info/memfs/097d5dcf-eda2-4b29-9661-fcc035d7770f_output_0.m3u8?session=YfkSxaizRZbFzmGAk4arpy' },
            { id: 'cctv-player-2', url: 'https://cctv-stream.bandaacehkota.info/memfs/7eec00d1-9025-4c1c-9007-7a742cac3dd8_output_0.m3u8?session=RpRPECagkXWFiSPtU2WPWz' }
        ];

        streams.forEach(stream => {
            const video = document.getElementById(stream.id);
            if (Hls.isSupported()) {
                const hls = new Hls();
                hls.loadSource(stream.url);
                hls.attachMedia(video);
            }
        });

        if (typeof window.Echo !== 'undefined') {
            const statusEl = document.getElementById('realtime-status');
            const spinner = document.getElementById('status-spinner');

            window.Echo.connector.pusher.connection.bind('connected', () => {
                statusEl.textContent = 'LIVE';
                statusEl.className = 'badge bg-success text-white border-0 me-3';
                spinner.className = 'spinner-grow spinner-grow-sm text-success';
            });

            window.Echo.channel('traffic-channel').listen('TrafficDataUpdated', (data) => {
                const actualData = data.statistics || data;
                document.getElementById('total-vehicles').textContent = actualData.total_vehicles || 0;

                if (actualData.streams) {
                    actualData.streams.forEach(s => {
                        const num = s.id === 'stream-1' ? '1' : (s.id === 'stream-2' ? '2' : null);
                        if (num) {
                            const count = s.car_count || 0;
                            document.getElementById(`car-count-${num}`).textContent = `${count} VEHICLES`;
                            document.getElementById(`car-count-${num}-stats`).textContent = count;

                            const progress = document.getElementById(`density-meter-1`);
                            const label = document.getElementById(`density-label-1`);
                            const percent = Math.min(count * 5, 100);

                            progress.style.width = percent + '%';
                            if (count > 15) {
                                progress.className = 'progress-bar bg-danger';
                                label.textContent = 'STATUS: HEAVY';
                                label.className = 'text-xxs text-danger font-weight-bold';
                            } else if (count > 8) {
                                progress.className = 'progress-bar bg-warning';
                                label.textContent = 'STATUS: MODERATE';
                                label.className = 'text-xxs text-warning font-weight-bold';
                            } else {
                                progress.className = 'progress-bar bg-primary';
                                label.textContent = 'STATUS: NORMAL';
                                label.className = 'text-xxs text-success font-weight-bold';
                            }
                        }
                    });
                }
                document.getElementById('last-update').textContent = new Date().toLocaleTimeString();
            });
        }
    });
</script>
@endpush
@endsection
