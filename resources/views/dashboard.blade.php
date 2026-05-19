@extends('layouts.admin')

@section('title', 'Dashboard Gereja')

@section('content')
    <div class="dashboard-wrapper">

        <!-- Welcome Section -->
        <div class="card-custom mb-4 welcome-card position-relative overflow-hidden">
            <div class="card-body p-4 position-relative z-1">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <h1 class="h3 mb-2 fw-bold text-dashboard-welcome">Shalom, {{ $user->name }}! 👋</h1>
                        <p class="mb-4 dashboard-quote">"Segala perkara dapat kutanggung di dalam Dia yang memberi kekuatan
                            kepadaku." - Filipi 4:13</p>

                        <div class="d-flex flex-wrap gap-3">
                            <div class="dashboard-badge">
                                <i class="ri-calendar-check-line"></i>
                                <span class="small">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</span>
                            </div>
                            <div class="dashboard-badge">
                                <i class="ri-time-line"></i>
                                <span class="small" id="clock">00:00:00</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-end d-none d-lg-block">
                        <div class="user-avatar-large d-inline-flex align-items-center justify-content-center">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="position-absolute top-0 end-0 mt-n5 me-n5 dashboard-bg-icon">
                <i class="ri-church-line"></i>
            </div>
        </div>

        <!-- Main Stats Grid -->
        <div class="row g-3 mb-4">
            <!-- Total Jemaat -->
            <div class="col-md-9 col-xl-4">
                <div class="stat-card card-custom h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon stat-icon-primary rounded-3">
                                <i class="ri-team-line fs-4"></i>
                            </div>
                            <span class="badge badge-success-soft rounded-pill">+12%</span>
                        </div>
                        <h3 class="fw-bold mb-1">{{ number_format($stats['total_jemaat'] ?? 0) }}</h3>
                        <p class="text-muted-custom small mb-0">Total Jemaat Terdaftar</p>
                    </div>
                </div>
            </div>
{{-- 
            <!-- Saldo Kas Gereja -->
            <div class="col-md-6 col-xl-3">
                <div class="stat-card card-custom h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon stat-icon-success rounded-3">
                                <i class="ri-wallet-3-line fs-4"></i>
                            </div>
                            <span class="badge badge-success-soft rounded-pill">Aman</span>
                        </div>
                        <h3 class="fw-bold mb-1 text-success-custom">Rp
                            {{ number_format($stats['saldo_kas'] ?? 0, 0, ',', '.') }}</h3>
                        <p class="text-muted-custom small mb-0">Saldo Kas Gereja</p>
                    </div>
                </div>
            </div> --}}

            <!-- Event Bulan Ini -->
            <div class="col-md-9 col-xl-4">
                <div class="stat-card card-custom h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon stat-icon-warning rounded-3">
                                <i class="ri-calendar-event-line fs-4"></i>
                            </div>
                            <span class="badge badge-warning-soft rounded-pill">{{ $stats['event_bulan_ini'] ?? 0 }}
                                Baru</span>
                        </div>
                        <h3 class="fw-bold mb-1">{{ number_format($stats['total_event'] ?? 0) }}</h3>
                        <p class="text-muted-custom small mb-0">Total Event & Kegiatan</p>
                    </div>
                </div>
            </div>

            <!-- Permohonan Doa -->
            <div class="col-md-9 col-xl-4">
                <div class="stat-card card-custom h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon stat-icon-info rounded-3">
                                <i class="ri-heart-pulse-line fs-4"></i>
                            </div>
                            <span class="badge badge-danger-soft rounded-pill">{{ $stats['doa_urgent'] ?? 0 }} Urgent</span>
                        </div>
                        <h3 class="fw-bold mb-1">{{ number_format($stats['total_doa'] ?? 0) }}</h3>
                        <p class="text-muted-custom small mb-0">Permohonan Doa Aktif</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Chart Section -->
            <div class="col-lg-8">
                <div class="card-custom h-100">
                    <div
                        class="card-header-custom border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-semibold"><i class="ri-bar-chart-box-line me-2 text-primary-custom"></i>Grafik
                            Persembahan & Kehadiran</h6>
                        <select class="form-select form-select-sm w-auto form-select-custom-dark">
                            <option value="6">6 Bulan Terakhir</option>
                            <option value="12">Tahun Ini</option>
                        </select>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-container d-flex align-items-end justify-content-between gap-2"
                            style="height: 250px;">
                            @for ($i = 1; $i <= 6; $i++)
                                <div class="d-flex flex-column align-items-center gap-2 w-100">
                                    <div class="d-flex gap-1 align-items-end w-100 justify-content-center"
                                        style="height: 200px;">
                                        <div class="bg-primary-custom rounded-top"
                                            style="width: 20px; height: {{ rand(40, 100) }}%; opacity: 0.8;"
                                            title="Persembahan"></div>
                                        <div class="bg-success-custom rounded-top"
                                            style="width: 20px; height: {{ rand(30, 90) }}%; opacity: 0.6;"
                                            title="Kehadiran"></div>
                                    </div>
                                    <span class="text-muted-custom small">{{ date('M', strtotime("-$i months")) }}</span>
                                </div>
                            @endfor
                        </div>
                        <div class="d-flex justify-content-center gap-4 mt-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle bg-primary-custom" style="width: 10px; height: 10px;"></span>
                                <small class="text-muted-custom">Persembahan</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle bg-success-custom" style="width: 10px; height: 10px;"></span>
                                <small class="text-muted-custom">Kehadiran Ibadah</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-custom h-100">
                    <div class="card-header-custom border-bottom py-3 px-4">
                        <h6 class="mb-0 fw-semibold"><i class="ri-calendar-todo-line me-2 text-warning-custom"></i>Jadwal
                            Dekat</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($upcoming_events ?? [] as $evt)
                                <div class="list-group-item px-4 py-3 border-bottom border-custom"
                                    style="background-color: var(--bg-surface);">
                                    <div class="d-flex gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="date-box bg-primary-soft text-primary-custom rounded-3 text-center p-2"
                                                style="min-width: 50px;">
                                                <span
                                                    class="d-block fw-bold lh-1">{{ $evt->tanggal_mulai->format('d') }}</span>
                                                <small class="lh-1 text-uppercase"
                                                    style="font-size: 0.6rem;">{{ $evt->tanggal_mulai->format('M') }}</small>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-semibold text-truncate text-dashboard-title"
                                                title="{{ $evt->nama_event }}">
                                                {{ $evt->nama_event }}
                                            </h6>
                                            <p class="mb-0 small text-muted-custom">
                                                <i class="ri-time-line me-1"></i> {{ $evt->tanggal_mulai->format('H:i') }}
                                                WIB
                                                <br>
                                                <i class="ri-map-pin-line me-1"></i> {{ Str::limit($evt->lokasi, 20) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-muted-custom">
                                    <i class="ri-calendar-line fs-1 opacity-50"></i>
                                    <p class="mt-2 small">Tidak ada jadwal dekat</p>
                                </div>
                            @endforelse
                        </div>
                        <div class="p-3 text-center border-top border-custom"
                            style="background-color: var(--bg-surface);">
                            <a href="{{ route('admin.events.index') }}"
                                class="btn btn-sm btn-link text-decoration-none text-primary-custom">Lihat Semua Jadwal</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card-custom h-100">
                    <div class="card-header-custom border-bottom py-3 px-4 bg-warning-soft">
                        <h6 class="mb-0 fw-semibold text-warning-custom"><i class="ri-gift-line me-2"></i>Ulang Tahun
                            Bulan Ini</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($birthdays ?? [] as $bday)
                                <!-- PERBAIKAN: Hapus class 'card-custom' dari li, gunakan style inline -->
                                <li class="list-group-item px-4 py-3 d-flex align-items-center gap-3"
                                    style="background-color: var(--bg-surface); border-bottom: 1px solid var(--border-color);">
                                    <div class="avatar-initials rounded-circle bg-warning-custom text-white d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px; font-weight: bold;">
                                        {{ substr($bday->nama_lengkap, 0, 1) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-semibold small" style="color: var(--text-primary);">
                                            {{ $bday->nama_lengkap }}</h6>
                                        <small class="text-muted-custom">{{ $bday->tanggal_lahir->format('d M') }}
                                            ({{ $bday->umur }} thn)
                                        </small>
                                    </div>
                                    <a href="#" class="btn btn-sm btn-outline-warning rounded-circle"><i
                                            class="ri-whatsapp-line"></i></a>
                                </li>
                            @empty
                                <li class="list-group-item px-4 py-4 text-center text-muted-custom small"
                                    style="background-color: var(--bg-surface);">
                                    Tidak ada ulang tahun bulan ini.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Recent Prayer Requests -->
            {{-- <div class="col-md-6 col-lg-4">
                <div class="card-custom h-100">
                    <div class="card-header-custom border-bottom py-3 px-4 bg-info-soft">
                        <h6 class="mb-0 fw-semibold text-info-custom"><i class="ri-heart-hand-line me-2"></i>Doa Terbaru
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-3" style="background-color: var(--bg-surface);">
                            @forelse($prayer_requests ?? [] as $prayer)
                                <div class="mb-3 pb-3 border-bottom border-custom last-no-border">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-semibold small"
                                            style="color: var(--text-primary);">{{ $prayer->nama_pengirim ?? 'Anonim' }}</span>
                                        <span
                                            class="badge bg-body-tertiary text-muted-custom border">{{ $prayer->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="small text-muted-custom mb-2 fst-italic">
                                        "{{ Str::limit($prayer->isi_doa, 80) }}"</p>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-xs btn-outline-primary py-0"
                                            style="font-size: 0.7rem;">Amin
                                            (<span>{{ $prayer->jml_amen ?? 0 }}</span>)
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted-custom small py-4">
                                    Belum ada permohonan doa.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div> --}}

            <div class="col-lg-6">
                <div class="card-custom h-100">
                    <div class="card-header-custom border-bottom py-3 px-4">
                        <h6 class="mb-0 fw-semibold"><i class="ri-shield-keyhole-line me-2 text-secondary"></i>Aktivitas
                            Admin</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <!-- Hapus class 'table-hover' jika ada -->
                            <table class="table mb-0 align-middle small"
                                style="margin-bottom: 0; background-color: var(--bg-surface);">
                                <tbody>
                                    @forelse($recentLogins as $activity)
                                        <!-- PENTING: background-color: transparent !important untuk mengalahkan default bootstrap -->
                                        <tr
                                            style="
                                            background-color: transparent !important; 
                                            border-bottom: 1px solid var(--border-color);">

                                            <td class="py-3 px-4">
                                                <div class="d-flex align-items-center gap-2">
                                                    @if ($activity->success)
                                                        <i class="ri-login-circle-line"
                                                            style="color: #22c55e; font-size: 1.2rem;"></i>
                                                    @else
                                                        <i class="ri-error-warning-line"
                                                            style="color: #ef4444; font-size: 1.2rem;"></i>
                                                    @endif

                                                    <div>
                                                        <!-- Gunakan var(--text-primary) agar teks jadi putih di dark mode -->
                                                        <div class="fw-semibold" style="color: var(--text-primary);">
                                                            {{ $activity->ip_address }}
                                                        </div>
                                                        <!-- Gunakan var(--text-secondary) agar teks jadi abu-abu terang di dark mode -->
                                                        <div style="color: var(--text-secondary); font-size: 0.75rem;">
                                                            {{ $activity->attempted_at->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="py-3 px-4 text-end">
                                                @if ($activity->success)
                                                    <span
                                                        style="
                                                        background-color: rgba(34, 197, 94, 0.15); 
                                                        color: #22c55e; 
                                                        padding: 0.35em 0.65em; 
                                                        border-radius: 20px; 
                                                        font-size: 0.75rem; 
                                                        font-weight: 600;">
                                                        Sukses
                                                    </span>
                                                @else
                                                    <span
                                                        style="
                                                        background-color: rgba(239, 68, 68, 0.15); 
                                                        color: #ef4444; 
                                                        padding: 0.35em 0.65em; 
                                                        border-radius: 20px; 
                                                        font-size: 0.75rem; 
                                                        font-weight: 600;">
                                                        Gagal
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr style="background-color: transparent !important;">
                                            <td colspan="2" class="py-4 text-center"
                                                style="color: var(--text-secondary);">
                                                Tidak ada aktivitas login.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <style>
        /* ... (Style sebelumnya tetap sama) ... */
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

        .welcome-card {
            background: linear-gradient(135deg, var(--accent-blue) 0%, #4f46e5 100%);
            color: white;
        }

        [data-theme="dark"] .welcome-card {
            background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
        }

        .text-dashboard-welcome {
            color: white !important;
        }

        .dashboard-quote {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .dashboard-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dashboard-bg-icon {
            font-size: 10rem;
            color: rgba(255, 255, 255, 0.1);
            pointer-events: none;
        }

        .user-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 2rem;
            font-weight: 600;
            backdrop-filter: blur(5px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .stat-card {
            transition: var(--transition);
            border-left: 4px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-left-color: var(--accent-blue);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Custom Colors */
        .text-primary-custom {
            color: var(--accent-blue) !important;
        }

        .text-success-custom {
            color: #22c55e !important;
        }

        .text-warning-custom {
            color: #f59e0b !important;
        }

        .text-info-custom {
            color: #3b82f6 !important;
        }

        .text-danger-custom {
            color: #ef4444 !important;
        }

        .bg-primary-custom {
            background-color: var(--accent-blue) !important;
        }

        .bg-success-custom {
            background-color: #22c55e !important;
        }

        .bg-warning-custom {
            background-color: #f59e0b !important;
        }

        /* Soft Backgrounds */
        .stat-icon-primary {
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--accent-blue);
        }

        .stat-icon-success {
            background-color: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .stat-icon-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .stat-icon-info {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .bg-primary-soft {
            background-color: rgba(37, 99, 235, 0.1) !important;
        }

        .bg-warning-soft {
            background-color: rgba(245, 158, 11, 0.1) !important;
        }

        .bg-info-soft {
            background-color: rgba(59, 130, 246, 0.1) !important;
        }

        .badge-success-soft {
            background-color: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .badge-warning-soft {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .badge-danger-soft {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .last-no-border:last-child {
            border-bottom: none !important;
        }

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
        }
    </style>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID');
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
@endsection
