<!-- features/pelayanan/_petugas_list.blade.php -->

@if ($jemaats->isEmpty())
    <div class="empty-state-card">
        <i class="ri-team-line"></i>
        <p class="empty-state-title">Belum ada jemaat aktif</p>
        <small class="text-muted-custom">Tambahkan data jemaat terlebih dahulu</small>
    </div>
@else
    @foreach ($jemaats as $jemaat)
        @php
            $isChecked = in_array($jemaat->id, (array) ($selected ?? [])) ? 'checked' : '';
            $selectedKategori = $kategori_ids[$jemaat->id] ?? '';
        @endphp

        <div class="petugas-item {{ $isChecked ? 'selected' : '' }}">

            <!-- Checkbox + Info Jemaat -->
            <div class="petugas-main">
                <div class="petugas-check">
                    <input type="checkbox" id="jemaat_{{ $jemaat->id }}" name="jemaat_ids[]" value="{{ $jemaat->id }}"
                        class="jemaat-checkbox form-check-input-custom" {{ $isChecked }}
                        onchange="toggleKategoriField(this)">
                </div>

                <label for="jemaat_{{ $jemaat->id }}" class="petugas-label">
                    <div class="petugas-avatar">
                        {{ substr($jemaat->nama_lengkap ?? 'J', 0, 1) }}
                    </div>
                    <div class="petugas-details">
                        <div class="petugas-name">{{ $jemaat->nama_lengkap }}</div>
                        @if ($jemaat->no_hp)
                            <div class="petugas-phone">{{ $jemaat->no_hp }}</div>
                        @endif
                    </div>
                </label>
            </div>

            <!-- Dropdown Kategori - Hanya muncul jika diceklis -->
            <div class="kategori-wrapper {{ $isChecked ? 'show' : '' }}">
                <label class="form-label small fw-semibold text-muted-custom mb-2">
                    Kategori Pelayanan <span class="text-danger">*</span>
                </label>
                <select name="kategori_id[{{ $jemaat->id }}]" class="form-select form-select-custom kategori-select">
                    <option value="">Pilih Kategori...</option>
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori->id }}"
                            {{ $kategori->id == $selectedKategori ? 'selected' : '' }}>
                            {{ $kategori->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    @endforeach
@endif

<style>
    /* ===== EMPTY STATE ===== */
    .empty-state-card {
        padding: 3rem 1rem;
        text-align: center;
        color: var(--text-secondary);
        background: var(--bg-body);
        border: 1px dashed var(--border-color);
        border-radius: var(--radius);
    }

    .empty-state-card i {
        font-size: 3.5rem;
        opacity: 0.4;
        color: var(--text-secondary);
        display: block;
        margin-bottom: 1rem;
    }

    .empty-state-title {
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 0.25rem;
    }

    /* ===== PETUGAS ITEM ===== */
    .petugas-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.875rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        background: var(--bg-surface);
        transition: var(--transition);
        margin-bottom: 0.5rem;
    }

    .petugas-item:hover {
        border-color: var(--accent-blue);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08);
    }

    .petugas-item.selected {
        background: rgba(37, 99, 235, 0.06);
        border-color: var(--accent-blue);
    }

    .petugas-main {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        flex: 1;
        min-width: 0;
    }

    .petugas-check {
        display: flex;
        align-items: center;
    }

    .petugas-label {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        cursor: pointer;
        margin: 0;
        min-width: 0;
    }

    .petugas-avatar {
        width: 42px;
        height: 42px;
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

    .petugas-details {
        min-width: 0;
    }

    .petugas-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .petugas-phone {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    /* ===== KATEGORI WRAPPER ===== */
    .kategori-wrapper {
        width: 220px;
        flex-shrink: 0;
        opacity: 0;
        visibility: hidden;
        transform: translateX(-10px);
        transition: all 0.25s ease;
    }

    .kategori-wrapper.show {
        opacity: 1;
        visibility: visible;
        transform: translateX(0);
    }

    .kategori-select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
    }

    /* ===== FORM ELEMENTS (Consistent with main system) ===== */
    .form-check-input-custom {
        width: 18px;
        height: 18px;
        border: 2px solid var(--border-color);
        border-radius: 4px;
        cursor: pointer;
        transition: var(--transition);
        accent-color: var(--accent-blue);
    }

    .form-check-input-custom:checked {
        background-color: var(--accent-blue);
        border-color: var(--accent-blue);
    }

    .form-select-custom {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-sm);
        font-size: 0.85rem;
        transition: var(--transition);
    }

    .form-select-custom:focus {
        border-color: var(--accent-blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        outline: none;
    }

    /* ===== DARK MODE ===== */
    [data-theme="dark"] .petugas-item:hover {
        background: var(--bg-sidebar);
        border-color: var(--accent-blue);
    }

    [data-theme="dark"] .petugas-item.selected {
        background: rgba(37, 99, 235, 0.12);
    }

    [data-theme="dark"] .empty-state-card {
        background: var(--bg-sidebar);
        border-color: var(--border-color);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .petugas-item {
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .kategori-wrapper {
            width: 100%;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid var(--border-color);
        }

        .kategori-wrapper.show {
            transform: none;
        }
    }
</style>

<script>
    // Toggle kategori field visibility + validation
    function toggleKategoriField(checkbox) {
        const item = checkbox.closest('.petugas-item');
        const wrapper = item?.querySelector('.kategori-wrapper');
        const select = item?.querySelector('.kategori-select');

        if (!wrapper || !select) return;

        if (checkbox.checked) {
            wrapper.classList.add('show');
            select.required = true;
            select.disabled = false;
            // Auto focus for better UX
            setTimeout(() => select.focus(), 150);
        } else {
            wrapper.classList.remove('show');
            select.required = false;
            select.disabled = true;
            select.value = ''; // Reset selection
        }

        // Update item visual state
        item?.classList.toggle('selected', checkbox.checked);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.jemaat-checkbox:checked').forEach(cb => {
            toggleKategoriField(cb);
        });
    });
</script>
