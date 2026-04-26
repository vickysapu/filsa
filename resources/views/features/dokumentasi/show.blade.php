@extends('layouts.admin')

@section('title', 'Detail Album')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('admin.dokumentasi.index') }}" class="btn btn-outline-custom btn-sm">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div>
                <h1 class="h4 fw-bold mb-1">{{ $album->judul_album }}</h1>
                <p class="text-muted-custom small mb-0">
                    <i class="ri-image-line me-1"></i> {{ $album->jumlah_foto }} Foto
                </p>
            </div>
        </div>

        <div class="row g-4">

            <!-- Photos Grid (Main Content) -->
            <div class="col-lg-8">
                <div class="card-custom">
                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold">
                                <i class="ri-gallery-line me-2 text-primary"></i>Koleksi Foto
                            </h6>
                            @if (auth()->user()->hasPermission('dokumentasi.edit'))
                                <button type="button" class="btn btn-custom btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#uploadModal">
                                    <i class="ri-add-line me-1"></i> Upload Foto
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if ($album->photos->isEmpty())
                            <div class="empty-state text-center py-5">
                                <div class="empty-state-icon mb-3">
                                    <i class="ri-image-line"></i>
                                </div>
                                <h6 class="fw-semibold mb-2">Belum ada foto</h6>
                                <p class="text-muted-custom small mb-3">Mulai upload foto pertama untuk album ini</p>
                                @if (auth()->user()->hasPermission('dokumentasi.edit'))
                                    <button type="button" class="btn btn-custom btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#uploadModal">
                                        <i class="ri-add-line me-1"></i> Upload Foto
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="row g-3" id="photoGrid">
                                @foreach ($album->photos as $photo)
                                    <div class="col-4 col-md-3">
                                        <div class="photo-item" data-bs-toggle="modal" data-bs-target="#lightboxModal"
                                            data-src="{{ asset('storage/' . $photo->file_path) }}"
                                            data-caption="{{ $photo->caption ?? '' }}">
                                            <img src="{{ asset('storage/' . $photo->file_path) }}"
                                                alt="{{ $photo->caption ?? 'Foto' }}" class="photo-thumbnail">
                                            @if ($photo->is_cover)
                                                <span class="photo-badge">Cover</span>
                                            @endif
                                            <div class="photo-overlay">
                                                <i class="ri-zoom-in-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-lg-4">
                <!-- Album Info Card -->
                <div class="card-custom mb-4">
                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                        <h6 class="mb-0 fw-semibold">
                            <i class="ri-information-line me-2 text-primary"></i>Info Album
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="info-list">
                            <div class="info-item">
                                <span class="info-label">Kategori</span>
                                <span class="info-value">{{ $album->kategori->nama_kategori ?? '-' }}</span>
                            </div>
                            @if ($album->event)
                                <div class="info-item">
                                    <span class="info-label">Event</span>
                                    <span class="info-value">{{ $album->event->nama_event }}</span>
                                </div>
                            @endif
                            <div class="info-item">
                                <span class="info-label">Tanggal</span>
                                <span class="info-value">{{ $album->tanggal_album->format('d M Y') }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fotografer</span>
                                <span class="info-value">{{ $album->fotografer ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status</span>
                                <span class="badge badge-status-{{ $album->status }}">
                                    {{ ucfirst($album->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="card-custom">
                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                        <h6 class="mb-0 fw-semibold">
                            <i class="ri-tools-line me-2 text-primary"></i>Aksi
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex flex-column gap-2">
                            @if (auth()->user()->hasPermission('dokumentasi.edit'))
                                <a href="{{ route('admin.dokumentasi.edit', $album) }}" class="btn btn-custom">
                                    <i class="ri-pencil-line me-1"></i> Edit Album
                                </a>
                            @endif
                            <button type="button" onclick="window.print()" class="btn btn-outline-custom">
                                <i class="ri-printer-line me-1"></i> Cetak
                            </button>
                            @if (auth()->user()->hasPermission('dokumentasi.delete'))
                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                                    <i class="ri-delete-bin-line me-1"></i> Hapus Album
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Description Card -->
                @if ($album->deskripsi)
                    <div class="card-custom mt-4">
                        <div class="card-header-custom border-bottom border-custom py-3 px-4">
                            <h6 class="mb-0 fw-semibold">Deskripsi</h6>
                        </div>
                        <div class="card-body p-4">
                            <p class="mb-0 text-muted-custom">{{ $album->deskripsi }}</p>
                        </div>
                    </div>
                @endif
            </div>

        </div>

    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form action="{{ route('admin.dokumentasi.upload', $album) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold">Upload Foto Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Pilih Foto <span
                                    class="text-danger">*</span></label>
                            <input type="file" name="photos[]" class="form-control form-control-custom"
                                accept="image/*" multiple required>
                            <small class="text-muted-custom"><i class="ri-information-line me-1"></i>Max 10MB per file.
                                Bisa pilih multiple.</small>
                        </div>
                        <div id="uploadPreview" class="row g-2 d-none"></div>
                    </div>
                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom">
                            <i class="ri-upload-line me-1"></i> Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lightbox Modal -->
    <div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-custom bg-transparent border-0">
                <div class="modal-body p-0 position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                    <img src="" alt="" class="img-fluid rounded" id="lightboxImage"
                        style="max-height: 85vh; object-fit: contain;">
                    <p class="text-center text-white mt-3" id="lightboxCaption"></p>
                </div>
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

        /* ===== PHOTO GRID ===== */
        .photo-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: var(--radius-sm);
            overflow: hidden;
            cursor: pointer;
            transition: var(--transition);
        }

        .photo-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .photo-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .photo-item:hover .photo-thumbnail {
            transform: scale(1.05);
        }

        .photo-badge {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            padding: 0.2em 0.5em;
            border-radius: 20px;
            background: var(--accent-blue);
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            z-index: 2;
        }

        .photo-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
            color: white;
            font-size: 1.5rem;
        }

        .photo-item:hover .photo-overlay {
            opacity: 1;
        }

        /* ===== INFO LIST ===== */
        .info-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .info-value {
            font-weight: 600;
            color: var(--text-primary);
            text-align: right;
            max-width: 60%;
            word-break: break-word;
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

        /* ===== EMPTY STATE ===== */
        .empty-state {
            background: var(--bg-body);
            border: 1px dashed var(--border-color);
            border-radius: var(--radius);
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

        .form-control-custom[type="file"] {
            padding: 0.375rem 0.625rem;
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

        /* ===== LIGHTBOX ===== */
        #lightboxModal .modal-content {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }

        #lightboxModal .modal-body {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #lightboxImage {
            max-height: 85vh;
            object-fit: contain;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        #lightboxCaption {
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            max-width: 80%;
        }

        /* ===== UPLOAD PREVIEW ===== */
        .upload-preview-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .upload-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ===== DARK MODE ===== */
        [data-theme="dark"] .empty-state {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .empty-state-icon {
            background: var(--bg-sidebar);
        }

        [data-theme="dark"] #lightboxCaption {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991px) {
            .info-value {
                max-width: 50%;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .photo-item {
                aspect-ratio: 4/3;
            }

            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .info-value {
                text-align: left;
                max-width: 100%;
            }

            #lightboxImage {
                max-height: 70vh;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== TOOLTIP INIT =====
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });

            // ===== LIGHTBOX FUNCTIONALITY =====
            const lightboxModal = document.getElementById('lightboxModal');
            if (lightboxModal) {
                lightboxModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const src = button.dataset.src;
                    const caption = button.dataset.caption;

                    document.getElementById('lightboxImage').src = src;
                    document.getElementById('lightboxCaption').textContent = caption;
                });

                lightboxModal.addEventListener('hidden.bs.modal', function() {
                    document.getElementById('lightboxImage').src = '';
                    document.getElementById('lightboxCaption').textContent = '';
                });
            }

            // ===== UPLOAD PREVIEW =====
            const photoInput = document.querySelector('#uploadModal input[type="file"]');
            const uploadPreview = document.getElementById('uploadPreview');

            photoInput?.addEventListener('change', function() {
                uploadPreview.innerHTML = '';
                uploadPreview.classList.add('d-none');

                Array.from(this.files).forEach(file => {
                    if (!file.type.startsWith('image/')) return;

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-4';
                        col.innerHTML =
                            `<div class="upload-preview-item"><img src="${e.target.result}" alt="Preview"></div>`;
                        uploadPreview.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                });

                if (this.files.length > 0) {
                    uploadPreview.classList.remove('d-none');
                }
            });

            // ===== DELETE CONFIRMATION =====
            window.confirmDelete = function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Hapus Album?',
                        text: 'Album dan semua fotonya akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `{{ route('admin.dokumentasi.destroy', $album) }}`;
                            form.innerHTML = `@csrf @method('DELETE')`;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                } else {
                    if (confirm('Yakin ingin menghapus album ini?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `{{ route('admin.dokumentasi.destroy', $album) }}`;
                        form.innerHTML = `@csrf @method('DELETE')`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
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
        });
    </script>
@endsection
