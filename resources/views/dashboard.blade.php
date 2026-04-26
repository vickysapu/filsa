@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="dashboard-wrapper">

        <!-- Welcome Section -->
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <h1 class="h3 mb-0 fw-bold">Selamat Datang, {{ $user->name }}! 👋</h1>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-time-line text-muted-custom"></i>
                                <span class="text-muted-custom small">
                                    Login terakhir:
                                    {{ $user->last_login_at ? $user->last_login_at->format('d M Y, H:i') : 'Pertama kali' }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <i class="ri-shield-line text-muted-custom"></i>
                                <span class="text-muted-custom small me-1">Role:</span>
                                @foreach ($user->roles as $role)
                                    <span
                                        class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">
                                        {{ $role->display_name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="user-avatar-large d-inline-flex align-items-center justify-content-center">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="row g-4 mb-4">
            <!-- Total Users -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card card-custom h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted-custom text-uppercase small mb-2 fw-semibold letter-spacing-05">Total
                                    Users</p>
                                <h3 class="mb-0 fw-bold text-primary">{{ number_format($stats['total_users']) }}</h3>
                            </div>
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="ri-user-3-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card card-custom h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted-custom text-uppercase small mb-2 fw-semibold letter-spacing-05">Users
                                    Aktif</p>
                                <h3 class="mb-0 fw-bold text-success">{{ number_format($stats['active_users']) }}</h3>
                            </div>
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="ri-user-follow-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Logins -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card card-custom h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted-custom text-uppercase small mb-2 fw-semibold letter-spacing-05">Login
                                    (7 Hari)</p>
                                <h3 class="mb-0 fw-bold text-info">{{ number_format($stats['recent_logins']) }}</h3>
                            </div>
                            <div class="stat-icon bg-info bg-opacity-10 text-info">
                                <i class="ri-login-circle-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Failed Logins -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card card-custom h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted-custom text-uppercase small mb-2 fw-semibold letter-spacing-05">Login
                                    Gagal</p>
                                <h3 class="mb-0 fw-bold text-danger">{{ number_format($stats['failed_logins']) }}</h3>
                            </div>
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                <i class="ri-error-warning-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Login Activity -->
        <div class="card-custom">
            <div class="card-header-custom border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-history-line text-primary fs-5"></i>
                    <h5 class="mb-0 fw-semibold">Riwayat Login Anda</h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-body-tertiary">
                            <tr>
                                <th
                                    class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold letter-spacing-05">
                                    Tanggal</th>
                                <th
                                    class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold letter-spacing-05">
                                    IP Address</th>
                                <th
                                    class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold letter-spacing-05">
                                    Device</th>
                                <th
                                    class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold letter-spacing-05">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLogins as $activity)
                                <tr class="border-bottom border-custom">
                                    <td class="py-3 px-4">
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold">{{ $activity->attempted_at->diffForHumans() }}</span>
                                            <small
                                                class="text-muted-custom">{{ $activity->attempted_at->format('d M Y, H:i') }}</small>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <code class="code-custom">{{ $activity->ip_address }}</code>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="d-flex align-items-center gap-2">
                                            @if (str_contains($activity->user_agent, 'Chrome'))
                                                <i class="ri-chrome-line text-primary"></i>
                                                <span>Chrome</span>
                                            @elseif(str_contains($activity->user_agent, 'Firefox'))
                                                <i class="ri-firefox-line text-orange"></i>
                                                <span>Firefox</span>
                                            @elseif(str_contains($activity->user_agent, 'Safari'))
                                                <i class="ri-safari-line text-info"></i>
                                                <span>Safari</span>
                                            @elseif(str_contains($activity->user_agent, 'Edge'))
                                                <i class="ri-edge-line text-success"></i>
                                                <span>Edge</span>
                                            @else
                                                <i class="ri-global-line text-muted-custom"></i>
                                                <span class="text-muted-custom">Other</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($activity->success)
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                                                <i class="ri-check-line me-1"></i> Berhasil
                                            </span>
                                        @else
                                            <span
                                                class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2">
                                                <i class="ri-close-line me-1"></i> Gagal
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-5 text-center">
                                        <div class="text-muted-custom">
                                            <i class="ri-inbox-line fs-1 d-block mb-3 opacity-50"></i>
                                            <p class="mb-0">Belum ada riwayat login</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <style>
        /* Dashboard Specific Styles */
        .dashboard-wrapper {
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .letter-spacing-05 {
            letter-spacing: 0.5px;
        }

        /* User Avatar Large */
        .user-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--accent-blue);
            color: white;
            font-size: 2rem;
            font-weight: 600;
            box-shadow: var(--shadow-md);
        }

        /* Stat Cards */
        .stat-card {
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Code Tag Custom */
        .code-custom {
            background-color: var(--bg-body);
            color: var(--text-primary);
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            border: 1px solid var(--border-color);
            font-family: 'Courier New', monospace;
        }

        /* Table Custom */
        .table> :not(caption)>*>* {
            background-color: var(--bg-surface);
        }

        .table-hover>tbody>tr:hover>* {
            background-color: var(--bg-body);
        }

        /* Card Header Custom */
        .card-header-custom {
            background-color: var(--bg-surface);
            border-bottom: 1px solid var(--border-color) !important;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .user-avatar-large {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
            }

            .stat-card .card-body {
                padding: 1.5rem !important;
            }
        }

        /* Dark Mode Specific */
        [data-theme="dark"] .bg-body-tertiary {
            background-color: var(--bg-sidebar) !important;
        }

        /* Badge Improvements */
        .badge {
            font-weight: 500;
            font-size: 0.85rem;
        }

        /* Orange color for Firefox */
        .text-orange {
            color: #e67e22 !important;
        }

        .bg-orange {
            background-color: #e67e22 !important;
        }
    </style>
@endsection
