@extends('layouts.admin')

@section('title', 'Buat Album Dokumentasi')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('admin.dokumentasi.index') }}" class="btn btn-outline-custom btn-sm">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div>
                <h1 class="h4 fw-bold mb-1">Buat Album Dokumentasi</h1>
                <p class="text-muted-custom small mb-0">Tambahkan album foto baru untuk dokumentasi kegiatan</p>
            </div>
        </div>

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

        <form action="{{ route('admin.dokumentasi.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">

                <!-- Kolom Kiri: Info Album -->
                <div class="col-lg-6">
                    <div class="card-custom h-100">
                        <div class="card-header-custom border-bottom border-custom py-3 px-4">
                            <h6 class="mb-0 fw-semibold">
                                <i class="ri-image-line me-2 text-primary"></i>Informasi Album
                            </h6>
                        </div>
                        <div class="card-body p-4">

                            @if ($isSuperAdmin ?? false)
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted-custom">Cabang <span
                                            class="text-danger">*</span></label>
                                    <select name="branch_id" class="form-select form-select-custom" required>
                                        <option value="">Pilih Cabang...</option>
                                        @foreach ($branches ?? collect() as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Judul Album <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="judul_album"
                                    class="form-control form-control-custom @error('judul_album') is-invalid @enderror"
                                    value="{{ old('judul_album') }}" placeholder="Contoh: Kebaktian Natal 2024" required>
                                @error('judul_album')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Deskripsi</label>
                                <textarea name="deskripsi"
                                    class="form-control form-control-custom textarea-custom @error('deskripsi') is-invalid @enderror" rows="3"
                                    placeholder="Deskripsi album...">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted-custom">Kategori</label>
                                    <select name="kategori_id" class="form-select form-select-custom">
                                        <option value="">Tanpa Kategori</option>
                                        @foreach ($kategoris as $kategori)
                                            <option value="{{ $kategori->id }}"
                                                {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                                {{ $kategori->nama_kategori }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted-custom">Event (Opsional)</label>
                                    <select name="event_id" class="form-select form-select-custom">
                                        <option value="">Tidak Ada</option>
                                        @foreach ($events as $event)
                                            <option value="{{ $event->id }}"
                                                {{ old('event_id') == $event->id ? 'selected' : '' }}>
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
                                        value="{{ old('fotografer') }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted-custom">Tanggal Album <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_album"
                                        class="form-control form-control-custom @error('tanggal_album') is-invalid @enderror"
                                        value="{{ old('tanggal_album', date('Y-m-d')) }}" required>
                                    @error('tanggal_album')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-0 mt-3">
                                <label class="form-label small fw-semibold text-muted-custom">Lokasi</label>
                                <input type="text" name="lokasi" class="form-control form-control-custom"
                                    value="{{ old('lokasi') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Upload Foto -->
                <div class="col-lg-6">
                    <div class="card-custom h-100">
                        <div class="card-header-custom border-bottom border-custom py-3 px-4">
                            <h6 class="mb-0 fw-semibold">
                                <i class="ri-gallery-upload-line me-2 text-primary"></i>Upload Foto
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted-custom">Foto Album <span
                                        class="text-danger">*</span></label>
                                <input type="file" name="photos[]"
                                    class="form-control form-control-custom @error('photos.*') is-invalid @enderror"
                                    accept="image/*" multiple required>
                                @error('photos.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted-custom"><i class="ri-information-line me-1"></i> Pilih multiple
                                    files. Max 10MB per file</small>

                                <div id="uploadPreview" class="row g-2 mt-3 d-none"></div>
                            </div>

                            <hr class="border-custom my-4">

                            <div class="mb-0">
                                <label class="form-label small fw-semibold text-muted-custom mb-3">Opsi Tampilan</label>
                                <div class="d-flex flex-column gap-3">
                                    <label class="form-check form-switch-custom">
                                        <input type="checkbox" name="status" value="published"
                                            class="form-check-input-custom"
                                            {{ old('status') == 'published' ? 'checked' : '' }}>
                                        <span class="form-check-label-custom">Published (Langsung tampil)</span>
                                    </label>
                                    <label class="form-check form-switch-custom">
                                        <input type="checkbox" name="is_featured" value="1"
                                            class="form-check-input-custom" {{ old('is_featured') ? 'checked' : '' }}>
                                        <span class="form-check-label-custom">Featured Album</span>
                                    </label>
                                    <label class="form-check form-switch-custom">
                                        <input type="checkbox" name="allow_download" value="1"
                                            class="form-check-input-custom" {{ old('allow_download') ? 'checked' : '' }}>
                                        <span class="form-check-label-custom">Allow Download</span>
                                    </label>
                                    <label class="form-check form-switch-custom">
                                        <input type="checkbox" name="show_in_website" value="1"
                                            class="form-check-input-custom"
                                            {{ old('show_in_website', true) ? 'checked' : '' }} checked>
                                        <span class="form-check-label-custom">Tampilkan di Website</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4 pt-4 border-top border-custom">
                <a href="{{ route('admin.dokumentasi.index') }}" class="btn btn-outline-custom">
                    <i class="ri-close-line me-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-custom">
                    <i class="ri-save-line me-1"></i> Simpan Album
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
