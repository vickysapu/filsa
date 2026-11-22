<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Gereja Filadelfia - Komunitas Iman, Pengharapan, dan Kasih Persaudaraan di Dalam Kristus.">
    <meta name="keywords" content="gereja filadelfia, ibadah minggu, persekutuan kristen, firman tuhan, pelayanan doa">
    <title>@yield('title', 'Gereja Filadelfia - Welcome Home')</title>
    <link rel="icon" href="{{ asset('img/white.tree-logo.png') }}" type="image/svg+xml">

    <!-- Fonts: Plus Jakarta Sans & Playfair Display (Pure Architectural Editorial Hierarchy) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;0,900;1,400;1,600&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Icons: Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <!-- Professional Olive Tree Design System -->
    <style>
        :root {
            /* Nature of Faith palette */
            --zaitun-deep: #174C35;
            --zaitun-deep-dark: #103B2B;
            --zaitun-primary: #174C35;
            --zaitun-primary-hover: #103B2B;
            --zaitun-young: #DCE6D8;
            --zaitun-gold: #C5A66A;
            --zaitun-gold-light: #DCE6D8;
            --zaitun-cream: #DCE6D8;
            --zaitun-sand: #F5F1E7;
            --zaitun-white: #F5F1E7;
            --zaitun-terracotta: #174C35;
            --zaitun-terracotta-hover: #103B2B;
            --zaitun-text-primary: #103B2B;
            --zaitun-text-secondary: #43584B;
            --zaitun-text-muted: #66766C;
            --zaitun-border: #D7DED2;
            --zaitun-border-subtle: #E5E8DD;

            /* Compatibility Tokens */
            --brand-primary: var(--zaitun-primary);
            --brand-secondary: var(--zaitun-deep);
            --brand-accent: var(--zaitun-gold);
            --brand-dark: var(--zaitun-deep-dark);
            --brand-light: var(--zaitun-sand);

            /* Shadows & Geometry */
            --shadow-subtle: 0 2px 10px rgba(18, 61, 45, 0.05);
            --shadow-card: 0 10px 30px rgba(18, 61, 45, 0.08);
            --shadow-hover: 0 14px 32px rgba(18, 61, 45, 0.12);
            --radius-pill: 999px;
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--zaitun-sand);
            color: var(--zaitun-text-primary);
            line-height: 1.7;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1, h2, h3, h4, h5, h6, .font-serif {
            font-family: 'Playfair Display', Georgia, serif;
            color: var(--zaitun-text-primary);
            letter-spacing: -0.015em;
            line-height: 1.2;
        }

        a {
            color: inherit;
            text-decoration: none;
            transition: var(--transition);
        }

        /* ===== NAVBAR ===== */
        .site-navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: rgba(23, 77, 55, 0.97);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(245, 241, 230, 0.12);
            z-index: 1000;
            transition: var(--transition);
        }

        .site-navbar.scrolled {
            background: rgba(23, 77, 55, 0.99);
            border-bottom-color: rgba(245, 241, 230, 0.16);
            box-shadow: 0 4px 20px rgba(18, 61, 45, 0.16);
        }

        .navbar-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0.9rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .brand-icon-box {
            width: 44px;
            height: 44px;
            background: var(--zaitun-deep);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 7px;
            box-shadow: 0 4px 12px rgba(52, 67, 44, 0.2);
        }

        .brand-icon-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-name-group {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: var(--zaitun-sand);
            line-height: 1;
        }

        .brand-sub {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: var(--zaitun-gold);
            margin-top: 3px;
        }

        .nav-links-list {
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
        }

        .nav-links-list a {
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--zaitun-sand);
            position: relative;
            padding: 0.25rem 0;
        }

        .nav-links-list a::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--zaitun-gold);
            transition: width 0.25s ease;
        }

        .nav-links-list a:hover {
            color: var(--zaitun-gold);
        }

        .nav-links-list a:hover::after,
        .nav-links-list a.active::after {
            width: 100%;
        }

        .nav-links-list a.active {
            color: var(--zaitun-gold);
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-worship-nav {
            background: var(--zaitun-primary);
            color: white !important;
            padding: 0.6rem 1.35rem;
            border-radius: 4px;
            font-size: 0.88rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            box-shadow: none;
            transition: var(--transition);
        }

        .btn-worship-nav:hover {
            background: var(--zaitun-terracotta-hover);
            transform: translateY(-2px);
            box-shadow: none;
        }

        .mobile-hamburger {
            display: none;
            background: transparent;
            border: none;
            font-size: 1.6rem;
            color: var(--zaitun-sand);
            cursor: pointer;
            padding: 0.4rem;
        }

        /* ===== COMMON PAGE HERO FOR SUBPAGES ===== */
        .page-hero {
            padding: 7.5rem 2rem 3.5rem;
            background: var(--zaitun-cream);
            border-bottom: 1px solid var(--zaitun-border);
            position: relative;
        }

        .page-hero-container {
            max-width: 1280px;
            margin: 0 auto;
        }

        .page-breadcrumbs {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--zaitun-text-muted);
            margin-bottom: 1rem;
        }

        .page-breadcrumbs a:hover {
            color: var(--zaitun-primary);
        }

        .page-kicker {
            display: inline-block;
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--zaitun-primary);
            margin-bottom: 0.6rem;
        }

        .page-hero-title {
            font-size: clamp(2.4rem, 4.5vw, 3.4rem);
            font-weight: 800;
            color: var(--zaitun-text-primary);
            margin-bottom: 0.8rem;
        }

        .page-hero-desc {
            font-size: 1.15rem;
            color: var(--zaitun-text-secondary);
            max-width: 650px;
            line-height: 1.7;
        }

        /* ===== GENERAL LAYOUT & UTILITIES ===== */
        .content-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 5rem 2rem;
        }

        .section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: var(--zaitun-primary);
            margin-bottom: 0.8rem;
        }

        .section-headline {
            font-size: clamp(2rem, 3.5vw, 2.8rem);
            font-weight: 800;
            color: var(--zaitun-text-primary);
            line-height: 1.2;
            margin-bottom: 1rem;
        }

        .section-lead {
            font-size: 1.1rem;
            color: var(--zaitun-text-secondary);
            line-height: 1.75;
            max-width: 640px;
        }

        /* Buttons */
        .btn-primary-olive {
            background: var(--zaitun-deep);
            color: var(--zaitun-sand);
            padding: 0.85rem 2rem;
            border-radius: 4px;
            font-weight: 700;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-primary-olive:hover {
            background: var(--zaitun-primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(52, 67, 44, 0.25);
        }

        .btn-terracotta-cta {
            background: var(--zaitun-terracotta);
            color: white;
            padding: 0.85rem 2rem;
            border-radius: 4px;
            font-weight: 700;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            box-shadow: none;
            transition: var(--transition);
        }

        .btn-terracotta-cta:hover {
            background: var(--zaitun-terracotta-hover);
            color: white;
            transform: translateY(-2px);
            box-shadow: none;
        }

        .btn-outline-sand {
            background: var(--zaitun-white);
            color: var(--zaitun-deep);
            border: 1.5px solid var(--zaitun-border);
            padding: 0.85rem 1.8rem;
            border-radius: 4px;
            font-weight: 700;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .btn-outline-sand:hover {
            border-color: var(--zaitun-deep);
            background: var(--zaitun-deep);
            color: var(--zaitun-sand);
            transform: translateY(-2px);
        }

        /* Cards & Forms */
        .editorial-card {
            background: transparent;
            border: 1px solid var(--zaitun-border);
            border-radius: var(--radius-lg);
            padding: 2.2rem;
            box-shadow: var(--shadow-card);
            transition: var(--transition);
        }

        .editorial-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
            border-color: var(--zaitun-young);
        }

        .form-label-olive {
            display: block;
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--zaitun-text-primary);
            margin-bottom: 0.45rem;
        }

        .form-control-olive {
            width: 100%;
            padding: 0.85rem 1.1rem;
            background: var(--zaitun-sand);
            border: 1.5px solid var(--zaitun-border);
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.95rem;
            color: var(--zaitun-text-primary);
            transition: var(--transition);
        }

        .form-control-olive:focus {
            outline: none;
            background: #FFFFFF;
            border-color: var(--zaitun-primary);
            box-shadow: 0 0 0 3px rgba(82, 110, 59, 0.15);
        }

        /* ===== FOOTER ===== */
        .site-footer {
            background: var(--zaitun-deep-dark);
            color: #C2CEC0;
            padding: 5rem 2rem 2.5rem;
            border-top: 4px solid var(--zaitun-primary);
        }

        .footer-grid {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
            gap: 3.5rem;
            margin-bottom: 3.5rem;
        }

        .footer-brand-lockup {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            margin-bottom: 1.2rem;
        }

        .footer-brand-icon {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(232, 221, 181, 0.25);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
        }

        .footer-brand-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .footer-brand-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: #EADEB8;
        }

        .footer-copy-text {
            font-size: 0.92rem;
            line-height: 1.75;
            color: #9FB29A;
            margin-bottom: 1.5rem;
        }

        .footer-social-row {
            display: flex;
            gap: 0.75rem;
        }

        .footer-social-link {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #EADEB8;
            font-size: 1.05rem;
            transition: var(--transition);
        }

        .footer-social-link:hover {
            background: var(--zaitun-primary);
            border-color: var(--zaitun-primary);
            color: white;
            transform: translateY(-2px);
        }

        .footer-heading {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #EADEB8;
            margin-bottom: 1.4rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .footer-heading::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 28px;
            height: 2px;
            background: var(--zaitun-gold);
        }

        .footer-nav-list, .footer-contact-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .footer-nav-list a {
            font-size: 0.92rem;
            color: #C2CEC0;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: var(--transition);
        }

        .footer-nav-list a:hover {
            color: #EADEB8;
            transform: translateX(4px);
        }

        .footer-contact-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.92rem;
            color: #C2CEC0;
        }

        .footer-contact-item i {
            color: var(--zaitun-gold);
            font-size: 1.1rem;
            margin-top: 3px;
        }

        .footer-bottom-bar {
            max-width: 1280px;
            margin: 0 auto;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.86rem;
            color: #AFC4B4;
        }

        /* ===== SCROLL REVEAL ===== */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 2.5rem;
            }
        }

        @media (max-width: 1120px) {
            .nav-links-list {
                position: fixed;
                top: 68px;
                left: 0;
                width: 100%;
                background: var(--zaitun-deep);
                border-bottom: 1px solid rgba(245, 241, 230, 0.16);
                box-shadow: 0 12px 28px rgba(18, 61, 45, 0.2);
                flex-direction: column;
                padding: 1.5rem 2rem;
                gap: 1.1rem;
                align-items: flex-start;
                display: none;
            }

            .nav-links-list.active {
                display: flex;
            }

            .navbar-actions {
                display: none;
            }

            .mobile-hamburger {
                display: block;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .footer-bottom-bar {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>

    @stack('styles')
    <style>
        :root {
            --editorial-width: 1240px;
        }

        .navbar-inner {
            min-height: 72px;
            padding-top: 0.65rem;
            padding-bottom: 0.65rem;
        }

        .brand-icon-box {
            width: 42px;
            height: 42px;
            border: 1px solid rgba(245, 241, 231, 0.18);
            border-radius: 3px;
            background: var(--zaitun-deep-dark);
            box-shadow: none;
        }

        .brand-title {
            font-size: 1.2rem;
        }

        .nav-links-list {
            gap: 1.65rem;
        }

        .nav-links-list a {
            font-size: 0.86rem;
        }

        .btn-worship-nav {
            border-radius: 3px;
            min-height: 40px;
        }

        .btn-worship-nav {
            background: var(--zaitun-gold);
            color: var(--zaitun-deep-dark) !important;
        }

        .btn-worship-nav:hover {
            background: #D4B77D;
            color: var(--zaitun-deep-dark) !important;
        }

        .site-footer {
            border-top: 1px solid rgba(197, 166, 106, 0.38);
            background: var(--zaitun-deep-dark);
        }

        .footer-grid {
            grid-template-columns: 1.35fr 0.8fr 1fr 1.15fr;
        }

        .footer-brand-icon,
        .footer-social-link {
            border-radius: 3px;
        }

        .footer-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .page-hero {
            background: var(--zaitun-sand);
            border-bottom: 1px solid var(--zaitun-border);
        }

        .public-page-hero {
            position: relative;
            display: flex;
            min-height: 500px;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 8rem 2rem 4rem;
            border: 0;
            background-position: center;
            background-size: cover;
            color: var(--zaitun-sand);
            text-align: center;
        }

        .public-page-hero--about {
            background-image: linear-gradient(90deg, rgba(16, 59, 43, 0.8), rgba(16, 59, 43, 0.44)), url('https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=1800&auto=format&fit=crop&q=82');
        }

        .public-page-hero--events {
            background-image: linear-gradient(90deg, rgba(16, 59, 43, 0.84), rgba(16, 59, 43, 0.46)), url('https://images.unsplash.com/photo-1507692049790-de58290a4334?w=1800&auto=format&fit=crop&q=82');
        }

        .public-page-hero--gallery {
            background-image: linear-gradient(90deg, rgba(16, 59, 43, 0.82), rgba(16, 59, 43, 0.42)), url('https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=1800&auto=format&fit=crop&q=82');
        }

        .public-page-hero--contact {
            background-image: linear-gradient(90deg, rgba(16, 59, 43, 0.84), rgba(16, 59, 43, 0.46)), url('https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=1800&auto=format&fit=crop&q=82');
        }

        .public-page-hero .page-hero-container {
            position: relative;
            z-index: 1;
            max-width: 900px;
        }

        .public-page-hero .page-breadcrumbs {
            justify-content: center;
            margin-bottom: 1.4rem;
            color: rgba(245, 241, 231, 0.76);
        }

        .public-page-hero .page-breadcrumbs span,
        .public-page-hero .page-kicker {
            color: var(--zaitun-gold-light) !important;
        }

        .public-page-hero .page-kicker {
            margin-bottom: 0.85rem;
        }

        .public-page-hero .page-hero-title {
            max-width: 850px;
            margin: 0 auto 1rem;
            color: var(--zaitun-sand);
            font-size: clamp(2.6rem, 5.3vw, 4.5rem);
            font-weight: 500;
            line-height: 1.08;
            text-wrap: balance;
        }

        .public-page-hero .page-hero-desc {
            max-width: 680px;
            margin: 0 auto;
            color: rgba(245, 241, 231, 0.88);
            font-size: 1.05rem;
        }

        .page-image-note {
            position: absolute;
            right: 1.5rem;
            bottom: 1.25rem;
            z-index: 1;
            color: rgba(245, 241, 231, 0.72);
            font-size: 0.65rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .public-editorial-page .site-navbar {
            background: rgba(16, 59, 43, 0.3);
            border-bottom-color: rgba(245, 241, 231, 0.14);
            box-shadow: none;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .public-editorial-page .site-navbar.scrolled {
            background: rgba(16, 59, 43, 0.97);
            box-shadow: 0 4px 20px rgba(16, 59, 43, 0.16);
        }

        .page-hero-title {
            max-width: 780px;
            color: var(--zaitun-deep-dark);
        }

        .section-kicker,
        .page-kicker {
            color: var(--zaitun-primary);
        }

        .editorial-card {
            border-radius: 4px;
            box-shadow: none;
        }

        .btn-primary-olive,
        .btn-terracotta-cta,
        .btn-outline-sand {
            border-radius: 3px;
        }

        .btn-terracotta-cta {
            background: var(--zaitun-deep);
        }

        .events-editorial-list .event-row-card:first-child {
            border-left: 3px solid var(--zaitun-gold);
        }

        /* Public editorial refinements */
        .hero-video-section {
            background-image:
                linear-gradient(90deg, rgba(16, 59, 43, 0.94) 0%, rgba(16, 59, 43, 0.76) 48%, rgba(16, 59, 43, 0.24) 100%),
                url('https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=1800&auto=format&fit=crop&q=82');
            background-position: center;
            background-size: cover;
        }

        .hero-background-video {
            display: none;
        }

        .hero-video-shade {
            background: linear-gradient(0deg, rgba(16, 59, 43, 0.48), transparent 62%);
        }

        .welcome-panel {
            padding-top: 4.5rem;
            padding-bottom: 4.5rem;
        }

        .welcome-h2 {
            margin-bottom: 1rem;
        }

        .who-we-are-panel {
            padding-top: 5.5rem;
        }

        .who-image-wrap {
            max-height: 520px;
            aspect-ratio: 5 / 4;
        }

        .ministries-grid-wrap {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            max-width: 1080px;
        }

        .ministry-box {
            display: grid;
            grid-template-columns: 43% 57%;
            min-height: 220px;
        }

        .ministry-box-img {
            height: 100%;
        }

        .events-panel {
            background: var(--zaitun-sand);
        }

        .events-editorial-list {
            border-top: 1px solid var(--zaitun-border);
        }

        .events-editorial-list .event-row-card {
            border: 0;
            border-bottom: 1px solid var(--zaitun-border);
            border-radius: 0;
            padding: 1.5rem 0.5rem;
            background: transparent;
        }

        .gallery-quad-grid {
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }

        .gallery-quad-item {
            grid-column: span 3;
            aspect-ratio: 4 / 3;
        }

        .gallery-quad-item:nth-child(1),
        .gallery-quad-item:nth-child(4) {
            grid-column: span 6;
            aspect-ratio: 16 / 9;
        }

        .schedule-three-grid {
            max-width: 1080px;
        }

        .schedule-tile {
            border-radius: 3px;
            border-top: 2px solid var(--zaitun-border);
        }

        .schedule-tile.featured-tile {
            border-top-color: var(--zaitun-gold);
        }

        @media (max-width: 1120px) {
            .navbar-inner {
                min-height: 68px;
                padding-right: 1.25rem;
                padding-left: 1.25rem;
            }

            .nav-links-list {
                top: 68px;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 640px) {
            .brand-title {
                font-size: 1.05rem;
            }

            .brand-sub {
                font-size: 0.58rem;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }

            .footer-bottom-bar {
                align-items: flex-start;
                text-align: left;
            }

            .public-page-hero {
                min-height: 430px;
                padding: 7.5rem 1.25rem 3.5rem;
                background-position: center;
            }

            .public-page-hero .page-hero-title {
                font-size: clamp(2.3rem, 10vw, 3.4rem);
            }

            .public-page-hero .page-hero-desc {
                font-size: 0.95rem;
            }

            .page-image-note {
                right: 1rem;
                bottom: 0.75rem;
                font-size: 0.58rem;
            }

            .hero-video-section {
                min-height: 720px;
                background-position: 58% center;
            }

            .ministries-grid-wrap {
                grid-template-columns: 1fr;
            }

            .ministry-box {
                grid-template-columns: 38% 62%;
                min-height: 190px;
            }

            .gallery-quad-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .gallery-quad-item,
            .gallery-quad-item:nth-child(1),
            .gallery-quad-item:nth-child(4) {
                grid-column: span 1;
                aspect-ratio: 4 / 3;
            }

            body main > .content-container > div[style*="repeat(auto-fill, minmax(340px"] article {
                grid-template-columns: 1fr !important;
            }

            body main > .content-container > div[style*="repeat(auto-fill, minmax(340px"] article > div:first-child {
                height: 190px !important;
            }

            body main > .content-container > div[style*="repeat(auto-fill, minmax(320px"] {
                grid-template-columns: 1fr !important;
            }

            body main > .content-container > div[style*="repeat(auto-fill, minmax(320px"] > .editorial-card,
            body main > .content-container > div[style*="repeat(auto-fill, minmax(320px"] > .editorial-card:nth-child(4n + 1) {
                grid-column: auto;
            }
        }
    </style>
</head>

<body class="{{ request()->routeIs('public.about', 'public.events', 'public.event.detail', 'public.gallery', 'public.gallery.album', 'public.contact') ? 'public-editorial-page' : '' }}">

    <!-- Main Navigation Bar -->
    <header class="site-navbar" id="siteNavbar">
        <div class="navbar-inner">
            <a href="{{ route('public.home') }}" class="brand-link">
                <div class="brand-icon-box">
                    <img src="{{ asset('img/white.tree-logo.png') }}" alt="Filadelfia" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'ri-ancient-gate-line\' style=\'color:#EADEB8;\'></i>';">
                </div>
                <div class="brand-name-group">
                    <span class="brand-title">FILADELFIA</span>
                    <span class="brand-sub">Gereja Kristen</span>
                </div>
            </a>

            <nav>
                <ul class="nav-links-list" id="navLinksList">
                    <li><a href="{{ route('public.home') }}" class="{{ request()->routeIs('public.home') ? 'active' : '' }}">Beranda</a></li>
                    <li><a href="{{ route('public.about') }}" class="{{ request()->routeIs('public.about') ? 'active' : '' }}">Tentang Kami</a></li>
                    <li><a href="{{ route('public.events') }}" class="{{ request()->routeIs('public.events*') || request()->routeIs('public.event.detail') ? 'active' : '' }}">Agenda</a></li>
                    <li><a href="{{ route('public.gallery') }}" class="{{ request()->routeIs('public.gallery*') ? 'active' : '' }}">Galeri</a></li>
                    <li><a href="{{ route('public.contact') }}" class="{{ request()->routeIs('public.contact') ? 'active' : '' }}">Kontak</a></li>
                </ul>
            </nav>

            <div class="navbar-actions">
                <a href="{{ route('public.home') }}#jadwal-ibadah" class="btn-worship-nav">
                    <i class="ri-time-line"></i>
                    <span>Jadwal Ibadah</span>
                </a>
            </div>

            <button class="mobile-hamburger" id="mobileToggle" aria-label="Buka Menu Navigasi">
                <i class="ri-menu-line" id="mobileIcon"></i>
            </button>
        </div>
    </header>

    <!-- Main Dynamic Content -->
    <main>
        @yield('content')
    </main>

    <!-- Professional Deep Olive Footer -->
    <footer class="site-footer">
        <div class="footer-grid">
            <div>
                <div class="footer-brand-lockup">
                    <div class="footer-brand-icon">
                        <img src="{{ asset('img/white.tree-logo.png') }}" alt="Logo Filadelfia">
                    </div>
                    <span class="footer-brand-title">FILADELFIA</span>
                </div>
                <p class="footer-copy-text">
                    "Tetapi aku ini seperti pohon zaitun yang menghijau di dalam rumah Allah; aku percaya akan kasih setia Allah untuk seterusnya dan selamanya." — Mazmur 52:10
                </p>
                <div class="footer-social-row">
                    <a href="https://facebook.com" target="_blank" class="footer-social-link" aria-label="Facebook"><i class="ri-facebook-fill"></i></a>
                    <a href="https://instagram.com" target="_blank" class="footer-social-link" aria-label="Instagram"><i class="ri-instagram-fill"></i></a>
                    <a href="https://youtube.com" target="_blank" class="footer-social-link" aria-label="YouTube"><i class="ri-youtube-fill"></i></a>
                    <a href="https://wa.me/6281234567890" target="_blank" class="footer-social-link" aria-label="WhatsApp"><i class="ri-whatsapp-line"></i></a>
                </div>
            </div>

            <div>
                <h4 class="footer-heading">Navigasi Halaman</h4>
                <ul class="footer-nav-list">
                    <li><a href="{{ route('public.home') }}"><i class="ri-arrow-right-s-line"></i> Beranda Utama</a></li>
                    <li><a href="{{ route('public.about') }}"><i class="ri-arrow-right-s-line"></i> Profil & Visi Misi</a></li>
                    <li><a href="{{ route('public.events') }}"><i class="ri-arrow-right-s-line"></i> Agenda & Acara</a></li>
                    <li><a href="{{ route('public.gallery') }}"><i class="ri-arrow-right-s-line"></i> Dokumentasi Foto</a></li>
                    <li><a href="{{ route('public.contact') }}"><i class="ri-arrow-right-s-line"></i> Lokasi & Hubungi Kami</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-heading">Ibadah Mingguan</h4>
                <div class="footer-contact-list">
                    <div class="footer-contact-item">
                        <i class="ri-sun-line"></i>
                        <div>
                            <strong style="color: #EADEB8;">Ibadah Raya Sesi I</strong><br>
                            Minggu, 08:00 - 10:00 WIB
                        </div>
                    </div>
                    <div class="footer-contact-item">
                        <i class="ri-moon-line"></i>
                        <div>
                            <strong style="color: #EADEB8;">Ibadah Raya Sesi II</strong><br>
                            Minggu, 18:00 - 20:00 WIB
                        </div>
                    </div>
                    <div class="footer-contact-item">
                        <i class="ri-fire-line"></i>
                        <div>
                            <strong style="color: #EADEB8;">Doa Tengah Minggu</strong><br>
                            Rabu, 19:00 WIB
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h4 class="footer-heading">Sekretariat</h4>
                <div class="footer-contact-list">
                    <div class="footer-contact-item">
                        <i class="ri-map-pin-2-line"></i>
                        <div>Jl. Inhoftank No. 12, Bojongloa Kidul, Kota Bandung, Jawa Barat</div>
                    </div>
                    <div class="footer-contact-item">
                        <i class="ri-phone-line"></i>
                        <div>(022) 1234-5678 / 0812-3456-7890</div>
                    </div>
                    <div class="footer-contact-item">
                        <i class="ri-mail-line"></i>
                        <div>sekretariat@gerejafiladelfia.com</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom-bar">
            <div>&copy; {{ date('Y') }} Gereja Filadelfia. Berakar, Bertumbuh, dan Berbuah bagi Kemuliaan Kristus.</div>
            <div>Gereja Filadelfia · Bandung</div>
        </div>
    </footer>

    <!-- Interactive Scripts -->
    <script>
        // Sticky Navbar Scroll
        const navbar = document.getElementById('siteNavbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 30) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile Menu Toggle
        const mobileToggle = document.getElementById('mobileToggle');
        const navLinksList = document.getElementById('navLinksList');
        const mobileIcon = document.getElementById('mobileIcon');

        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                navLinksList.classList.toggle('active');
                if (navLinksList.classList.contains('active')) {
                    mobileIcon.classList.remove('ri-menu-line');
                    mobileIcon.classList.add('ri-close-line');
                } else {
                    mobileIcon.classList.remove('ri-close-line');
                    mobileIcon.classList.add('ri-menu-line');
                }
            });
        }

        // Scroll Observer for .fade-in elements
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.08,
            rootMargin: '0px 0px -30px 0px'
        });

        document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
    </script>

    @stack('scripts')
</body>

</html>
