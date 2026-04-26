@extends('layouts.admin')

@section('title', 'Kategori Dokumentasi')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Kategori Dokumentasi</h1>
                <p class="text-muted-custom mb-0">Kelola kategori album dokumentasi</p>
            </div>
            <button type="button" class="btn btn-custom" onclick="openModal()">
                <i class="ri-add-line me-1"></i> Tambah Kategori
            </button>
        </div>

        <!-- Success Alert -->
        @if (session('success'))
            <div class="alert-custom success mb-4" data-auto-close>
                <i class="ri-checkbox-circle-fill"></i>
                <span>{{ session('success') }}</span>
                <button class="alert-close">&times;</button>
            </div>
        @endif

        <!-- Categories Grid -->
        @if ($kategoris->count() > 0)
            <div class="row g-4" id="categoriesGrid">
                @foreach ($kategoris as $kategori)
                    <div class="col-md-6 col-lg-4">
                        <div class="category-card" style="--category-color: {{ $kategori->warna }};">

                            <!-- Card Header with Color Accent -->
                            <div class="category-card-header" style="border-top-color: {{ $kategori->warna }};">
                                <h6 class="category-card-title">{{ $kategori->nama_kategori }}</h6>
                                <span
                                    class="badge {{ $kategori->is_active ? 'badge-status-active' : 'badge-status-inactive' }}">
                                    {{ $kategori->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </div>

                            <!-- Card Body -->
                            <div class="category-card-body">
                                @if ($kategori->deskripsi)
                                    <p class="category-card-description text-muted-custom small mb-3">
                                        {{ $kategori->deskripsi }}
                                    </p>
                                @endif

                                <div class="category-card-meta mb-3">
                                    <i class="ri-image-line me-1"></i>
                                    <span class="fw-medium">{{ $kategori->albums->count() }}</span> Albums
                                </div>

                                <!-- Color Preview -->
                                <div class="category-color-preview mb-3">
                                    <span class="small text-muted-custom me-2">Warna:</span>
                                    <span class="color-dot" style="background: {{ $kategori->warna }};"></span>
                                    <code class="small text-muted-custom">{{ $kategori->warna }}</code>
                                </div>

                                <!-- Actions -->
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-custom btn-sm flex-fill"
                                        onclick="openEditModal({{ json_encode($kategori) }})">
                                        <i class="ri-pencil-line me-1"></i> Edit
                                    </button>
                                    @if ($kategori->albums->count() == 0)
                                        <form action="{{ route('admin.dokumentasi-kategori.destroy', $kategori) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirmDelete(event, '{{ $kategori->nama_kategori }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="empty-state-card text-center py-5">
                <div class="empty-state-icon mb-3">
                    <i class="ri-folder-line"></i>
                </div>
                <h5 class="fw-semibold mb-2">Belum Ada Kategori</h5>
                <p class="text-muted-custom small mb-4">Mulai buat kategori pertama untuk mengorganisir album dokumentasi
                </p>
                <button type="button" class="btn btn-custom" onclick="openModal()">
                    <i class="ri-add-line me-1"></i> Tambah Kategori
                </button>
            </div>
        @endif

    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="modalKategori" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="formKategori" method="POST">
                    @csrf
                    <input type="hidden" id="methodField" name="_method" value="">

                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold" id="modalTitle">Tambah Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Nama Kategori <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="nama_kategori" id="namaKategori"
                                class="form-control form-control-custom" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsiKategori" class="form-control form-control-custom textarea-custom"
                                rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Warna</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="color" name="warna" id="warnaKategori"
                                    class="form-control form-control-color" value="#4f46e5"
                                    style="width: 60px; height: 40px; padding: 4px; border-radius: var(--radius-sm);">
                                <span class="text-muted-custom small" id="colorValue">#4f46e5</span>
                            </div>
                        </div>

                        <div class="form-check form-switch-custom">
                            <input type="checkbox" name="is_active" id="isActiveKategori" class="form-check-input-custom"
                                value="1" checked>
                            <label class="form-check-label-custom" for="isActiveKategori">Aktif</label>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom">
                            <i class="ri-save-line me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
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

        /* ===== CATEGORY CARD ===== */
        .category-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            border-top: 4px solid var(--category-color, var(--accent-blue));
            overflow: hidden;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .category-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent-blue);
        }

        .category-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 4px solid var(--category-color, var(--accent-blue));
        }

        .category-card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            line-height: 1.4;
        }

        .category-card-body {
            padding: 1rem 1.25rem 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .category-card-description {
            line-height: 1.5;
        }

        .category-card-meta {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .category-card-meta i {
            color: var(--accent-blue);
        }

        /* Color Preview */
        .category-color-preview {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .color-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* ===== STATUS BADGES ===== */
        .badge-status-active {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-inactive {
            background: rgba(100, 116, 139, 0.15);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
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

        .form-control-color {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            cursor: pointer;
            padding: 2px;
        }

        .form-control-color::-webkit-color-swatch {
            border: none;
            border-radius: calc(var(--radius-sm) - 2px);
        }

        .form-control-color::-moz-color-swatch {
            border: none;
            border-radius: calc(var(--radius-sm) - 2px);
        }

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

        .form-check-input-custom[type="checkbox"] {
            border-radius: 4px;
        }

        .form-check-label-custom {
            font-size: 0.9rem;
            color: var(--text-primary);
            cursor: pointer;
            margin-left: 0.35rem;
        }

        .form-switch-custom .form-check-input-custom {
            width: 40px;
            height: 22px;
            border-radius: 11px;
            background-image: none;
            background-color: var(--border-color);
        }

        .form-switch-custom .form-check-input-custom:checked {
            background-color: var(--accent-blue);
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

        /* ===== MODAL CUSTOM ===== */
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

        /* ===== ALERT CUSTOM ===== */
        .alert-custom {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.25rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            animation: slideIn 0.3s ease;
        }

        .alert-custom.success {
            background: rgba(34, 197, 94, 0.1);
            border-left: 4px solid #22c55e;
            color: #166534;
        }

        .alert-custom i {
            font-size: 1.25rem;
        }

        @keyframes slideIn {
            from {
                transform: translateX(-20px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* ===== DARK MODE ===== */
        [data-theme="dark"] .empty-state-card {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .empty-state-icon {
            background: var(--bg-sidebar);
        }

        [data-theme="dark"] .category-card {
            background: var(--bg-surface);
        }

        [data-theme="dark"] .color-dot {
            border-color: var(--border-color);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .category-card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .d-flex.gap-2 {
                flex-direction: column;
            }

            .d-flex.gap-2 .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== MODAL INSTANCE =====
            let modalKategori;
            const modalEl = document.getElementById('modalKategori');
            if (modalEl && bootstrap.Modal) {
                modalKategori = new bootstrap.Modal(modalEl);
            }

            // ===== COLOR PICKER PREVIEW =====
            const colorInput = document.getElementById('warnaKategori');
            const colorValue = document.getElementById('colorValue');

            colorInput?.addEventListener('input', function() {
                if (colorValue) colorValue.textContent = this.value;
            });

            // ===== OPEN MODAL (CREATE) =====
            window.openModal = function() {
                resetForm();
                document.getElementById('modalTitle').textContent = 'Tambah Kategori';
                document.getElementById('formKategori').action =
                    "{{ route('admin.dokumentasi-kategori.store') }}";
                document.getElementById('methodField').value = '';

                if (modalKategori) modalKategori.show();
            }

            // ===== OPEN MODAL (EDIT) =====
            window.openEditModal = function(kategori) {
                // Parse if string
                if (typeof kategori === 'string') {
                    try {
                        kategori = JSON.parse(kategori);
                    } catch (e) {
                        return;
                    }
                }

                document.getElementById('modalTitle').textContent = 'Edit Kategori';
                document.getElementById('formKategori').action = `/admin/dokumentasi-kategori/${kategori.id}`;
                document.getElementById('methodField').value = 'PUT';

                document.getElementById('namaKategori').value = kategori.nama_kategori || '';
                document.getElementById('deskripsiKategori').value = kategori.deskripsi || '';
                document.getElementById('warnaKategori').value = kategori.warna || '#4f46e5';
                document.getElementById('isActiveKategori').checked = kategori.is_active == 1;

                if (colorValue) colorValue.textContent = kategori.warna || '#4f46e5';

                if (modalKategori) modalKategori.show();
            }

            // ===== CLOSE MODAL =====
            window.closeModal = function() {
                if (modalKategori) modalKategori.hide();
            }

            // ===== RESET FORM =====
            function resetForm() {
                const form = document.getElementById('formKategori');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated');
                }
                document.getElementById('methodField').value = '';
                document.getElementById('warnaKategori').value = '#4f46e5';
                document.getElementById('isActiveKategori').checked = true;
                if (colorValue) colorValue.textContent = '#4f46e5';
            }

            // ===== CONFIRM DELETE =====
            window.confirmDelete = function(e, nama) {
                if (typeof Swal !== 'undefined') {
                    e.preventDefault();
                    const form = e.target.closest('form');

                    Swal.fire({
                        title: 'Hapus kategori?',
                        text: `Kategori "${nama}" akan dihapus permanen.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed && form) {
                            form.submit();
                        }
                    });
                    return false;
                }
                return true; // Fallback to native confirm
            }

            // ===== FORM SUBMIT =====
            const formKategori = document.getElementById('formKategori');
            formKategori?.addEventListener('submit', function(e) {
                if (!formKategori.checkValidity()) {
                    e.preventDefault();
                    formKategori.classList.add('was-validated');
                    return;
                }
                // Let form submit normally (Blade form with CSRF)
            });

            // ===== AUTO-CLOSE ALERTS =====
            document.querySelectorAll('[data-auto-close]').forEach(alert => {
                const closeBtn = alert.querySelector('.alert-close');
                closeBtn?.addEventListener('click', () => alert.remove());
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

            // ===== KEYBOARD: ESC TO CLOSE MODAL =====
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modalKategori?._isShown) {
                    modalKategori.hide();
                }
            });
        });
    </script>
@endsection
