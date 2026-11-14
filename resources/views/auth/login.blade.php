<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Inventory Management System</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 1100px;
            min-height: 600px;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            margin: 20px;
        }

        /* Left Panel - Branding */
        .login-branding {
            width: 45%;
            padding: 50px 45px;
            background: linear-gradient(145deg, #0f172a 0%, #1a2744 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .login-branding::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .login-branding .brand-top {
            position: relative;
            z-index: 1;
        }

        .login-branding .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .login-branding .brand-logo .logo-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.4rem;
        }

        .login-branding .brand-logo h2 {
            color: #fff;
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .login-branding .brand-logo h2 span {
            color: #818cf8;
        }

        .login-branding .brand-tagline {
            margin-top: 50px;
            position: relative;
            z-index: 1;
        }

        .login-branding .brand-tagline h1 {
            color: #fff;
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1.2;
            margin: 0;
            letter-spacing: -1px;
        }

        .login-branding .brand-tagline h1 span {
            color: #818cf8;
        }

        .login-branding .brand-tagline p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 1rem;
            margin-top: 16px;
            line-height: 1.6;
            max-width: 85%;
        }

        .login-branding .brand-features {
            position: relative;
            z-index: 1;
            margin-top: 40px;
        }

        .login-branding .brand-features .feature-item {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
        }

        .login-branding .brand-features .feature-item .feature-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(79, 70, 229, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #818cf8;
            font-size: 0.9rem;
        }

        .login-branding .brand-features .feature-item span {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            font-weight: 400;
        }

        .login-branding .brand-bottom {
            position: relative;
            z-index: 1;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .login-branding .brand-bottom p {
            color: rgba(255, 255, 255, 0.25);
            font-size: 0.75rem;
            margin: 0;
            letter-spacing: 0.5px;
        }

        /* Right Panel - Login Form */
        .login-form-panel {
            width: 55%;
            padding: 50px 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #fff;
        }

        .login-form-panel .form-header {
            margin-bottom: 32px;
        }

        .login-form-panel .form-header h3 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .login-form-panel .form-header p {
            color: #64748b;
            font-size: 0.95rem;
            margin: 6px 0 0;
        }

        .login-form-panel .form-group {
            margin-bottom: 22px;
        }

        .login-form-panel .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .login-form-panel .form-group label .label-icon {
            color: #4f46e5;
            margin-right: 6px;
        }

        .login-form-panel .form-group .input-wrapper {
            position: relative;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            transition: all 0.25s ease;
            background: #fafbfc;
        }

        .login-form-panel .form-group .input-wrapper:focus-within {
            border-color: #4f46e5;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.06);
        }

        .login-form-panel .form-group .input-wrapper .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.9rem;
            transition: color 0.25s ease;
        }

        .login-form-panel .form-group .input-wrapper:focus-within .input-icon {
            color: #4f46e5;
        }

        .login-form-panel .form-group .input-wrapper input {
            width: 100%;
            padding: 13px 14px 13px 44px;
            border: none;
            background: transparent;
            font-size: 0.9rem;
            color: #0f172a;
            outline: none;
            font-weight: 400;
        }

        .login-form-panel .form-group .input-wrapper input::placeholder {
            color: #94a3b8;
            font-weight: 300;
        }

        .login-form-panel .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 6px 0 28px;
        }

        .login-form-panel .form-options .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .login-form-panel .form-options .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 2px solid #cbd5e1;
            cursor: pointer;
            accent-color: #4f46e5;
        }

        .login-form-panel .form-options .remember-me label {
            font-size: 0.85rem;
            color: #475569;
            cursor: pointer;
            margin: 0;
        }

        .login-form-panel .form-options .forgot-link {
            font-size: 0.85rem;
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .login-form-panel .form-options .forgot-link:hover {
            color: #4338ca;
        }

        .login-form-panel .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-form-panel .btn-login:hover {
            background: linear-gradient(135deg, #4338ca, #6d28d9);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.25);
        }

        .login-form-panel .btn-login:active {
            transform: translateY(0);
        }

        .login-form-panel .btn-login i {
            font-size: 0.9rem;
        }

        .login-form-panel .alert {
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.85rem;
            border: none;
            margin-bottom: 20px;
        }

        .login-form-panel .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .login-form-panel .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        /* Loading State */
        .btn-login.loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .btn-login .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        .btn-login.loading .spinner {
            display: block;
        }

        .btn-login.loading .btn-text {
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .login-wrapper {
                flex-direction: column;
                max-width: 500px;
                min-height: auto;
                border-radius: 20px;
            }

            .login-branding {
                width: 100%;
                padding: 35px 30px;
                min-height: 240px;
            }

            .login-branding .brand-tagline h1 {
                font-size: 1.6rem;
            }

            .login-branding .brand-tagline p {
                max-width: 100%;
            }

            .login-branding .brand-features {
                display: none;
            }

            .login-form-panel {
                width: 100%;
                padding: 35px 30px;
            }

            .login-form-panel .form-header h3 {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 480px) {
            .login-wrapper {
                margin: 10px;
                border-radius: 16px;
            }

            .login-branding {
                padding: 25px 20px;
                min-height: 180px;
            }

            .login-branding .brand-tagline h1 {
                font-size: 1.3rem;
            }

            .login-branding .brand-tagline p {
                font-size: 0.85rem;
                margin-top: 10px;
            }

            .login-form-panel {
                padding: 25px 20px;
            }

            .login-form-panel .form-options {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <!-- Left Panel - Branding -->
        <div class="login-branding">
            <div class="brand-top">
                <div class="brand-logo">
                    <div class="logo-icon">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <h2>Asset<span>Core</span></h2>
                </div>
                <div class="brand-tagline">
                    <h1>Manage your<br>inventory <span>with ease</span></h1>
                    <p>Complete asset management solution for modern organizations</p>
                </div>
            </div>

            <div class="brand-features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <span>Track inventory & stock levels</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <span>Manage purchases & suppliers</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <span>Real-time analytics & reports</span>
                </div>
            </div>

            <div class="brand-bottom">
                <p>&copy; {{ date('Y') }} AssetCore Inventory Management System</p>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="login-form-panel">
            <div class="form-header">
                <h3>Welcome back</h3>
                <p>Sign in to access your dashboard</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ $errors->first() }}
                </div>
            @endif

            @if(session('status'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i> {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope label-icon"></i> Email Address
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" name="email" id="email" 
                               placeholder="admin@example.com" 
                               value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock label-icon"></i> Password
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" id="password" 
                               placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember">Remember me</label>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <span class="spinner"></span>
                    <span class="btn-text">
                        <i class="fas fa-arrow-right-to-bracket"></i> Sign In
                    </span>
                </button>
            </form>

            <!-- Demo Credentials -->
            <div class="mt-4 text-center" style="border-top: 1px solid #e2e8f0; padding-top: 20px;">
                <p class="text-muted small mb-2">Demo Credentials</p>
                <div class="d-flex justify-content-center gap-4 flex-wrap">
                    <div>
                        <span class="badge bg-primary bg-opacity-10 text-primary">Admin</span>
                        <span class="text-muted small d-block">admin@example.com</span>
                    </div>
                    <div>
                        <span class="badge bg-primary bg-opacity-10 text-primary">Password</span>
                        <span class="text-muted small d-block">password</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailField = document.getElementById('email');
            if (!emailField.value) {
                emailField.focus();
            }

            // Form loading state
            const form = document.getElementById('loginForm');
            const btn = document.getElementById('loginBtn');

            form.addEventListener('submit', function() {
                btn.classList.add('loading');
            });

            // Enter key to submit
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const activeElement = document.activeElement;
                    if (activeElement && (activeElement.id === 'email' || activeElement.id === 'password')) {
                        form.submit();
                    }
                }
            });
        });
    </script>
</body>
</html>