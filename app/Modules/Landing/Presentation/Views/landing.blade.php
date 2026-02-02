<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('landing.title') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ global_asset('favicon.svg') }}">
    <!-- Bootstrap 5 CSS -->
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @endif
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if(app()->getLocale() == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
            rel="stylesheet">
    @endif
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #0f172a;
            --accent-color: #3b82f6;
            --accent-hover: #2563eb;
            --text-color: #334155;
            --light-bg: #f8fafc;
        }

        body {
            font-family: '{{ app()->getLocale() == 'ar' ? 'Cairo' : 'Plus Jakarta Sans' }}', sans-serif;
            color: var(--text-color);
            background-color: white;
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.9);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .nav-link {
            font-weight: 500;
            color: #64748b;
            transition: color 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--accent-color);
        }

        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            border-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }

        .btn-outline-primary {
            color: var(--accent-color);
            border-color: var(--accent-color);
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            border-radius: 50px;
        }

        .btn-outline-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        /* Hero Section */
        .hero-section {
            padding: 8rem 0 5rem 0;
            background: radial-gradient(circle at top
                    {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}
                    , #eff6ff 0%, #ffffff 40%);
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            color: var(--primary-color);
            letter-spacing: -0.02em;
            margin-bottom: 1.5rem;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }

        /* Features */
        .feature-card {
            border: none;
            background: white;
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid #f1f5f9;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.01);
            border-color: transparent;
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #eff6ff;
            color: var(--accent-color);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Stats */
        .stat-card {
            background: var(--primary-color);
            color: white;
            border-radius: 20px;
            padding: 3rem;
            background-image: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        /* Footer */
        .footer {
            background-color: white;
            padding-top: 5rem;
            padding-bottom: 2rem;
            border-top: 1px solid #f1f5f9;
        }

        .footer-heading {
            font-weight: 700;
            margin-bottom: 1.2rem;
            color: var(--primary-color);
        }

        .footer-link {
            color: #64748b;
            text-decoration: none;
            margin-bottom: 0.8rem;
            display: block;
            transition: color 0.2s;
        }

        .footer-link:hover {
            color: var(--accent-color);
        }

        /* Floating shapes animation */
        .shape {
            position: absolute;
            filter: blur(50px);
            z-index: -1;
            opacity: 0.6;
        }

        .shape-1 {
            top: -10%;
            {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}
            : -5%;
            width: 500px;
            height: 500px;
            background: #dbeafe;
            border-radius: 50%;
        }

        .shape-2 {
            bottom: 10%;
            {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}
            : -10%;
            width: 400px;
            height: 400px;
            background: #e0e7ff;
            border-radius: 50%;
        }
    </style>
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-rocket-takeoff-fill text-primary me-2"></i> Riyada<span class="text-primary">CRM</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">{{ __('landing.nav.home') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">{{ __('landing.nav.features') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">{{ __('landing.nav.pricing') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">{{ __('landing.nav.about') }}</a>
                    </li>

                    <!-- Language Switcher -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-globe2 me-1"></i> {{ app()->getLocale() == 'ar' ? 'عربي' : 'English' }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('lang.switch', 'en') }}">English</a></li>
                            <li><a class="dropdown-item" href="{{ route('lang.switch', 'ar') }}">عربي</a></li>
                        </ul>
                    </li>
                </ul>
                <div class="d-flex gap-2">
                    <a href="{{ route('master.login') }}"
                        class="btn btn-outline-primary">{{ __('landing.nav.login') }}</a>
                    <a href="{{ route('master.login') }}"
                        class="btn btn-primary">{{ __('landing.nav.get_started') }}</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section position-relative" id="home">
        <div class="shape shape-1"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h1 class="hero-title">
                        {!! __('landing.hero.title') !!}
                    </h1>
                    <p class="hero-subtitle">
                        {{ __('landing.hero.subtitle') }}
                    </p>
                    <div class="d-flex gap-3">
                        <a href="{{ route('master.login') }}"
                            class="btn btn-primary btn-lg px-4">{{ __('landing.hero.trial_btn') }}</a>
                        <a href="#features"
                            class="btn btn-outline-primary btn-lg px-4">{{ __('landing.hero.features_btn') }}</a>
                    </div>
                    <div class="mt-4 pt-3 d-flex align-items-center gap-3">
                        <div class="d-flex">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <span class="text-muted fw-medium">{{ __('landing.hero.trusted') }}</span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2670&auto=format&fit=crop"
                            alt="Dashboard Preview" class="img-fluid rounded-4 shadow-lg border border-white border-4"
                            style="transform: perspective(1000px) rotateY(-10deg) rotateX(2deg);">

                        <!-- Floating Element -->
                        <div class="card position-absolute shadow bg-white rounded-4 p-3 border-0"
                            style="bottom: -20px; {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: -20px; width: 200px;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center"
                                    style="width: 40px; height: 40px;">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">{{ __('landing.hero.monthly_growth') }}</small>
                                    <span class="fw-bold text-dark">+128%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Clients Logo Section -->
    <div class="py-5 border-bottom bg-light">
        <div class="container">
            <p class="text-center text-muted mb-4 small fw-bold text-uppercase tracking-wide">
                {{ __('landing.clients.trusted_by') }}
            </p>
            <div class="row align-items-center justify-content-center opacity-50 grayscale"
                style="filter: grayscale(100%);">
                <div class="col-4 col-md-2 text-center mb-4 mb-md-0">
                    <h4 class="fw-bold text-dark m-0">ACME</h4>
                </div>
                <div class="col-4 col-md-2 text-center mb-4 mb-md-0">
                    <h4 class="fw-bold text-dark m-0">Global</h4>
                </div>
                <div class="col-4 col-md-2 text-center mb-4 mb-md-0">
                    <h4 class="fw-bold text-dark m-0">Stripe</h4>
                </div>
                <div class="col-4 col-md-2 text-center mb-4 mb-md-0">
                    <h4 class="fw-bold text-dark m-0">Uber</h4>
                </div>
                <div class="col-4 col-md-2 text-center mb-4 mb-md-0">
                    <h4 class="fw-bold text-dark m-0">Bolt</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <section class="py-6 py-lg-7" id="features" style="padding-top: 5rem; padding-bottom: 5rem;">
        <div class="container">
            <div class="text-center mb-5 mw-800 mx-auto" style="max-width: 700px;">
                <span
                    class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill fw-semibold">{{ __('landing.features.badge') }}</span>
                <h2 class="display-6 fw-bold mb-3">{!! __('landing.features.title') !!}</h2>
                <p class="text-muted lead">{{ __('landing.features.subtitle') }}</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4 class="fw-bold h5 mb-3">{{ __('landing.features.items.lead.title') }}</h4>
                        <p class="text-muted mb-0">{{ __('landing.features.items.lead.desc') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-bar-chart"></i>
                        </div>
                        <h4 class="fw-bold h5 mb-3">{{ __('landing.features.items.analytics.title') }}</h4>
                        <p class="text-muted mb-0">{{ __('landing.features.items.analytics.desc') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-lightning"></i>
                        </div>
                        <h4 class="fw-bold h5 mb-3">{{ __('landing.features.items.workflow.title') }}</h4>
                        <p class="text-muted mb-0">{{ __('landing.features.items.workflow.desc') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <h4 class="fw-bold h5 mb-3">{{ __('landing.features.items.omnichannel.title') }}</h4>
                        <p class="text-muted mb-0">{{ __('landing.features.items.omnichannel.desc') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="fw-bold h5 mb-3">{{ __('landing.features.items.security.title') }}</h4>
                        <p class="text-muted mb-0">{{ __('landing.features.items.security.desc') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                        <h4 class="fw-bold h5 mb-3">{{ __('landing.features.items.mobile.title') }}</h4>
                        <p class="text-muted mb-0">{{ __('landing.features.items.mobile.desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-6 py-lg-7 bg-light" id="pricing" style="padding-top: 5rem; padding-bottom: 5rem;">
        <div class="container">
            <div class="text-center mb-5 mw-800 mx-auto" style="max-width: 700px;">
                <span
                    class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill fw-semibold">{{ __('landing.pricing.badge') }}</span>
                <h2 class="display-6 fw-bold mb-3">{!! __('landing.pricing.title') !!}</h2>
                <p class="text-muted lead">{{ __('landing.pricing.desc') }}</p>
            </div>

            <div class="row g-4 align-items-center justify-content-center">
                <!-- Starter Plan -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 feature-card">
                        <div class="card-body p-4 p-xl-5">
                            <div class="mb-4">
                                <span
                                    class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2 mb-3">{{ __('landing.pricing.starter.title') }}</span>
                                <h3 class="fw-bold display-5 mb-0">$29<small class="text-muted fs-6 fw-normal">/
                                        {{ __('landing.pricing.monthly') }}</small></h3>
                                <p class="text-muted mt-2">{{ __('landing.pricing.starter.desc') }}</p>
                            </div>
                            <ul class="list-unstyled mb-4 d-grid gap-3">
                                @foreach(__('landing.pricing.starter.features') as $feature)
                                    <li class="d-flex align-items-center"><i
                                            class="bi bi-check-circle-fill text-success me-2"></i> {{ $feature }}</li>
                                @endforeach
                            </ul>
                            <a href="#"
                                class="btn btn-outline-primary w-100 rounded-pill py-2 fw-semibold">{{ __('landing.pricing.starter.btn') }}</a>
                        </div>
                    </div>
                </div>

                <!-- Pro Plan -->
                <div class="col-md-6 col-lg-4">
                    <div
                        class="card border-primary border-2 shadow rounded-4 h-100 position-relative overflow-hidden feature-card">
                        <div
                            class="position-absolute top-0 end-0 bg-primary text-white text-uppercase fw-bold py-1 px-3 small rounded-bottom-start">
                            {{ __('landing.pricing.pro.badge') }}
                        </div>
                        <div class="card-body p-4 p-xl-5">
                            <div class="mb-4">
                                <span
                                    class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 mb-3">{{ __('landing.pricing.pro.title') }}</span>
                                <h3 class="fw-bold display-5 mb-0">$79<small
                                        class="text-muted fs-6 fw-normal">/{{ __('landing.pricing.monthly') }}</small>
                                </h3>
                                <p class="text-muted mt-2">{{ __('landing.pricing.pro.desc') }}</p>
                            </div>
                            <ul class="list-unstyled mb-4 d-grid gap-3">
                                @foreach(__('landing.pricing.pro.features') as $feature)
                                    <li class="d-flex align-items-center"><i
                                            class="bi bi-check-circle-fill text-primary me-2"></i> {{ $feature }}</li>
                                @endforeach
                            </ul>
                            <a href="#"
                                class="btn btn-primary w-100 rounded-pill py-2 fw-semibold">{{ __('landing.pricing.pro.btn') }}</a>
                        </div>
                    </div>
                </div>

                <!-- Enterprise Plan -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 feature-card">
                        <div class="card-body p-4 p-xl-5">
                            <div class="mb-4">
                                <span
                                    class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2 mb-3">{{ __('landing.pricing.enterprise.title') }}</span>
                                <h3 class="fw-bold display-5 mb-0">{{ __('landing.pricing.enterprise.price') }}</h3>
                                <p class="text-muted mt-2">{{ __('landing.pricing.enterprise.desc') }}</p>
                            </div>
                            <ul class="list-unstyled mb-4 d-grid gap-3">
                                @foreach(__('landing.pricing.enterprise.features') as $feature)
                                    <li class="d-flex align-items-center"><i
                                            class="bi bi-check-circle-fill text-success me-2"></i> {{ $feature }}</li>
                                @endforeach
                            </ul>
                            <a href="#"
                                class="btn btn-outline-primary w-100 rounded-pill py-2 fw-semibold">{{ __('landing.pricing.enterprise.btn') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-6 py-lg-7" id="about" style="padding-top: 5rem; padding-bottom: 5rem;">
        <div class="container">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6 order-lg-2">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2670&auto=format&fit=crop"
                            alt="Team collaboration" class="img-fluid rounded-4 shadow-lg">
                        <div class="shape shape-2 opacity-50"
                            style="{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: -30px; bottom: -30px; {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: auto; width: 200px; height: 200px;">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 order-lg-1">
                    <span
                        class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill fw-semibold">{{ __('landing.about.badge') }}</span>
                    <h2 class="display-5 fw-bold mb-4">{!! __('landing.about.title') !!}</h2>
                    <p class="lead text-muted mb-4">{{ __('landing.about.text1') }}</p>
                    <p class="text-muted mb-4">{{ __('landing.about.text2') }}</p>
                    <div class="d-flex gap-4 pt-2">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">{{ __('landing.about.vision.title') }}</h4>
                            <p class="text-muted small">{{ __('landing.about.vision.desc') }}</p>
                        </div>
                        <div>
                            <h4 class="fw-bold text-dark mb-1">{{ __('landing.about.values.title') }}</h4>
                            <p class="text-muted small">{{ __('landing.about.values.desc') }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="#"
                            class="btn btn-link text-primary fw-semibold text-decoration-none p-0">{{ __('landing.about.link') }}
                            <i class="bi bi-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5">
        <div class="container">
            <div class="stat-card">
                <div class="row text-center g-4">
                    <div class="col-md-3 border-end border-secondary border-opacity-25">
                        <h2 class="display-4 fw-bold mb-1">10k+</h2>
                        <p class="text-white-50 mb-0">{{ __('landing.stats.users') }}</p>
                    </div>
                    <div class="col-md-3 border-end border-secondary border-opacity-25">
                        <h2 class="display-4 fw-bold mb-1">98%</h2>
                        <p class="text-white-50 mb-0">{{ __('landing.stats.satisfaction') }}</p>
                    </div>
                    <div class="col-md-3 border-end border-secondary border-opacity-25">
                        <h2 class="display-4 fw-bold mb-1">24/7</h2>
                        <p class="text-white-50 mb-0">{{ __('landing.stats.support') }}</p>
                    </div>
                    <div class="col-md-3">
                        <h2 class="display-4 fw-bold mb-1">50M+</h2>
                        <p class="text-white-50 mb-0">{{ __('landing.stats.records') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-6 py-lg-7 text-center" style="padding: 5rem 0;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="display-5 fw-bold mb-4">{{ __('landing.cta.title') }}</h2>
                    <p class="lead text-muted mb-5">{{ __('landing.cta.desc') }}</p>
                    <a href="{{ route('master.login') }}"
                        class="btn btn-primary btn-lg px-5 py-3">{{ __('landing.cta.btn') }}</a>
                    <p class="mt-3 small text-muted">{{ __('landing.cta.note') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <a class="navbar-brand mb-4 d-block" href="#">
                        <i class="bi bi-rocket-takeoff-fill text-primary me-2"></i> Riyada<span
                            class="text-primary">CRM</span>
                    </a>
                    <p class="text-muted mb-4">{{ __('landing.footer.desc') }}</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-muted fs-5"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="text-muted fs-5"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-muted fs-5"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-muted fs-5"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <h5 class="footer-heading">{{ __('landing.footer.product') }}</h5>
                    @foreach(array_slice(__('landing.footer.links'), 0, 4) as $link)
                        <a href="#" class="footer-link">{{ $link }}</a>
                    @endforeach
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <h5 class="footer-heading">{{ __('landing.footer.resources') }}</h5>
                    @foreach(array_slice(__('landing.footer.links'), 4, 4) as $link)
                        <a href="#" class="footer-link">{{ $link }}</a>
                    @endforeach
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <h5 class="footer-heading">{{ __('landing.footer.company') }}</h5>
                    @foreach(array_slice(__('landing.footer.links'), 8, 4) as $link)
                        <a href="#" class="footer-link">{{ $link }}</a>
                    @endforeach
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <h5 class="footer-heading">{{ __('landing.footer.support') }}</h5>
                    @foreach(array_slice(__('landing.footer.links'), 12, 4) as $link)
                        <a href="#" class="footer-link">{{ $link }}</a>
                    @endforeach
                </div>
            </div>
            <div class="border-top mt-5 pt-4 text-center text-muted small">
                &copy; {{ date('Y') }} Riyada CRM. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>