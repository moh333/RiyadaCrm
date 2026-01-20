<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('tenant::tenant.dashboard') }} - Riyada CRM</title>
    <link rel="icon" type="image/svg+xml" href="{{ global_asset('favicon.svg') }}">

    <!-- Bootstrap 5 CSS -->
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @endif

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    @if(app()->getLocale() == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
            rel="stylesheet">
    @endif

    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #6366f1;
            --primary-light: #eef2ff;
            --bg-light: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: '{{ app()->getLocale() == 'ar' ? 'Cairo' : 'Plus Jakarta Sans' }}', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}
            : 0;
            top: 0;
            background: white;
            border-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 1px solid #e2e8f0;
            z-index: 1000;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .logo-box {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 0.75rem;
        }

        .nav-list {
            list-style: none;
            padding: 1.5rem 1rem;
            margin: 0;
            flex-grow: 1;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .nav-link i {
            font-size: 1.25rem;
            margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 0.75rem;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }

        /* Top Bar */
        .topbar {
            height: 70px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .search-box {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            width: 300px;
        }

        .search-box input {
            background: transparent;
            border: none;
            outline: none;
            margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 0.5rem;
            width: 100%;
            font-size: 0.875rem;
        }

        /* Cards & Components */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
        }

        .stats-card {
            padding: 1.5rem;
            display: flex;
            align-items: center;
        }

        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 1rem;
        }

        .bg-soft-primary {
            background: #eef2ff;
            color: #6366f1;
        }

        .bg-soft-success {
            background: #f0fdf4;
            color: #22c55e;
        }

        .bg-soft-warning {
            background: #fffbeb;
            color: #f59e0b;
        }

        .bg-soft-danger {
            background: #fef2f2;
            color: #ef4444;
        }

        .user-profile {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 0.75rem;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX({{ app()->getLocale() == 'ar' ? '100%' : '-100%' }});
            }

            .main-content {
                margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 0;
            }

            .sidebar.active {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar d-flex flex-column">
        <div class="sidebar-header">
            <div class="logo-box">
                <i class="bi bi-rocket-takeoff-fill"></i>
            </div>
            <span class="fs-5 fw-bold">Tenan<span class="text-primary">tHub</span></span>
        </div>

        <ul class="nav-list">
            <li class="nav-item">
                <a href="{{ route('tenant.dashboard') }}"
                    class="nav-link {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    {{ __('tenant::tenant.dashboard') }}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('tenant.contacts.index') }}"
                    class="nav-link {{ request()->routeIs('tenant.contacts.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    {{ __('contacts::contacts.contacts') }}
                </a>
            </li>


            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-briefcase-fill"></i>
                    {{ __('tenant::tenant.opportunities') }}
                </a>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    {{ __('tenant::tenant.reports') }}
                </a>
            </li>
            <li class="nav-item mt-4">
                <small class="text-muted fw-bold text-uppercase px-3">{{ __('tenant::tenant.administration') }}</small>
            </li>
            <li class="nav-item">
                <a href="{{ route('tenant.settings.modules.index') }}"
                    class="nav-link {{ request()->routeIs('tenant.settings.modules.*') ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    {{ __('tenant::tenant.module_management') }}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('tenant.settings') }}"
                    class="nav-link {{ request()->routeIs('tenant.settings') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i>
                    {{ __('tenant::tenant.settings') }}
                </a>
            </li>

            <!-- Language Switcher in Sidebar for mobile or easy access -->
            <li class="nav-item mt-auto border-top pt-3">
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('lang.switch', 'en') }}"
                        class="btn btn-sm {{ app()->getLocale() == 'en' ? 'btn-primary' : 'btn-outline-secondary' }}">EN</a>
                    <a href="{{ route('lang.switch', 'ar') }}"
                        class="btn btn-sm {{ app()->getLocale() == 'ar' ? 'btn-primary' : 'btn-outline-secondary' }}">AR</a>
                </div>
            </li>
        </ul>

        <div class="p-3 border-top mt-2">
            <form action="{{ route('tenant.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 rounded-pill">
                    <i
                        class="bi bi-box-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} me-2"></i>{{ __('tenant::tenant.sign_out') }}
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar justify-content-between mb-4 rounded-4 shadow-sm px-4">
            <div class="search-box">
                <i class="bi bi-search text-muted"></i>
                <input type="text" placeholder="{{ __('tenant::tenant.search_placeholder') }}">
            </div>

            <div class="d-flex align-items-center gap-4">
                <div class="position-relative">
                    <i class="bi bi-bell fs-5 text-muted"></i>
                    <span
                        class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                </div>

                <div class="user-profile dropdown">
                    <div class="d-flex align-items-center" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ auth('tenant')->user()->user_name }}&background=6366f1&color=fff"
                            class="user-avatar" alt="User">
                        <div class="d-none d-md-block">
                            <h6 class="mb-0 fw-bold">{{ auth('tenant')->user()->user_name }}</h6>
                            <small class="text-muted">{{ __('tenant::tenant.administrator') }}</small>
                        </div>
                    </div>
                    <ul
                        class="dropdown-menu dropdown-menu-{{ app()->getLocale() == 'ar' ? 'start' : 'end' }} shadow border-0 mt-3 rounded-4">
                        <li><a class="dropdown-item py-2" href="{{ route('tenant.profile') }}"><i
                                    class="bi bi-person me-2"></i>
                                {{ __('tenant::tenant.my_profile') }}</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('tenant.settings') }}"><i
                                    class="bi bi-gear me-2"></i>
                                {{ __('tenant::tenant.settings') }}</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('tenant.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i
                                        class="bi bi-box-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} me-2"></i>
                                    {{ __('tenant::tenant.sign_out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content -->
        @yield('content')
    </main>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>

</html>