@extends('layouts.user_type.auth')

@section('content')

    <div class="row">

        <div class="col-lg-7 mb-lg-0 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Live Stream CCTV</h6>
                </div>
                <div class="card-body p-3">
                    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

                    <video id="cctv-player" controls autoplay muted style="width: 100%; border-radius: 0.5rem; background-color: #000;"></video>

                    <script>
                        var video = document.getElementById('cctv-player');

                        // !! PENTING !!
                        // GANTI DENGAN URL STREAM .m3u8 ANDA
                        var streamUrl = 'https://atcs-dishub.bandung.go.id:1990/PaskalPTZ2/stream.m3u8'; // <-- GANTI INI

                        if (Hls.isSupported()) {
                            var hls = new Hls();
                            hls.loadSource(streamUrl);
                            hls.attachMedia(video);
                            hls.on(Hls.Events.MANIFEST_PARSED, function() {
                                video.play();
                            });
                        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                            // Dukungan HLS asli (misal di Safari)
                            video.src = streamUrl;
                            video.addEventListener('loadedmetadata', function() {
                                video.play();
                            });
                        }
                    </script>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6>Analisis Real-Time</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Kepadatan Saat Ini (Kendaraan)
                            <span id="realtime-car-count" class="badge bg-gradient-primary rounded-pill h4">...</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Status
                            <span id="realtime-status" class="badge bg-gradient-secondary rounded-pill">Menghubungkan...</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection @push('scripts')
    {{-- <script src="{{ asset('js/app.js') }}"></script>  --}}

    <script type="module">
        // Ambil elemen HTML
        const carCountElement = document.getElementById('realtime-car-count');
        const statusElement = document.getElementById('realtime-status');

        // Pastikan variabel VITE_PUSHER... sudah diatur di .env
        // Jika tidak, Anda bisa hardcode di sini
        if (typeof window.Echo === 'undefined') {
            // Jika Echo belum di-setup di app.js, kita setup minimal di sini
            // (Ini BUKAN praktik terbaik, idealnya ada di app.js/bootstrap.js)
            console.error('Laravel Echo belum di-setup. Silakan setup di bootstrap.js');
        } else {
            // Konfigurasi Echo untuk Soketi
            window.Echo.options.host = window.location.hostname + ':6001';

            console.log("Mencoba terhubung ke WebSocket...");
            statusElement.innerText = 'Menghubungkan...';

            window.Echo.channel('traffic-channel')
                .listen('TrafficDataUpdated', (e) => {
                    console.log("Data diterima:", e.statistics);

                    // Update angka di dashboard
                    let count = e.statistics.car_count;
                    carCountElement.innerText = count;

                    // Update status
                    if (count > 10) {
                        statusElement.innerText = 'Padat';
                        statusElement.className = 'badge bg-gradient-danger rounded-pill';
                    } else if (count > 5) {
                        statusElement.innerText = 'Ramai';
                        statusElement.className = 'badge bg-gradient-warning rounded-pill';
                    } else {
                        statusElement.innerText = 'Lancar';
                        statusElement.className = 'badge bg-gradient-success rounded-pill';
                    }
                })
                .error((error) => {
                    console.error("Kesalahan WebSocket:", error);
                    statusElement.innerText = 'Koneksi Gagal';
                    statusElement.className = 'badge bg-gradient-danger rounded-pill';
                });

            // Handle koneksi awal
            window.Echo.connector.socket.on('connect', () => {
                console.log('Berhasil terhubung ke WebSocket.');
                statusElement.innerText = 'Terhubung';
                statusElement.className = 'badge bg-gradient-success rounded-pill';
            });

            window.Echo.connector.socket.on('disconnect', () => {
                console.log('Koneksi WebSocket terputus.');
                statusElement.innerText = 'Terputus';
                statusElement.className = 'badge bg-gradient-danger rounded-pill';
            });
        }
    </script>
@endpush
