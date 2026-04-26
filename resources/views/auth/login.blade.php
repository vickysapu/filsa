<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Admin Panel</title>
    <link rel="icon" href="{{ asset('img/logo.jpeg') }}">

    <link rel="stylesheet" href="{{ asset('css/luxury-core.css?v=2.0') }}">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <style>
        /* ===== LOGIN PAGE STYLES ===== */
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--bg-gradient-start, #1f2937) 0%, var(--bg-gradient-end, #111827) 100%);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative background elements */
        .login-page::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.1) 0%, transparent 70%);
            top: -200px;
            right: -200px;
            animation: float 20s ease-in-out infinite;
        }

        .login-page::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(217, 119, 6, 0.1) 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            animation: float 15s ease-in-out infinite reverse;
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
            background: var(--bg-surface, white);
            border-radius: var(--radius-lg, 16px);
            padding: 2.5rem;
            box-shadow: var(--shadow-2xl, 0 25px 50px -12px rgba(0, 0, 0, 0.4));
            border: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
            backdrop-filter: blur(10px);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo img {
            height: 120px;
            margin-bottom: 1rem;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }

        .login-logo h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-primary, #1f2937);
            margin-bottom: 0.25rem;
            letter-spacing: -0.025em;
        }

        .login-logo h1 span {
            color: var(--accent-amber, #d97706);
        }

        .login-logo p {
            color: var(--text-secondary, #6b7280);
            font-size: 0.9rem;
        }

        /* Form Styles */
        .lux-form-group {
            margin-bottom: 1.25rem;
        }

        .lux-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary, #374151);
            margin-bottom: 0.5rem;
        }

        .lux-label i {
            color: var(--accent-blue, #4f46e5);
        }

        .login-input-icon {
            position: relative;
        }

        .login-input-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted, #9ca3af);
            font-size: 1.1rem;
            pointer-events: none;
        }

        .lux-input {
            width: 100%;
            padding: 0.75rem 1rem;
            padding-left: 2.75rem;
            font-size: 0.95rem;
            color: var(--text-primary, #1f2937);
            background: var(--bg-surface, white);
            border: 2px solid var(--border-color, #e5e7eb);
            border-radius: var(--radius, 10px);
            transition: all 0.2s ease;
        }

        .lux-input:focus {
            outline: none;
            border-color: var(--accent-blue, #4f46e5);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
        }

        .lux-input.lux-input-danger {
            border-color: var(--danger, #ef4444);
        }

        .lux-input.lux-input-danger:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
        }

        .lux-input::placeholder {
            color: var(--text-secondary, #9ca3af);
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted, #9ca3af);
            background: none;
            border: none;
            font-size: 1.1rem;
            padding: 0.25rem;
            border-radius: var(--radius-sm, 6px);
            transition: all 0.2s ease;
        }

        .password-toggle:hover {
            color: var(--accent-amber, #d97706);
            background: var(--bg-hover, rgba(0, 0, 0, 0.05));
        }

        .lux-form-error {
            color: var(--danger, #ef4444);
            font-size: 0.8rem;
            margin-top: 0.375rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .lux-form-error::before {
            content: '•';
        }

        /* Remember & Forgot */
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
            color: var(--text-primary, #374151);
            cursor: pointer;
        }

        .lux-checkbox input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--accent-blue, #4f46e5);
            cursor: pointer;
        }

        .login-forgot {
            font-size: 0.875rem;
            color: var(--accent-blue, #4f46e5);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .login-forgot:hover {
            color: var(--accent-amber, #d97706);
            text-decoration: underline;
        }

        /* Login Button */
        .login-btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--accent-blue, #4f46e5) 0%, var(--accent-blue-hover, #4338ca) 100%);
            color: white;
            border: none;
            border-radius: var(--radius, 10px);
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
        }

        .login-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.5);
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .login-btn i {
            font-size: 1.1rem;
        }

        /* Alerts */
        .lux-alert {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.875rem 1.25rem;
            border-radius: var(--radius, 10px);
            font-size: 0.9rem;
            margin-bottom: 1.25rem;
            animation: slideIn 0.3s ease;
        }

        .lux-alert i {
            font-size: 1.25rem;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .lux-alert-success {
            background: rgba(34, 197, 94, 0.1);
            border-left: 4px solid #22c55e;
            color: #166534;
        }

        .lux-alert-success i {
            color: #22c55e;
        }

        .lux-alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .lux-alert-danger i {
            color: #ef4444;
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

        /* Security Info */
        .login-security {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color, #e5e7eb);
            text-align: center;
        }

        .login-security p {
            color: var(--text-muted, #9ca3af);
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
        }

        .login-security-icons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            color: var(--accent-amber, #d97706);
        }

        .login-security-icons i {
            font-size: 1.1rem;
            transition: transform 0.2s ease;
        }

        .login-security-icons i:hover {
            transform: scale(1.1);
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            .login-card {
                background: var(--bg-surface-dark, #1f2937);
                border-color: var(--border-color-dark, #374151);
            }

            .login-logo h1 {
                color: var(--text-primary-dark, #f9fafb);
            }

            .lux-label {
                color: var(--text-primary-dark, #f9fafb);
            }

            .lux-input {
                background: var(--bg-input-dark, #374151);
                border-color: var(--border-color-dark, #4b5563);
                color: var(--text-primary-dark, #f9fafb);
            }

            .lux-input::placeholder {
                color: var(--text-secondary-dark, #9ca3af);
            }

            .lux-checkbox {
                color: var(--text-primary-dark, #f9fafb);
            }

            .login-security {
                border-top-color: var(--border-color-dark, #374151);
            }

            .login-security p {
                color: var(--text-muted-dark, #6b7280);
            }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
            }

            .login-logo h1 {
                font-size: 1.25rem;
            }

            .login-remember {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

    <div class="login-page">
        <div class="login-container">
            <div class="login-card">
                <!-- Logo -->
                <div class="login-logo">
                    <img src="{{ asset('img/logo.jpeg') }}" alt="logo">
                    <h1>ADMIN<span>PANEL</span></h1>
                    <p>Silakan login untuk melanjutkan</p>
                </div>

                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="lux-alert lux-alert-success" data-auto-close>
                        <i class="ri-checkbox-circle-fill"></i>
                        <span>{{ session('success') }}</span>
                        <button class="alert-close" type="button">&times;</button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="lux-alert lux-alert-danger" data-auto-close>
                        <i class="ri-error-warning-fill"></i>
                        <span>{{ session('error') }}</span>
                        <button class="alert-close" type="button">&times;</button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="lux-alert lux-alert-danger" data-auto-close>
                        <i class="ri-error-warning-fill"></i>
                        <span>{{ $errors->first() }}</span>
                        <button class="alert-close" type="button">&times;</button>
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
                                aria-label="Toggle password visibility">
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

        // ===== AUTO-CLOSE ALERTS =====
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
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateX(-20px)';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);
            });
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

            // Prevent multiple submissions
            setTimeout(() => {
                if (btn.disabled) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ri-login-box-line"></i> Login';
                }
            }, 30000); // Reset after 30 seconds as fallback
        });

        // ===== ENTER KEY HANDLING =====
        document.getElementById('password')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('loginForm')?.requestSubmit();
            }
        });

        // ===== FOCUS MANAGEMENT =====
        // Auto-focus email if empty
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            if (emailInput && !emailInput.value) {
                emailInput.focus();
            }
        });
    </script>

</body>

</html>
