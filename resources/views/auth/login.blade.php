<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Filadelfia Admin</title>
    <link rel="icon" href="{{ asset('img/logo.jpeg') }}">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <style>
        /* ===== CSS VARIABLES - LIGHT MODE (MATCHING DASHBOARD) ===== */
        :root {
            --bg-body: #f8fafc;
            --bg-surface: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --accent-blue: #2563eb;
            --accent-blue-hover: #1d4ed8;
            --accent-amber: #d97706;
            --danger: #ef4444;
            --success: #22c55e;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --radius: 12px;
            --radius-sm: 8px;
            --radius-lg: 16px;
            --transition: all 0.2s ease;
        }

        /* ===== RESET & BASE ===== */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            line-height: 1.6;
            transition: var(--transition);
            min-height: 100vh;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        button {
            font-family: inherit;
        }

        /* ===== LOGIN PAGE STYLES ===== */
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--bg-body) 0%, #e2e8f0 100%);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative background elements - Light Mode */
        .login-page::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.08) 0%, transparent 70%);
            top: -200px;
            right: -200px;
            animation: float 20s ease-in-out infinite;
            pointer-events: none;
        }

        .login-page::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(217, 119, 6, 0.06) 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            animation: float 15s ease-in-out infinite reverse;
            pointer-events: none;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(20px, 20px);
            }
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            background: var(--bg-surface);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .login-card:hover {
            box-shadow: var(--shadow-lg);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo img {
            height: 100px;
            margin-bottom: 1rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
        }

        .login-logo h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            letter-spacing: -0.025em;
        }

        .login-logo h1 span {
            color: var(--accent-blue);
        }

        .login-logo p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* ===== FORM STYLES ===== */
        .lux-form-group {
            margin-bottom: 1.25rem;
        }

        .lux-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .lux-label i {
            color: var(--accent-blue);
            font-size: 1rem;
        }

        .login-input-icon {
            position: relative;
        }

        .login-input-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1.1rem;
            pointer-events: none;
            transition: var(--transition);
        }

        .lux-input {
            width: 100%;
            padding: 0.75rem 1rem;
            padding-left: 2.75rem;
            font-size: 0.95rem;
            color: var(--text-primary);
            background: var(--bg-surface);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .lux-input:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .lux-input:focus+i,
        .lux-input:focus~.password-toggle i {
            color: var(--accent-blue);
        }

        .lux-input.lux-input-danger {
            border-color: var(--danger);
        }

        .lux-input.lux-input-danger:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
        }

        .lux-input::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-secondary);
            background: none;
            border: none;
            font-size: 1.1rem;
            padding: 0.25rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
        }

        .password-toggle:hover {
            color: var(--accent-blue);
            background: rgba(37, 99, 235, 0.08);
        }

        .lux-form-error {
            color: var(--danger);
            font-size: 0.8rem;
            margin-top: 0.375rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .lux-form-error::before {
            content: '•';
            font-weight: bold;
        }

        /* ===== REMEMBER & FORGOT ===== */
        .login-remember {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .lux-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-primary);
            cursor: pointer;
            user-select: none;
        }

        .lux-checkbox input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--accent-blue);
            cursor: pointer;
            border-radius: 4px;
        }

        .login-forgot {
            font-size: 0.875rem;
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .login-forgot:hover {
            color: var(--accent-blue-hover);
            text-decoration: underline;
        }

        /* ===== LOGIN BUTTON ===== */
        .login-btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow-md);
        }

        .login-btn:hover:not(:disabled) {
            background: var(--accent-blue-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .login-btn:active:not(:disabled) {
            transform: translateY(0);
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: var(--shadow-sm);
        }

        .login-btn i {
            font-size: 1.1rem;
        }

        /* ===== ALERTS (MATCHING DASHBOARD) ===== */
        .lux-alert {
            background: var(--bg-surface);
            border-left: 4px solid var(--accent-blue);
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
            padding: 0.875rem 1.25rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: var(--shadow-sm);
            animation: slideIn 0.3s ease;
            position: relative;
        }

        .lux-alert.lux-alert-success {
            border-left-color: var(--success);
        }

        .lux-alert.lux-alert-danger {
            border-left-color: var(--danger);
        }

        .lux-alert i {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .lux-alert-success i {
            color: var(--success);
        }

        .lux-alert-danger i {
            color: var(--danger);
        }

        .lux-alert span {
            flex: 1;
            font-size: 0.9rem;
            color: var(--text-primary);
        }

        .alert-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0.7;
            transition: var(--transition);
            padding: 0.25rem;
            line-height: 1;
            border-radius: var(--radius-sm);
        }

        .alert-close:hover {
            opacity: 1;
            background: rgba(0, 0, 0, 0.05);
            color: var(--text-primary);
        }

        @keyframes slideIn {
            from {
                transform: translateX(-20px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* ===== SECURITY INFO ===== */
        .login-security {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            text-align: center;
        }

        .login-security p {
            color: var(--text-secondary);
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
        }

        .login-security p i {
            color: var(--success);
        }

        .login-security-icons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            color: var(--accent-blue);
        }

        .login-security-icons i {
            font-size: 1.1rem;
            transition: var(--transition);
            cursor: default;
        }

        .login-security-icons i:hover {
            transform: scale(1.1);
            color: var(--accent-blue-hover);
        }

        /* ===== SCROLLBAR (MATCHING DASHBOARD) ===== */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--text-secondary);
            border-radius: 3px;
            opacity: 0.3;
        }

        ::-webkit-scrollbar-thumb:hover {
            opacity: 0.5;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
            }

            .login-logo h1 {
                font-size: 1.25rem;
            }

            .login-logo img {
                height: 80px;
            }

            .login-remember {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* ===== UTILITIES ===== */
        .text-muted-custom {
            color: var(--text-secondary) !important;
        }

        .border-custom {
            border-color: var(--border-color) !important;
        }
    </style>
</head>

<body>

    <div class="login-page">
        <div class="login-container">
            <div class="login-card">
                <!-- Logo -->
                <div class="login-logo">
                    <img src="{{ asset('img/logo.jpeg') }}" alt="Filadelfia Logo" class="img-fluid">
                    <h1>FILADELFIA<span>ADMIN</span></h1>
                    <p>Silakan login untuk melanjutkan</p>
                </div>

                <!-- Flash Messages (Matching Dashboard Alert Style) -->
                @if (session('success'))
                    <div class="lux-alert lux-alert-success" data-auto-close>
                        <i class="ri-checkbox-circle-fill"></i>
                        <span>{{ session('success') }}</span>
                        <button class="alert-close" type="button" aria-label="Tutup">&times;</button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="lux-alert lux-alert-danger" data-auto-close>
                        <i class="ri-error-warning-fill"></i>
                        <span>{{ session('error') }}</span>
                        <button class="alert-close" type="button" aria-label="Tutup">&times;</button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="lux-alert lux-alert-danger" data-auto-close>
                        <i class="ri-error-warning-fill"></i>
                        <span>{{ $errors->first() }}</span>
                        <button class="alert-close" type="button" aria-label="Tutup">&times;</button>
                    </div>
                @endif

                <!-- Login Form -->
                <form action="{{ route('login') }}" method="POST" id="loginForm" novalidate>
                    @csrf

                    <div class="lux-form-group">
                        <label class="lux-label" for="email">
                            <i class="ri-mail-line"></i> Email
                        </label>
                        <div class="login-input-icon">
                            <i class="ri-user-3-line"></i>
                            <input type="email" id="email" name="email"
                                class="lux-input @error('email') lux-input-danger @enderror"
                                placeholder="nama@contoh.com" value="{{ old('email') }}" required autofocus
                                autocomplete="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                        </div>
                        @error('email')
                            <p class="lux-form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lux-form-group">
                        <label class="lux-label" for="password">
                            <i class="ri-lock-line"></i> Password
                        </label>
                        <div class="login-input-icon">
                            <i class="ri-key-2-line"></i>
                            <input type="password" id="password" name="password"
                                class="lux-input @error('password') lux-input-danger @enderror" placeholder="••••••••"
                                required autocomplete="current-password" minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword()"
                                aria-label="Tampilkan/Sembunyikan password">
                                <i class="ri-eye-off-line" id="eyeIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="lux-form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="login-remember">
                        <label class="lux-checkbox">
                            <input type="checkbox" name="remember" value="1"
                                {{ old('remember') ? 'checked' : '' }}>
                            Ingat saya
                        </label>
                        <a href="#" class="login-forgot">Lupa password?</a>
                    </div>

                    <button type="submit" class="login-btn" id="loginBtn">
                        <i class="ri-login-box-line"></i> Login
                    </button>
                </form>

                <!-- Security Info -->
                <div class="login-security">
                    <p><i class="ri-shield-check-line"></i> Login Aman & Terenkripsi</p>
                    <div class="login-security-icons">
                        <i class="ri-shield-check-line" title="SSL Secure"></i>
                        <i class="ri-lock-2-line" title="Password Encrypted"></i>
                        <i class="ri-time-line" title="Session Timeout"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ===== TOGGLE PASSWORD VISIBILITY =====
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('ri-eye-off-line');
                eyeIcon.classList.add('ri-eye-line');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('ri-eye-line');
                eyeIcon.classList.add('ri-eye-off-line');
            }
        }

        // ===== PREVENT FORM RESUBMISSION =====
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // ===== AUTO-CLOSE ALERTS (MATCHING DASHBOARD) =====
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-auto-close]').forEach(alert => {
                const closeBtn = alert.querySelector('.alert-close');

                // Close button handler
                closeBtn?.addEventListener('click', () => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(-20px)';
                    setTimeout(() => alert.remove(), 300);
                });

                // Auto close after 5 seconds
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateX(-20px)';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);
            });

            // ===== FOCUS MANAGEMENT =====
            const emailInput = document.getElementById('email');
            if (emailInput && !emailInput.value) {
                setTimeout(() => emailInput.focus(), 300);
            }
        });

        // ===== LOADING STATE ON SUBMIT =====
        document.getElementById('loginForm')?.addEventListener('submit', function(e) {
            const form = e.currentTarget;
            const btn = document.getElementById('loginBtn');

            // Basic validation check
            if (!form.checkValidity()) {
                e.preventDefault();
                form.classList.add('was-validated');
                return;
            }

            // Show loading state
            btn.disabled = true;
            btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Memproses...';

            // Prevent multiple submissions - reset after 30s as fallback
            setTimeout(() => {
                if (btn.disabled) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ri-login-box-line"></i> Login';
                }
            }, 30000);
        });

        // ===== ENTER KEY HANDLING =====
        document.getElementById('password')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('loginForm')?.requestSubmit();
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
