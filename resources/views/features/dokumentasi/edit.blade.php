@extends('layouts.admin')

@section('title', 'Edit Album')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('admin.dokumentasi.index') }}" class="btn btn-outline-custom btn-sm">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div>
                <h1 class="h4 fw-bold mb-1">Edit Album</h1>
                <p class="text-muted-custom small mb-0">Perbarui informasi dan foto album dokumentasi</p>
            </div>
        </div>

        <!-- Validation Errors -->
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

        <form action="{{ route('admin.dokumentasi.update', $album) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">

                <!-- Left Column: Album Info -->
                <div class="col-lg-6">
                    <div class="card-custom h-100">
                        <div class="card-header-custom border-bottom border-custom py-3 px-4">
                            <h6 class="mb-0 fw-semibold">
                                <i class="ri-information-line me-2 text-primary"></i>Informasi Album
                            </h6>
                        </div>
                        <div class="card-body p-4">

                            @if ($isSuperAdmin ?? false)
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted-custom">Cabang <span
                                            class="text-danger">*</span></label>
                                    <select name="branch_id" class="form-select form-select-custom" required>
                                        @foreach ($branches ?? collect() as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ old('branch_id', $album->branch_id) == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Judul Album <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="judul_album" class="form-control form-control-custom"
                                    value="{{ old('judul_album', $album->judul_album) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control form-control-custom textarea-custom" rows="3">{{ old('deskripsi', $album->deskripsi) }}</textarea>
                            </div>

                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted-custom">Kategori</label>
                                    <select name="kategori_id" class="form-select form-select-custom">
                                        <option value="">Tanpa Kategori</option>
                                        @foreach ($kategoris as $kategori)
                                            <option value="{{ $kategori->id }}"
                                                {{ old('kategori_id', $album->kategori_id) == $kategori->id ? 'selected' : '' }}>
                                                {{ $kategori->nama_kategori }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted-custom">Event</label>
                                    <select name="event_id" class="form-select form-select-custom">
                                        <option value="">Tidak Ada</option>
                                        @foreach ($events as $event)
                                            <option value="{{ $event->id }}"
                                                {{ old('event_id', $album->event_id) == $event->id ? 'selected' : '' }}>
                                                {{ $event->nama_event }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted-custom">Fotografer</label>
                                    <input type="text" name="fotografer" class="form-control form-control-custom"
                                        value="{{ old('fotografer', $album->fotografer) }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted-custom">Tanggal Album</label>
                                    <input type="date" name="tanggal_album" class="form-control form-control-custom"
                                        value="{{ old('tanggal_album', $album->tanggal_album->format('Y-m-d')) }}">
                                </div>
                            </div>

                            <div class="mb-0 mt-3">
                                <label class="form-label small fw-semibold text-muted-custom">Lokasi</label>
                                <input type="text" name="lokasi" class="form-control form-control-custom"
                                    value="{{ old('lokasi', $album->lokasi) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Photos & Options -->
                <div class="col-lg-6">
                    <!-- Upload New Photos -->
                    <div class="card-custom mb-4">
                        <div class="card-header-custom border-bottom border-custom py-3 px-4">
                            <h6 class="mb-0 fw-semibold">
                                <i class="ri-gallery-upload-line me-2 text-primary"></i>Upload Foto Baru
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Tambah Foto</label>
                                <input type="file" name="photos[]" class="form-control form-control-custom"
                                    accept="image/*" multiple>
                                <small class="text-muted-custom"><i class="ri-information-line me-1"></i>Max 10MB per file.
                                    Bisa pilih multiple.</small>
                                <div id="uploadPreview" class="row g-2 mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Existing Photos -->
                    @if ($album->photos->count() > 0)
                        <div class="card-custom mb-4">
                            <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="ri-delete-bin-line me-2 text-danger"></i>Hapus Foto
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="photo-delete-list">
                                    @foreach ($album->photos as $photo)
                                        <label class="photo-delete-item">
                                            <input type="checkbox" name="delete_photos[]" value="{{ $photo->id }}"
                                                class="form-check-input-custom">
                                            <img src="{{ asset('storage/' . $photo->file_path) }}"
                                                alt="{{ $photo->file_name }}" class="photo-delete-thumbnail">
                                            <span class="photo-delete-name">{{ $photo->file_name }}</span>
                                            @if ($photo->is_cover)
                                                <span class="badge badge-status-published ms-auto">Cover</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Options -->
                    <div class="card-custom">
                        <div class="card-header-custom border-bottom border-custom py-3 px-4">
                            <h6 class="mb-0 fw-semibold">
                                <i class="ri-settings-line me-2 text-primary"></i>Opsi Tampilan
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex flex-column gap-3">
                                <label class="form-check form-switch-custom">
                                    <input type="checkbox" name="status" value="published"
                                        class="form-check-input-custom"
                                        {{ old('status', $album->status) == 'published' ? 'checked' : '' }}>
                                    <span class="form-check-label-custom">Published (Tampil di gallery)</span>
                                </label>
                                <label class="form-check form-switch-custom">
                                    <input type="checkbox" name="is_featured" value="1"
                                        class="form-check-input-custom"
                                        {{ old('is_featured', $album->is_featured) ? 'checked' : '' }}>
                                    <span class="form-check-label-custom">Featured Album</span>
                                </label>
                                <label class="form-check form-switch-custom">
                                    <input type="checkbox" name="show_in_website" value="1"
                                        class="form-check-input-custom"
                                        {{ old('show_in_website', $album->show_in_website) ? 'checked' : '' }} checked>
                                    <span class="form-check-label-custom">Tampilkan di Website</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-3 mt-4 pt-4 border-top border-custom">
                <a href="{{ route('admin.dokumentasi.index') }}" class="btn btn-outline-custom">
                    <i class="ri-close-line me-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-custom">
                    <i class="ri-save-line me-1"></i> Update Album
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

        /* ===== PHOTO DELETE LIST ===== */
        .photo-delete-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-height: 300px;
            overflow-y: auto;
        }

        .photo-delete-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            background: var(--bg-surface);
            cursor: pointer;
            transition: var(--transition);
        }

        .photo-delete-item:hover {
            border-color: var(--accent-blue);
            background: var(--bg-body);
        }

        .photo-delete-thumbnail {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-sm);
            object-fit: cover;
            border: 1px solid var(--border-color);
        }

        .photo-delete-name {
            font-size: 0.85rem;
            color: var(--text-primary);
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        /* ===== BADGES ===== */
        .badge-status-published {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.2em 0.5em;
            font-weight: 500;
            font-size: 0.7rem;
        }

        /* ===== ALERT CUSTOM ===== */
        .alert-custom {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
        }

        .alert-custom.error {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            color: #ef4444;
        }

        .alert-custom i {
            font-size: 1.25rem;
            margin-top: 2px;
        }

        /* ===== DARK MODE ===== */
        [data-theme="dark"] .photo-delete-item {
            background: var(--bg-surface);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .photo-delete-item:hover {
            background: var(--bg-sidebar);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .photo-delete-item {
                flex-wrap: wrap;
            }

            .photo-delete-name {
                order: 3;
                width: 100%;
                margin-top: 0.25rem;
            }

            .badge {
                order: 2;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== UPLOAD PREVIEW =====
            const photoInput = document.querySelector('input[name="photos[]"]');
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
