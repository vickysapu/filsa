@foreach ($latihans as $latihan)
    @php
        $statusClass = $latihan->status === 'active' ? 'badge-status-active' : 'badge-status-cancelled';
        $statusLabel = ucfirst($latihan->status);
    @endphp

    <div class="col-md-6 col-lg-4">
        <div class="latihan-card" style="--status-color: {{ $latihan->status === 'active' ? '#22c55e' : '#ef4444' }}">

            <!-- Card Header -->
            <div class="latihan-card-header">
                <div>
                    <h6 class="latihan-card-title">{{ $latihan->nama_latihan }}</h6>
                    <small class="text-muted-custom">
                        {{ $latihan->pelayanan?->nama_pelayanan ?? '-' }}
                    </small>
                </div>
                <span class="latihan-card-status {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>
            </div>

            <!-- Card Body -->
            <div class="latihan-card-body">
                <div class="latihan-info-list">
                    <div class="latihan-info-item">
                        <i class="ri-calendar-line"></i>
                        <span>{{ \Carbon\Carbon::parse($latihan->tanggal)->format('d M Y') }}</span>
                    </div>
                    <div class="latihan-info-item">
                        <i class="ri-time-line"></i>
                        <span>{{ \Carbon\Carbon::parse($latihan->waktu)->format('H:i') }} WIB</span>
                    </div>
                    <div class="latihan-info-item">
                        <i class="ri-map-pin-line"></i>
                        <span>{{ $latihan->lokasi ?? 'Gedung Utama' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card Footer -->
            <div class="latihan-card-footer">
                <button class="btn btn-outline-custom btn-sm flex-fill btn-edit-latihan" data-id="{{ $latihan->id }}"
                    data-nama="{{ $latihan->nama_latihan }}" title="Edit">
                    <i class="ri-pencil-line me-1"></i> Edit
                </button>
                <button class="btn btn-outline-danger btn-sm flex-fill btn-delete-latihan"
                    data-id="{{ $latihan->id }}" data-nama="{{ addslashes($latihan->nama_latihan) }}" title="Hapus">
                    <i class="ri-delete-bin-line me-1"></i> Hapus
                </button>
            </div>
        </div>
    </div>
@endforeach

@if ($latihans->isEmpty())
    <div class="col-12">
        <div class="empty-state-card text-center py-5">
            <div class="empty-state-icon mb-3">
                <i class="ri-calendar-schedule-line"></i>
            </div>
            <h5 class="fw-semibold mb-2">Belum ada jadwal latihan</h5>
            <p class="text-muted-custom small mb-4">Buat jadwal latihan pertama untuk pelayanan Anda</p>
            <button type="button" class="btn btn-custom btn-tambah-latihan">
                <i class="ri-calendar-add-line me-1"></i> Buat Jadwal Latihan
            </button>
        </div>
    </div>
@endif

<style>
    /* ===== LATIHAN CARD (Consistent with jadwal-card system) ===== */
    .latihan-card {
        position: relative;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-left: 4px solid var(--status-color, var(--accent-blue));
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    .latihan-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--accent-blue);
    }

    /* Card Header */
    .latihan-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .latihan-card-title {
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 0.25rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Status Badge */
    .latihan-card-status {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
        border-radius: 20px;
        font-weight: 500;
        white-space: nowrap;
    }

    .badge-status-active {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }

    .badge-status-cancelled {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    /* Card Body */
    .latihan-card-body {
        padding: 0 1.25rem 1rem;
        flex: 1;
    }

    .latihan-info-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .latihan-info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: var(--text-primary);
    }

    .latihan-info-item i {
        color: var(--accent-blue);
        font-size: 1rem;
        width: 16px;
        text-align: center;
    }

    .latihan-info-item span {
        font-weight: 500;
    }

    /* Card Footer */
    .latihan-card-footer {
        padding: 0.75rem 1.25rem 1.25rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 0.5rem;
    }

    .latihan-card-footer .btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.8rem;
    }

    /* ===== EMPTY STATE (Consistent) ===== */
    .empty-state-card {
        background: var(--bg-surface);
        border: 1px dashed var(--border-color);
        border-radius: var(--radius);
        padding: 3rem 1.5rem;
    }

    .empty-state-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--bg-body);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        font-size: 2.5rem;
        opacity: 0.6;
    }

    /* ===== DARK MODE ===== */
    [data-theme="dark"] .latihan-card:hover {
        background: var(--bg-sidebar);
        border-color: var(--accent-blue);
    }

    [data-theme="dark"] .empty-state-card {
        background: var(--bg-sidebar);
        border-color: var(--border-color);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .latihan-card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .latihan-card-footer {
            flex-wrap: wrap;
        }

        .latihan-card-footer .btn {
            flex: 1 1 auto;
            justify-content: center;
        }
    }
</style>
