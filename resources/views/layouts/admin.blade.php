<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>

    <link rel="stylesheet" href="{{ asset('css/luxury-core.css?v=2.0') }}">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

    <!-- Sidebar -->
    <aside class="lux-sidebar" id="sidebar">
        <div class="lux-sidebar-brand">
            FILADELFIA<span>MUSA</span>
        </div>
        <nav>
            <a href="{{ route('dashboard') }}"
                class="lux-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="ri-dashboard-line"></i> Dashboard
            </a>

            @if (auth()->user()->hasPermission('users.view') ||
                    auth()->user()->hasPermission('roles.view') ||
                    auth()->user()->hasPermission('permissions.view'))
                <div class="lux-nav-label">Jemaat</div>
            @endif

            @if (auth()->user()->hasPermission('jemaat.view'))
                <a href="{{ route('admin.jemaat.index') }}"
                    class="lux-nav-item {{ request()->routeIs('admin.jemaat.*') ? 'active' : '' }}">
                    <i class="ri-user-line"></i> Data Jemaat
                </a>
            @endif

            @if (auth()->user()->hasPermission('pelayanan.view'))
                <a href="{{ route('admin.jadwal.index') }}"
                    class="lux-nav-item {{ request()->routeIs('admin.jadwal.*') ? 'active' : '' }}">
                    <i class="ri-user-line"></i> Data Pelayanan
                </a>
            @endif

            @if (auth()->user()->hasPermission('users.view') ||
                    auth()->user()->hasPermission('roles.view') ||
                    auth()->user()->hasPermission('permissions.view'))
                <div class="lux-nav-label">Administrasi</div>
            @endif

            @if (auth()->user()->hasPermission('laporan.view'))
                <a href="{{ route('admin.keuangan.dashboard') }}"
                    class="lux-nav-item {{ request()->routeIs('admin.keuangan.*') ? 'active' : '' }}">
                    <i class="ri-user-line"></i> Laporan
                </a>
            @endif

            @if (auth()->user()->hasPermission('pemasukan.view'))
                <a href="{{ route('admin.pemasukan.index') }}"
                    class="lux-nav-item {{ request()->routeIs('admin.pemasukan.*') ? 'active' : '' }}">
                    <i class="ri-user-line"></i> Pemasukan
                </a>
            @endif

            @if (auth()->user()->hasPermission('pengeluaran.view'))
                <a href="{{ route('admin.pengeluaran.index') }}"
                    class="lux-nav-item {{ request()->routeIs('admin.pengeluaran.*') ? 'active' : '' }}">
                    <i class="ri-user-line"></i> Pengeluaran
                </a>
            @endif

            @if (auth()->user()->hasPermission('users.view') ||
                    auth()->user()->hasPermission('roles.view') ||
                    auth()->user()->hasPermission('permissions.view'))
                <div class="lux-nav-label">Media</div>
            @endif

            @if (auth()->user()->hasPermission('event.view'))
                <a href="{{ route('admin.events.index') }}"
                    class="lux-nav-item {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                    <i class="ri-user-line"></i> Event
                </a>
            @endif

            @if (auth()->user()->hasPermission('dokumentasi.view'))
                <a href="{{ route('admin.dokumentasi.index') }}"
                    class="lux-nav-item {{ request()->routeIs('admin.dokumentasi.*') ? 'active' : '' }}">
                    <i class="ri-user-line"></i> Dokumentasi
                </a>
            @endif

            @if (auth()->user()->hasPermission('users.view') ||
                    auth()->user()->hasPermission('roles.view') ||
                    auth()->user()->hasPermission('permissions.view'))
                <div class="lux-nav-label">Data Kategori</div>
            @endif

            @if (auth()->user()->hasPermission('pelayanan.kategori.view'))
                <a href="{{ route('admin.kategori.pelayanan.index') }}"
                    class="lux-nav-item {{ request()->routeIs('admin.kategori.pelayanan.*') ? 'active' : '' }}">
                    <i class="ri-user-line"></i> Kategori Pelayanan
                </a>
            @endif

            @if (auth()->user()->hasPermission('event.kategori.view'))
                <a href="{{ route('admin.event-kategori.index') }}"
                    class="lux-nav-item {{ request()->routeIs('admin.event-kategori.*') ? 'active' : '' }}">
                    <i class="ri-user-line"></i> Kategori Event
                </a>
            @endif

            @if (auth()->user()->hasPermission('dokumentasi.kategori.view'))
                <a href="{{ route('admin.dokumentasi-kategori.index') }}"
                    class="lux-nav-item {{ request()->routeIs('admin.dokumentasi-kategori.*') ? 'active' : '' }}">
                    <i class="ri-user-line"></i> Kategori Dokumentasi
                </a>
            @endif


            @if (auth()->user()->hasPermission('users.view') ||
                    auth()->user()->hasPermission('roles.view') ||
                    auth()->user()->hasPermission('permissions.view'))
                <div class="lux-nav-label">Pengaturan</div>
            @endif

            @if (auth()->user()->hasPermission('users.view'))
                <a href="{{ route('admin.users.index') }}"
                    class="lux-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="ri-user-line"></i> Users
                </a>
            @endif

            @if (auth()->user()->hasPermission('roles.view'))
                <a href="{{ route('admin.roles.index') }}"
                    class="lux-nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="ri-shield-user-line"></i> Roles
                </a>
            @endif

            @if (auth()->user()->hasPermission('permissions.view'))
                <a href="{{ route('admin.permissions.index') }}"
                    class="lux-nav-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                    <i class="ri-lock-line"></i> Permissions
                </a>
            @endif

        </nav>
    </aside>

    <div class="lux-sidebar-overlay" id="overlay"></div>

    <main class="lux-main-content">
        <header class="lux-navbar flex-between mb-5">
            <button class="lux-btn lux-btn-ghost lux-sidebar-toggle">
                <i class="ri-menu-line"></i>
            </button>

            <div class="flex items-center gap-2">
                <div class="text-right mr-3">
                    <p class="text-bold text-sm">{{ auth()->user()->name }}</p>
                    <p class="text-muted text-sm">{{ auth()->user()->role_names }}</p>
                </div>
                <div class="lux-avatar">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <form action="{{ route('logout') }}" method="POST" class="ml-2">
                    @csrf
                    <button type="submit" class="lux-btn lux-btn-ghost lux-btn-sm">
                        <i class="ri-logout-box-line"></i>
                    </button>
                </form>
            </div>
        </header>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="lux-alert lux-alert-success" data-auto-close>
                <i class="ri-checkbox-circle-line"></i>
                <span>{{ session('success') }}</span>
                <button class="lux-alert-close">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="lux-alert lux-alert-danger" data-auto-close>
                <i class="ri-error-warning-line"></i>
                <span>{{ session('error') }}</span>
                <button class="lux-alert-close">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('js/luxury-core.js?v=2.0') }}"></script>
    @stack('scripts')
</body>

</html>
