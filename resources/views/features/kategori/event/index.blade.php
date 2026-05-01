@extends('layouts.admin')

@section('title', 'Kategori Event')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Kategori Event</h1>
                <p class="text-muted-custom mb-0">Kelola kategori event untuk website</p>
            </div>
            <button type="button" class="btn btn-custom" id="btnAddKategori">
                <i class="ri-add-line me-1"></i> Tambah Kategori
            </button>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="mb-4"></div>

        <!-- Categories Grid -->
        @if ($kategoris->count() > 0)
            <div class="row g-4" id="categoriesGrid">
                @foreach ($kategoris as $kategori)
                    <div class="col-md-6 col-lg-4 kategori-item" data-id="{{ $kategori->id }}">
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
                                    <i class="ri-calendar-line me-1"></i>
                                    <span class="fw-medium">{{ $kategori->events->count() }}</span> Events
                                </div>

                                <!-- Color Preview -->
                                <div class="category-color-preview mb-3">
                                    <span class="small text-muted-custom me-2">Warna:</span>
                                    <span class="color-dot" style="background: {{ $kategori->warna }};"></span>
                                    <code class="small text-muted-custom">{{ $kategori->warna }}</code>
                                </div>

                                <!-- Actions -->
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-custom btn-sm flex-fill btn-edit-kategori"
                                        data-id="{{ $kategori->id }}" data-nama="{{ e($kategori->nama_kategori) }}"
                                        data-deskripsi="{{ e($kategori->deskripsi ?? '') }}"
                                        data-warna="{{ $kategori->warna }}"
                                        data-is-active="{{ $kategori->is_active ? 1 : 0 }}">
                                        <i class="ri-pencil-line me-1"></i> Edit
                                    </button>
                                    @if ($kategori->events->count() == 0)
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete-kategori"
                                            data-id="{{ $kategori->id }}" data-nama="{{ e($kategori->nama_kategori) }}"
                                            title="Hapus">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
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
                <p class="text-muted-custom small mb-4">Mulai buat kategori pertama untuk mengorganisir event</p>
                <button type="button" class="btn btn-custom" id="btnAddKategori2">
                    <i class="ri-add-line me-1"></i> Tambah Kategori
                </button>
            </div>
        @endif

    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="modalKategori" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="formKategori">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="kategoriId">

                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold" id="modalTitle">Tambah Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="alert alert-danger d-none" id="formErrors">
                            <i class="ri-error-warning-line me-2"></i>
                            <ul class="mb-0 small" id="errorList"></ul>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Nama Kategori <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="nama_kategori" id="namaKategori"
                                class="form-control form-control-custom" required maxlength="100">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsiKategori" class="form-control form-control-custom textarea-custom"
                                rows="2" maxlength="500"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Warna</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="color" name="warna" id="warnaKategori"
                                    class="form-control form-control-color" value="#4f46e5"
                                    style="width: 60px; height: 40px; padding: 4px; border-radius: var(--radius-sm);">
                                <span class="text-muted-custom small" id="warnaPreview">#4f46e5</span>
                            </div>
                        </div>

                        <div class="form-check form-switch-custom">
                            <input type="checkbox" name="is_active" id="isActiveKategori"
                                class="form-check-input-custom" value="1" checked>
                            <label class="form-check-label-custom" for="isActiveKategori">Aktif</label>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSubmitKategori">
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
        .form-control-custom {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.625rem 0.875rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .form-control-custom:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        .form-control-custom.is-invalid {
            border-color: #ef4444;
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

        .alert-custom.error {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            color: #991b1b;
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

        /* Loading Button */
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
            const modalEl = document.getElementById('modalKategori');
            let modalKategori = null;
            if (modalEl && typeof bootstrap !== 'undefined') {
                modalKategori = new bootstrap.Modal(modalEl);
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            const form = document.getElementById('formKategori');
            const btnSave = document.getElementById('btnSubmitKategori');
            const alertContainer = document.getElementById('alertContainer');
            const colorInput = document.getElementById('warnaKategori');
            const colorValue = document.getElementById('warnaPreview');

            let isEditMode = false;

            // Color Picker Preview
            if (colorInput) {
                colorInput.addEventListener('input', function() {
                    if (colorValue) colorValue.textContent = this.value;
                });
            }

            // Helper: Show Alert
            function showAlert(type, message) {
                const alertClass = type === 'success' ? 'success' : 'error';
                const icon = type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill';

                const html = `
                    <div class="alert-custom ${alertClass}" data-auto-close>
                        <i class="${icon}"></i>
                        <span>${message}</span>
                        <button class="alert-close">&times;</button>
                    </div>`;

                alertContainer.innerHTML = html;
                const alertEl = alertContainer.firstElementChild;

                const closeBtn = alertEl.querySelector('.alert-close');
                if (closeBtn) closeBtn.addEventListener('click', () => alertEl.remove());

                setTimeout(() => {
                    if (alertEl) {
                        alertEl.style.opacity = '0';
                        alertEl.style.transform = 'translateX(-20px)';
                        setTimeout(() => alertEl.remove(), 300);
                    }
                }, 5000);
            }

            // Helper: Clear Errors
            function clearErrors() {
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
                const errorBox = document.getElementById('formErrors');
                if (errorBox) errorBox.classList.add('d-none');
            }

            // Helper: Show Validation Errors
            function showValidationErrors(errors) {
                clearErrors();
                const errorBox = document.getElementById('formErrors');
                const errorList = document.getElementById('errorList');

                if (errorBox && errorList) {
                    let html = '';
                    for (const [field, messages] of Object.entries(errors)) {
                        const input = document.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                        }
                        const msgArray = Array.isArray(messages) ? messages : [messages];
                        msgArray.forEach(msg => {
                            html += `<li><strong>${field}:</strong> ${msg}</li>`;
                        });
                    }
                    errorList.innerHTML = html;
                    errorBox.classList.remove('d-none');
                }
            }

            // Open Modal
            function openModal(mode = 'create', data = null) {
                clearErrors();
                isEditMode = mode === 'edit';
                document.getElementById('modalTitle').textContent = mode === 'edit' ? 'Edit Kategori' :
                    'Tambah Kategori';
                document.getElementById('formMethod').value = mode === 'edit' ? 'PUT' : 'POST';

                if (mode === 'edit' && data) {
                    document.getElementById('kategoriId').value = data.id;
                    document.getElementById('namaKategori').value = data.nama || '';
                    document.getElementById('deskripsiKategori').value = data.deskripsi || '';
                    document.getElementById('warnaKategori').value = data.warna || '#4f46e5';
                    document.getElementById('isActiveKategori').checked = data.isActive == 1;
                    if (colorValue) colorValue.textContent = data.warna || '#4f46e5';
                } else {
                    document.getElementById('kategoriId').value = '';
                    document.getElementById('namaKategori').value = '';
                    document.getElementById('deskripsiKategori').value = '';
                    document.getElementById('warnaKategori').value = '#4f46e5';
                    document.getElementById('isActiveKategori').checked = true;
                    if (colorValue) colorValue.textContent = '#4f46e5';
                }

                if (modalKategori) modalKategori.show();
            }

            // Close Modal
            function closeModal() {
                if (modalKategori) modalKategori.hide();
            }

            // Event Listeners for Buttons
            document.getElementById('btnAddKategori')?.addEventListener('click', () => openModal('create'));
            document.getElementById('btnAddKategori2')?.addEventListener('click', () => openModal('create'));

            document.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.btn-edit-kategori');
                if (editBtn) {
                    e.preventDefault();
                    const data = {
                        id: editBtn.dataset.id,
                        nama: editBtn.dataset.nama,
                        deskripsi: editBtn.dataset.deskripsi,
                        warna: editBtn.dataset.warna,
                        isActive: editBtn.dataset.isActive
                    };
                    openModal('edit', data);
                }
            });

            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.btn-delete-kategori');
                if (deleteBtn) {
                    e.preventDefault();
                    const id = deleteBtn.dataset.id;
                    const nama = deleteBtn.dataset.nama;

                    if (confirm(`Yakin ingin menghapus kategori "${nama}"?`)) {
                        deleteKategori(id);
                    }
                }
            });

            // Handle Form Submit
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    clearErrors();

                    const id = document.getElementById('kategoriId').value;
                    const method = document.getElementById('formMethod').value;

                    let url = "{{ route('admin.event-kategori.store') }}";
                    if (isEditMode && id) {
                        url = `/admin/event-kategori/${id}`;
                    }

                    const formData = new FormData(form);

                    btnSave.classList.add('btn-loading');
                    btnSave.disabled = true;

                    fetch(url, {
                            method: 'POST', // Laravel handles PUT via _method field in FormData
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(response => {
                            return response.json().then(data => ({
                                status: response.status,
                                body: data
                            }));
                        })
                        .then(({
                            status,
                            body
                        }) => {
                            if (status === 200 || status === 201) {
                                showAlert('success', body.message);
                                closeModal();
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                if (body.errors) {
                                    showValidationErrors(body.errors);
                                } else {
                                    showAlert('error', body.message || 'Terjadi kesalahan');
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showAlert('error', 'Gagal terhubung ke server');
                        })
                        .finally(() => {
                            btnSave.classList.remove('btn-loading');
                            btnSave.disabled = false;
                        });
                });
            }

            // Handle Delete
            function deleteKategori(id) {
                const url = `/admin/event-kategori/${id}`;

                fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', data.message);
                            // Remove item from DOM
                            const item = document.querySelector(`.kategori-item[data-id="${id}"]`);
                            if (item) item.remove();

                            // Check if grid is empty
                            if (document.querySelectorAll('.kategori-item').length === 0) {
                                setTimeout(() => window.location.reload(), 1000);
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

            // Close on Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modalKategori?._isShown) {
                    closeModal();
                }
            });
        });

        function getRandomColor() {
            const letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }

        const modalKategori = document.getElementById('modalKategori');
        const warnaInput = document.getElementById('warnaKategori');
        const warnaPreview = document.getElementById('warnaPreview');

        modalKategori.addEventListener('show.bs.modal', function() {
            const randomColor = getRandomColor();
            warnaInput.value = randomColor;
            warnaPreview.textContent = randomColor;
        });

        warnaInput.addEventListener('input', function() {
            warnaPreview.textContent = this.value;
        });
    </script>
@endsection
