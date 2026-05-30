<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Filadelfia - @yield('title', 'Admin Dashboard')</title>
    <link rel="icon" href="{{ asset('img/logo.jpeg') }}">

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
    </style>
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
                </a>

                @if (auth()->user()->hasPermission('jemaat.view') || auth()->user()->hasPermission('pelayanan.view'))
                    <div class="nav-label">Jemaat</div>
                @endif
                @if (auth()->user()->hasPermission('jemaat.view'))
                    <a href="@routeToken('admin.jemaat.index')"
                        class="nav-item {{ request()->routeIs('admin.jemaat.*') ? 'active' : '' }}">
                        <i class="ri-user-line"></i>
                        <span>Data Jemaat</span>
                    </a>
                @endif
                @if (auth()->user()->hasPermission('pelayanan.view'))
                    <a href="@routeToken('admin.jadwal.index')"
                        class="nav-item {{ request()->routeIs('admin.jadwal.*') ? 'active' : '' }}">
                        <i class="ri-calendar-line"></i>
                        <span>Data Pelayanan</span>
                    </a>
                    <a href="@routeToken('admin.latihan-pelayanan.index')"
                        class="nav-item {{ request()->routeIs('admin.latihan-pelayanan.*') ? 'active' : '' }}">
                        <i class="ri-calendar-event-line"></i>
                        <span>Latihan Pelayanan</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('church_structure.view'))
                    <div class="nav-label"><i class="ri-org-chart-line"></i> Struktur Gereja</div>
                    <a href="@routeToken('admin.church_structure.index')"
                        class="nav-item {{ request()->routeIs('admin.church_structure.*') ? 'active' : '' }}">
                        <i class="ri-tree-line"></i>
                        <span>Struktur Gereja</span>
                    </a>
                @endif

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
                    <div class="nav-label"><i class="ri-wallet-3-line"></i> Keuangan</div>
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
                    <div class="nav-label"><i class="ri-shield-keyhole-line"></i> Admin Pusat</div>
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

                @if (auth()->user()->hasPermission('event.view') || auth()->user()->hasPermission('dokumentasi.view'))
                    <div class="nav-label">Media</div>
                @endif
                @if (auth()->user()->hasPermission('event.view'))
                    <a href="@routeToken('admin.events.index')"
                        class="nav-item {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                        <i class="ri-calendar-event-line"></i>
                        <span>Event</span>
                    </a>
                @endif
                @if (auth()->user()->hasPermission('dokumentasi.view'))
                    <a href="@routeToken('admin.dokumentasi.index')"
                        class="nav-item {{ request()->routeIs('admin.dokumentasi.*') ? 'active' : '' }}">
                        <i class="ri-image-line"></i>
                        <span>Dokumentasi</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermission('inventaris.view'))
                    <div class="nav-label">Inventaris</div>
                    <a href="@routeToken('admin.inventaris.index')"
                        class="nav-item {{ request()->routeIs('admin.inventaris.*') ? 'active' : '' }}">
                        <i class="ri-archive-line"></i>
                        <span>Data Inventaris</span>
                    </a>
                @endif

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

                @if (auth()->user()->hasPermission('meetings.view'))
                    <div class="nav-label">Meeting</div>
                    <a href="@routeToken('admin.meetings.index')"
                        class="nav-item {{ request()->routeIs('admin.meetings.*') ? 'active' : '' }}">
                        <i class="ri-vidicon-line"></i>
                        <span>Zoom</span>
                    </a>
                @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.church_profile.index') }}" 
                        class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.church_profile.*') ? 'active' : '' }}">
                            <i class="ri-building-line"></i>
                            <span>Profile Gereja</span>
                        </a>
                    </li>

                @if (auth()->user()->hasPermission('users.view') ||
                        auth()->user()->hasPermission('roles.view') ||
                        auth()->user()->hasPermission('permissions.view') ||
                        auth()->user()->hasPermission('branches.view'))
                    <div class="nav-label">Pengaturan</div>
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

                @if (auth()->user()->hasPermission('data.access.view') || auth()->user()->hasPermission('data-tables.view'))
                    <div class="nav-label">API</div>
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

                <div class="d-flex align-items-center gap-2">
                    <!-- Theme Toggle -->
                    <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
                        <i class="ri-moon-line" id="themeIcon"></i>
                    </button>

                    <!-- User Menu -->
                    <div class="user-menu">
                        <div class="user-info d-none d-sm-block">
                            <p class="user-name">{{ auth()->user()->name }}</p>
                            <p class="user-role">{{ auth()->user()->role_names }}</p>
                        </div>

                        <!-- Avatar + Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-link p-0 border-0 text-decoration-none dropdown-toggle"
                                type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2"
                                style="min-width: 200px; border-radius: var(--radius);">
                                <!-- Header -->
                                <li class="px-3 py-2 border-bottom border-custom">
                                    <small class="text-muted-custom d-block">Signed in as</small>
                                    <span class="fw-semibold text-truncate d-block"
                                        style="max-width: 150px;">{{ auth()->user()->name }}</span>
                                </li>

                                <!-- Menu Items -->
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                                        href="@routeToken('profile.index')" id="navProfileLink">
                                        <i class="ri-user-line text-muted-custom"></i>
                                        <span>Profil Saya</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                                        href="@routeToken('profile.index')#password-section" id="navPasswordLink">
                                        <i class="ri-lock-line text-muted-custom"></i>
                                        <span>Ubah Password</span>
                                    </a>
                                </li>

                                <li>
                                    <hr class="dropdown-divider border-custom my-1">
                                </li>

                                <!-- Logout -->
                                <li>
                                    <form action="@routeToken('logout')" method="POST" class="w-100">
                                        @csrf
                                        <button type="submit"
                                            class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-danger">
                                            <i class="ri-logout-box-line"></i>
                                            <span>Logout</span>
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

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== SIDEBAR TOGGLE =====
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const sidebarToggle = document.getElementById('sidebarToggle');

            function isMobile() {
                return window.innerWidth < 992;
            }

            sidebarToggle?.addEventListener('click', () => {
                if (isMobile()) {
                    sidebar.classList.toggle('mobile-open');
                    overlay.classList.toggle('active');
                } else {
                    sidebar.classList.toggle('collapsed');
                    // Save preference
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                }
            });

            overlay?.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
            });

            // Restore sidebar state on desktop
            if (!isMobile() && localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
            }

            // ===== THEME TOGGLE =====
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
            const html = document.documentElement;

            // Load saved theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            html.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);

            themeToggle?.addEventListener('click', () => {
                const current = html.getAttribute('data-theme');
                const next = current === 'light' ? 'dark' : 'light';
                html.setAttribute('data-theme', next);
                localStorage.setItem('theme', next);
                updateThemeIcon(next);
            });

            function updateThemeIcon(theme) {
                themeIcon.className = theme === 'light' ? 'ri-moon-line' : 'ri-sun-line';
            }

            // ===== AUTO CLOSE ALERTS =====
            document.querySelectorAll('[data-auto-close]').forEach(alert => {
                const closeBtn = alert.querySelector('.alert-close');
                closeBtn?.addEventListener('click', () => alert.remove());

                // Auto close after 5 seconds
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

            // ===== NAV TOKEN HANDLER (preserve existing logic) =====
            const currentToken = '{{ request()->attributes->get('page_token') ?? '' }}';
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
                document.querySelectorAll('form[action*="/admin"]').forEach(form => {
                    try {
                        const url = new URL(form.action, window.location.origin);
                        if (url.pathname.startsWith('/admin')) {
                            url.searchParams.set('_token_nav', currentToken);
                            form.action = url.toString();
                        }
                    } catch (e) {}
                });
            }

            // ===== ACTIVE NAV ITEM HIGHLIGHT (enhanced) =====
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', function() {
                    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove(
                        'active'));
                    this.classList.add('active');
                });
            });
        });

        // Handle window resize for sidebar
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            if (window.innerWidth >= 992) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
                // Restore collapsed state
                if (localStorage.getItem('sidebarCollapsed') === 'true') {
                    sidebar.classList.add('collapsed');
                }
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
