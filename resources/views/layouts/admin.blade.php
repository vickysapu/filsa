<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Filadelfia - @yield('title', 'Admin Dashboard')</title>
    <link rel="icon" href="{{ asset('img/logo.png') }}">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <style>
        /* ===== CSS VARIABLES - THEMING ===== */
        :root {
            /* Light Mode */
            --bg-body: #f8fafc;
            --bg-surface: #ffffff;
            --bg-sidebar: #1e3a8a;
            --bg-sidebar-hover: #3b82f6;
            --bg-sidebar-active: #2563eb;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-sidebar: #ffffff;
            --text-sidebar-muted: #cbd5e1;
            --border-color: #e2e8f0;
            --accent-blue: #2563eb;
            --accent-blue-hover: #1d4ed8;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --radius: 12px;
            --radius-sm: 8px;
            --transition: all 0.2s ease;
        }

        [data-theme="dark"] {
            /* Dark Mode - Full Gelap */
            --bg-body: #0f172a;
            --bg-surface: #1e293b;
            --bg-sidebar: #020617;
            --bg-sidebar-hover: #1e3a8a;
            --bg-sidebar-active: #1d4ed8;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-sidebar: #e2e8f0;
            --text-sidebar-muted: #64748b;
            --border-color: #334155;
            --accent-blue: #3b82f6;
            --accent-blue-hover: #60a5fa;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
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

        /* ===== LAYOUT ===== */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 260px;
            background: var(--bg-sidebar);
            color: var(--text-sidebar);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            transition: var(--transition);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
        }

        .sidebar.collapsed {
            width: 72px;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            font-weight: 700;
            font-size: 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            white-space: nowrap;
        }

        .sidebar-brand span {
            font-size: 0.75rem;
            font-weight: 400;
            opacity: 0.85;
            background: rgba(255, 255, 255, 0.15);
            padding: 2px 8px;
            border-radius: 20px;
        }

        .sidebar.collapsed .sidebar-brand span {
            display: none;
        }

        .sidebar-nav {
            padding: 1rem 0;
            flex: 1;
        }

        .nav-label {
            padding: 0.75rem 1.5rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
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
            padding: 0.75rem 1.5rem;
            color: var(--text-sidebar);
            font-weight: 500;
            font-size: 0.9rem;
            border-left: 3px solid transparent;
            transition: var(--transition);
            white-space: nowrap;
            cursor: pointer;
        }

        .nav-item:hover {
            background: var(--bg-sidebar-hover);
            color: #fff;
        }

        .nav-item.active {
            background: var(--bg-sidebar-active);
            border-left-color: #fff;
            color: #fff;
            font-weight: 600;
        }

        .nav-item i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
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
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 600;
        }

        .sidebar.collapsed .nav-badge {
            display: none;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            flex: 1;
            margin-left: 260px;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .sidebar.collapsed+.main-content {
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
        }

        .navbar-toggle {
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .navbar-toggle:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        [data-theme="dark"] .navbar-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
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
            background: var(--accent-blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
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
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        /* ===== ALERTS ===== */
        .alert-custom {
            background: var(--bg-surface);
            border-left: 4px solid var(--accent-blue);
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
            padding: 0.875rem 1.25rem;
            margin: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: var(--shadow-sm);
            animation: slideIn 0.3s ease;
        }

        .alert-custom.success {
            border-left-color: #22c55e;
        }

        .alert-custom.error {
            border-left-color: #ef4444;
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
        }

        /* ===== THEME TOGGLE ===== */
        .theme-toggle {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            margin-right: 0.5rem;
        }

        .theme-toggle:hover {
            color: var(--accent-blue);
            background: rgba(37, 99, 235, 0.1);
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
            background: var(--text-secondary);
            border-radius: 3px;
            opacity: 0.3;
        }

        ::-webkit-scrollbar-thumb:hover {
            opacity: 0.5;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
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
        }

        .btn-custom {
            background: var(--accent-blue);
            color: white;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: var(--radius-sm);
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-custom:hover {
            background: var(--accent-blue-hover);
            transform: translateY(-1px);
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
            animation: fadeIn 0.2s ease;
        }

        .dropdown-item {
            color: var(--text-primary) !important;
            font-size: 0.9rem;
            border-radius: var(--radius-sm) !important;
            margin: 2px 4px;
            transition: var(--transition);
        }

        .dropdown-item:hover {
            background: var(--bg-sidebar-hover) !important;
            color: #fff !important;
        }

        .dropdown-item:hover i {
            color: #fff !important;
        }

        .dropdown-item.text-danger:hover {
            background: rgba(239, 68, 68, 0.15) !important;
            color: #ef4444 !important;
        }

        .dropdown-item.text-danger:hover i {
            color: #ef4444 !important;
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
            transition: var(--transition);
        }

        .form-select-custom-dark:focus {
            background-color: var(--bg-surface) !important;
            border-color: var(--accent-blue) !important;
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.15);
            color: var(--text-primary) !important;
        }

        .form-select-custom-dark {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        }

        [data-theme="dark"] .form-select-custom-dark {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        }

        .text-dashboard-title {
            color: var(--text-primary) !important;
            transition: var(--transition);
        }

        .hover-bg-light:hover .text-dashboard-title {
            color: var(--accent-blue) !important;
        }

        .table {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: transparent;
            --bs-table-hover-bg: rgba(0, 0, 0, 0.075);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .table {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(255, 255, 255, 0.05);
            --bs-table-hover-bg: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .table td,
        .table th {
            background-color: transparent !important;
            color: inherit;
            border-bottom-color: var(--border-color);
        }

        /* ===== PERBAIKAN INPUT & DATE PICKER UNTUK DARK MODE ===== */

        /* 1. Pastikan warna teks isi input mengikuti tema */
        .form-control-custom,
        .form-select-custom {
            background-color: var(--bg-surface);
            color: var(--text-primary);
            /* Penting: Agar teks ketikan terlihat */
            border-color: var(--border-color);
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
                $query = $branchId ? \App\Models\ChurchProfile::where('branch_id', $branchId)->first() : null;
                $namaGereja = $query ? $query->church_name : 'Filadelfia';
            @endphp
            <div class="sidebar-brand">
                <i class="ri-church-line"></i>
                {{ $namaGereja }}
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

                @if (auth()->user()->hasPermission('finance.report.view'))
                    <a href="{{ route('admin.finance.churches.index') }}"
                        class="nav-item {{ request()->routeIs('admin.finance.churches.*') ? 'active' : '' }}">
                        <i class="ri-price-tag-3-line"></i>
                        <span>Data Item</span>
                    </a>
                @endif
{{-- 
                @if (auth()->user()->hasPermission('finance.report.create'))
                    <a href="@routeToken('admin.finance.reports.create')"
                        class="nav-item {{ request()->routeIs('admin.finance.reports.create') ? 'active' : '' }}">
                        <i class="ri-add-circle-line"></i>
                        <span>Buat Laporan</span>
                    </a>
                @endif --}}

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

                <!-- === NOTIFIKASI DROPDOWN (PERBAIKAN) === -->
                <div class="dropdown notification-dropdown">
                    <button class="btn btn-light position-relative border-0 shadow-sm" type="button"
                        id="notifDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false"
                        onclick="fetchNotifications(true)"> <!-- Fetch saat diklik juga -->
                        <i class="ri-notification-3-line fs-5 text-secondary"></i>
                        <!-- Badge Angka Merah -->
                        <span id="notifBadge"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"
                            style="font-size: 0.6rem; z-index: 2;">
                            0
                        </span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0"
                        aria-labelledby="notifDropdownBtn"
                        style="width: 350px; max-height: 400px; overflow: hidden; z-index: 1060;">
                        
                        <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-dark small">Notifikasi</h6>
                            <small class="text-primary fw-medium" style="cursor:pointer; font-size: 0.75rem;"
                                onclick="markAllRead()">Tandai Dibaca</small>
                        </div>

                        <div id="notifList" style="max-height: 300px; overflow-y: auto; scroll-behavior: smooth;">
                            <div class="p-4 text-center text-muted small">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                <div class="mt-2">Memuat...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- USER MENU (Tetap sama, pastikan z-index aman) -->
                <div class="user-menu d-flex align-items-center gap-2 position-relative">
                    <div class="user-info d-none d-sm-block text-end">
                        <p class="user-name mb-0 fw-bold small">{{ auth()->user()->name }}</p>
                        <p class="user-role mb-0 text-muted" style="font-size: 0.75rem;">
                            {{ auth()->user()->role_names }}
                        </p>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-link p-0 border-0 text-decoration-none dropdown-toggle"
                            type="button" id="userDropdownBtn" data-bs-toggle="dropdown"
                            data-bs-auto-close="outside" aria-expanded="false">
                            <div class="user-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                style="width: 38px; height: 38px; font-weight: bold; cursor: pointer;">
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

            function isMobile() {
                return window.innerWidth < 992;
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
            if (window.innerWidth >= 992) {
                if (sidebar) sidebar.classList.remove('mobile-open');
                if (overlay) overlay.classList.remove('active');
                if (localStorage.getItem('sidebarCollapsed') === 'true' && sidebar) {
                    sidebar.classList.add('collapsed');
                }
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
