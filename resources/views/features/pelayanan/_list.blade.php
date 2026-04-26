<!-- resources/views/admin/jadwal/_list.blade.php -->

@if ($pelayanans->isEmpty())
    <div class="col-12">
        <div class="empty-state-card text-center py-5">
            <div class="empty-state-icon mb-3">
                <i class="ri-calendar-off-line"></i>
            </div>
            <h5 class="fw-semibold mb-2">Belum ada jadwal pelayanan</h5>
            <p class="text-muted-custom small mb-4">Mulai dengan membuat jadwal baru untuk kegiatan pelayanan gereja</p>
            <button type="button" class="btn btn-custom btn-tambah-jadwal">
                <i class="ri-calendar-add-line me-1"></i> Buat Jadwal Pertama
            </button>
        </div>
    </div>
@else
    @foreach ($pelayanans as $pelayanan)
        @php
            $statusConfig = [
                'draft' => ['class' => 'badge-status-draft', 'label' => 'Draft', 'border' => '#6b7280'],
                'active' => ['class' => 'badge-status-active', 'label' => 'Aktif', 'border' => '#22c55e'],
                'completed' => ['class' => 'badge-status-completed', 'label' => 'Selesai', 'border' => '#3b82f6'],
                'cancelled' => ['class' => 'badge-status-cancelled', 'label' => 'Dibatalkan', 'border' => '#ef4444'],
            ];
            $cfg = $statusConfig[$pelayanan->status] ?? [
                'class' => 'badge-status-draft',
                'label' => ucfirst($pelayanan->status),
                'border' => '#6b7280',
            ];
            $activeCount = $pelayanan->details->where('status', 'active')->count();
        @endphp

        <div class="col-md-6 col-lg-4">
            <div class="jadwal-card" style="--status-color: {{ $cfg['border'] }}">

                <!-- Status Badge -->
                <div class="jadwal-card-status-badge">
                    <span class="badge {{ $cfg['class'] }}">{{ $cfg['label'] }}</span>
                </div>

                <!-- Card Header: Title -->
                <div class="jadwal-card-header">
                    <h6 class="jadwal-card-title">{{ $pelayanan->nama_pelayanan }}</h6>
                </div>

                <!-- Card Body: Info -->
                <div class="jadwal-card-body">
                    <div class="jadwal-info-list">
                        <div class="jadwal-info-item">
                            <i class="ri-calendar-line"></i>
                            <span>{{ $pelayanan->tanggal->format('d M Y') }}</span>
                        </div>
                        <div class="jadwal-info-item">
                            <i class="ri-time-line"></i>
                            <span>{{ \Carbon\Carbon::parse($pelayanan->waktu)->format('H:i') }} WIB</span>
                        </div>
                        <div class="jadwal-info-item">
                            <i class="ri-map-pin-line"></i>
                            <span>{{ $pelayanan->lokasi }}</span>
                        </div>
                        <div class="jadwal-info-item">
                            <i class="ri-team-line"></i>
                            <span>{{ $activeCount }} Petugas Aktif</span>
                        </div>
                    </div>

                    <!-- Preview Petugas -->
                    @if ($activeCount > 0)
                        <div class="jadwal-petugas-preview">
                            <span class="jadwal-petugas-label">Petugas:</span>
                            <div class="jadwal-petugas-badges">
                                @foreach ($pelayanan->details->where('status', 'active')->take(4) as $detail)
                                    <span class="jadwal-petugas-badge">
                                        {{ explode(' ', $detail->jemaat->nama_lengkap)[0] ?? 'Jemaat' }}
                                        @if ($detail->role)
                                            <small class="jadwal-petugas-role">({{ $detail->role }})</small>
                                        @endif
                                    </span>
                                @endforeach
                                @if ($activeCount > 4)
                                    <span class="jadwal-petugas-badge more">
                                        +{{ $activeCount - 4 }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Card Footer: Actions -->
                <div class="jadwal-card-footer">
                    <a href="{{ route('admin.jadwal.show', $pelayanan) }}"
                        class="btn btn-outline-custom btn-sm flex-fill">
                        <i class="ri-eye-line me-1"></i> Detail
                    </a>

                    @if (in_array($pelayanan->status, ['draft', 'active']) && auth()->user()->hasPermission('pelayanan.edit'))
                        <button type="button" class="btn btn-custom btn-sm btn-edit-jadwal"
                            data-id="{{ $pelayanan->id }}" data-nama="{{ $pelayanan->nama_pelayanan }}"
                            title="Edit">
                            <i class="ri-pencil-line"></i>
                        </button>
                    @endif

                    @if (auth()->user()->hasPermission('pelayanan.delete'))
                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete-jadwal"
                            data-id="{{ $pelayanan->id }}" data-nama="{{ $pelayanan->nama_pelayanan }}"
                            title="Hapus">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
@endif

<style>
    /* ===== JADWAL CARD ===== */
    .jadwal-card {
        position: relative;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-top: 4px solid var(--status-color, #6b7280);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    .jadwal-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--accent-blue);
    }

    /* Status Badge Position */
    .jadwal-card-status-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 2;
    }

    /* Card Header */
    .jadwal-card-header {
        padding: 1.25rem 1.25rem 0.75rem;
    }

    .jadwal-card-title {
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Card Body */
    .jadwal-card-body {
        padding: 0 1.25rem 1rem;
        flex: 1;
    }

    .jadwal-info-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .jadwal-info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: var(--text-primary);
    }

    .jadwal-info-item i {
        color: var(--accent-blue);
        font-size: 1rem;
        width: 16px;
        text-align: center;
    }

    .jadwal-info-item span {
        font-weight: 500;
    }

    /* Petugas Preview */
    .jadwal-petugas-preview {
        padding: 0.75rem;
        background: var(--bg-body);
        border-radius: var(--radius-sm);
        margin-top: 0.5rem;
    }

    .jadwal-petugas-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        display: block;
    }

    .jadwal-petugas-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .jadwal-petugas-badge {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        font-size: 0.75rem;
        padding: 0.25em 0.5em;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-weight: 500;
    }

    .jadwal-petugas-badge small {
        color: var(--text-secondary);
        font-weight: 400;
    }

    .jadwal-petugas-badge.more {
        background: var(--border-color);
        color: var(--text-secondary);
        border: none;
    }

    /* Card Footer */
    .jadwal-card-footer {
        padding: 0.75rem 1.25rem 1.25rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 0.5rem;
    }

    .jadwal-card-footer .btn {
        padding: 0.375rem 0.75rem;
    }

    .jadwal-card-footer .btn-sm {
        font-size: 0.8rem;
    }

    /* ===== BADGES (Consistent with system) ===== */
    .badge-status-draft {
        background: rgba(100, 116, 139, 0.15);
        color: #64748b;
        border: 1px solid rgba(100, 116, 139, 0.3);
        padding: 0.35em 0.65em;
        font-weight: 500;
        font-size: 0.75rem;
    }

    .badge-status-active {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
        padding: 0.35em 0.65em;
        font-weight: 500;
        font-size: 0.75rem;
    }

    .badge-status-completed {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.3);
        padding: 0.35em 0.65em;
        font-weight: 500;
        font-size: 0.75rem;
    }

    .badge-status-cancelled {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
        padding: 0.35em 0.65em;
        font-weight: 500;
        font-size: 0.75rem;
    }

    /* ===== EMPTY STATE ===== */
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
    [data-theme="dark"] .jadwal-card:hover {
        border-color: var(--accent-blue);
        background: var(--bg-sidebar);
    }

    [data-theme="dark"] .jadwal-petugas-badge {
        background: var(--bg-sidebar);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .empty-state-card {
        background: var(--bg-sidebar);
        border-color: var(--border-color);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .jadwal-card-footer {
            flex-wrap: wrap;
        }

        .jadwal-card-footer .btn {
            flex: 1 1 auto;
            justify-content: center;
        }

        .jadwal-petugas-badges {
            gap: 0.25rem;
        }

        .jadwal-petugas-badge {
            font-size: 0.7rem;
            padding: 0.2em 0.4em;
        }
    }
</style>
