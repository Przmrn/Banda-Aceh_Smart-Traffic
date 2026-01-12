<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <title>Banda Aceh Smart Traffic</title>

    <!-- Modern Tech Fonts: Space Grotesk & Syne -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">

    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link id="pagestyle" href="{{ asset('assets/css/soft-ui-dashboard.css?v=1.0.3') }}" rel="stylesheet" />

    <style>
        :root {
            --primary-gradient: linear-gradient(310deg, #21d4fd 0%, #2152ff 100%);
            --dark-bg: #0f172a;
        }
        body {
            font-family: 'Space Grotesk', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            font-size: 1rem;
            letter-spacing: -0.01em;
        }

        /* Monument-like Heading Style */
        h1, h2, h3, h4, h5, h6, .navbar-brand, .nav-link {
            font-family: 'Syne', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 800;
        }

        .navbar-main {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            background-color: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: saturate(200%) blur(20px);
            padding: 1.2rem 0;
        }
        .navbar-brand {
            font-size: 1.4rem !important;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card {
            border: none;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04);
            border-radius: 1.25rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }
        .nav-link {
            font-size: 0.85rem !important;
            color: #475569 !important;
            padding: 0.6rem 1.2rem !important;
            border-radius: 0.5rem;
            margin: 0 0.5rem;
            transition: all 0.2s ease;
        }
        .nav-link.active {
            color: #fff !important;
            background: #1e293b;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .main-content {
            padding-top: 50px;
            min-height: calc(100vh - 120px);
        }
        .container-fluid {
            padding-left: 4rem !important;
            padding-right: 4rem !important;
        }
        .badge {
            font-family: 'Space Grotesk', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            padding: 0.5em 1em;
        }
        .bg-gradient-primary {
            background-image: var(--primary-gradient);
        }
        @media (max-width: 1400px) {
            .container-fluid {
                padding-left: 2rem !important;
                padding-right: 2rem !important;
            }
        }
    </style>
</head>

<body class="g-sidenav-show">
    <nav class="navbar navbar-expand-lg sticky-top navbar-main">
        <div class="container-fluid">
            <a class="navbar-brand ms-lg-0" href="{{ route('dashboard') }}">
                BA SMART TRAFFIC
            </a>
            <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon mt-2">
                    <span class="navbar-toggler-bar bar1"></span>
                    <span class="navbar-toggler-bar bar2"></span>
                    <span class="navbar-toggler-bar bar3"></span>
                </span>
            </button>
            <div class="collapse navbar-collapse" id="navigation">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            Real-time
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('static-analysis') ? 'active' : '' }}" href="{{ route('static.index') }}">
                            Static Analysis
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="container-fluid">
            @yield('content')
        </div>
    </main>

    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/soft-ui-dashboard.min.js?v=1.0.3') }}"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
