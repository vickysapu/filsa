<div class="modal fade" id="modalLatihanForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-custom">

            <div class="modal-header border-bottom border-custom">
                <h5 class="modal-title fw-semibold" id="modalTitle">Buat Jadwal Latihan Pelayanan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formLatihan" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="id" id="latihanId" value="">

                <div class="modal-body p-4">

                    <div class="card card-custom">
                        <div class="card-header-custom border-bottom border-custom py-3 px-4">
                            <h6 class="mb-0 fw-semibold">
                                <i class="ri-calendar-schedule-line me-2 text-primary"></i>Informasi Jadwal Latihan
                            </h6>
                        </div>
                        <div class="card-body p-4">

                            <!-- Pelayanan -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">
                                    Pelayanan <span class="text-danger">*</span>
                                </label>
                                <select name="pelayanan_id" id="pelayanan_id"
                                    class="form-select form-select-custom @error('pelayanan_id') is-invalid @enderror"
                                    required>
                                    <option value="">Pilih Pelayanan</option>
                                    @foreach ($pelayanans ?? [] as $p)
                                        <option value="{{ $p->id }}"
                                            {{ old('pelayanan_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->nama_pelayanan }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pelayanan_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nama Latihan -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">
                                    Nama Latihan <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nama_latihan" id="nama_latihan"
                                    class="form-control form-control-custom @error('nama_latihan') is-invalid @enderror"
                                    required placeholder="Contoh: Latihan Pelayanan Ibadah Minggu"
                                    value="{{ old('nama_latihan') }}">
                                @error('nama_latihan')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tanggal & Waktu -->
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted-custom">
                                        Tanggal <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tanggal" id="tanggal"
                                        class="form-control form-control-custom @error('tanggal') is-invalid @enderror"
                                        required value="{{ old('tanggal') }}">
                                    @error('tanggal')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted-custom">
                                        Waktu <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" name="waktu" id="waktu"
                                        class="form-control form-control-custom @error('waktu') is-invalid @enderror"
                                        required value="{{ old('waktu') }}">
                                    @error('waktu')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Lokasi -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Lokasi</label>
                                <input type="text" name="lokasi" id="lokasi"
                                    class="form-control form-control-custom @error('lokasi') is-invalid @enderror"
                                    value="{{ old('lokasi', 'Gedung Utama') }}">
                                @error('lokasi')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Keterangan -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Keterangan</label>
                                <textarea name="keterangan" id="keterangan"
                                    class="form-control form-control-custom textarea-custom @error('keterangan') is-invalid @enderror" rows="3"
                                    placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label
                                    class="form-label small fw-semibold text-muted-custom d-block mb-2">Status</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <label class="form-check-radio">
                                        <input type="radio" name="status" value="active"
                                            class="form-check-input-custom"
                                            {{ old('status', 'active') == 'active' ? 'checked' : '' }}>
                                        <span class="form-check-label-custom">
                                            <span class="badge badge-status-active me-1">Aktif</span> Aktif
                                        </span>
                                    </label>
                                    <label class="form-check-radio">
                                        <input type="radio" name="status" value="cancelled"
                                            class="form-check-input-custom"
                                            {{ old('status') == 'cancelled' ? 'checked' : '' }}>
                                        <span class="form-check-label-custom">
                                            <span class="badge badge-status-cancelled me-1">Dibatalkan</span> Dibatalkan
                                        </span>
                                    </label>
                                </div>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top border-custom py-3 px-4">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-custom" id="btnSubmitLatihan">
                        <i class="ri-save-line me-1"></i> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* ===== MODAL SPECIFIC STYLES ===== */
    .modal-content-custom {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
    }

    .modal-header {
        background: var(--bg-surface);
        padding: 1rem 1.5rem;
    }

    .modal-body {
        background: var(--bg-body);
    }

    .modal-footer {
        background: var(--bg-surface);
    }

    /* ===== FORM ELEMENTS (Consistent with system) ===== */
    .form-control-custom,
    .form-select-custom {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 0.625rem 0.875rem;
        border-radius: var(--radius-sm);
        font-size: 0.9rem;
        transition: var(--transition);
    }

    .form-control-custom:focus,
    .form-select-custom:focus {
        border-color: var(--accent-blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        outline: none;
    }

    .form-control-custom::placeholder {
        color: var(--text-secondary);
    }

    .textarea-custom {
        resize: vertical;
        min-height: 80px;
        line-height: 1.5;
    }

    /* Validation States */
    .form-control-custom.is-invalid,
    .form-select-custom.is-invalid {
        border-color: #ef4444;
        padding-right: calc(0.625rem + 1.5rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23ef4444'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23ef4444' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1rem 1rem;
    }

    .form-control-custom.is-invalid:focus,
    .form-select-custom.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
    }

    .invalid-feedback {
        color: #ef4444;
        font-size: 0.8rem;
        margin-top: 0.25rem;
        display: none;
    }

    .invalid-feedback.d-block {
        display: block;
    }

    /* Radio/Checkbox Custom */
    .form-check-radio {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }

    .form-check-input-custom {
        width: 18px;
        height: 18px;
        border: 2px solid var(--border-color);
        border-radius: 4px;
        cursor: pointer;
        transition: var(--transition);
    }

    .form-check-input-custom:checked {
        background-color: var(--accent-blue);
        border-color: var(--accent-blue);
    }

    .form-check-input-custom[type="radio"] {
        border-radius: 50%;
    }

    .form-check-label-custom {
        font-size: 0.9rem;
        color: var(--text-primary);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    /* ===== BADGES ===== */
    .badge-status-active {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
        padding: 0.2em 0.5em;
        font-weight: 500;
        font-size: 0.7rem;
    }

    .badge-status-cancelled {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
        padding: 0.2em 0.5em;
        font-weight: 500;
        font-size: 0.7rem;
    }

    /* ===== DARK MODE ===== */
    [data-theme="dark"] .modal-content-custom {
        background: var(--bg-surface);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .form-control-custom,
    [data-theme="dark"] .form-select-custom {
        background: var(--bg-surface);
        border-color: var(--border-color);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 576px) {
        .modal-dialog-scrollable .modal-content {
            max-height: calc(100vh - 2rem);
        }

        .modal-body {
            padding: 1rem !important;
        }

        .card-body.p-4 {
            padding: 1rem !important;
        }
    }
</style>

<script>
    // ===== FORM VALIDATION ENHANCEMENT =====
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formLatihan');
        if (!form) return;

        // Real-time validation feedback
        form.querySelectorAll('.form-control-custom, .form-select-custom').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                    const feedback = this.parentNode.querySelector('.invalid-feedback');
                    if (feedback) feedback.style.display = 'none';
                }
            });
        });

        // Clear errors on modal show
        const modal = document.getElementById('modalLatihanForm');
        modal?.addEventListener('shown.bs.modal', function() {
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');
        });
    });
</script>
