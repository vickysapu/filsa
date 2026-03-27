<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Filadelfia - @yield('title', 'Admin Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/luxury-core.css?v=2.0') }}">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <aside class="lux-sidebar" id="sidebar">
        <div class="lux-sidebar-brand">FILADELFIA<span>MUSA</span></div>
        <nav>
            <a href="@routeToken('dashboard')" class="lux-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="ri-dashboard-line"></i> Dashboard
            </a>

            @if (auth()->user()->hasPermission('jemaat.view') || auth()->user()->hasPermission('pelayanan.view'))
                <div class="lux-nav-label">Jemaat</div>
            @endif
            @if (auth()->user()->hasPermission('jemaat.view'))
                <a href="@routeToken('admin.jemaat.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.jemaat.*') ? 'active' : '' }}">
                    <i class="ri-user-line"></i> Data Jemaat
                </a>
            @endif
            @if (auth()->user()->hasPermission('pelayanan.view'))
                <a href="@routeToken('admin.jadwal.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.jadwal.*') ? 'active' : '' }}">
                    <i class="ri-calendar-line"></i> Data Pelayanan
                </a>
            @endif
            @if (auth()->user()->hasPermission('pelayanan.view'))
                <a href="@routeToken('admin.jadwal.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.jadwal.*') ? 'active' : '' }}">
                    <i class="ri-calendar-line"></i> Latihan Pelayanan
                </a>
            @endif

            @if (auth()->user()->hasPermission('laporan.view') ||
                    auth()->user()->hasPermission('pemasukan.view') ||
                    auth()->user()->hasPermission('pengeluaran.view') ||
                    auth()->user()->hasPermission('users.view') ||
                    auth()->user()->hasPermission('roles.view') ||
                    auth()->user()->hasPermission('permissions.view'))
                <div class="lux-nav-label">Administrasi</div>
            @endif
            @if (auth()->user()->hasPermission('laporan.view'))
                <a href="@routeToken('admin.keuangan.dashboard')"
                    class="lux-nav-item {{ request()->routeIs('admin.keuangan.*') ? 'active' : '' }}">
                    <i class="ri-bar-chart-line"></i> Laporan
                </a>
            @endif
            @if (auth()->user()->hasPermission('pemasukan.view'))
                <a href="@routeToken('admin.pemasukan.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.pemasukan.*') ? 'active' : '' }}">
                    <i class="ri-wallet-line"></i> Pemasukan
                </a>
            @endif
            @if (auth()->user()->hasPermission('pengeluaran.view'))
                <a href="@routeToken('admin.pengeluaran.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.pengeluaran.*') ? 'active' : '' }}">
                    <i class="ri-bank-card-line"></i> Pengeluaran
                </a>
            @endif
            @if (auth()->user()->hasPermission('event.view') || auth()->user()->hasPermission('dokumentasi.view'))
                <div class="lux-nav-label">Media</div>
            @endif
            @if (auth()->user()->hasPermission('event.view'))
                <a href="@routeToken('admin.events.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                    <i class="ri-calendar-event-line"></i> Event
                </a>
            @endif
            @if (auth()->user()->hasPermission('dokumentasi.view'))
                <a href="@routeToken('admin.dokumentasi.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.dokumentasi.*') ? 'active' : '' }}">
                    <i class="ri-image-line"></i> Dokumentasi
                </a>
            @endif

            @if (auth()->user()->hasPermission('pelayanan.kategori.view') ||
                    auth()->user()->hasPermission('event.kategori.view') ||
                    auth()->user()->hasPermission('dokumentasi.kategori.view'))
                <div class="lux-nav-label">Data Kategori</div>
            @endif
            @if (auth()->user()->hasPermission('pelayanan.kategori.view'))
                <a href="@routeToken('admin.kategori.pelayanan.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.kategori.pelayanan.*') ? 'active' : '' }}">
                    <i class="ri-list-check"></i> Kategori Pelayanan
                </a>
            @endif
            @if (auth()->user()->hasPermission('event.kategori.view'))
                <a href="@routeToken('admin.event-kategori.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.event-kategori.*') ? 'active' : '' }}">
                    <i class="ri-list-check"></i> Kategori Event
                </a>
            @endif
            @if (auth()->user()->hasPermission('dokumentasi.kategori.view'))
                <a href="@routeToken('admin.dokumentasi-kategori.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.dokumentasi-kategori.*') ? 'active' : '' }}">
                    <i class="ri-list-check"></i> Kategori Dokumentasi
                </a>
            @endif

            @if (auth()->user()->hasPermission('meetings.view') ||
                    auth()->user()->hasPermission('roles.view') ||
                    auth()->user()->hasPermission('permissions.view') ||
                    auth()->user()->hasPermission('branches.view'))
                <div class="lux-nav-label">Meeting</div>
            @endif
            @if (auth()->user()->hasPermission('meetings.view'))
                <a href="@routeToken('admin.meetings.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.meetings.*') ? 'active' : '' }}">
                    <i class="ri-vidicon-line"></i> Zoom
                </a>
            @endif

            @if (auth()->user()->hasPermission('users.view') ||
                    auth()->user()->hasPermission('roles.view') ||
                    auth()->user()->hasPermission('permissions.view') ||
                    auth()->user()->hasPermission('branches.view'))
                <div class="lux-nav-label">Pengaturan</div>
            @endif
            @if (auth()->user()->hasPermission('users.view'))
                <a href="@routeToken('admin.users.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="ri-user-line"></i> Users
                </a>
            @endif
            @if (auth()->user()->hasPermission('roles.view'))
                <a href="@routeToken('admin.roles.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="ri-shield-user-line"></i> Roles
                </a>
            @endif
            @if (auth()->user()->hasPermission('permissions.view'))
                <a href="@routeToken('admin.permissions.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                    <i class="ri-lock-line"></i> Permissions
                </a>
            @endif
            @if (auth()->user()->hasPermission('branches.view'))
                <a href="@routeToken('admin.branches.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
                    <i class="ri-building-line"></i> Branch
                </a>
            @endif
            @if (auth()->user()->hasPermission('users.view') ||
                    auth()->user()->hasPermission('roles.view') ||
                    auth()->user()->hasPermission('permissions.view') ||
                    auth()->user()->hasPermission('branches.view'))
                <div class="lux-nav-label">API</div>
            @endif
            @if (auth()->user()->hasPermission('data.access.view'))
                <a href="@routeToken('admin.data-access.requests')"
                    class="lux-nav-item {{ request()->routeIs('admin.data-access.*') ? 'active' : '' }}">
                    <i class="ri-lock-line"></i> Request API
                </a>
            @endif
            @if (auth()->user()->hasPermission('data-tables.view'))
                <a href="@routeToken('admin.data-tables.index')"
                    class="lux-nav-item {{ request()->routeIs('admin.data-tables.*') ? 'active' : '' }}">
                    <i class="ri-lock-line"></i> Request Table
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
                <div class="text-right me-3">
                    <p class="text-bold text-sm">{{ auth()->user()->name }}</p>
                    <p class="text-muted text-sm">{{ auth()->user()->role_names }}</p>
                </div>
                <div class="lux-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                <form action="@routeToken('logout')" method="POST" class="ms-2">
                    @csrf
                    <button type="submit" class="lux-btn lux-btn-ghost lux-btn-sm">
                        <i class="ri-logout-box-line"></i>
                    </button>
                </form>
            </div>
        </header>

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
    @stack('scripts')
</body>

</html>
