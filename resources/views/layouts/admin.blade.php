<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Filadelfia - @yield('title', 'Admin Dashboard')</title>
    <link rel="icon" href="{{ asset('img/olive-tree-logo.svg') }}" type="image/svg+xml">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <!-- Google Fonts: Plus Jakarta Sans & Playfair Display (Pohon Zaitun Biblical Theme) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <style>
        /* ===== CSS VARIABLES - POHON ZAITUN BIBLICAL THEMING ===== */
        :root {
            /* Palette Filosofi Pohon Zaitun */
            --zaitun-deep: #3F5035;       /* Deep Olive: Keteguhan (Sidebar & struktur utama) */
            --zaitun-primary: #718F50;    /* Olive Green: Daun Zaitun (Tombol & navigasi aktif) */
            --zaitun-primary-hover: #5D793F;
            --zaitun-young: #A9C77D;      /* Young Olive: Pembaruan (Aksen, border aktif & badges) */
            --zaitun-gold: #E5D89B;       /* Divine Gold: Kemuliaan (Aksen emas, badges, highlight) */
            --zaitun-cream: #F6EBCB;      /* Light Cream: Kehangatan (Background utama kanvas) */
            --zaitun-white: #FFFFFF;      /* Pure White: Kesucian (Card, modal, form) */
            --zaitun-ripe: #7D4856;       /* Ripe Olive Fruit: Urgent / badge notifikasi */

            /* Compatibility mappings */
            --fig-deep: var(--zaitun-deep);
            --fig-olive: var(--zaitun-primary);
            --fig-olive-hover: var(--zaitun-primary-hover);
            --fig-sage: var(--zaitun-young);
            --fig-burgundy: var(--zaitun-ripe);
            --fig-bark: #856649;
            --fig-ivory: #FBF8EE;

            /* Light Mode Mapping */
            --bg-body: #FBF8EE;           /* Warm soft cream tone */
            --bg-body-warm: #F6EBCB;      /* Light Cream */
            --bg-surface: #FFFFFF;        /* Pure White */
            --bg-sidebar: #3F5035;        /* Deep Olive */
            --bg-sidebar-hover: #4E6242;  /* Rich Olive hover */
            --bg-sidebar-active: #718F50; /* Olive Green active */
            --text-primary: #253320;      /* Deep Olive Charcoal */
            --text-secondary: #5E6E58;    /* Muted olive foliage */
            --text-sidebar: #FBF8EE;      /* Cream on deep olive sidebar */
            --text-sidebar-muted: #A9C77D;/* Young Olive */
            --border-color: #E3DCBF;      /* Warm golden-cream border */
            --border-color-subtle: #EFE9D4;
            --accent-blue: #718F50;       /* Olive Green */
            --accent-blue-hover: #5D793F;
            --shadow-sm: 0 1px 3px rgba(63, 80, 53, 0.05);
            --shadow-md: 0 4px 14px -2px rgba(63, 80, 53, 0.08);
            --shadow-lg: 0 12px 24px -4px rgba(63, 80, 53, 0.12);
            --radius: 12px;
            --radius-sm: 8px;
            --transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        [data-theme="dark"] {
            /* Dark Mode - Deep Olive Grove Variant */
            --bg-body: #1A2318;
            --bg-surface: #243021;
            --bg-sidebar: #131A11;
            --bg-sidebar-hover: #2F3E2B;
            --bg-sidebar-active: #5E7A40;
            --text-primary: #F5F8F2;
            --text-secondary: #A7B89F;
            --text-sidebar: #FBF8EE;
            --text-sidebar-muted: #8FA676;
            --border-color: #344330;
            --border-color-subtle: #293626;
            --accent-blue: #88A666;
            --accent-blue-hover: #9BB878;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.35);
            --shadow-md: 0 4px 14px -2px rgba(0, 0, 0, 0.45);
            --shadow-lg: 0 12px 24px -4px rgba(0, 0, 0, 0.55);
        }

        /* ===== RESET & BASE ===== */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            width: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            line-height: 1.6;
            transition: var(--transition);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6 {
            color: var(--text-primary);
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.01em;
        }

        .font-serif {
            font-family: 'Playfair Display', Georgia, serif !important;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        button {
            font-family: inherit;
        }

        /* ===== LAYOUT ===== */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
            position: relative;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 260px;
            min-width: 260px;
            background: var(--bg-sidebar);
            color: var(--text-sidebar);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            transition: var(--transition);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 16px rgba(52, 72, 59, 0.08);
        }

        .sidebar.collapsed {
            width: 72px;
            min-width: 72px;
        }

        .sidebar-brand {
            padding: 1.5rem 1rem 1.1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.14);
            transition: var(--transition);
            flex-shrink: 0;
            gap: 0.5rem;
        }

        .brand-logo-container {
            width: 96px;
            height: 108px;
            min-width: 96px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.35));
        }

        .brand-logo-img {
            width: 96px;
            height: 108px;
            object-fit: contain;
            transition: transform 0.3s ease;
            display: block;
        }

        .brand-logo-img:hover {
            transform: scale(1.05);
        }

        .brand-text-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow: hidden;
            min-width: 0;
            width: 100%;
            transition: var(--transition);
        }

        .brand-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 700;
            font-size: 1.25rem;
            letter-spacing: 0.08em;
            color: #EEDBBA;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            text-shadow: 0 2px 6px rgba(0,0,0,0.5);
        }

        .brand-tagline {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.58rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--zaitun-gold, #E5D89B);
            opacity: 0.8;
            margin-top: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Collapsed sidebar — show only small icon */
        .sidebar.collapsed .sidebar-brand {
            padding: 0.9rem 0;
            gap: 0;
        }

        .sidebar.collapsed .brand-logo-container {
            width: 44px;
            height: 44px;
            min-width: 44px;
        }

        .sidebar.collapsed .brand-logo-img {
            width: 44px;
            height: 44px;
        }

        .sidebar.collapsed .brand-text-container {
            display: none;
            width: 0;
            overflow: hidden;
        }

        .sidebar-nav {
            padding: 1rem 0;
            flex: 1;
        }

        .nav-label {
            padding: 0.85rem 1.5rem 0.45rem;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-sidebar-muted);
            white-space: nowrap;
        }

        .sidebar.collapsed .nav-label {
            display: none;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.72rem 1.35rem;
            color: rgba(251, 248, 238, 0.85);
            font-weight: 500;
            font-size: 0.88rem;
            border-left: 3.5px solid transparent;
            margin: 2px 8px 2px 0;
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
            transition: var(--transition);
            white-space: nowrap;
            cursor: pointer;
        }

        .nav-item:hover {
            background: var(--bg-sidebar-hover);
            color: #ffffff;
            padding-left: 1.55rem;
        }

        .nav-item.active {
            background: var(--bg-sidebar-active);
            border-left-color: var(--zaitun-young, #A9C77D);
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .nav-item i {
            font-size: 1.15rem;
            width: 20px;
            text-align: center;
            opacity: 0.9;
        }

        .sidebar.collapsed .nav-item span:not(i) {
            display: none;
        }

        .sidebar.collapsed .nav-item {
            justify-content: center;
            padding: 0.75rem;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--zaitun-ripe, #7D4856);
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(125, 72, 86, 0.35);
        }

        .sidebar.collapsed .nav-badge {
            display: none;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            flex: 1;
            min-width: 0;
            margin-left: 260px;
            transition: margin-left 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar.collapsed + .main-content {
            margin-left: 72px;
        }

        /* ===== NAVBAR ===== */
        .navbar {
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border-color);
            padding: 0.875rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: var(--transition);
            box-shadow: 0 1px 4px rgba(52, 72, 59, 0.05);
        }

        .navbar-toggle {
            background: none;
            border: 1px solid transparent;
            color: var(--text-secondary);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.45rem 0.6rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .navbar-toggle:hover {
            background: rgba(102, 118, 83, 0.1);
            color: var(--fig-olive, #667653);
            border-color: rgba(102, 118, 83, 0.2);
        }

        [data-theme="dark"] .navbar-toggle:hover {
            background: rgba(102, 118, 83, 0.15);
            color: var(--fig-sage, #A5AD91);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-info {
            text-align: right;
            line-height: 1.3;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-primary);
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--fig-olive);
            color: #F7F5ED;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            box-shadow: 0 2px 6px rgba(102, 118, 83, 0.25);
        }

        .btn-logout {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .btn-logout:hover {
            color: var(--fig-burgundy);
            background: rgba(117, 70, 83, 0.12);
        }

        /* ===== ALERTS ===== */
        .alert-custom {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--fig-olive);
            border-radius: var(--radius-sm);
            padding: 0.875rem 1.25rem;
            margin: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: var(--shadow-sm);
            animation: slideIn 0.3s ease;
        }

        .alert-custom.success {
            border-left-color: var(--fig-olive);
        }

        .alert-custom.success i {
            color: var(--fig-olive);
        }

        .alert-custom.error {
            border-left-color: var(--fig-burgundy);
        }

        .alert-custom.error i {
            color: var(--fig-burgundy);
        }

        .alert-custom i {
            font-size: 1.2rem;
        }

        .alert-custom span {
            flex: 1;
            font-size: 0.9rem;
        }

        .alert-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0.7;
            transition: var(--transition);
        }

        .alert-close:hover {
            opacity: 1;
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

        /* ===== CONTENT AREA ===== */
        .content-area {
            padding: 1.5rem;
            flex: 1;
            min-width: 0;
            overflow-x: hidden;
        }

        /* ===== THEME TOGGLE ===== */
        .theme-toggle {
            background: none;
            border: 1px solid transparent;
            color: var(--text-secondary);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 0.45rem 0.6rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            margin-right: 0.25rem;
        }

        .theme-toggle:hover {
            color: var(--fig-olive, #667653);
            background: rgba(102, 118, 83, 0.1);
            border-color: rgba(102, 118, 83, 0.2);
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
            background: var(--fig-sage, #A5AD91);
            border-radius: 3px;
            opacity: 0.5;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--fig-olive, #667653);
            opacity: 0.8;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(165, 173, 145, 0.35);
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(165, 173, 145, 0.6);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0 !important;
            }

            .overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: var(--transition);
            }

            .overlay.active {
                opacity: 1;
                visibility: visible;
            }
        }

        /* ===== UTILITIES ===== */
        .card-custom {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .card-custom:hover {
            box-shadow: var(--shadow-md);
            border-color: rgba(165, 173, 145, 0.4);
        }

        .btn-custom {
            background: var(--fig-olive);
            color: #FFFFFF !important;
            border: none;
            padding: 0.55rem 1.35rem;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.9rem;
            transition: var(--transition);
            box-shadow: 0 2px 6px rgba(102, 118, 83, 0.25);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-custom:hover {
            background: var(--fig-olive-hover);
            color: #FFFFFF !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 118, 83, 0.35);
        }

        .btn-custom:active {
            transform: translateY(0);
        }

        .text-muted-custom {
            color: var(--text-secondary) !important;
        }

        .border-custom {
            border-color: var(--border-color) !important;
        }

        /* ===== AUTO CLOSE ALERT ===== */
        [data-auto-close] {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== DROPDOWN USER MENU ===== */
        .dropdown-menu {
            background: var(--bg-surface) !important;
            border: 1px solid var(--border-color) !important;
            box-shadow: var(--shadow-lg) !important;
            border-radius: var(--radius) !important;
            animation: fadeIn 0.2s ease;
        }

        .dropdown-item {
            color: var(--text-primary) !important;
            font-size: 0.88rem;
            font-weight: 500;
            border-radius: var(--radius-sm) !important;
            margin: 2px 6px;
            padding: 0.5rem 0.85rem;
            transition: var(--transition);
        }

        .dropdown-item:hover {
            background: rgba(102, 118, 83, 0.12) !important;
            color: var(--fig-deep) !important;
        }

        .dropdown-item:hover i {
            color: var(--fig-olive) !important;
        }

        .dropdown-item.text-danger:hover {
            background: rgba(117, 70, 83, 0.15) !important;
            color: var(--fig-burgundy) !important;
        }

        .dropdown-item.text-danger:hover i {
            color: var(--fig-burgundy) !important;
        }

        .dropdown-divider {
            border-color: var(--border-color) !important;
            margin: 0.25rem 0 !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-select-custom-dark {
            background-color: var(--bg-body) !important;
            color: var(--text-primary) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: var(--radius-sm) !important;
            transition: var(--transition);
        }

        .form-select-custom-dark:focus {
            background-color: var(--bg-surface) !important;
            border-color: var(--fig-olive, #667653) !important;
            box-shadow: 0 0 0 3px rgba(102, 118, 83, 0.15) !important;
            color: var(--text-primary) !important;
        }

        .form-select-custom-dark {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        }

        [data-theme="dark"] .form-select-custom-dark {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%239EAEA2' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        }

        .text-dashboard-title {
            color: var(--text-primary) !important;
            transition: var(--transition);
        }

        .hover-bg-light:hover .text-dashboard-title {
            color: var(--fig-olive, #667653) !important;
        }

        .table {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: transparent;
            --bs-table-hover-bg: rgba(102, 118, 83, 0.04);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .table {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(255, 255, 255, 0.03);
            --bs-table-hover-bg: rgba(102, 118, 83, 0.08);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .table td,
        .table th {
            background-color: transparent !important;
            color: inherit;
            border-bottom-color: var(--border-color);
        }

        /* Table header subtle background */
        .table thead th {
            background-color: var(--bg-body) !important;
            border-bottom: 2px solid var(--border-color) !important;
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-secondary) !important;
        }

        /* ===== GLOBAL DATATABLE FIXES ===== */
        /* Prevent cell content from squishing — override per-cell with wrap-cell */
        .table td, .table th {
            white-space: nowrap;
            vertical-align: middle;
        }
        /* Columns that NEED wrapping (apply class to <td>) */
        .table td.wrap-cell {
            white-space: normal;
            min-width: 220px;
            max-width: 340px;
            word-break: break-word;
        }
        /* Amount / money column */
        .table td.amount-cell {
            white-space: nowrap !important;
            min-width: 140px;
        }
        /* Date column */
        .table td.date-cell {
            white-space: nowrap;
            min-width: 110px;
        }
        /* Location column */
        .table td.location-cell {
            white-space: normal;
            min-width: 140px;
            max-width: 200px;
            word-break: break-word;
        }
        /* Action column */
        .table td.action-cell {
            white-space: nowrap;
            min-width: 90px;
        }

        /* ===== DATATABLE TOOLBAR ===== */
        .dt-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-surface);
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .dt-toolbar .dt-entries {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dt-toolbar .dt-entries select {
            width: auto;
            min-width: 70px;
            padding: 0.3rem 0.5rem;
            font-size: 0.85rem;
            background: var(--bg-body);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: var(--radius-sm);
        }
        .dt-toolbar .dt-entries select:focus {
            outline: none;
            border-color: var(--zaitun-primary);
            box-shadow: 0 0 0 2px rgba(113,143,80,0.15);
        }
        .dt-toolbar .dt-entries label,
        .dt-toolbar .dt-info {
            font-size: 0.82rem;
            color: var(--text-secondary);
        }
        .dt-toolbar .dt-search {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .dt-toolbar .dt-search input {
            padding: 0.3rem 0.6rem 0.3rem 2rem;
            font-size: 0.85rem;
            background: var(--bg-body);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: var(--radius-sm);
            min-width: 180px;
            transition: var(--transition);
        }
        .dt-toolbar .dt-search input:focus {
            outline: none;
            border-color: var(--zaitun-primary);
            box-shadow: 0 0 0 2px rgba(113,143,80,0.15);
        }
        .dt-toolbar .dt-search .dt-search-wrap {
            position: relative;
        }
        .dt-toolbar .dt-search .dt-search-wrap i {
            position: absolute;
            left: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 0.9rem;
            pointer-events: none;
        }
        .dt-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1.25rem;
            border-top: 1px solid var(--border-color);
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .dt-footer .dt-info {
            font-size: 0.82rem;
            color: var(--text-secondary);
        }
        @media (max-width: 576px) {
            .dt-toolbar { flex-direction: column; align-items: flex-start; }
            .dt-toolbar .dt-search input { min-width: 140px; }
        }

        /* ===== PERBAIKAN INPUT & DATE PICKER UNTUK DARK MODE ===== */

        /* 1. Pastikan warna teks isi input mengikuti tema */
        .form-control-custom,
        .form-select-custom {
            background-color: var(--bg-surface);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: var(--fig-olive, #667653) !important;
            box-shadow: 0 0 0 3px rgba(102, 118, 83, 0.15) !important;
        }

        /* Bootstrap form-control, form-select base override */
        .form-control:focus,
        .form-select:focus {
            border-color: var(--fig-olive, #667653) !important;
            box-shadow: 0 0 0 3px rgba(102, 118, 83, 0.12) !important;
        }

        /* Checkbox & radio accent */
        .form-check-input:checked {
            background-color: var(--fig-olive, #667653) !important;
            border-color: var(--fig-olive, #667653) !important;
        }

        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(0);
            cursor: pointer;
            transition: var(--transition);
        }

        [data-theme="dark"] input[type="date"]::-webkit-calendar-picker-indicator,
        [data-theme="dark"] input[type="time"]::-webkit-calendar-picker-indicator,
        [data-theme="dark"] input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            opacity: 0.7;
        }

        input[type="date"]::-webkit-calendar-picker-indicator:hover,
        [data-theme="dark"] input[type="date"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }

        .form-control-custom::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }

        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        textarea:-webkit-autofill,
        textarea:-webkit-autofill:hover,
        textarea:-webkit-autofill:focus,
        select:-webkit-autofill,
        select:-webkit-autofill:hover,
        select:-webkit-autofill:focus {
            -webkit-text-fill-color: var(--text-primary);
            -webkit-box-shadow: 0 0 0px 1000px var(--bg-surface) inset;
            transition: background-color 5000s ease-in-out 0s;
        }

        /* ===== PELAYANAN CARD ===== */
        .pelayanan-card {
            transition: var(--transition);
        }

        .pelayanan-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
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
    <div class="admin-wrapper">
        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            @php
                $branchId = auth()->user()->branch_id;
                $query = $branchId ? \App\Models\ChurchProfile::where('branch_id', $branchId)->first() : \App\Models\ChurchProfile::first();
                $namaGereja = $query ? $query->church_name : 'Filadelfia';
                // Shorten long name: "Gereja Kristen Filadelfia" -> "Filadelfia"
                $displayName = strlen($namaGereja) > 16
                    ? collect(explode(' ', $namaGereja))->last()
                    : $namaGereja;
            @endphp
            <div class="sidebar-brand">
                <div class="brand-logo-container">
                    <img src="{{ asset('img/olive-tree-logo.svg') }}?v={{ time() }}" alt="Logo Filadelfia" class="brand-logo-img">
                </div>
                <div class="brand-text-container">
                    <span class="brand-title">Filadelfia</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="@routeToken('dashboard')" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="ri-dashboard-line"></i>
                    <span>Dashboard</span>
                    <span id="dashboard-notif-badge" class="nav-badge d-none">0</span>
                </a>

                @if (auth()->user()->hasPermission('jemaat.view') || auth()->user()->hasPermission('pelayanan.view'))
                    <div class="nav-label">Jemaat & Pelayanan</div>
                @endif

                @if (auth()->user()->hasPermission('jemaat.view'))
                    <a href="@routeToken('admin.jemaat.index')"
                        class="nav-item {{ request()->routeIs('admin.jemaat.*') ? 'active' : '' }}">
                        <i class="ri-user-line"></i>
                        <span>Data Jemaat</span>
                        <span id="jemaat-notif-badge" class="nav-badge d-none">0</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('pelayanan.view'))
                    <a href="@routeToken('admin.jadwal.index')"
                        class="nav-item {{ request()->routeIs('admin.jadwal.*') ? 'active' : '' }}">
                        <i class="ri-calendar-line"></i>
                        <span>Data Pelayanan</span>
                        <span id="pelayanan-notif-badge" class="nav-badge d-none">0</span>
                    </a>
                    <a href="@routeToken('admin.latihan-pelayanan.index')"
                        class="nav-item {{ request()->routeIs('admin.latihan-pelayanan.*') ? 'active' : '' }}">
                        <i class="ri-calendar-event-line"></i>
                        <span>Latihan Pelayanan</span>
                        <span id="latihan-notif-badge" class="nav-badge d-none">0</span>
                    </a>
                @endif

                <!-- STRUKTUR GEREJA -->
                @if (auth()->user()->hasPermission('church_structure.view'))
                    <div class="nav-label">Struktur Gereja</div>
                    <a href="@routeToken('admin.church_structure.index')"
                        class="nav-item {{ request()->routeIs('admin.church_structure.*') ? 'active' : '' }}">
                        <i class="ri-tree-line"></i>
                        <span>Struktur Gereja</span>
                    </a>
                @endif

                <!-- KEUANGAN -->
                @php
                    $hasFinanceAccess =
                        auth()->user()->hasPermission('finance.transaction.view') ||
                        auth()->user()->hasPermission('finance.report.view') ||
                        auth()->user()->hasPermission('finance.center.view') ||
                        auth()->user()->hasPermission('finance.manage.all');
                    $isPusatCabang =
                        auth()->user()->hasPermission('finance.center.view') ||
                        auth()->user()->hasPermission('finance.center.approve');
                    $isSuperAdmin = auth()->user()->hasPermission('finance.manage.all');
                @endphp

                @if ($hasFinanceAccess)
                    <div class="nav-label">Keuangan</div>
                @endif

                @if (auth()->user()->hasPermission('finance.transaction.view'))
                    <a href="@routeToken('admin.finance.transactions.index')"
                        class="nav-item {{ request()->routeIs('admin.finance.transactions.*') ? 'active' : '' }}">
                        <i class="ri-exchange-line"></i>
                        <span>Transaksi</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('finance.report.view'))
                    <a href="@routeToken('admin.finance.reports.index', ['scope' => 'internal'])"
                        class="nav-item {{ request()->routeIs('admin.finance.reports.index') ? 'active' : '' }}">
                        <i class="ri-file-list-3-line"></i>
                        <span>Laporan Saya</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('finance.report.create'))
                    <a href="@routeToken('admin.finance.reports.create')"
                        class="nav-item {{ request()->routeIs('admin.finance.reports.create') ? 'active' : '' }}">
                        <i class="ri-add-circle-line"></i>
                        <span>Buat Laporan</span>
                    </a>
                @endif

                @if ($isPusatCabang)
                    @php
                        $branchId = auth()->user()->roles->first()?->branch_id;
                        $pendingCount = $branchId
                            ? \App\Models\FinancialReport::where('submitted_to', $branchId)
                                ->where('status', 'submitted')
                                ->count()
                            : 0;
                    @endphp
                    <a href="@routeToken('admin.finance.reports.received')"
                        class="nav-item {{ request()->routeIs('admin.finance.reports.received') ? 'active' : '' }}">
                        <i class="ri-inbox-archive-line"></i>
                        <span>Laporan Diterima</span>
                        @if ($pendingCount > 0)
                            <span class="nav-badge">{{ $pendingCount }}</span>
                        @endif
                    </a>
                @endif

                @if ($isSuperAdmin)
                    <div class="nav-label">Admin Pusat</div>
                    <a href="@routeToken('admin.finance.reports.index', ['scope' => 'all'])"
                        class="nav-item {{ request()->routeIs('admin.finance.reports.index') ? 'active' : '' }}">
                        <i class="ri-global-line"></i>
                        <span>Semua Laporan</span>
                    </a>
                    <a href="@routeToken('admin.finance.transactions.index')"
                        class="nav-item {{ request()->routeIs('admin.finance.transactions.index') ? 'active' : '' }}">
                        <i class="ri-database-2-line"></i>
                        <span>Semua Transaksi</span>
                    </a>
                    <a href="@routeToken('admin.finance.reports.received')"
                        class="nav-item {{ request()->routeIs('admin.finance.reports.received') ? 'active' : '' }}">
                        <i class="ri-stack-line"></i>
                        <span>Konsolidasi Pusat</span>
                    </a>
                @endif

                <!-- MEDIA & EVENT -->
                @if (auth()->user()->hasPermission('event.view') || auth()->user()->hasPermission('dokumentasi.view'))
                    <div class="nav-label">Media & Event</div>
                @endif

                @if (auth()->user()->hasPermission('event.view'))
                    <a href="@routeToken('admin.events.index')"
                        class="nav-item {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                        <i class="ri-calendar-event-line"></i>
                        <span>Event</span>
                        <span id="event-notif-badge" class="nav-badge d-none">0</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('dokumentasi.view'))
                    <a href="@routeToken('admin.dokumentasi.index')"
                        class="nav-item {{ request()->routeIs('admin.dokumentasi.*') ? 'active' : '' }}">
                        <i class="ri-image-line"></i>
                        <span>Dokumentasi</span>
                    </a>
                @endif

                <!-- INVENTARIS -->
                @if (auth()->user()->hasPermission('inventaris.view'))
                    <div class="nav-label">Inventaris</div>
                    <a href="@routeToken('admin.inventaris.index')"
                        class="nav-item {{ request()->routeIs('admin.inventaris.*') ? 'active' : '' }}">
                        <i class="ri-archive-line"></i>
                        <span>Data Inventaris</span>
                    </a>
                @endif

                <!-- KATEGORI -->
                @if (auth()->user()->hasPermission('pelayanan.kategori.view') ||
                        auth()->user()->hasPermission('event.kategori.view') ||
                        auth()->user()->hasPermission('dokumentasi.kategori.view') ||
                        auth()->user()->hasPermission('finance.categories.view'))
                    <div class="nav-label">Data Kategori</div>
                @endif

                @foreach (['finance.categories.view' => ['admin.finance.transaction-categories.index', 'ri-list-check', 'Kategori Transaksi'], 'pelayanan.kategori.view' => ['admin.kategori.pelayanan.index', 'ri-list-check', 'Kategori Pelayanan'], 'event.kategori.view' => ['admin.event-kategori.index', 'ri-list-check', 'Kategori Event'], 'dokumentasi.kategori.view' => ['admin.dokumentasi-kategori.index', 'ri-list-check', 'Kategori Dokumentasi']] as $perm => $data)
                    @if (auth()->user()->hasPermission($perm))
                        <a href="@routeToken($data[0])"
                            class="nav-item {{ request()->routeIs(str_replace('.index', '.*', $data[0])) ? 'active' : '' }}">
                            <i class="{{ $data[1] }}"></i>
                            <span>{{ $data[2] }}</span>
                        </a>
                    @endif
                @endforeach

                <!-- MEETING & PROFILE -->
                @if (auth()->user()->hasPermission('meetings.view'))
                    <div class="nav-label">Meeting & Profile</div>
                    <a href="@routeToken('admin.meetings.index')"
                        class="nav-item {{ request()->routeIs('admin.meetings.*') ? 'active' : '' }}">
                        <i class="ri-vidicon-line"></i>
                        <span>Zoom Meeting</span>
                    </a>
                @endif

                <!-- Perbaikan: Menggunakan tag <a> langsung agar konsisten dengan item lain -->
                <a href="{{ route('admin.church_profile.index') }}"
                    class="nav-item {{ request()->routeIs('admin.church_profile.*') ? 'active' : '' }}">
                    <i class="ri-building-line"></i>
                    <span>Profile Gereja</span>
                </a>

                <!-- PENGATURAN -->
                @if (auth()->user()->hasPermission('users.view') ||
                        auth()->user()->hasPermission('roles.view') ||
                        auth()->user()->hasPermission('permissions.view') ||
                        auth()->user()->hasPermission('branches.view'))
                    <div class="nav-label">Pengaturan Sistem</div>
                @endif

                @foreach (['users.view' => ['admin.users.index', 'ri-user-line', 'Users'], 'roles.view' => ['admin.roles.index', 'ri-shield-user-line', 'Roles'], 'permissions.view' => ['admin.permissions.index', 'ri-lock-line', 'Permissions'], 'branches.view' => ['admin.branches.index', 'ri-building-line', 'Branch']] as $perm => $data)
                    @if (auth()->user()->hasPermission($perm))
                        <a href="@routeToken($data[0])"
                            class="nav-item {{ request()->routeIs(str_replace('.index', '.*', $data[0])) ? 'active' : '' }}">
                            <i class="{{ $data[1] }}"></i>
                            <span>{{ $data[2] }}</span>
                        </a>
                    @endif
                @endforeach

                <!-- API / DATA ACCESS -->
                @if (auth()->user()->hasPermission('data.access.view') || auth()->user()->hasPermission('data-tables.view'))
                    <div class="nav-label">API & Data</div>
                @endif

                @if (auth()->user()->hasPermission('data.access.view'))
                    <a href="@routeToken('admin.data-access.requests')"
                        class="nav-item {{ request()->routeIs('admin.data-access.*') ? 'active' : '' }}">
                        <i class="ri-key-line"></i>
                        <span>Request API</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('data-tables.view'))
                    <a href="@routeToken('admin.data-tables.index')"
                        class="nav-item {{ request()->routeIs('admin.data-tables.*') ? 'active' : '' }}">
                        <i class="ri-table-line"></i>
                        <span>Request Table</span>
                    </a>
                @endif
            </nav>
        </aside>

        <!-- OVERLAY (Mobile) -->
        <div class="overlay" id="overlay"></div>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- NAVBAR -->
            <header class="navbar">
                <button class="navbar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <i class="ri-menu-line"></i>
                </button>

            <div class="d-flex align-items-center gap-3 ms-auto">

                <!-- === TOMBOL LIHAT WEBSITE === -->
                <a href="{{ route('public.home') }}" target="_blank" class="btn btn-light d-flex align-items-center gap-2 border shadow-sm px-3 py-2"
                    style="background: var(--bg-surface); border-color: var(--border-color) !important; border-radius: var(--radius-sm); font-size: 0.825rem; color: var(--text-primary); text-decoration: none;"
                    title="Buka Landing Page Website">
                    <i class="ri-global-line fs-5" style="color: var(--fig-olive);"></i>
                    <span class="d-none d-md-inline fw-medium">Lihat Website</span>
                </a>

                <!-- === NOTIFIKASI DROPDOWN === -->
                <div class="dropdown notification-dropdown">
                    <button class="btn btn-light position-relative border-0 shadow-sm" type="button"
                        id="notifDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false"
                        onclick="fetchNotifications(true)"
                        style="background: var(--bg-surface); border: 1px solid var(--border-color) !important; border-radius: var(--radius-sm);">
                        <i class="ri-notification-3-line fs-5 text-secondary"></i>
                        <!-- Badge Angka Burgundy -->
                        <span id="notifBadge"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill d-none"
                            style="font-size: 0.6rem; z-index: 2; background-color: var(--fig-burgundy) !important; color: white;">
                            0
                        </span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0"
                        aria-labelledby="notifDropdownBtn"
                        style="width: 350px; max-height: 400px; overflow: hidden; z-index: 1060; border-radius: var(--radius);">
                        
                        <div class="p-3 border-bottom d-flex justify-content-between align-items-center"
                            style="background: var(--bg-body);">
                            <h6 class="m-0 fw-bold small" style="color: var(--text-primary);">Notifikasi</h6>
                            <small class="fw-medium" style="cursor:pointer; font-size: 0.75rem; color: var(--fig-olive);"
                                onclick="markAllRead()">Tandai Dibaca</small>
                        </div>

                        <div id="notifList" style="max-height: 300px; overflow-y: auto; scroll-behavior: smooth;">
                            <div class="p-4 text-center text-muted small">
                                <div class="spinner-border spinner-border-sm" style="color: var(--fig-olive);" role="status"></div>
                                <div class="mt-2">Memuat...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- USER MENU -->
                <div class="user-menu d-flex align-items-center gap-2 position-relative">
                    <div class="user-info d-none d-sm-block text-end">
                        <p class="user-name mb-0 fw-bold small" style="color: var(--text-primary);">{{ auth()->user()->name }}</p>
                        <p class="user-role mb-0 text-muted" style="font-size: 0.75rem; color: var(--text-secondary) !important;">
                            {{ auth()->user()->role_names }}
                        </p>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-link p-0 border-0 text-decoration-none dropdown-toggle"
                            type="button" id="userDropdownBtn" data-bs-toggle="dropdown"
                            data-bs-auto-close="outside" aria-expanded="false">
                            <div class="user-avatar text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                style="width: 38px; height: 38px; font-weight: bold; cursor: pointer; background: var(--fig-olive);">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2"
                            aria-labelledby="userDropdownBtn" style="min-width: 200px; z-index: 1060;">
                            <li class="px-3 py-2 border-bottom">
                                <small class="text-muted">Masuk sebagai</small>
                                <div class="fw-bold text-truncate">{{ auth()->user()->name }}</div>
                            </li>
                            <li><a class="dropdown-item py-2" href="#"><i
                                        class="ri-user-line me-2 text-secondary"></i> Profil</a></li>
                            <li><a class="dropdown-item py-2" href="#"><i
                                        class="ri-settings-4-line me-2 text-secondary"></i> Pengaturan</a></li>
                            <li>
                                <hr class="dropdown-divider my-2">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="w-100">
                                    @csrf
                                    <button type="submit"
                                        class="dropdown-item py-2 text-danger w-100 text-start">
                                        <i class="ri-logout-box-line me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            </header>

            <!-- ALERTS -->
            @if (session('success'))
                <div class="alert-custom success" data-auto-close>
                    <i class="ri-checkbox-circle-fill"></i>
                    <span>{{ session('success') }}</span>
                    <button class="alert-close">&times;</button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert-custom error" data-auto-close>
                    <i class="ri-error-warning-fill"></i>
                    <span>{{ session('error') }}</span>
                    <button class="alert-close">&times;</button>
                </div>
            @endif

            <!-- CONTENT -->
            <div class="content-area">
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Setup CSRF Token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let isFetchingNotif = false;

            // --- FUNGSI NOTIFIKASI ---
            
            function fetchNotifications(force = false) {
                if (isFetchingNotif) return;
                
                isFetchingNotif = true;
                const listContainer = $('#notifList');
                
                const badgeUmum = $('#dashboard-notif-badge');     // Source: UMUM / DASHBOARD
                const badgeJemaat = $('#jemaat-notif-badge');      // Source: JEMAAT
                const badgePelayanan = $('#pelayanan-notif-badge');// Source: PELAYANAN (Perlu ditambahkan HTML nya di sidebar jika belum ada)
                const badgeLatihan = $('#latihan-notif-badge');    // Source: LATIHAN (Perlu ditambahkan HTML nya di sidebar jika belum ada)
                const badgeEvent = $('#event-notif-badge');        // Source: EVENT (Perlu ditambahkan HTML nya di sidebar jika belum ada)
                const badgeGlobal = $('#notifBadge');              // Badge di Navbar (Total Semua)

                $.ajax({
                    url: '/api/notifications',
                    type: 'GET',
                    timeout: 5000,
                    success: function(data) {
                        let listHtml = '';
                        
                        // Counter per Kategori
                        let counts = {
                            'UMUM': 0,
                            'JEMAAT': 0,
                            'PELAYANAN': 0,
                            'LATIHAN': 0,
                            'EVENT': 0,
                            'TOTAL': 0
                        };

                        // 1. Hitung Kategori & Generate List HTML
                        data.forEach(function(notif) {
                            // Ambil source/feature, default ke UMUM jika kosong
                            let feature = (notif.feature || notif.data?.source || 'UMUM').toUpperCase();
                            
                            // Increment counter
                            if (counts[feature] !== undefined) {
                                counts[feature]++;
                            } else {
                                // Jika ada source baru yang tidak terduga, masukkan ke UMUM atau buat baru
                                counts['UMUM']++; 
                            }
                            counts['TOTAL']++;

                            // Generate HTML List Item
                            let title = notif.data?.title || 'Notifikasi';
                            let message = notif.data?.message || '';
                            let url = notif.data?.url || '#';
                            let icon = notif.data?.icon || 'ri-notification-3-line';
                            let color = notif.data?.color || 'text-primary';
                            let time = formatTimeAgo(notif.created_at);

                            listHtml += `
                                <a href="${url}" class="dropdown-item border-bottom py-3 notif-item hover-bg-light" onclick="handleNotifClick('${notif.id}', '${url}', event)">
                                    <div class="d-flex align-items-start">
                                        <div class="me-2 mt-1">
                                            <i class="${icon} ${color} fs-5"></i>
                                        </div>
                                        <div style="width: 100%;">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="badge bg-light text-dark border" style="font-size: 0.65rem; font-weight: 600;">${feature}</span>
                                                <span class="text-muted" style="font-size: 0.7rem;">${time}</span>
                                            </div>
                                            <div class="fw-bold small text-truncate" style="max-width: 180px;">${title}</div>
                                            <div class="text-muted small text-truncate">${message}</div>
                                        </div>
                                    </div>
                                </a>
                            `;
                        });

                        // 2. Update Badges di Sidebar & Navbar
                        updateBadge(badgeUmum, counts['UMUM']);
                        updateBadge(badgeJemaat, counts['JEMAAT']);
                        updateBadge(badgePelayanan, counts['PELAYANAN']);
                        updateBadge(badgeLatihan, counts['LATIHAN']);
                        updateBadge(badgeEvent, counts['EVENT']);
                        updateBadge(badgeGlobal, counts['TOTAL']);

                        // 3. Render List jika Dropdown sedang terbuka atau force refresh
                        if ($('#notifDropdownBtn').attr('aria-expanded') === 'true' || force || listContainer.html().includes('Memuat')) {
                            if (counts['TOTAL'] === 0) {
                                listHtml = `
                                    <div class="p-4 text-center text-muted small">
                                        <i class="ri-checkbox-circle-line fs-1 text-success opacity-50"></i>
                                        <div class="mt-2">Tidak ada notifikasi baru</div>
                                    </div>`;
                            }
                            listContainer.html(listHtml);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.warn('Notif Error:', error);
                        if(listContainer.html().includes('Memuat')) {
                             listContainer.html('<div class="p-3 text-center text-danger small">Gagal memuat notifikasi</div>');
                        }
                    },
                    complete: function() {
                        isFetchingNotif = false;
                    }
                });
            }

            function updateBadge(element, count) {
                if (!element || !element.length) return;
                if (count > 0) {
                    element.removeClass('d-none').text(count > 99 ? '99+' : count);
                } else {
                    element.addClass('d-none').text('0');
                }
            }

            function formatTimeAgo(dateString) {
                if (!dateString) return 'Baru saja';
                const date = new Date(dateString);
                const now = new Date();
                const seconds = Math.floor((now - date) / 1000);
                
                if (seconds < 60) return 'Baru saja';
                if (seconds < 3600) return Math.floor(seconds / 60) + ' mnt lalu';
                if (seconds < 86400) return Math.floor(seconds / 3600) + ' jam lalu';
                return Math.floor(seconds / 86400) + ' hari lalu';
            }

            // Handler Klik Notifikasi
            window.handleNotifClick = function(id, url, event) {
                // Mark as read via AJAX
                $.post(`/api/notifications/${id}/read`).fail(function(){
                    console.log('Gagal mark read');
                });
                // Biarkan link berjalan normal
            };

            // Mark All Read
            window.markAllRead = function() {
                if (confirm('Tandai semua notifikasi sebagai dibaca?')) {
                    $.post('/api/notifications/read-all', function() {
                        fetchNotifications(true); // Refresh list segera
                    });
                }
            };

            // Jalankan pertama kali
            fetchNotifications(true);
            
            // Interval polling (setiap 15 detik)
            setInterval(() => fetchNotifications(false), 15000);


            // --- LOGIKA SIDEBAR & UI LAINNYA ---

            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mainContent = document.querySelector('.main-content');

            function isMobile() {
                return window.innerWidth < 992;
            }

            function updateMainContentMargin() {
                if (!mainContent || isMobile()) return;
                if (sidebar && sidebar.classList.contains('collapsed')) {
                    mainContent.style.marginLeft = '72px';
                } else {
                    mainContent.style.marginLeft = '260px';
                }
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    if (isMobile()) {
                        if (sidebar) sidebar.classList.toggle('mobile-open');
                        if (overlay) overlay.classList.toggle('active');
                    } else {
                        if (sidebar) {
                            sidebar.classList.toggle('collapsed');
                            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                            updateMainContentMargin();
                        }
                    }
                });
            }

            if (overlay) {
                overlay.addEventListener('click', () => {
                    if (sidebar) sidebar.classList.remove('mobile-open');
                    overlay.classList.remove('active');
                });
            }

            // Restore Sidebar State
            if (!isMobile() && localStorage.getItem('sidebarCollapsed') === 'true' && sidebar) {
                sidebar.classList.add('collapsed');
            }
            updateMainContentMargin();

            // Auto Close Alerts
            document.querySelectorAll('[data-auto-close]').forEach(alert => {
                const closeBtn = alert.querySelector('.alert-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => alert.remove());
                }
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

            // Token Navigation Helper
            const currentToken = '{{ request()->attributes->get("page_token") ?? "" }}';
            if (currentToken && window.location.pathname.startsWith('/admin')) {
                document.querySelectorAll('a[href*="/admin"]').forEach(link => {
                    try {
                        const url = new URL(link.href, window.location.origin);
                        if (url.pathname.startsWith('/admin')) {
                            url.searchParams.set('_token_nav', currentToken);
                            link.href = url.toString();
                        }
                    } catch (e) {}
                });
            }
        });

        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const mainContent = document.querySelector('.main-content');
            if (window.innerWidth >= 992) {
                if (sidebar) sidebar.classList.remove('mobile-open');
                if (overlay) overlay.classList.remove('active');
                if (localStorage.getItem('sidebarCollapsed') === 'true' && sidebar) {
                    sidebar.classList.add('collapsed');
                    if (mainContent) mainContent.style.marginLeft = '72px';
                } else {
                    if (mainContent) mainContent.style.marginLeft = '260px';
                }
            } else {
                if (mainContent) mainContent.style.marginLeft = '0';
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
