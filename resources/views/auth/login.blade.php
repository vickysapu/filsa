<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Admin Panel</title>
    
    <link rel="stylesheet" href="{{ asset('css/luxury-core.css?v=2.0') }}">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        
        .login-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .login-logo h1 span {
            color: #d97706;
        }
        
        .login-logo p {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .login-input-icon {
            position: relative;
        }
        
        .login-input-icon i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.1rem;
        }
        
        .login-input-icon input {
            padding-left: 44px;
        }
        
        .login-remember {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .login-security {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .login-security p {
            color: #9ca3af;
            font-size: 0.8rem;
            margin-bottom: 10px;
        }
        
        .login-security-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
            color: #d97706;
        }
        
        .login-security-icons i {
            font-size: 1.2rem;
        }
        
        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9ca3af;
            background: none;
            border: none;
            font-size: 1.1rem;
        }
        
        .password-toggle:hover {
            color: #d97706;
        }
    </style>
</head>
<body>

<div class="login-page">
    <div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <div class="login-logo">
                <h1>ADMIN<span>PANEL</span></h1>
                <p>Silakan login untuk melanjutkan</p>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
            <div class="lux-alert lux-alert-success">
                <i class="ri-checkbox-circle-line"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="lux-alert lux-alert-danger">
                <i class="ri-error-warning-line"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="lux-alert lux-alert-danger">
                <i class="ri-error-warning-line"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST" id="loginForm">
                @csrf
                
                <div class="lux-form-group">
                    <label class="lux-label" for="email">
                        <i class="ri-mail-line"></i> Email
                    </label>
                    <div class="login-input-icon">
                        <i class="ri-user-3-line"></i>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="lux-input @error('email') lux-input-danger @enderror" 
                            placeholder="nama@contoh.com"
                            value="{{ old('email') }}"
                            required 
                            autofocus
                            autocomplete="email"
                        >
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
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="lux-input @error('password') lux-input-danger @enderror" 
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                            minlength="8"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="ri-eye-off-line" id="eyeIcon"></i>
                        </button>
                    </div>
                    @error('password')
                    <p class="lux-form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="login-remember">
                    <label class="lux-checkbox">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        Ingat saya
                    </label>
                </div>

                <button type="submit" class="lux-btn lux-btn-primary login-btn" id="loginBtn">
                    <i class="ri-login-box-line"></i> Login
                </button>
            </form>

            <!-- Security Info -->
            <div class="login-security">
                <p>🔒 Login Aman & Terenkripsi</p>
                <div class="login-security-icons">
                    <i class="ri-shield-check-line" title="SSL Secure"></i>
                    <i class="ri-lock-2-line" title="Password Encrypted"></i>
                    <i class="ri-time-line" title="Session Timeout"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/luxury-core.js?v=2.0') }}"></script>
<script>
    // Toggle Password Visibility
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

    // Prevent Form Resubmission
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.lux-alert');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);

    // Loading State on Submit
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-4-line"></i> Memproses...';
    });
</script>

</body>
</html>