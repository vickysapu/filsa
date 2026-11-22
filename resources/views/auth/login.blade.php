<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Filadelfia Admin</title>
    <link rel="icon" href="{{ asset('img/white.tree-logo.png') }}" type="image/svg+xml">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <!-- Google Fonts: Plus Jakarta Sans & Playfair Display (sama dengan layout admin) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet">

    <style>
        /* ===== CSS VARIABLES - POHON ZAITUN (SAMA DENGAN LAYOUT ADMIN) ===== */
        :root {
            --zaitun-deep: #3F5035;
            --zaitun-primary: #718F50;
            --zaitun-primary-hover: #5D793F;
            --zaitun-young: #A9C77D;
            --zaitun-gold: #E5D89B;
            --zaitun-cream: #F6EBCB;
            --zaitun-white: #FFFFFF;
            --zaitun-ripe: #7D4856;

            --bg-body: #FBF8EE;
            --bg-surface: #FFFFFF;
            --bg-sidebar: #3F5035;
            --text-primary: #253320;
            --text-secondary: #5E6E58;
            --border-color: #E3DCBF;
            --border-color-subtle: #EFE9D4;
            --accent: #718F50;
            --accent-hover: #5D793F;
            --danger: #7D4856;
            --success: #718F50;
            --shadow-sm: 0 1px 3px rgba(63, 80, 53, 0.05);
            --shadow-md: 0 4px 14px -2px rgba(63, 80, 53, 0.08);
            --shadow-lg: 0 12px 24px -4px rgba(63, 80, 53, 0.12);
            --shadow-xl: 0 24px 48px -12px rgba(63, 80, 53, 0.22);
            --radius: 12px;
            --radius-sm: 8px;
            --radius-lg: 18px;
            --transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        [data-theme="dark"] {
            --bg-body: #1A2318;
            --bg-surface: #243021;
            --bg-sidebar: #131A11;
            --text-primary: #F5F8F2;
            --text-secondary: #A7B89F;
            --border-color: #344330;
            --border-color-subtle: #293626;
            --accent: #88A666;
            --accent-hover: #9BB878;
            --danger: #C98595;
            --success: #88A666;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.35);
            --shadow-md: 0 4px 14px -2px rgba(0, 0, 0, 0.45);
            --shadow-lg: 0 12px 24px -4px rgba(0, 0, 0, 0.55);
            --shadow-xl: 0 24px 48px -12px rgba(0, 0, 0, 0.6);
        }

        /* ===== RESET & BASE ===== */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            width: 100%;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            transition: var(--transition);
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        button {
            font-family: inherit;
        }

        /* ===== LAYOUT: SPLIT SCREEN ===== */
        .login-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.05fr 1fr;
        }

        /* ----- Brand panel (kiri) — sama seperti sidebar: Deep Olive ----- */
        .login-brand {
            position: relative;
            background: var(--bg-sidebar);
            color: #FBF8EE;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 3rem 2.5rem;
            overflow: hidden;
        }

        .login-brand::before,
        .login-brand::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .login-brand::before {
            width: 520px;
            height: 520px;
            top: -180px;
            right: -180px;
            background: radial-gradient(circle, rgba(169, 199, 125, 0.18) 0%, transparent 70%);
            animation: float 20s ease-in-out infinite;
        }

        .login-brand::after {
            width: 380px;
            height: 380px;
            bottom: -140px;
            left: -120px;
            background: radial-gradient(circle, rgba(229, 216, 155, 0.14) 0%, transparent 70%);
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

        .brand-inner {
            position: relative;
            z-index: 1;
            max-width: 380px;
            animation: slideUp 0.6s ease;
        }

        .brand-logo {
            width: 140px;
            height: 158px;
            object-fit: contain;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 6px 16px rgba(0, 0, 0, 0.4));
        }

        .brand-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 700;
            font-size: 2.4rem;
            letter-spacing: 0.1em;
            color: #FFFFFF;
            line-height: 1.15;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        .brand-divider {
            width: 56px;
            height: 3px;
            border-radius: 3px;
            background: var(--zaitun-gold);
            margin: 1.1rem auto;
            opacity: 0.9;
        }

        .brand-tagline {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--zaitun-gold);
            opacity: 0.9;
        }

        .brand-desc {
            margin-top: 1rem;
            font-size: 0.92rem;
            color: rgba(251, 248, 238, 0.75);
        }

        /* ----- Form panel (kanan) ----- */
        .login-form-panel {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            background: var(--bg-body);
        }

        .theme-toggle {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 0.45rem 0.65rem;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .theme-toggle:hover {
            color: var(--accent);
            border-color: var(--accent);
        }

        .login-container {
            width: 100%;
            max-width: 430px;
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

        .login-heading {
            margin-bottom: 1.75rem;
        }

        .login-mobile-logo {
            display: none;
            width: 76px;
            height: 76px;
            padding: 12px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: var(--bg-sidebar);
            box-shadow: var(--shadow-md);
        }

        .login-mobile-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .login-heading h1 {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            letter-spacing: -0.01em;
        }

        .login-heading p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* ===== FORM ===== */
        .lux-form-group {
            margin-bottom: 1.25rem;
        }

        .lux-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .lux-label i {
            color: var(--accent);
            font-size: 1rem;
        }

        .login-input-icon {
            position: relative;
        }

        .login-input-icon>i.field-icon {
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
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            font-family: inherit;
            font-size: 0.95rem;
            color: var(--text-primary);
            background: var(--bg-body);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .lux-input:focus {
            outline: none;
            background: var(--bg-surface);
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(113, 143, 80, 0.15);
        }

        .lux-input:focus~i.field-icon {
            color: var(--accent);
        }

        .lux-input.lux-input-danger {
            border-color: var(--danger);
        }

        .lux-input.lux-input-danger:focus {
            box-shadow: 0 0 0 4px rgba(125, 72, 86, 0.15);
        }

        .lux-input::placeholder {
            color: var(--text-secondary);
            opacity: 0.6;
        }

        /* Autofill ikut tema */
        .lux-input:-webkit-autofill,
        .lux-input:-webkit-autofill:hover,
        .lux-input:-webkit-autofill:focus {
            -webkit-text-fill-color: var(--text-primary);
            -webkit-box-shadow: 0 0 0 1000px var(--bg-surface) inset;
            transition: background-color 5000s ease-in-out 0s;
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-secondary);
            background: none;
            border: none;
            font-size: 1.1rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
        }

        .password-toggle:hover {
            color: var(--accent);
            background: rgba(113, 143, 80, 0.12);
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
            accent-color: var(--accent);
            cursor: pointer;
        }

        .login-forgot {
            font-size: 0.875rem;
            color: var(--accent);
            font-weight: 600;
        }

        .login-forgot:hover {
            color: var(--accent-hover);
            text-decoration: underline;
        }

        /* ===== LOGIN BUTTON (sama dengan .btn-custom di layout) ===== */
        .login-btn {
            width: 100%;
            padding: 0.85rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: var(--zaitun-primary);
            color: #FFFFFF;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 2px 6px rgba(113, 143, 80, 0.3);
        }

        .login-btn:hover:not(:disabled) {
            background: var(--zaitun-primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(113, 143, 80, 0.4);
        }

        .login-btn:active:not(:disabled) {
            transform: translateY(0);
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .login-btn i {
            font-size: 1.1rem;
        }

        /* ===== ALERTS (sama dengan .alert-custom di layout) ===== */
        .lux-alert {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--zaitun-primary);
            border-radius: var(--radius-sm);
            padding: 0.8rem 1.1rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: var(--shadow-sm);
            animation: slideIn 0.3s ease;
        }

        .lux-alert.lux-alert-success {
            border-left-color: var(--zaitun-primary);
        }

        .lux-alert.lux-alert-danger {
            border-left-color: var(--zaitun-ripe);
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
            font-size: 0.88rem;
            color: var(--text-primary);
        }

        .alert-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0.7;
            line-height: 1;
            padding: 0.25rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .alert-close:hover {
            opacity: 1;
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
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border-color);
            text-align: center;
        }

        .login-security p {
            color: var(--text-secondary);
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            margin: 0;
        }

        .login-security p i {
            color: var(--accent);
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--zaitun-young);
            border-radius: 3px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .login-page {
                grid-template-columns: 1fr;
            }

            .login-brand {
                display: none;
            }

            .login-mobile-logo {
                display: block;
            }

            .login-heading {
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
            }

            .login-heading h1 {
                font-size: 1.5rem;
            }

            .login-remember {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>

<body>

    <div class="login-page">

        <!-- PANEL BRAND (kiri) -->
        <aside class="login-brand">
            <div class="brand-inner">
                <img src="{{ asset('img/white.tree-logo.png') }}?v={{ time() }}" alt="Logo Filadelfia"
                    class="brand-logo">
                <h2 class="brand-title">Filadelfia</h2>
                <div class="brand-divider"></div>
                <div class="brand-tagline">Sistem Administrasi Gereja</div>
                <p class="brand-desc">Kolose 3:23 "Apa pun juga yang kamu perbuat, perbuatlah dengan segenap hatimu seperti untuk Tuhan dan bukan untuk manusia."</p>
            </div>
        </aside>

        <!-- PANEL FORM (kanan) -->
        <main class="login-form-panel">
            <button type="button" class="theme-toggle" id="themeToggle" aria-label="Ganti tema">
                <i class="ri-moon-line" id="themeIcon"></i>
            </button>

            <div class="login-container">
                <div class="login-card">

                    <div class="login-heading">
                        <div class="login-mobile-logo">
                            <img src="{{ asset('img/white.tree-logo.png') }}" alt="Logo Filadelfia">
                        </div>
                        <h1>Selamat Datang</h1>
                        <p>Silakan login untuk melanjutkan</p>
                    </div>

                    <!-- Flash Messages -->
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
                                <input type="email" id="email" name="email"
                                    class="lux-input @error('email') lux-input-danger @enderror"
                                    placeholder="nama@contoh.com" value="{{ old('email') }}" required autofocus
                                    autocomplete="email" pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$">
                                <i class="ri-user-3-line field-icon"></i>
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
                                <input type="password" id="password" name="password"
                                    class="lux-input @error('password') lux-input-danger @enderror"
                                    placeholder="••••••••" required autocomplete="current-password" minlength="8">
                                <i class="ri-key-2-line field-icon"></i>
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
                    </div>
                </div>
            </div>
        </main>
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
                eyeIcon.classList.replace('ri-eye-off-line', 'ri-eye-line');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.replace('ri-eye-line', 'ri-eye-off-line');
            }
        }

        // ===== THEME TOGGLE (berbagi key 'theme' dengan layout admin) =====
        (function() {
            const root = document.documentElement;
            const icon = document.getElementById('themeIcon');
            const btn = document.getElementById('themeToggle');

            function syncIcon() {
                const isDark = root.getAttribute('data-theme') === 'dark';
                icon.className = isDark ? 'ri-sun-line' : 'ri-moon-line';
            }
            syncIcon();

            btn.addEventListener('click', function() {
                const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-theme', next);
                localStorage.setItem('theme', next);
                syncIcon();
            });
        })();

        // ===== PREVENT FORM RESUBMISSION =====
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // ===== AUTO-CLOSE ALERTS =====
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-auto-close]').forEach(alert => {
                const closeBtn = alert.querySelector('.alert-close');

                closeBtn?.addEventListener('click', () => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(-20px)';
                    setTimeout(() => alert.remove(), 300);
                });

                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateX(-20px)';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);
            });

            const emailInput = document.getElementById('email');
            if (emailInput && !emailInput.value) {
                setTimeout(() => emailInput.focus(), 300);
            }
        });

        // ===== LOADING STATE ON SUBMIT =====
        document.getElementById('loginForm')?.addEventListener('submit', function(e) {
            const form = e.currentTarget;
            const btn = document.getElementById('loginBtn');

            if (!form.checkValidity()) {
                e.preventDefault();
                form.classList.add('was-validated');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Memproses...';

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