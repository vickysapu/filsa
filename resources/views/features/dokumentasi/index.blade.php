@extends('layouts.admin')

@section('title', 'Dokumentasi & Gallery')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Dokumentasi</h1>
                <p class="text-muted-custom mb-0">Gallery foto dan dokumentasi kegiatan gereja</p>
            </div>
            @if (auth()->user()->hasPermission('dokumentasi.create'))
                <button type="button" class="btn btn-custom btn-tambah-album">
                    <i class="ri-gallery-upload-line me-1"></i> Buat Album Baru
                </button>
            @endif
        </div>

        <!-- Success Alert -->
        @if (session('success'))
            <div class="alert-custom success mb-4" data-auto-close>
                <i class="ri-checkbox-circle-fill"></i>
                <span>{{ session('success') }}</span>
                <button class="alert-close">&times;</button>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <form id="formFilter">
                    <div class="row g-3 align-items-end">
                        @if ($isSuperAdmin)
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-semibold text-muted-custom mb-2">Cabang</label>
                                <select id="filter_branch" name="branch_id" class="form-select form-select-custom">
                                    <option value="">Semua Cabang</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-muted-custom mb-2">Kategori</label>
                            <select id="kategori_id" name="kategori_id" class="form-select form-select-custom">
                                <option value="">Semua Kategori</option>
                                @foreach ($kategoris as $kat)
                                    <option value="{{ $kat->id }}"
                                        {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                        {{ $kat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-muted-custom mb-2">Event</label>
                            <select id="event_id" name="event_id" class="form-select form-select-custom">
                                <option value="">Semua Event</option>
                                @foreach ($events as $event)
                                    <option value="{{ $event->id }}"
                                        {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                        {{ $event->nama_event }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-muted-custom mb-2">Status</label>
                            <select id="status" name="status" class="form-select form-select-custom">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>
                                    Published
                                </option>
                                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived
                                </option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-muted-custom mb-2">Cari</label>
                            <div class="position-relative">
                                <i
                                    class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted-custom"></i>
                                <input type="text" id="search" name="search"
                                    class="form-control form-control-custom ps-5" value="{{ request('search') }}"
                                    placeholder="Judul album...">
                            </div>
                        </div>
                        <div class="col-12 col-md-auto d-flex gap-2">
                            <button type="button" id="btnFilter" class="btn btn-custom flex-fill flex-md-fill-0">
                                <i class="ri-filter-line me-1"></i> Filter
                            </button>
                            <button type="button" id="btnReset" class="btn btn-outline-custom" title="Reset">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Albums Grid -->
        <div class="row g-4" id="albumsGrid">
            @include('features.dokumentasi._grid')
        </div>

        <!-- Pagination -->
        @if ($albums->hasPages())
            <div class="d-flex justify-content-center mt-4" id="paginationContainer">
                {{ $albums->links('pagination::simple-bootstrap-4') }}
            </div>
        @endif

    </div>

    <!-- ================== MODAL CREATE & EDIT ================== -->
    <div class="modal fade" id="modalAlbumForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-content-custom" style="max-height: 90vh;">

                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold" id="modalAlbumLabel">Buat Album Dokumentasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formAlbum" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="albumId" value="">

                    <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                        <div class="alert alert-danger d-none" id="formErrors">
                            <i class="ri-error-warning-line me-2"></i>
                            <ul class="mb-0 small" id="errorList"></ul>
                        </div>

                        <div class="row g-4">
                            <!-- Kolom Kiri: Informasi Album -->
                            <div class="col-lg-6">
                                <div class="card card-custom h-100">
                                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="ri-image-line me-2 text-primary"></i>Informasi Album
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">

                                        @if ($isSuperAdmin)
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted-custom">Cabang <span
                                                        class="text-danger">*</span></label>
                                                <select name="branch_id" id="branch_id_form"
                                                    class="form-select form-select-custom" required>
                                                    <option value="">Pilih Cabang...</option>
                                                    @foreach ($branches as $branch)
                                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Judul Album <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="judul_album" id="judul_album"
                                                class="form-control form-control-custom" required
                                                placeholder="Contoh: Kebaktian Natal 2024">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Deskripsi</label>
                                            <textarea name="deskripsi" id="deskripsi" class="form-control form-control-custom textarea-custom" rows="3"
                                                placeholder="Deskripsi album..."></textarea>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label
                                                    class="form-label small fw-semibold text-muted-custom">Kategori</label>
                                                <select name="kategori_id" id="kategori_id_form"
                                                    class="form-select form-select-custom">
                                                    <option value="">Tanpa Kategori</option>
                                                    @foreach ($kategoris as $kategori)
                                                        <option value="{{ $kategori->id }}">
                                                            {{ $kategori->nama_kategori }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted-custom">Event
                                                    (Opsional)</label>
                                                <select name="event_id" id="event_id_form"
                                                    class="form-select form-select-custom">
                                                    <option value="">Tidak Ada</option>
                                                    @foreach ($events as $event)
                                                        <option value="{{ $event->id }}">{{ $event->nama_event }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row g-3 mt-3">
                                            <div class="col-6">
                                                <label
                                                    class="form-label small fw-semibold text-muted-custom">Fotografer</label>
                                                <input type="text" name="fotografer" id="fotografer"
                                                    class="form-control form-control-custom">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted-custom">Tanggal Album
                                                    <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_album" id="tanggal_album"
                                                    class="form-control form-control-custom" required>
                                            </div>
                                        </div>

                                        <div class="mb-0">
                                            <label class="form-label small fw-semibold text-muted-custom">Lokasi</label>
                                            <input type="text" name="lokasi" id="lokasi"
                                                class="form-control form-control-custom">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Upload & Opsi -->
                            <div class="col-lg-6">
                                <div class="card card-custom mb-4">
                                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="ri-gallery-upload-line me-2 text-primary"></i>Upload Foto
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Foto Album <span
                                                    class="text-danger">*</span></label>
                                            <input type="file" name="photos[]" id="photos"
                                                class="form-control form-control-custom" accept="image/*" multiple
                                                required>
                                            <small class="text-muted-custom"><i class="ri-information-line me-1"></i>Pilih
                                                multiple files. Max 10MB per file</small>

                                            <!-- Preview Container -->
                                            <div id="photoPreview" class="row g-2 mt-3 d-none"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Opsi Tampilan -->
                                <div class="card card-custom">
                                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="ri-settings-line me-2 text-primary"></i>Opsi Tampilan
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="d-flex flex-column gap-3">
                                            <label class="form-check form-switch-custom">
                                                <input type="checkbox" name="status" value="published"
                                                    id="chkPublished" class="form-check-input-custom">
                                                <span class="form-check-label-custom">Published (Langsung tampil)</span>
                                            </label>
                                            <label class="form-check form-switch-custom">
                                                <input type="checkbox" name="is_featured" value="1"
                                                    id="chkFeatured" class="form-check-input-custom">
                                                <span class="form-check-label-custom">Featured Album</span>
                                            </label>
                                            <label class="form-check form-switch-custom">
                                                <input type="checkbox" name="allow_download" value="1"
                                                    id="chkDownload" class="form-check-input-custom">
                                                <span class="form-check-label-custom">Allow Download</span>
                                            </label>
                                            <label class="form-check form-switch-custom">
                                                <input type="checkbox" name="show_in_website" value="1"
                                                    id="chkShowWeb" class="form-check-input-custom" checked>
                                                <span class="form-check-label-custom">Tampilkan di Website</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSubmitAlbum">
                            <i class="ri-save-line me-1"></i> Simpan Album
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

        /* ===== ALBUM CARD (Grid Item) ===== */
        .album-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            overflow: hidden;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .album-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent-blue);
        }

        .album-thumbnail {
            position: relative;
            aspect-ratio: 16/9;
            overflow: hidden;
            background: var(--bg-body);
        }

        .album-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .album-card:hover .album-thumbnail img {
            transform: scale(1.05);
        }

        .album-badge {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            padding: 0.25em 0.6em;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.95);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .album-badge.featured {
            background: var(--accent-blue);
            color: white;
            border: none;
        }

        .album-body {
            padding: 1rem 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .album-title {
            font-weight: 600;
            font-size: 1rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .album-meta {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
        }

        .album-meta i {
            margin-right: 0.25rem;
            font-size: 0.9rem;
        }

        .album-footer {
            padding: 0.75rem 1.25rem 1.25rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        /* ===== STATUS BADGES ===== */
        .badge-status-draft {
            background: rgba(100, 116, 139, 0.15);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-published {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-archived {
            background: rgba(139, 92, 246, 0.15);
            color: #7c3aed;
            border: 1px solid rgba(139, 92, 246, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        /* ===== ACTION BUTTONS ===== */
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

        .btn-action-info {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .btn-action-info:hover {
            background: var(--accent-blue);
            color: white;
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

        .form-control-custom[type="file"] {
            padding: 0.375rem 0.625rem;
        }

        .photo-preview-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .photo-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            text-align: center;
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

        .modal-dialog-scrollable .modal-content {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            flex: 1 1 auto;
        }

        .modal-dialog-scrollable .modal-header,
        .modal-dialog-scrollable .modal-footer {
            flex-shrink: 0;
        }

        /* ===== PAGINATION ===== */
        .pagination .page-link {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.5rem 0.875rem;
            margin: 0 2px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .pagination .page-link:hover,
        .pagination .page-item.active .page-link {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        .pagination .page-item.disabled .page-link {
            background: var(--bg-body);
            color: var(--text-secondary);
        }

        /* ===== DARK MODE ===== */
        [data-theme="dark"] .bg-body-tertiary {
            background: var(--bg-sidebar) !important;
        }

        [data-theme="dark"] .album-card {
            background: var(--bg-surface);
        }

        [data-theme="dark"] .album-badge {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .empty-state-card {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .empty-state-icon {
            background: var(--bg-sidebar);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .album-thumbnail {
                aspect-ratio: 4/3;
            }

            .table-responsive table {
                min-width: 700px;
            }

            .d-flex.flex-wrap.gap-2 {
                flex-direction: column;
            }

            .d-flex.flex-wrap.gap-2 .btn {
                width: 100%;
                justify-content: center;
            }

            .photo-preview-item {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== MODAL INSTANCE =====
            let modalAlbum;
            const modalEl = document.getElementById('modalAlbumForm');
            if (modalEl && bootstrap.Modal) {
                modalAlbum = new bootstrap.Modal(modalEl);
            }

            // ===== TOOLTIP INIT =====
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });

            // ===== CONSTANTS =====
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            // ===== EVENT: Tambah Album =====
            document.querySelector('.btn-tambah-album')?.addEventListener('click', function() {
                resetForm('create');
                document.getElementById('modalAlbumLabel').textContent = 'Buat Album Dokumentasi';
                if (modalAlbum) modalAlbum.show();
            });

            // ===== EVENT: Edit Album (delegation) =====
            document.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.btn-edit-album');
                if (editBtn) {
                    e.preventDefault();
                    loadAlbumForEdit(editBtn.dataset.id);
                }
            });

            // ===== EVENT: Delete Album (delegation) =====
            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.btn-delete-album');
                if (deleteBtn) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Hapus Album?',
                        text: 'Album dan semua fotonya akan dihapus permanen.',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) {
                            fetch(`/admin/dokumentasi/${deleteBtn.dataset.id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': csrfToken,
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(r => r.json())
                                .then(data => {
                                    if (data.success) {
                                        showToast('success', 'Berhasil', data.message);
                                        reloadGrid();
                                    } else {
                                        showToast('error', 'Gagal', data.message);
                                    }
                                });
                        }
                    });
                }
            });

            // ===== EVENT: Filter =====
            document.getElementById('btnFilter')?.addEventListener('click', function() {
                reloadGrid();
            });

            document.getElementById('btnReset')?.addEventListener('click', function() {
                document.getElementById('kategori_id').value = '';
                document.getElementById('event_id').value = '';
                document.getElementById('status').value = '';
                document.getElementById('search').value = '';
                @if ($isSuperAdmin)
                    document.getElementById('filter_branch').value = '';
                @endif
                reloadGrid();
            });

            @if ($isSuperAdmin)
                document.getElementById('filter_branch')?.addEventListener('change', function() {
                    reloadGrid();
                });
            @endif

            // ===== EVENT: Form Submit =====
            const formAlbum = document.getElementById('formAlbum');
            const btnSubmit = document.getElementById('btnSubmitAlbum');

            formAlbum?.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!formAlbum.checkValidity()) {
                    formAlbum.classList.add('was-validated');
                    return;
                }

                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
                }

                const formData = new FormData(formAlbum);

                try {
                    const response = await fetch(formAlbum.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        showToast('success', 'Berhasil', data.message || 'Album berhasil disimpan');
                        if (modalAlbum) modalAlbum.hide();
                        reloadGrid();
                    } else {
                        if (data.errors) {
                            showErrors(data.errors);
                        } else {
                            showToast('error', 'Gagal', data.message || 'Validasi gagal');
                        }
                    }
                } catch (err) {
                    console.error(err);
                    showToast('error', 'Error', 'Terjadi kesalahan koneksi');
                } finally {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="ri-save-line me-1"></i> Simpan Album';
                    }
                }
            });

            // ===== EVENT: Photo Preview =====
            document.getElementById('photos')?.addEventListener('change', function() {
                const preview = document.getElementById('photoPreview');
                preview.innerHTML = '';
                preview.classList.remove('d-none');

                Array.from(this.files).forEach(file => {
                    if (!file.type.startsWith('image/')) return;

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-4 col-md-3';
                        col.innerHTML =
                            `<div class="photo-preview-item"><img src="${e.target.result}" alt="Preview"></div>`;
                        preview.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                });
            });

            // ===== FUNCTION: Reset Form =====
            function resetForm(mode = 'create') {
                const form = document.getElementById('formAlbum');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated');
                    form.action = '{{ route('admin.dokumentasi.store') }}';
                }

                document.getElementById('formMethod').value = 'POST';
                document.getElementById('albumId').value = '';

                const preview = document.getElementById('photoPreview');
                if (preview) {
                    preview.innerHTML = '';
                    preview.classList.add('d-none');
                }

                const errorBox = document.getElementById('formErrors');
                if (errorBox) errorBox.classList.add('d-none');

                // Reset checkboxes
                document.getElementById('chkPublished').checked = false;
                document.getElementById('chkFeatured').checked = false;
                document.getElementById('chkDownload').checked = false;
                document.getElementById('chkShowWeb').checked = true;
            }

            // ===== FUNCTION: Load Album for Edit =====
            function loadAlbumForEdit(id) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Memuat...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                }

                fetch(`/admin/dokumentasi/${id}/edit?ajax=1`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (typeof Swal !== 'undefined') Swal.close();

                        if (!data.success) {
                            showToast('error', 'Gagal', data.message || 'Data album tidak ditemukan');
                            return;
                        }

                        resetForm('edit');

                        const form = document.getElementById('formAlbum');
                        if (form) {
                            form.action = `/admin/dokumentasi/${id}`;
                        }
                        document.getElementById('formMethod').value = 'PUT';
                        document.getElementById('albumId').value = id;

                        @if ($isSuperAdmin)
                            document.getElementById('branch_id_form').value = data.branch_id || '';
                        @endif

                        document.getElementById('judul_album').value = data.judul_album || '';
                        document.getElementById('deskripsi').value = data.deskripsi || '';
                        document.getElementById('kategori_id_form').value = data.kategori_id || '';
                        document.getElementById('event_id_form').value = data.event_id || '';
                        document.getElementById('fotografer').value = data.fotografer || '';
                        document.getElementById('tanggal_album').value = data.tanggal_album || '';
                        document.getElementById('lokasi').value = data.lokasi || '';

                        document.getElementById('chkPublished').checked = data.status === 'published';
                        document.getElementById('chkFeatured').checked = !!data.is_featured;
                        document.getElementById('chkDownload').checked = !!data.allow_download;
                        document.getElementById('chkShowWeb').checked = !!data.show_in_website;

                        const preview = document.getElementById('photoPreview');
                        preview.innerHTML = '';
                        if (data.photos && data.photos.length > 0) {
                            data.photos.forEach(photo => {
                                const col = document.createElement('div');
                                col.className = 'col-4 col-md-3';
                                col.innerHTML =
                                    `<div class="photo-preview-item"><img src="/storage/${photo}" alt="Existing photo"></div>`;
                                preview.appendChild(col);
                            });
                            preview.classList.remove('d-none');
                        }

                        document.getElementById('modalAlbumLabel').textContent = 'Edit Album: ' + (data
                            .judul_album || '');
                        if (modalAlbum) modalAlbum.show();
                    })
                    .catch(err => {
                        if (typeof Swal !== 'undefined') Swal.close();
                        console.error('Error load edit:', err);
                        showToast('error', 'Error', 'Gagal memuat data album');
                    });
            }

            // ===== FUNCTION: Reload Grid =====
            function reloadGrid() {
                const params = new URLSearchParams({
                    @if ($isSuperAdmin)
                        branch_id: document.getElementById('filter_branch')?.value || '',
                    @endif
                    kategori_id: document.getElementById('kategori_id')?.value || '',
                    event_id: document.getElementById('event_id')?.value || '',
                    status: document.getElementById('status')?.value || '',
                    search: document.getElementById('search')?.value?.trim() || '',
                    ajax: 1
                });

                const gridEl = document.getElementById('albumsGrid');
                if (gridEl) {
                    gridEl.innerHTML = `
                <div class="col-12">
                    <div class="empty-state-card">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted-custom mb-0">Memuat album...</p>
                    </div>
                </div>`;
                }

                fetch(`{{ route('admin.dokumentasi.index') }}?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    })
                    .then(r => r.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        const newGrid = doc.querySelector('#albumsGrid');
                        const newPagination = doc.querySelector('#paginationContainer');

                        if (newGrid && gridEl) {
                            gridEl.innerHTML = newGrid.innerHTML;
                        }
                        if (newPagination) {
                            const paginationEl = document.getElementById('paginationContainer');
                            if (paginationEl) {
                                paginationEl.innerHTML = newPagination.innerHTML;
                                bindPaginationEvents();
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Reload grid error:', err);
                        showToast('error', 'Gagal', 'Tidak bisa memuat ulang daftar album');
                    });
            }

            // ===== FUNCTION: Bind Pagination Events =====
            function bindPaginationEvents() {
                document.querySelectorAll('#paginationContainer a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const url = new URL(this.href);
                        const page = url.searchParams.get('page') || 1;

                        // Update URL params for pagination click to keep filters
                        const currentParams = new URLSearchParams(window.location.search);
                        currentParams.set('page', page);

                        // Trigger reload with current filter values instead of just clicking link
                        // Or simpler: just call reloadGrid which reads current inputs
                        reloadGrid();
                    });
                });
            }

            // ===== FUNCTION: Show Toast =====
            function showToast(type, title, message) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: type,
                        title: title,
                        text: message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                } else {
                    alert(`${title}: ${message}`);
                }
            }

            // ===== FUNCTION: Show Validation Errors =====
            function showErrors(errors) {
                const errorBox = document.getElementById('formErrors');
                const errorList = document.getElementById('errorList');
                if (!errorBox || !errorList) return;

                let html = '';
                for (let field in errors) {
                    const messages = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                    messages.forEach(msg => {
                        html += `<li><strong>${field}:</strong> ${msg}</li>`;
                    });
                }
                errorList.innerHTML = html;
                errorBox.classList.remove('d-none');
                errorBox.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

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

            // ===== INIT =====
            bindPaginationEvents();
        });
    </script>
@endsection
