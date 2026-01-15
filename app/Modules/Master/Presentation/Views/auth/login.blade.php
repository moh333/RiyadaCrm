<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('master::master.master_login') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ global_asset('favicon.svg') }}">

    <!-- Bootstrap 5 CSS -->
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @endif

    <!-- Google Fonts -->
    @if(app()->getLocale() == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    @endif

    <style>
        body {
            font-family: '{{ app()->getLocale() == 'ar' ? 'Cairo' : 'Outfit' }}', sans-serif;
            background: url('{{ global_asset('login-bg.png') }}') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.4);
            /* Subtle overlay */
            z-index: 0;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            border-color: #6366f1;
        }

        .btn-primary {
            background-color: #6366f1;
            border: none;
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background-color: #4f46e5;
            transform: translateY(-1px);
        }

        .lang-switcher {
            position: absolute;
            top: -50px;
            {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}
            : 0;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="lang-switcher">
            <a href="{{ route('lang.switch', app()->getLocale() == 'ar' ? 'en' : 'ar') }}"
                class="btn btn-sm btn-white shadow-sm border text-primary fw-bold">
                {{ app()->getLocale() == 'ar' ? 'English' : 'عربي' }}
            </a>
        </div>

        <div class="text-center mb-4">
            <h4 class="fw-bold fs-3">{{ __('master::master.master_title') }}</h4>
            <p class="text-muted text-sm">{{ __('master::master.master_subtitle') }}</p>
        </div>

        <form action="{{ route('master.login.submit') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">{{ __('master::master.email_address') }}</label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>

            <div class="mb-4">
                <label class="form-label text-muted small fw-bold">{{ __('master::master.password') }}</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">{{ __('master::master.login') }}</button>
        </form>
    </div>
</body>

</html>