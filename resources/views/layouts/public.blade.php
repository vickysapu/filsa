<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Gereja Filadelfia - Welcome Home">
    <meta name="keywords" content="gereja, filadelfia, ibadah, kristen, worship">
    <title>@yield('title', 'Gereja Filadelfia')</title>
    <link rel="icon" href="{{ asset('img/logo.jpeg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <!-- Lux Core CSS -->
    <link rel="stylesheet" href="{{ asset('css/luxury-core.css') }}">

    <!-- Custom Public Styles -->
    <style>
        :root {
            --brand-primary: #4f46e5;
            --brand-secondary: #06b6d4;
            --brand-accent: #f59e0b;
            --brand-dark: #0f172a;
            --brand-light: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--brand-light);
            color: var(--brand-dark);
            overflow-x: hidden;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            opacity: 0.05;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Navbar */
        .public-navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .public-navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }

        .nav-logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            -webkit-text-fill-color: white;
        }

        .nav-menu {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-menu a {
            text-decoration: none;
            color: var(--brand-dark);
            font-weight: 600;
            font-size: 0.95rem;
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-secondary));
            transition: width 0.3s ease;
        }

        .nav-menu a:hover::after {
            width: 100%;
        }

        .nav-menu a:hover {
            color: var(--brand-primary);
        }

        .nav-cta {
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            color: white !important;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
            transition: all 0.3s ease;
        }

        .nav-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.5);
        }

        .nav-cta::after {
            display: none;
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--brand-dark);
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 8rem 2rem 4rem;
        }

        .hero-content {
            text-align: center;
            max-width: 900px;
            z-index: 10;
        }

        .hero-badge {
            display: inline-block;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.1));
            border: 2px solid rgba(79, 70, 229, 0.3);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            color: var(--brand-primary);
            margin-bottom: 2rem;
            animation: fadeInUp 0.8s ease;
        }

        .hero-title {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary), var(--brand-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .hero-subtitle {
            font-size: clamp(1.1rem, 2vw, 1.4rem);
            color: var(--brand-dark);
            opacity: 0.8;
            margin-bottom: 2.5rem;
            line-height: 1.8;
            animation: fadeInUp 0.8s ease 0.4s both;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.6s both;
        }

        .hero-btn {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .hero-btn-primary {
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            color: white;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        }

        .hero-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(79, 70, 229, 0.5);
        }

        .hero-btn-secondary {
            background: white;
            color: var(--brand-primary);
            border: 2px solid var(--brand-primary);
        }

        .hero-btn-secondary:hover {
            background: var(--brand-primary);
            color: white;
            transform: translateY(-3px);
        }

        /* Floating Shapes */
        .floating-shape {
            position: absolute;
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
            opacity: 0.1;
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            top: 10%;
            left: -5%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, var(--brand-accent), #f97316);
            bottom: 20%;
            right: -3%;
            animation-delay: 2s;
        }

        .shape-3 {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, var(--brand-secondary), #10b981);
            top: 50%;
            right: 10%;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-30px) rotate(180deg);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Section Styles */
        .section {
            padding: 6rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-badge {
            display: inline-block;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.1));
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            color: var(--brand-primary);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .section-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: var(--brand-dark);
            opacity: 0.7;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.8;
        }

        /* Cards */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-secondary));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 1.5rem;
        }

        .feature-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--brand-dark);
        }

        .feature-description {
            color: var(--brand-dark);
            opacity: 0.7;
            line-height: 1.8;
        }

        /* Event Cards */
        .event-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
        }

        .event-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .event-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .event-card:hover .event-image {
            transform: scale(1.05);
        }

        .event-content {
            padding: 2rem;
        }

        .event-date {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.1));
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--brand-primary);
            margin-bottom: 1rem;
        }

        .event-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--brand-dark);
            line-height: 1.3;
        }

        .event-location {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--brand-dark);
            opacity: 0.7;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .event-btn {
            display: inline-block;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .event-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        }

        /* Gallery Grid */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .gallery-item {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            aspect-ratio: 1;
            cursor: pointer;
        }

        .gallery-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .gallery-item:hover .gallery-image {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
            display: flex;
            align-items: flex-end;
            padding: 1.5rem;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-title {
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .gallery-count {
            color: white;
            opacity: 0.8;
            font-size: 0.85rem;
        }

        /* Footer */
        .public-footer {
            background: linear-gradient(135deg, var(--brand-dark), #1e293b);
            color: white;
            padding: 5rem 2rem 2rem;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .footer-brand-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .footer-brand-text {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .footer-description {
            opacity: 0.8;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }

        .footer-social {
            display: flex;
            gap: 1rem;
        }

        .footer-social a {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-social a:hover {
            background: var(--brand-primary);
            transform: translateY(-3px);
        }

        .footer-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: white;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: white;
            opacity: 0.8;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-links a:hover {
            opacity: 1;
            padding-left: 5px;
        }

        .footer-contact {
            list-style: none;
        }

        .footer-contact li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }

        .footer-contact i {
            color: var(--brand-primary);
            margin-top: 4px;
        }

        .footer-bottom {
            max-width: 1400px;
            margin: 0 auto;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            opacity: 0.7;
            font-size: 0.9rem;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

            .hero-section {
                padding: 6rem 1rem 3rem;
            }

            .section {
                padding: 4rem 1rem;
            }

            .card-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Scroll Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Animated Background -->
    <div class="animated-bg"></div>

    <!-- Navigation -->
    <nav class="public-navbar" id="navbar">
        <div class="nav-container">
            <a href="{{ route('public.home') }}" class="nav-logo">
                <div class="nav-logo-icon">
                    <i class="ri-church-line"></i>
                </div>
                <span>FILADELFIA</span>
            </a>

            <ul class="nav-menu">
                <li><a href="{{ route('public.home') }}">Beranda</a></li>
                <li><a href="{{ route('public.about') }}">Tentang Kami</a></li>
                <li><a href="{{ route('public.events') }}">Events</a></li>
                <li><a href="{{ route('public.gallery') }}">Galeri</a></li>
                <li><a href="{{ route('public.contact') }}">Kontak</a></li>
                <li><a href="{{ route('login') }}" class="nav-cta">Login</a></li>
            </ul>

            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="ri-menu-line"></i>
            </button>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="footer-content">
            <div>
                <div class="footer-brand">
                    <div class="footer-brand-icon">
                        <i class="ri-church-line"></i>
                    </div>
                    <span class="footer-brand-text">FILADELFIA</span>
                </div>
                <p class="footer-description">
                    Welcome Home! Gereja Filadelfia adalah tempat dimana Anda diterima dengan kasih,
                    tumbuh dalam iman, dan melayani dengan sukacita.
                </p>
                <div class="footer-social">
                    <a href="#"><i class="ri-facebook-fill"></i></a>
                    <a href="#"><i class="ri-instagram-fill"></i></a>
                    <a href="#"><i class="ri-youtube-fill"></i></a>
                    <a href="#"><i class="ri-whatsapp-line"></i></a>
                </div>
            </div>

            <div>
                <h4 class="footer-title">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('public.home') }}">Beranda</a></li>
                    <li><a href="{{ route('public.about') }}">Tentang Kami</a></li>
                    <li><a href="{{ route('public.events') }}">Events</a></li>
                    <li><a href="{{ route('public.gallery') }}">Galeri</a></li>
                    <li><a href="{{ route('public.contact') }}">Kontak</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-title">Jam Ibadah</h4>
                <ul class="footer-contact">
                    <li>
                        <i class="ri-sun-line"></i>
                        <div>
                            <strong>Ibadah Minggu Pagi</strong><br>
                            08:00 WIB
                        </div>
                    </li>
                    <li>
                        <i class="ri-moon-line"></i>
                        <div>
                            <strong>Ibadah Malam</strong><br>
                            18:00 WIB
                        </div>
                    </li>
                    <li>
                        <i class="ri-calendar-line"></i>
                        <div>
                            <strong>Doa & Puji</strong><br>
                            Rabu, 19:00 WIB
                        </div>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="footer-title">Kontak</h4>
                <ul class="footer-contact">
                    <li>
                        <i class="ri-map-pin-line"></i>
                        <div>Jl. Gereja No. 123, Jakarta</div>
                    </li>
                    <li>
                        <i class="ri-phone-line"></i>
                        <div>(021) 1234-5678</div>
                    </li>
                    <li>
                        <i class="ri-mail-line"></i>
                        <div>info@gerejafiladelfia.com</div>
                    </li>
                    <li>
                        <i class="ri-whatsapp-line"></i>
                        <div>0812-3456-7890</div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} Gereja Filadelfia. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Navbar Scroll Effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const menu = document.querySelector('.nav-menu');
            menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
        }

        // Scroll Animation
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
