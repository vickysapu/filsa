@extends('layouts.admin')

@section('title', 'Buat Jadwal Pelayanan')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-custom btn-sm">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div>
                <h1 class="h4 fw-bold mb-1">Buat Jadwal Pelayanan Baru</h1>
                <p class="text-muted-custom mb-0">Tambah jadwal pelayanan dengan pemilihan petugas secara massal</p>
            </div>
        </div>

        <!-- Validation Error Alert -->
        @if ($errors->any())
            <div class="alert-custom error mb-4" data-auto-close>
                <i class="ri-error-warning-fill"></i>
                <div>
                    <strong>Terjadi kesalahan validasi</strong>
                    <ul class="mb-0 mt-1 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button class="alert-close">&times;</button>
            </div>
        @endif

        <form action="{{ route('admin.jadwal.store') }}" method="POST">
            @csrf

            <div class="row g-4">

                <!-- Kolom Kiri: Informasi Jadwal -->
                <div class="col-lg-6">
                    <div class="card-custom h-100">
                        <div class="card-header-custom border-bottom border-custom py-3 px-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-calendar-line text-primary"></i>
                                <h6 class="mb-0 fw-semibold">Informasi Jadwal</h6>
                            </div>
                        </div>
                        <div class="card-body p-4">

                            <!-- Kategori -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Kategori Pelayanan <span
                                        class="text-danger">*</span></label>
                                <select name="kategori_id"
                                    class="form-select form-select-custom @error('kategori_id') is-invalid @enderror">
                                    <option value="">Pilih Kategori...</option>
                                    @foreach ($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}"
                                            {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategori->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nama Pelayanan -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Nama Pelayanan <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nama_pelayanan"
                                    class="form-control form-control-custom @error('nama_pelayanan') is-invalid @enderror"
                                    value="{{ old('nama_pelayanan') }}"
                                    placeholder="Contoh: Ibadah Minggu Pagi, Kebaktian Pemuda, dll">
                                @error('nama_pelayanan')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tanggal & Waktu -->
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted-custom">Tanggal <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="tanggal"
                                        class="form-control form-control-custom @error('tanggal') is-invalid @enderror"
                                        value="{{ old('tanggal', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}">
                                    @error('tanggal')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted-custom">Waktu <span
                                            class="text-danger">*</span></label>
                                    <input type="time" name="waktu"
                                        class="form-control form-control-custom @error('waktu') is-invalid @enderror"
                                        value="{{ old('waktu', '08:00') }}">
                                    @error('waktu')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Lokasi -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Lokasi <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="lokasi"
                                    class="form-control form-control-custom @error('lokasi') is-invalid @enderror"
                                    value="{{ old('lokasi', 'Gedung Utama') }}"
                                    placeholder="Contoh: Gedung Utama, Aula Lantai 2">
                                @error('lokasi')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Keterangan -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Keterangan</label>
                                <textarea name="keterangan"
                                    class="form-control form-control-custom textarea-custom @error('keterangan') is-invalid @enderror" rows="3"
                                    placeholder="Catatan tambahan untuk jadwal ini...">{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="form-label small fw-semibold text-muted-custom d-block mb-2">Status Jadwal
                                    <span class="text-danger">*</span></label>
                                <div class="d-flex flex-wrap gap-3">
                                    <label class="form-check-radio">
                                        <input type="radio" name="status" value="draft" class="form-check-input-custom"
                                            {{ old('status', 'draft') == 'draft' ? 'checked' : '' }}>
                                        <span class="form-check-label-custom">
                                            <span class="badge badge-status-draft me-1">Draft</span> Belum Publik
                                        </span>
                                    </label>
                                    <label class="form-check-radio">
                                        <input type="radio" name="status" value="active" class="form-check-input-custom"
                                            {{ old('status') == 'active' ? 'checked' : '' }}>
                                        <span class="form-check-label-custom">
                                            <span class="badge badge-status-active me-1">Aktif</span> Sudah Publik
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

                <!-- Kolom Kanan: Pemilihan Petugas -->
                <div class="col-lg-6">
                    <div class="card-custom h-100">
                        <div class="card-header-custom border-bottom border-custom py-3 px-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-team-line text-primary"></i>
                                <h6 class="mb-0 fw-semibold">Petugas Pelayanan</h6>
                            </div>
                        </div>
                        <div class="card-body p-4">

                            <p class="text-muted-custom small mb-3">
                                <i class="ri-information-line me-1"></i> Pilih beberapa jemaat sekaligus untuk dijadikan
                                petugas pelayanan
                            </p>

                            <!-- Search Box -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Cari Jemaat</label>
                                <div class="position-relative">
                                    <i
                                        class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted-custom"></i>
                                    <input type="text" id="searchJemaat" class="form-control form-control-custom ps-5"
                                        placeholder="Ketik nama jemaat...">
                                </div>
                            </div>

                            <!-- Quick Select Buttons -->
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                                <button type="button" class="btn btn-outline-custom btn-sm" onclick="selectAll()">
                                    <i class="ri-checkbox-circle-line me-1"></i> Pilih Semua
                                </button>
                                <button type="button" class="btn btn-outline-custom btn-sm" onclick="deselectAll()">
                                    <i class="ri-close-circle-line me-1"></i> Batal Semua
                                </button>
                                <span class="badge badge-secondary-custom ms-auto" id="selectedCount">0 Terpilih</span>
                            </div>

                            <!-- Jemaat List with Checkboxes -->
                            <div class="jemaat-list-container mb-3" id="jemaatList">
                                @foreach ($jemaats as $jemaat)
                                    <label class="jemaat-item">
                                        <div class="d-flex align-items-center gap-3 flex-1">
                                            <input type="checkbox" name="jemaat_ids[]" value="{{ $jemaat->id }}"
                                                class="jemaat-checkbox form-check-input-custom"
                                                {{ in_array($jemaat->id, old('jemaat_ids', [])) ? 'checked' : '' }}>

                                            <div class="jemaat-avatar">
                                                {{ substr($jemaat->nama_lengkap, 0, 1) }}
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <div class="jemaat-name">{{ $jemaat->nama_lengkap }}</div>
                                                @if ($jemaat->no_hp)
                                                    <div class="jemaat-phone text-muted-custom small">{{ $jemaat->no_hp }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Role Input -->
                                        <input type="text" name="roles[]"
                                            class="form-control form-control-custom role-input"
                                            placeholder="Role (opsional)" value="{{ old('roles.' . $loop->index, '') }}">
                                    </label>
                                @endforeach
                            </div>

                            @error('jemaat_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <!-- Info Box -->
                            <div class="alert-info-custom">
                                <div class="d-flex gap-3">
                                    <i class="ri-lightbulb-line text-primary mt-1"></i>
                                    <div>
                                        <p class="fw-semibold text-primary mb-2 small">Tips Bulk Selection</p>
                                        <ul class="mb-0 small text-muted-custom" style="padding-left: 1rem;">
                                            <li>Gunakan "Pilih Semua" untuk memilih semua jemaat sekaligus</li>
                                            <li>Isi kolom "Role" untuk setiap jemaat (contoh: Leader, Vocal 1, Usher)</li>
                                            <li>Role bersifat opsional, bisa dikosongkan</li>
                                            <li>Gunakan search untuk menemukan jemaat dengan cepat</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-3 mt-4">
                <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-custom">Batal</a>
                <button type="submit" class="btn btn-custom">
                    <i class="ri-save-line me-1"></i> Simpan Jadwal
                </button>
            </div>
        </form>
    </div>

    <style>
        /* ===== PAGE SPECIFIC STYLES ===== */
        .page-wrapper {
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

        /* ===== JEMAAT LIST ===== */
        .jemaat-list-container {
            max-height: 420px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 0.5rem;
            background: var(--bg-surface);
        }

        .jemaat-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.625rem 0.75rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            margin-bottom: 0.25rem;
            cursor: pointer;
        }

        .jemaat-item:hover {
            background: var(--bg-body);
        }

        .jemaat-item.selected {
            background: rgba(37, 99, 235, 0.08);
            border: 1px solid rgba(37, 99, 235, 0.2);
        }

        .jemaat-checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid var(--border-color);
            border-radius: 4px;
            cursor: pointer;
            accent-color: var(--accent-blue);
        }

        .jemaat-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--accent-blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .jemaat-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-primary);
        }

        .jemaat-phone {
            font-size: 0.8rem;
        }

        .role-input {
            width: 140px;
            padding: 0.375rem 0.625rem;
            font-size: 0.85rem;
        }

        /* ===== FORM ELEMENTS ===== */
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

        .form-control-custom.is-invalid,
        .form-select-custom.is-invalid {
            border-color: #ef4444;
        }

        .form-control-custom.is-invalid:focus,
        .form-select-custom.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
        }

        .invalid-feedback {
            color: #ef4444;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

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
        .badge-status-draft {
            background: rgba(100, 116, 139, 0.15);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.3);
            padding: 0.2em 0.5em;
            font-weight: 500;
            font-size: 0.7rem;
        }

        .badge-status-active {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.2em 0.5em;
            font-weight: 500;
            font-size: 0.7rem;
        }

        .badge-secondary-custom {
            background: var(--bg-body);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            padding: 0.35em 0.65em;
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* ===== ALERT INFO ===== */
        .alert-info-custom {
            background: rgba(37, 99, 235, 0.08);
            border: 1px solid rgba(37, 99, 235, 0.2);
            border-radius: var(--radius-sm);
            padding: 1rem;
        }

        /* ===== UTILITY CLASSES ===== */
        .flex-1 {
            flex: 1;
        }

        .min-w-0 {
            min-width: 0;
        }

        /* ===== DARK MODE ===== */
        [data-theme="dark"] .jemaat-item:hover {
            background: var(--bg-sidebar);
        }

        [data-theme="dark"] .jemaat-item.selected {
            background: rgba(37, 99, 235, 0.15);
        }

        [data-theme="dark"] .alert-info-custom {
            background: rgba(37, 99, 235, 0.12);
            border-color: rgba(37, 99, 235, 0.3);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991px) {
            .role-input {
                width: 120px;
            }
        }

        @media (max-width: 768px) {
            .jemaat-item {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .role-input {
                width: 100%;
                margin-top: 0.5rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== UPDATE SELECTED COUNT =====
            function updateSelectedCount() {
                const count = document.querySelectorAll('.jemaat-checkbox:checked').length;
                const el = document.getElementById('selectedCount');
                if (el) el.textContent = count + ' Terpilih';
            }

            // ===== SELECT ALL =====
            window.selectAll = function() {
                document.querySelectorAll('.jemaat-checkbox').forEach(cb => {
                    cb.checked = true;
                    cb.closest('.jemaat-item')?.classList.add('selected');
                });
                updateSelectedCount();
            }

            // ===== DESELECT ALL =====
            window.deselectAll = function() {
                document.querySelectorAll('.jemaat-checkbox').forEach(cb => {
                    cb.checked = false;
                    cb.closest('.jemaat-item')?.classList.remove('selected');
                });
                updateSelectedCount();
            }

            // ===== SEARCH JEMAAT =====
            const searchInput = document.getElementById('searchJemaat');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const keyword = e.target.value.toLowerCase().trim();
                    document.querySelectorAll('#jemaatList .jemaat-item').forEach(item => {
                        const text = item.textContent.toLowerCase();
                        item.style.display = text.includes(keyword) ? 'flex' : 'none';
                    });
                });
            }

            // ===== CHECKBOX CHANGE HANDLER =====
            document.querySelectorAll('.jemaat-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    const item = this.closest('.jemaat-item');
                    if (item) {
                        item.classList.toggle('selected', this.checked);
                    }
                    updateSelectedCount();
                });
                // Init style
                if (cb.checked) cb.closest('.jemaat-item')?.classList.add('selected');
            });

            // ===== INIT =====
            updateSelectedCount();
        });
    </script>
@endsection
