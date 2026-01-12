<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
    <div class="container-fluid py-1 px-3 border-bottom" style="border-color: #333 !important; padding-bottom: 15px !important;">

        <nav aria-label="breadcrumb">
            <h4 class="font-dot text-white mb-0" style="letter-spacing: 2px; font-family: 'DotGothic16', sans-serif;">
                BA SMART <span style="color: #D71921;">TRAFFIC</span>
            </h4>
        </nav>

        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            <div class="ms-md-auto pe-md-3 d-flex align-items-center">

                <div class="btn-group me-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-nd {{ Request::is('dashboard') ? 'btn-nd-active' : '' }}">
                        REAL-TIME
                    </a>
                    <a href="{{ route('static.index') }}" class="btn btn-nd {{ Request::is('static-analysis') ? 'btn-nd-active' : '' }}">
                        STATIC ANALYSIS
                    </a>
                </div>

                <div class="d-none d-md-block text-end">
                    <small class="d-block text-muted font-dot" style="font-size: 0.6rem; font-family: 'DotGothic16', sans-serif;">SYSTEM TIME</small>
                    <span class="text-white font-dot fs-5" style="font-family: 'DotGothic16', sans-serif;">{{ date('H:i') }}</span>
                </div>

            </div>

            <ul class="navbar-nav  justify-content-end">
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:" class="nav-link text-body p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
