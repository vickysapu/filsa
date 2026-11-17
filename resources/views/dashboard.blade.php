@extends('layouts.admin')

@section('title', 'Dashboard Gereja')

@section('content')
    <div class="dashboard-wrapper">

        <!-- Welcome Section -->
        <div class="card-custom mb-4 welcome-card position-relative overflow-hidden">
            <div class="card-body p-4 position-relative z-1">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <h1 class="h3 mb-2 fw-bold text-dashboard-welcome">Shalom, {{ $user->name }}! </h1>
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
            <div class="col-md-4 col-sm-6 col-12">
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

            <!-- Event Bulan Ini -->
            <div class="col-md-4 col-sm-6 col-12">
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
            <div class="col-md-4 col-sm-12 col-12">
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

        <div class="row g-4 mb-4 align-items-start">
            <!-- Chart Section -->
            <div class="col-xl-7 col-lg-7 col-12">
                <div class="card-custom">
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
                            style="height: 210px;">
                            @for ($i = 1; $i <= 6; $i++)
                                <div class="d-flex flex-column align-items-center gap-2 w-100">
                                    <div class="d-flex gap-1 align-items-end w-100 justify-content-center"
                                        style="height: 170px;">
                                        <div class="bg-primary-custom rounded-top"
                                            style="width: 22px; height: {{ rand(45, 95) }}%; opacity: 0.85;"
                                            title="Persembahan"></div>
                                        <div class="bg-success-custom rounded-top"
                                            style="width: 22px; height: {{ rand(35, 85) }}%; opacity: 0.65;"
                                            title="Kehadiran"></div>
                                    </div>
                                    <span class="text-muted-custom small fw-medium">{{ date('M', strtotime("-$i months")) }}</span>
                                </div>
                            @endfor
                        </div>
                        <div class="d-flex justify-content-center gap-4 mt-3 pt-2 border-top border-custom">
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

            <!-- Jadwal Dekat Section -->
            <div class="col-xl-5 col-lg-5 col-12">
                <div class="card-custom">
                    <div class="card-header-custom border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="mb-0 fw-semibold"><i class="ri-calendar-todo-line me-2 text-warning-custom"></i>Jadwal Dekat</h6>
                            <span class="badge rounded-pill bg-primary-soft text-primary-custom" style="font-size: 0.7rem; font-weight: 600;">{{ count($upcoming_events ?? []) }} Agenda</span>
                        </div>
                        <!-- View Mode Switcher: List vs Carousel -->
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="view-toggle-btn active" id="btnViewList" title="Tampilan List (Scroll Vertikal)" onclick="setScheduleView('list')">
                                <i class="ri-list-check"></i>
                            </button>
                            <button type="button" class="view-toggle-btn" id="btnViewCarousel" title="Tampilan Kartu (Scroll Horisontal)" onclick="setScheduleView('carousel')">
                                <i class="ri-slideshow-3-line"></i>
                            </button>
                        </div>
                    </div>

                    <!-- MODE 1: Responsif List View dengan Smooth Scroll -->
                    <div id="scheduleListView" class="card-body p-0">
                        <div class="schedule-scroll-container">
                            @forelse($upcoming_events ?? [] as $evt)
                                <div class="schedule-item">
                                    <div class="d-flex gap-3 align-items-start">
                                        <!-- Date Badge -->
                                        <div class="flex-shrink-0">
                                            <div class="date-box bg-primary-soft text-primary-custom rounded-3 text-center py-2 px-2.5"
                                                style="min-width: 52px;">
                                                <span class="d-block fw-bold lh-1 fs-5">{{ $evt->tanggal_mulai->format('d') }}</span>
                                                <small class="lh-1 text-uppercase fw-bold" style="font-size: 0.62rem; letter-spacing: 0.04em;">{{ $evt->tanggal_mulai->format('M') }}</small>
                                            </div>
                                        </div>

                                        <!-- Content (Full responsive, no truncation) -->
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            @if($evt->kategori)
                                                <span class="badge badge-warning-soft px-2 py-0.5 rounded-pill mb-1" style="font-size: 0.65rem;">
                                                    {{ $evt->kategori->nama_kategori }}
                                                </span>
                                            @endif
                                            <h6 class="mb-1.5 fw-semibold text-dashboard-title" style="font-size: 0.92rem; line-height: 1.4; word-break: break-word;">
                                                {{ $evt->nama_event }}
                                            </h6>
                                            <div class="d-flex flex-wrap align-items-center gap-x-3 gap-y-1 text-muted-custom" style="font-size: 0.78rem;">
                                                <span class="d-inline-flex align-items-center">
                                                    <i class="ri-time-line me-1 text-warning-custom"></i>
                                                    {{ $evt->tanggal_mulai->format('H:i') }} WIB
                                                </span>
                                                <span class="d-inline-flex align-items-center" style="word-break: break-word;">
                                                    <i class="ri-map-pin-line me-1 text-primary-custom"></i>
                                                    {{ $evt->lokasi }}
                                                </span>
                                            </div>
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
                    </div>

                    <!-- MODE 2: Inovasi Carousel Horisontal (Scroll Samping Tanpa Batas) -->
                    <div id="scheduleCarouselView" class="card-body p-0 d-none">
                        <div class="schedule-carousel-container" id="scheduleCarouselTrack">
                            @forelse($upcoming_events ?? [] as $evt)
                                <div class="schedule-card-slide">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="date-box bg-primary-soft text-primary-custom rounded-2 px-2 py-1 d-inline-flex align-items-center gap-1.5">
                                                <i class="ri-calendar-event-line" style="font-size: 0.85rem;"></i>
                                                <span class="fw-bold" style="font-size: 0.82rem;">{{ $evt->tanggal_mulai->format('d M Y') }}</span>
                                            </div>
                                            @if($evt->kategori)
                                                <span class="badge badge-warning-soft px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">
                                                    {{ $evt->kategori->nama_kategori }}
                                                </span>
                                            @endif
                                        </div>
                                        <h6 class="fw-semibold text-dashboard-title mb-2" style="font-size: 0.92rem; line-height: 1.4; word-break: break-word;">
                                            {{ $evt->nama_event }}
                                        </h6>
                                    </div>
                                    <div class="pt-2 border-top border-custom mt-2" style="font-size: 0.78rem;">
                                        <div class="d-flex align-items-center text-muted-custom mb-1">
                                            <i class="ri-time-line me-1.5 text-warning-custom"></i>
                                            <span>{{ $evt->tanggal_mulai->format('H:i') }} WIB</span>
                                        </div>
                                        <div class="d-flex align-items-center text-muted-custom" style="word-break: break-word;">
                                            <i class="ri-map-pin-line me-1.5 text-primary-custom"></i>
                                            <span>{{ $evt->lokasi }}</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-muted-custom w-100">
                                    <p class="mb-0 small">Tidak ada jadwal dekat</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="p-2.5 px-4 text-center border-top border-custom d-flex justify-content-between align-items-center"
                        style="background-color: var(--bg-surface);">
                        <small class="text-muted-custom" style="font-size: 0.75rem;">
                            <i class="ri-scroll-to-bottom-line me-1"></i>Dapat di-scroll
                        </small>
                        <a href="{{ route('admin.events.index') }}"
                            class="btn btn-sm btn-link text-decoration-none text-primary-custom py-0 fw-semibold" style="font-size: 0.82rem;">
                            Lihat Semua Jadwal <i class="ri-arrow-right-s-line"></i>
                        </a>
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
            background: linear-gradient(135deg, var(--zaitun-deep, #3F5035) 0%, #4E6543 50%, var(--zaitun-primary, #718F50) 100%);
            color: #FBF8EE;
            border: 1px solid rgba(229, 216, 155, 0.35);
            box-shadow: 0 10px 25px -5px rgba(63, 80, 53, 0.22);
            border-radius: var(--radius);
        }

        [data-theme="dark"] .welcome-card {
            background: linear-gradient(135deg, #131A11 0%, #202D1B 55%, #34482B 100%);
            border: 1px solid rgba(229, 216, 155, 0.2);
        }

        .text-dashboard-welcome {
            color: #FBF8EE !important;
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .dashboard-quote {
            color: rgba(251, 248, 238, 0.94) !important;
            font-family: 'Playfair Display', Georgia, serif;
            font-style: italic;
            font-size: 1.05rem;
            line-height: 1.6;
        }

        .dashboard-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(251, 248, 238, 0.16);
            backdrop-filter: blur(8px);
            padding: 0.5rem 1.15rem;
            border-radius: 50px;
            color: #FBF8EE;
            border: 1px solid rgba(229, 216, 155, 0.35);
            font-weight: 500;
        }

        .dashboard-bg-icon {
            font-size: 11rem;
            color: rgba(229, 216, 155, 0.08);
            pointer-events: none;
        }

        .user-avatar-large {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(229, 216, 155, 0.4), rgba(113, 143, 80, 0.5));
            color: #FBF8EE;
            font-size: 2.2rem;
            font-weight: 700;
            font-family: 'Playfair Display', Georgia, serif;
            backdrop-filter: blur(8px);
            border: 2px solid rgba(229, 216, 155, 0.6);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.18);
        }

        .stat-card {
            transition: var(--transition);
            border-left: 4px solid transparent;
            background: var(--bg-surface);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-left-color: var(--zaitun-primary, #718F50);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Custom Colors (Pohon Zaitun Palette) */
        .text-primary-custom {
            color: var(--zaitun-primary, #718F50) !important;
        }

        .text-success-custom {
            color: #3F5035 !important;
        }

        .text-warning-custom {
            color: #8F7226 !important;
        }

        .text-info-custom {
            color: var(--zaitun-ripe, #7D4856) !important;
        }

        .text-danger-custom {
            color: var(--zaitun-ripe, #7D4856) !important;
        }

        .bg-primary-custom {
            background-color: var(--zaitun-primary, #718F50) !important;
        }

        .bg-success-custom {
            background-color: var(--zaitun-young, #A9C77D) !important;
        }

        .bg-warning-custom {
            background-color: #CBB968 !important;
        }

        /* Soft Backgrounds */
        .stat-icon-primary {
            background-color: rgba(113, 143, 80, 0.15);
            color: var(--zaitun-primary, #718F50);
        }

        .stat-icon-success {
            background-color: rgba(113, 143, 80, 0.15);
            color: var(--zaitun-primary, #718F50);
        }

        .stat-icon-warning {
            background-color: rgba(229, 216, 155, 0.3);
            color: #8F7226;
        }

        .stat-icon-info {
            background-color: rgba(125, 72, 86, 0.15);
            color: var(--zaitun-ripe, #7D4856);
        }

        .bg-primary-soft {
            background-color: rgba(113, 143, 80, 0.12) !important;
            color: var(--zaitun-primary, #718F50) !important;
        }

        .bg-warning-soft {
            background-color: rgba(229, 216, 155, 0.22) !important;
        }

        .bg-info-soft {
            background-color: rgba(125, 72, 86, 0.12) !important;
        }

        .badge-success-soft {
            background-color: rgba(169, 199, 125, 0.22);
            color: #3A4C2E;
            border: 1px solid rgba(169, 199, 125, 0.4);
            font-weight: 600;
        }

        .badge-warning-soft {
            background-color: rgba(229, 216, 155, 0.32);
            color: #786018;
            border: 1px solid rgba(229, 216, 155, 0.6);
            font-weight: 600;
        }

        .badge-danger-soft {
            background-color: rgba(125, 72, 86, 0.15);
            color: var(--zaitun-ripe, #7D4856);
            border: 1px solid rgba(125, 72, 86, 0.3);
            font-weight: 600;
        }

        .date-box {
            background-color: rgba(113, 143, 80, 0.12) !important;
            color: #5D793F !important;
            border: 1px solid rgba(113, 143, 80, 0.25) !important;
        }

        .last-no-border:last-child {
            border-bottom: none !important;
        }

        /* Card header styling */
        .card-header-custom {
            background: var(--bg-surface);
        }

        .bg-warning-soft {
            background-color: rgba(121, 92, 69, 0.12) !important;
        }

        .bg-info-soft {
            background-color: rgba(117, 70, 83, 0.12) !important;
        }

        /* Birthday card avatar */
        .avatar-initials {
            flex-shrink: 0;
        }

        /* ===== SCHEDULE SCROLL & CAROUSEL INNOVATION ===== */
        .schedule-scroll-container {
            max-height: 295px;
            overflow-y: auto;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        .schedule-scroll-container::-webkit-scrollbar,
        .schedule-carousel-container::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        .schedule-scroll-container::-webkit-scrollbar-track,
        .schedule-carousel-container::-webkit-scrollbar-track {
            background: rgba(113, 143, 80, 0.05);
            border-radius: 4px;
        }

        .schedule-scroll-container::-webkit-scrollbar-thumb,
        .schedule-carousel-container::-webkit-scrollbar-thumb {
            background: rgba(113, 143, 80, 0.35);
            border-radius: 4px;
        }

        .schedule-scroll-container::-webkit-scrollbar-thumb:hover,
        .schedule-carousel-container::-webkit-scrollbar-thumb:hover {
            background: var(--zaitun-primary, #718F50);
        }

        .schedule-item {
            padding: 0.85rem 1.15rem;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--bg-surface);
            transition: var(--transition);
            border-left: 3px solid transparent;
        }

        .schedule-item:hover {
            background-color: rgba(113, 143, 80, 0.04);
            border-left-color: var(--zaitun-primary, #718F50);
        }

        .schedule-item:last-child {
            border-bottom: none;
        }

        /* Carousel Horizontal Mode */
        .schedule-carousel-container {
            display: flex;
            gap: 0.85rem;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-snap-type: x mandatory;
            padding: 0.9rem 1.15rem;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .schedule-card-slide {
            flex: 0 0 260px;
            max-width: 260px;
            scroll-snap-align: start;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 0.9rem 1rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border-top: 3px solid var(--zaitun-primary, #718F50);
        }

        .schedule-card-slide:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: rgba(113, 143, 80, 0.4);
        }

        .view-toggle-btn {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.82rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .view-toggle-btn.active, .view-toggle-btn:hover {
            background: var(--zaitun-primary, #718F50);
            color: #ffffff;
            border-color: var(--zaitun-primary, #718F50);
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

            .schedule-card-slide {
                flex: 0 0 230px;
                max-width: 230px;
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

        function setScheduleView(mode) {
            const listView = document.getElementById('scheduleListView');
            const carouselView = document.getElementById('scheduleCarouselView');
            const btnList = document.getElementById('btnViewList');
            const btnCarousel = document.getElementById('btnViewCarousel');
            
            if (!listView || !carouselView) return;

            if (mode === 'carousel') {
                listView.classList.add('d-none');
                carouselView.classList.remove('d-none');
                if (btnList) btnList.classList.remove('active');
                if (btnCarousel) btnCarousel.classList.add('active');
                try { localStorage.setItem('scheduleViewMode', 'carousel'); } catch(e){}
            } else {
                carouselView.classList.add('d-none');
                listView.classList.remove('d-none');
                if (btnCarousel) btnCarousel.classList.remove('active');
                if (btnList) btnList.classList.add('active');
                try { localStorage.setItem('scheduleViewMode', 'list'); } catch(e){}
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            try {
                const savedMode = localStorage.getItem('scheduleViewMode') || 'list';
                setScheduleView(savedMode);
            } catch(e){}
        });
    </script>
@endsection
