@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="font-weight-bolder mb-1">Static Video Analysis</h5>
                <p class="text-sm mb-4">Upload a traffic video to perform vehicle detection analysis.</p>

                <form action="{{ route('static.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="form-group mb-0">
                                <input type="file" class="form-control form-control-alternative" name="video_file" accept="video/*" required>
                            </div>
                        </div>
                        <div class="col-md-4 mt-3 mt-md-0">
                            <button class="btn bg-gradient-primary w-100 mb-0" type="submit">
                                <i class="fa fa-upload me-2"></i> Analyze Video
                            </button>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted text-xs">Supported formats: MP4, AVI (Max 50MB)</small>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(isset($video_path))
    <div class="col-lg-8">
        <div class="card overflow-hidden">
            <div class="card-header pb-0">
                <h6 class="font-weight-bolder">Video Preview</h6>
            </div>
            <div class="card-body p-3">
                <div class="border-radius-lg overflow-hidden shadow-sm bg-black">
                    <video id="static-player" class="w-100" controls>
                        <source src="{{ asset($video_path) }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
                <div class="mt-3 p-3 bg-gray-100 border-radius-lg">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-terminal text-xs me-2"></i>
                        <span class="text-xs font-weight-bold">Processing File:</span>
                    </div>
                    <code class="text-xs d-block mt-1 text-primary">{{ $filename }}</code>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card h-100">
            <div class="card-header pb-0">
                <h6 class="font-weight-bolder">Detection Results</h6>
            </div>
            <div class="card-body p-3">
                <div class="text-center py-4">
                    <h1 class="display-4 font-weight-bolder text-primary mb-0" id="static-car-count">0</h1>
                    <p class="text-sm font-weight-bold text-secondary">Vehicles Detected</p>
                </div>

                <hr class="horizontal dark my-3">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-sm">Analysis Status</span>
                    <span id="static-status" class="badge badge-sm bg-gradient-secondary">Waiting...</span>
                </div>

                <div class="alert alert-light border-0 text-xs mb-0">
                    <i class="fa fa-info-circle me-1"></i>
                    Results are updated in real-time as the Python worker processes the video.
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    window.addEventListener('load', function() {
        const countEl = document.getElementById('static-car-count');
        const statusEl = document.getElementById('static-status');

        if(typeof window.Echo !== 'undefined' && countEl) {
            window.Echo.channel('traffic-channel')
                .listen('TrafficDataUpdated', (e) => {
                    const count = e.statistics.car_count;
                    countEl.innerText = count;

                    statusEl.innerText = count > 10 ? 'Heavy Traffic' : 'Normal';
                    statusEl.className = `badge badge-sm bg-gradient-${count > 10 ? 'danger' : 'success'}`;
                });
        }
    });
</script>
@endpush
@endsection
