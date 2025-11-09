@extends('layouts.user_type.auth')

@section('content')

    <div class="row">

        <div class="col-lg-7 mb-lg-0 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Analisis Kepadatan Lalu Lintas</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('dashboard.analyze') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="video" class="form-label">Upload Video CCTV (MP4, AVI, MPEG)</label>
                            <input class="form-control" type="file" id="video" name="video" required>
                        </div>

                        @error('video')
                        <div class="alert alert-danger text-white" role="alert">
                            <strong>Error!</strong> {{ $message }}
                        </div>
                        @enderror

                        <button type="submit" class="btn bg-gradient-primary">Analisis Sekarang</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6>Hasil Analisis</h6>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger text-white" role="alert">
                            <strong>Gagal!</strong> {{ session('error') }}
                        </div>
                    @endif

                    @if(session('statistics'))

                        @if(session('processed_video_url'))
                            <div class="mb-3">
                                <h6 class="text-sm mb-1">Video Analysis</h6>
                                <video controls style="width: 100%; border-radius: 0.5rem; max-height: 300px; background-color: #000;">

                                    <source src="{{ session('processed_video_url') }}" type="video/mp4">

                                    Browser Anda tidak mendukung pemutaran video ini.
                                </video>
                            </div>
                        @endif

                        @php
                            $stats = session('statistics');
                        @endphp
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Rata-rata Kepadatan (Kendaraan)
                                <span class="badge bg-gradient-primary rounded-pill">{{ number_format($stats['rata_rata_kepadatan'], 2) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Kepadatan Maksimum (Kendaraan)
                                <span class="badge bg-gradient-danger rounded-pill">{{ number_format($stats['kepadatan_maksimum'], 2) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Frame Dianalisis
                                <span class="badge bg-gradient-secondary rounded-pill">{{ $stats['total_frame'] }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Durasi Kepadatan Tinggi
                                <span class="badge bg-gradient-warning rounded-pill">{{ $stats['durasi_kepadatan_tinggi'] }} detik</span>
                            </li>
                        </ul>
                    @else
                        <p class="text-sm">Upload video untuk melihat statistik dan preview di sini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
