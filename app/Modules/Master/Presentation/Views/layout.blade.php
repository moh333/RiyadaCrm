<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Dashboard - Riyada CRM</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family={{ app()->getLocale() == 'ar' ? 'Cairo:wght@400;600;700' : 'Outfit:wght@300;400;500;600;700' }}&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #6366f1;
            --secondary-color: #4f46e5;
            --bg-light: #f3f4f6;
            --text-dark: #1f2937;
        }

        body {
            font-family: '{{ app()->getLocale() == 'ar' ? 'Cairo' : 'Outfit' }}', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
        }

        [dir="rtl"] .sidebar {
            left: auto;
            right: 0;
            border-right: none;
            border-left: 1px solid #e5e7eb;
        }

        [dir="rtl"] .main-content {
            margin-left: 0;
            margin-right: var(--sidebar-width);
        }

        [dir="rtl"] .nav-link i {
            margin-right: 0;
            margin-left: 0.75rem;
        }

        [dir="rtl"] .avatar-initials {
            margin-right: 0 !important;
            margin-left: 0.5rem !important;
        }

        [dir="rtl"] .ms-auto {
            margin-left: 0 !important;
            margin-right: auto !important;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .nav-link {
            color: #6b7280;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .nav-link i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: #eef2ff;
            color: var(--primary-color);
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background: #ffffff;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .avatar-initials {
            width: 40px;
            height: 40px;
            background-color: #eef2ff;
            color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .table thead th {
            font-weight: 600;
            color: #6b7280;
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        .form-label {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.625rem 0.875rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .card-header {
            border-bottom: 1px solid #f3f4f6;
            background-color: transparent !important;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column p-4">
        <a href="{{ route('master.dashboard') }}" class="d-flex align-items-center mb-4 text-decoration-none text-dark">
            <i class="bi bi-boxes fs-2 text-primary me-2"></i>
            <span class="fs-4 fw-bold">Riyada<span class="text-primary">CRM</span></span>
        </a>

        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('master.dashboard') }}"
                    class="nav-link {{ request()->routeIs('master.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i>
                    {{ __('master::master.dashboard') }}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('master.tenants.index') }}"
                    class="nav-link {{ request()->routeIs('master.tenants.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    {{ __('master::master.tenants') }}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('master.login-history.index') }}"
                    class="nav-link {{ request()->routeIs('master.login-history.index') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i>
                    {{ __('master::master.login_history') }}
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-gear"></i>
                    {{ __('master::master.settings') }}
                </a>
            </li>
        </ul>

        <div class="mb-4">
            <label class="form-label text-muted small text-uppercase fw-bold px-3">Language / اللغة</label>
            <div class="d-flex px-3">
                <a href="{{ route('lang.switch', 'en') }}"
                    class="btn btn-sm {{ app()->getLocale() == 'en' ? 'btn-primary' : 'btn-outline-secondary' }} me-2">EN</a>
                <a href="{{ route('lang.switch', 'ar') }}"
                    class="btn btn-sm {{ app()->getLocale() == 'ar' ? 'btn-primary' : 'btn-outline-secondary' }}">AR</a>
            </div>
        </div>


        <hr>

        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
                id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar-initials me-2">
                    {{ substr(auth('master')->user()->name, 0, 1) }}{{ str_contains(auth('master')->user()->name, ' ') ? substr(explode(' ', auth('master')->user()->name)[1], 0, 1) : '' }}
                </div>
                <strong>{{ auth('master')->user()->name }}</strong>
            </a>
            <ul class="dropdown-menu text-small shadow" aria-labelledby="dropdownUser1">
                <li><a class="dropdown-item"
                        href="{{ route('master.profile.show') }}">{{ __('master::master.profile') }}</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form action="{{ route('master.logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="dropdown-item text-danger">{{ __('master::master.sign_out') }}</button>
                    </form>
                </li>
            </ul>

        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>