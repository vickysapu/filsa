@forelse($inventaris as $item)
    <tr class="border-bottom border-custom">
        @if (!Auth::user()?->roles->first()?->branch_id)
            <td class="py-3 px-4">
                <span class="text-muted-custom small">{{ $item->branch->name ?? '—' }}</span>
            </td>
        @endif
        <td class="py-3 px-4">
            <span class="fw-medium">{{ $item->nama_barang }}</span>
        </td>
        <td class="py-3 px-4">
            <span class="badge badge-secondary-custom">{{ $item->kategori }}</span>
        </td>
        <td class="py-3 px-4">
            <span class="badge badge-condition-{{ Str::slug($item->kondisi) }}">
                {{ $item->kondisi }}
            </span>
        </td>
        <td class="py-3 px-4">
            <div class="d-flex gap-1 justify-content-center">
                <button type="button" class="btn-action btn-action-primary btn-edit-inventaris"
                    data-id="{{ $item->id }}" title="Edit">
                    <i class="ri-pencil-line"></i>
                </button>
                <button type="button" class="btn-action btn-action-danger btn-delete-inventaris"
                    data-id="{{ $item->id }}" title="Hapus">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="{{ !Auth::user()?->roles->first()?->branch_id ? '6' : '5' }}" class="py-5">
            <div class="text-center">
                <div class="empty-state-icon mb-3">
                    <i class="ri-archive-line"></i>
                </div>
                <h6 class="fw-semibold mb-2">Belum ada data inventaris</h6>
                <p class="text-muted-custom small mb-0">Mulai tambahkan inventaris pertama Anda</p>
            </div>
        </td>
    </tr>
@endforelse

<style>
    /* ===== CONDITION BADGES (Consistent with system) ===== */
    .badge-condition-baik {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
        padding: 0.35em 0.65em;
        font-weight: 500;
        font-size: 0.75rem;
    }

    .badge-condition-rusak-ringan {
        background: rgba(245, 158, 11, 0.15);
        color: #b45309;
        border: 1px solid rgba(245, 158, 11, 0.3);
        padding: 0.35em 0.65em;
        font-weight: 500;
        font-size: 0.75rem;
    }

    .badge-condition-rusak-berat {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
        padding: 0.35em 0.65em;
        font-weight: 500;
        font-size: 0.75rem;
    }

    /* ===== ACTION BUTTONS (Consistent with system) ===== */
    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-sm);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border-color);
        background: var(--bg-surface);
        color: var(--text-primary);
        transition: var(--transition);
        font-size: 1rem;
        padding: 0;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .btn-action-primary {
        border-color: var(--accent-blue);
        color: var(--accent-blue);
    }

    .btn-action-primary:hover {
        background: var(--accent-blue);
        color: white;
    }

    .btn-action-danger {
        border-color: #ef4444;
        color: #ef4444;
    }

    .btn-action-danger:hover {
        background: #ef4444;
        color: white;
    }

    /* ===== EMPTY STATE (Consistent with system) ===== */
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
    [data-theme="dark"] .empty-state-icon {
        background: var(--bg-sidebar);
    }
</style>
