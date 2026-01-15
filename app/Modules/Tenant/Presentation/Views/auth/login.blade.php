<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('tenant::tenant.sign_in') }} - Tenant Portal</title>
    <link rel="icon" type="image/svg+xml" href="{{ global_asset('favicon.svg') }}">

    <!-- Bootstrap 5 CSS -->
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @endif

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
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            font-family: '{{ app()->getLocale() == 'ar' ? 'Cairo' : 'Plus Jakarta Sans' }}', sans-serif;
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
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-logo {
            width: 64px;
            height: 64px;
            background: var(--primary-color);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            margin: 0 auto 16px;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 0.875rem;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            font-weight: 500;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            letter-spacing: 0.025em;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .input-group-text {
            border-radius:
                {{ app()->getLocale() == 'ar' ? '0 12px 12px 0' : '12px 0 0 12px' }}
            ;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: none;
            color: #64748b;
        }

        .form-control.has-icon {
            border-radius:
                {{ app()->getLocale() == 'ar' ? '12px 0 0 12px' : '0 12px 12px 0' }}
            ;
        }

        .remember-me {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
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

        <div class="login-header">
            <div class="brand-logo">
                <i class="bi bi-rocket-takeoff"></i>
            </div>
            <h2 class="fw-bold text-dark">{{ __('tenant::tenant.welcome_back') }}</h2>
            <p class="text-muted">{{ __('tenant::tenant.login_subtitle') }}</p>
        </div>

        <form action="{{ route('tenant.login.submit') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="user_name" class="form-label">{{ __('tenant::tenant.username') }}</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="user_name" id="user_name"
                        class="form-control has-icon @error('user_name') is-invalid @enderror"
                        placeholder="{{ __('tenant::tenant.username') }}" value="{{ old('user_name') }}" required
                        autofocus>
                </div>
                @error('user_name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="user_password" class="form-label">{{ __('tenant::tenant.password') }}</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="user_password" id="user_password" class="form-control has-icon"
                        placeholder="••••••••" required>
                </div>
            </div>

            <div class="remember-me">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label text-muted" for="remember">
                        {{ __('tenant::tenant.remember_me') }}
                    </label>
                </div>
                <a href="#"
                    class="text-primary text-decoration-none fw-semibold small">{{ __('tenant::tenant.forgot_password') }}</a>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                {{ __('tenant::tenant.sign_in') }} <i
                    class="bi bi-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} ms-2 me-2"></i>
            </button>
        </form>
    </div>

</body>

</html>