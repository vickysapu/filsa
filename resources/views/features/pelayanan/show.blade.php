@extends('layouts.admin')

@section('title', 'Detail Pelayanan - ' . $pelayanan->nama_pelayanan)

@section('content')
    <div class="page-wrapper">

        @php
            $statusMap = [
                'draft' => ['label' => 'Draft', 'class' => 'badge-status-draft'],
                'active' => ['label' => 'Aktif', 'class' => 'badge-status-active'],
                'completed' => ['label' => 'Selesai', 'class' => 'badge-status-completed'],
                'cancelled' => ['label' => 'Dibatalkan', 'class' => 'badge-status-cancelled'],
            ];
            $st = $statusMap[$pelayanan->status] ?? [
                'label' => ucfirst($pelayanan->status),
                'class' => 'badge-status-draft',
            ];
            $aktif = $pelayanan->details->where('status', 'active');
            $off = $pelayanan->details->where('status', 'off');
        @endphp

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.jadwal.index') }}" class="text-muted-custom text-decoration-none">
                                <i class="ri-arrow-left-line me-1"></i> Jadwal Pelayanan
                            </a>
                        </li>
                    </ol>
                </nav>
                <h1 class="h4 fw-bold mb-1">{{ $pelayanan->nama_pelayanan }}</h1>
                <p class="text-muted-custom small mb-0">{{ $pelayanan->nomor_jadwal }}</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge {{ $st['class'] }}">{{ $st['label'] }}</span>
                <a href="{{ route('admin.jadwal.edit', $pelayanan->id) }}" class="btn btn-custom btn-sm">
                    <i class="ri-pencil-line me-1"></i> Edit
                </a>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="info-item">
                            <i class="ri-calendar-line text-primary"></i>
                            <div>
                                <p class="text-muted-custom small mb-1">Tanggal</p>
                                <p class="fw-semibold mb-0">{{ $pelayanan->tanggal->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <i class="ri-time-line text-primary"></i>
                            <div>
                                <p class="text-muted-custom small mb-1">Waktu</p>
                                <p class="fw-semibold mb-0">
                                    {{ \Carbon\Carbon::parse($pelayanan->waktu)->format('H:i') }} WIB
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <i class="ri-map-pin-line text-primary"></i>
                            <div>
                                <p class="text-muted-custom small mb-1">Lokasi</p>
                                <p class="fw-semibold mb-0">{{ $pelayanan->lokasi }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($pelayanan->keterangan)
                    <hr class="border-custom my-4">
                    <div>
                        <p class="text-muted-custom small mb-2">Keterangan</p>
                        <p class="mb-0">{{ $pelayanan->keterangan }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Petugas Aktif -->
        <div class="card-custom mb-4">
            <div class="card-header-custom border-bottom border-custom py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">
                        <i class="ri-team-line me-2 text-primary"></i>Petugas Aktif
                    </h6>
                    <span class="badge badge-status-active">{{ $aktif->count() }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                @forelse($aktif as $d)
                    <div class="petugas-item border-bottom border-custom">
                        <div class="petugas-avatar">
                            {{ substr($d->jemaat->nama_lengkap, 0, 1) }}
                        </div>
                        <div class="petugas-info">
                            <div class="petugas-name">{{ $d->jemaat->nama_lengkap }}</div>
                            <div class="petugas-meta">
                                <span class="badge badge-secondary-custom">
                                    {{ $d->kategori->nama_kategori ?? '-' }}
                                </span>
                                @if ($d->role)
                                    <span class="badge badge-role">{{ $d->role }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="petugas-status">
                            <span class="badge badge-status-active">
                                <i class="ri-check-line me-1"></i> Aktif
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <p class="text-muted-custom mb-0">Belum ada petugas aktif</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Petugas OFF -->
        @if ($off->count())
            <div class="card-custom">
                <div class="card-header-custom border-bottom border-custom py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-semibold">
                            <i class="ri-user-unfollow-line me-2 text-muted-custom"></i>Petugas OFF
                        </h6>
                        <span class="badge badge-status-cancelled">{{ $off->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @foreach ($off as $d)
                        <div class="petugas-item border-bottom border-custom petugas-off">
                            <div class="petugas-avatar avatar-off">
                                {{ substr($d->jemaat->nama_lengkap, 0, 1) }}
                            </div>
                            <div class="petugas-info">
                                <div class="petugas-name name-off">{{ $d->jemaat->nama_lengkap }}</div>
                                @if ($d->alasan_off)
                                    <div class="petugas-reason">
                                        <i class="ri-error-warning-line me-1 text-danger"></i>
                                        <span class="text-danger small">{{ $d->alasan_off }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="petugas-status">
                                <span class="badge badge-status-cancelled">
                                    <i class="ri-close-line me-1"></i> OFF
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    <style>
        /* ===== PAGE SPECIFIC STYLES ===== */
        .page-wrapper {
            animation: fadeIn 0.4s ease;
            max-width: 900px;
            margin: 0 auto;
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

        /* ===== BREADCRUMB ===== */
        .breadcrumb {
            padding: 0;
            background: transparent;
            margin: 0;
        }

        .breadcrumb-item a {
            color: var(--text-secondary);
            transition: var(--transition);
        }

        .breadcrumb-item a:hover {
            color: var(--accent-blue);
        }

        .breadcrumb-item+.breadcrumb-item::before {
            color: var(--text-secondary);
        }

        /* ===== INFO ITEM ===== */
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .info-item i {
            font-size: 1.25rem;
            margin-top: 2px;
        }

        /* ===== PETUGAS ITEM ===== */
        .petugas-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            transition: var(--transition);
        }

        .petugas-item:hover {
            background: var(--bg-body);
        }

        .petugas-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent-blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .petugas-avatar.avatar-off {
            background: var(--border-color);
            color: var(--text-secondary);
        }

        .petugas-info {
            flex: 1;
            min-width: 0;
        }

        .petugas-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .petugas-name.name-off {
            text-decoration: line-through;
            color: var(--text-secondary);
        }

        .petugas-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
        }

        .petugas-reason {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.25rem;
        }

        .petugas-status {
            flex-shrink: 0;
        }

        /* ===== BADGES ===== */
        .badge-status-draft {
            background: rgba(100, 116, 139, 0.15);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.3);
            padding: 0.4em 0.75em;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .badge-status-active {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.4em 0.75em;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .badge-status-completed {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.3);
            padding: 0.4em 0.75em;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .badge-status-cancelled {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 0.4em 0.75em;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .badge-secondary-custom {
            background: var(--bg-body);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            padding: 0.25em 0.5em;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-role {
            background: rgba(37, 99, 235, 0.15);
            color: var(--accent-blue);
            border: 1px solid rgba(37, 99, 235, 0.3);
            padding: 0.25em 0.5em;
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* ===== DARK MODE ===== */
        [data-theme="dark"] .petugas-item:hover {
            background: var(--bg-sidebar);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .petugas-item {
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .petugas-status {
                margin-left: 52px;
            }
        }
    </style>
@endsection
