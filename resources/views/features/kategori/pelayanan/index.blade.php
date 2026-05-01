@extends('layouts.admin')

@section('title', 'Kategori Pelayanan')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Kategori Pelayanan</h1>
                <p class="text-muted-custom mb-0">Kelola jenis/kategori pelayanan gereja</p>
            </div>
            <button class="btn btn-custom" type="button" onclick="openModal()">
                <i class="ri-add-line me-1"></i> Tambah Kategori
            </button>
        </div>

        <!-- Alerts Container for AJAX Messages -->
        <div id="alertContainer"></div>

        <!-- Kategori Cards Grid -->
        <div class="row g-4" id="kategoriGrid">
            @if ($kategoris->count() > 0)
                @foreach ($kategoris as $kategori)
                    <div class="col-md-6 col-lg-4 kategori-item" data-id="{{ $kategori->id }}">
                        <div class="card-custom h-100 kategori-card" style="border-top: 4px solid {{ $kategori->warna }};">

                            <!-- Status Badge -->
                            <div class="position-absolute top-0 end-0 p-3">
                                @if ($kategori->is_active)
                                    <span class="badge badge-status-success">
                                        <i class="ri-check-line me-1"></i> Aktif
                                    </span>
                                @else
                                    <span class="badge badge-status-danger">
                                        <i class="ri-close-line me-1"></i> Non-Aktif
                                    </span>
                                @endif
                            </div>

                            <div class="card-body p-4 pt-5">
                                <!-- Nama Kategori -->
                                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                    <span class="color-dot" style="background: {{ $kategori->warna }};"></span>
                                    {{ $kategori->nama_kategori }}
                                </h5>

                                <!-- Deskripsi -->
                                @if ($kategori->deskripsi)
                                    <p class="text-muted-custom small mb-3 line-clamp-3">
                                        {{ $kategori->deskripsi }}
                                    </p>
                                @endif

                                <!-- Stats -->
                                <div class="d-flex align-items-center gap-2 text-muted-custom small mb-4">
                                    <i class="ri-calendar-line"></i>
                                    <span>{{ $kategori->pelayanans->count() }} Jadwal Pelayanan</span>
                                </div>

                                <!-- Actions -->
                                <div class="d-flex gap-2">
                                    <button class="btn-action btn-action-primary btn-sm w-100 edit-btn" type="button"
                                        data-kategori='@json($kategori)'>
                                        <i class="ri-pencil-line me-1"></i> Edit
                                    </button>
                                    @if ($kategori->pelayanans->count() == 0)
                                        <button class="btn-action btn-action-danger btn-sm w-100 delete-btn" type="button"
                                            data-id="{{ $kategori->id }}" data-name="{{ $kategori->nama_kategori }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <!-- Empty State -->
                <div class="col-12">
                    <div class="card-custom">
                        <div class="card-body p-5 text-center">
                            <div class="empty-state-icon mb-4">
                                <i class="ri-folder-off-line"></i>
                            </div>
                            <h5 class="fw-semibold mb-2">Belum Ada Kategori</h5>
                            <p class="text-muted-custom small mb-4">Mulai dengan menambahkan kategori pelayanan pertama Anda
                            </p>
                            <button class="btn btn-custom" type="button" onclick="openModal()">
                                <i class="ri-add-line me-1"></i> Tambah Kategori Pertama
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- Modal Add/Edit Kategori -->
    <div class="modal fade" id="modalKategori" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">

                <form id="formKategori">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="kategoriId">

                    <!-- Header -->
                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold" id="modalTitle">Tambah Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            onclick="closeModal()"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body p-4">

                        <!-- Nama Kategori -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Nama Kategori <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="nama_kategori" id="namaKategori"
                                class="form-control form-control-custom" required
                                placeholder="Contoh: Praise & Worship, Usher, Multimedia">
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsiKategori" rows="2"
                                class="form-control form-control-custom textarea-custom" placeholder="Deskripsi singkat tentang kategori ini..."></textarea>
                        </div>

                        <!-- Warna Badge -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Warna Badge</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="color-picker-wrapper">
                                    <input type="color" name="warna" id="warnaKategori"
                                        class="form-control-color-custom" value="#4f46e5">
                                    <span class="color-preview" id="colorPreview"></span>
                                </div>
                                <button type="button" class="btn btn-outline-custom btn-sm" onclick="randomizeColor()">
                                    <i class="ri-refresh-line me-1"></i> Acak Warna
                                </button>
                                <small class="text-muted-custom ms-auto">
                                    <i class="ri-magic-line me-1"></i>Diacak otomatis jika kosong
                                </small>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-check form-switch-custom p-3 bg-body-tertiary rounded">
                            <input type="checkbox" name="is_active" id="isActiveKategori"
                                class="form-check-input-custom" value="1" checked>
                            <label class="form-check-label-custom fw-semibold" for="isActiveKategori">
                                Kategori Aktif
                            </label>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal"
                            onclick="closeModal()">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-custom" id="btnSubmit">
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

        /* ===== KATEGORI CARD ===== */
        .kategori-card {
            transition: var(--transition);
            position: relative;
        }

        .kategori-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .color-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            border: 2px solid var(--bg-surface);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ===== COLOR PICKER CUSTOM ===== */
        .color-picker-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-control-color-custom {
            width: 48px;
            height: 40px;
            padding: 0;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-sm);
            cursor: pointer;
            background: var(--bg-surface);
        }

        .form-control-color-custom::-webkit-color-swatch {
            border: none;
            border-radius: calc(var(--radius-sm) - 2px);
        }

        .form-control-color-custom::-moz-color-swatch {
            border: none;
            border-radius: calc(var(--radius-sm) - 2px);
        }

        .color-preview {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-left: -12px;
            border: 2px solid var(--bg-surface);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        /* ===== EMPTY STATE ===== */
        .empty-state-icon {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: var(--bg-body);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 3rem;
            opacity: 0.5;
        }

        /* ===== BADGES ===== */
        .badge-status-success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.25);
            padding: 0.4em 0.75em;
            font-weight: 500;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
        }

        .badge-status-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.25);
            padding: 0.4em 0.75em;
            font-weight: 500;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
        }

        /* ===== ACTION BUTTONS ===== */
        .btn-action {
            border-radius: var(--radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-primary);
            transition: var(--transition);
            font-size: 0.85rem;
            padding: 0.4rem 0.75rem;
            font-weight: 500;
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

        /* ===== DARK MODE ADJUSTMENTS ===== */
        [data-theme="dark"] .bg-body-tertiary {
            background-color: var(--bg-sidebar) !important;
        }

        [data-theme="dark"] .color-dot {
            border-color: var(--bg-sidebar);
        }

        [data-theme="dark"] .color-preview {
            border-color: var(--bg-sidebar);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .kategori-card .card-body {
                padding: 1.25rem !important;
            }

            .color-picker-wrapper {
                flex-wrap: wrap;
                gap: 0.5rem !important;
            }
        }

        /* Loading Overlay */
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-loading::after {
            content: "";
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-left: 0.5rem;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('modalKategori');
            let modalInstance = null;
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                modalInstance = new bootstrap.Modal(modalEl);
            }

            const colorPalette = [
                '#4f46e5', '#10b981', '#f59e0b', '#ec4899', '#06b6d4',
                '#8b5cf6', '#ef4444', '#14b8a6', '#f97316', '#6366f1'
            ];

            const formKategori = document.getElementById('formKategori');
            const namaKategori = document.getElementById('namaKategori');
            const deskripsiKategori = document.getElementById('deskripsiKategori');
            const warnaKategori = document.getElementById('warnaKategori');
            const isActiveKategori = document.getElementById('isActiveKategori');
            const formMethod = document.getElementById('formMethod');
            const kategoriId = document.getElementById('kategoriId');
            const modalTitle = document.getElementById('modalTitle');
            const colorPreview = document.getElementById('colorPreview');
            const btnSubmit = document.getElementById('btnSubmit');
            const alertContainer = document.getElementById('alertContainer');
            const kategoriGrid = document.getElementById('kategoriGrid');

            // Helper: Show Alert
            function showAlert(type, message) {
                const alertClass = type === 'success' ? 'success' : 'error';
                const icon = type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill';

                const alertHtml = `
                    <div class="alert-custom ${alertClass} mb-4" data-auto-close>
                        <i class="${icon}"></i>
                        <span>${message}</span>
                        <button class="alert-close">&times;</button>
                    </div>
                `;

                alertContainer.innerHTML = alertHtml;

                // Auto close logic
                const alertEl = alertContainer.firstElementChild;
                const closeBtn = alertEl.querySelector('.alert-close');
                closeBtn?.addEventListener('click', () => alertEl.remove());

                setTimeout(() => {
                    if (alertEl) {
                        alertEl.style.opacity = '0';
                        alertEl.style.transform = 'translateX(-20px)';
                        setTimeout(() => alertEl.remove(), 300);
                    }
                }, 5000);
            }

            // Helper: Clear Validation Errors
            function clearErrors() {
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            }

            // Helper: Show Validation Errors
            function showErrors(errors) {
                clearErrors();
                for (const [field, messages] of Object.entries(errors)) {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = messages[0];
                        }
                    }
                }
            }

            function openModal() {
                resetForm();
                randomizeColor();
                modalTitle.textContent = 'Tambah Kategori';
                formKategori.action = "{{ route('admin.kategori.pelayanan.store') }}";
                formMethod.value = 'POST';
                kategoriId.value = '';

                if (modalInstance) {
                    modalInstance.show();
                } else {
                    modalEl.style.display = 'block';
                    modalEl.classList.add('show');
                }
                document.body.classList.add('modal-open');
                document.body.style.overflow = 'hidden';
            }

            function openEditModal(kategori) {
                modalTitle.textContent = 'Edit Kategori';
                formKategori.action = `/admin/pelayanan/kategori/${kategori.id}`;
                formMethod.value = 'PUT';
                kategoriId.value = kategori.id;

                namaKategori.value = kategori.nama_kategori || '';
                deskripsiKategori.value = kategori.deskripsi || '';
                warnaKategori.value = kategori.warna || '#4f46e5';
                isActiveKategori.checked = kategori.is_active == 1;

                updateColorPreview(warnaKategori.value);
                clearErrors();

                if (modalInstance) {
                    modalInstance.show();
                } else {
                    modalEl.style.display = 'block';
                    modalEl.classList.add('show');
                }
                document.body.classList.add('modal-open');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                if (modalInstance) {
                    modalInstance.hide();
                } else {
                    modalEl.style.display = 'none';
                    modalEl.classList.remove('show');
                }
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                setTimeout(() => resetForm(), 300);
            }

            function resetForm() {
                if (formKategori) formKategori.reset();
                if (namaKategori) namaKategori.value = '';
                if (deskripsiKategori) deskripsiKategori.value = '';
                if (isActiveKategori) isActiveKategori.checked = true;
                if (kategoriId) kategoriId.value = '';
                if (colorPreview) colorPreview.style.backgroundColor = '';
                clearErrors();
            }

            function randomizeColor() {
                const randomColor = colorPalette[Math.floor(Math.random() * colorPalette.length)];
                if (warnaKategori) warnaKategori.value = randomColor;
                updateColorPreview(randomColor);
            }

            function updateColorPreview(color) {
                if (colorPreview) {
                    colorPreview.style.backgroundColor = color;
                }
            }

            // Event Listeners
            if (warnaKategori) {
                warnaKategori.addEventListener('input', function(e) {
                    updateColorPreview(e.target.value);
                });
            }

            // Handle Form Submit via AJAX
            formKategori.addEventListener('submit', function(e) {
                e.preventDefault();
                clearErrors();

                const formData = new FormData(formKategori);
                const method = formMethod.value;
                const url = formKategori.action;

                // Convert FormData to object for easier handling if needed, or send directly
                // Laravel accepts FormData directly

                btnSubmit.classList.add('btn-loading');
                btnSubmit.disabled = true;

                fetch(url, {
                        method: method === 'POST' ? 'POST' :
                        'POST', // Laravel uses POST with _method override
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.content || '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            // Don't set Content-Type for FormData, browser sets it with boundary
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', data.message);
                            closeModal();

                            // Refresh page to show new data or update DOM manually
                            // For simplicity and consistency with Blade rendering, we reload
                            // But if you want no reload, you'd need to append the card HTML here
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showAlert('error', data.message || 'Terjadi kesalahan');
                            if (data.errors) {
                                showErrors(data.errors);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('error', 'Gagal terhubung ke server');
                    })
                    .finally(() => {
                        btnSubmit.classList.remove('btn-loading');
                        btnSubmit.disabled = false;
                    });
            });

            // Handle Edit Button Click
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const kategori = JSON.parse(this.getAttribute('data-kategori'));
                    openEditModal(kategori);
                });
            });

            // Handle Delete Button Click
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');

                    if (confirm(`Yakin ingin menghapus kategori "${name}"?`)) {
                        const url = `/admin/pelayanan/kategori/${id}`;

                        fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]')?.content ||
                                        '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    showAlert('success', data.message);
                                    // Remove card from DOM
                                    const card = document.querySelector(
                                        `.kategori-item[data-id="${id}"]`);
                                    if (card) {
                                        card.remove();
                                        // Check if grid is empty
                                        if (document.querySelectorAll('.kategori-item')
                                            .length === 0) {
                                            window.location
                                        .reload(); // Reload to show empty state
                                        }
                                    }
                                } else {
                                    showAlert('error', data.message || 'Gagal menghapus data');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                showAlert('error', 'Gagal terhubung ke server');
                            });
                    }
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && (modalEl.classList.contains('show') || modalEl.style.display ===
                        'block')) {
                    closeModal();
                }
            });

            if (modalEl) {
                modalEl.addEventListener('click', function(e) {
                    if (e.target === modalEl) {
                        closeModal();
                    }
                });
            }

            if (warnaKategori && colorPreview) {
                updateColorPreview(warnaKategori.value);
            }

            window.openModal = openModal;
            window.openEditModal = openEditModal;
            window.closeModal = closeModal;
            window.randomizeColor = randomizeColor;
        });
    </script>
@endsection
