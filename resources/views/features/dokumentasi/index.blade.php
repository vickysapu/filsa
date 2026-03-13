@extends('layouts.admin')

@section('title', 'Dokumentasi & Gallery')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .modal-xl { max-width: 1100px; }
        .modal-content {
            border-radius: var(--radius-lg);
            border: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .modal-header, .modal-footer {
            position: sticky;
            background: white;
            z-index: 10;
        }
        .modal-header { top: 0; border-bottom: 1px solid #e2e8f0; }
        .modal-footer {
            bottom: 0;
            border-top: 1px solid #e2e8f0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.08);
            padding: 1rem 1.5rem;
        }
        .modal-body { padding-bottom: 140px !important; }
        @media (max-width: 576px) {
            .modal-xl { max-width: 96%; margin: 0.5rem auto; }
            .lux-grid-2 { grid-template-columns: 1fr !important; }
            .modal-body { padding: 1rem !important; }
        }
    </style>
@endpush

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Dokumentasi</h1>
                <p class="text-muted">Gallery foto dan dokumentasi kegiatan gereja</p>
            </div>
            @if (auth()->user()->hasPermission('dokumentasi.create'))
                <button type="button" class="lux-btn lux-btn-primary btn-tambah-album">
                    <i class="ri-gallery-upload-line"></i> Buat Album Baru
                </button>
            @endif
        </div>

        <!-- Alert Success -->
        @if (session('success'))
            <div class="lux-card mb-4" style="border-left: 4px solid var(--success); background: #f0fdf4;">
                <span style="color: #166534; font-weight: 600;">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Toast Notification -->
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 2000">
            <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fw-bold" id="toastMessage">Operasi berhasil!</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="lux-card mb-5">
            <form id="formFilter" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div>
                    <label class="lux-label">Kategori</label>
                    <select id="kategori_id" name="kategori_id" class="lux-select">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="lux-label">Event</label>
                    <select id="event_id" name="event_id" class="lux-select">
                        <option value="">Semua Event</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                {{ $event->nama_event }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="lux-label">Status</label>
                    <select id="status" name="status" class="lux-select">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>

                <div>
                    <label class="lux-label">Cari</label>
                    <input type="text" id="search" name="search" class="lux-input" value="{{ request('search') }}"
                        placeholder="Judul album, fotografer...">
                </div>

                <div style="display: flex; gap: 0.5rem; align-self: end;">
                    <button type="button" id="btnFilter" class="lux-btn lux-btn-primary" style="flex: 1;">
                        <i class="ri-filter-line"></i> Filter
                    </button>
                    <a href="{{ route('admin.dokumentasi.index') }}" class="lux-btn lux-btn-outline" title="Reset">
                        <i class="ri-refresh-line"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Albums Grid -->
        <div class="lux-grid lux-grid-4" id="albumsGrid">
            @include('features.dokumentasi._grid')
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" style="margin-top: 2rem; display: flex; justify-content: center;">
            {{ $albums->links('pagination::simple-bootstrap-4') }}
        </div>

    </div>

    <!-- ================== MODAL CREATE & EDIT ================== -->
    <div class="modal fade" id="modalAlbumForm" tabindex="-1" aria-labelledby="modalAlbumLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header lux-card-header" style="position: sticky; top: 0; background: white; z-index: 10; border-bottom: 1px solid #e2e8f0;">
                    <h5 class="modal-title" id="modalAlbumLabel">Buat Album Dokumentasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body lux-container" style="padding-bottom: 140px !important;">
                    <form id="formAlbum" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod" value="POST">
                        <input type="hidden" name="id" id="albumId" value="">

                        <div class="alert alert-danger" id="formErrors" style="display:none;">
                            <ul class="mb-0" id="errorList"></ul>
                        </div>

                        <div class="lux-grid lux-grid-2">

                            <!-- Kolom Kiri -->
                            <div class="lux-card">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">Informasi Album</h3>
                                    <i class="ri-image-line" style="color: var(--primary);"></i>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Judul Album *</label>
                                    <input type="text" name="judul_album" id="judul_album" class="lux-input" required placeholder="Contoh: Kebaktian Natal 2024">
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Deskripsi</label>
                                    <textarea name="deskripsi" id="deskripsi" class="lux-textarea" rows="3" placeholder="Deskripsi album..."></textarea>
                                </div>

                                <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                                    <div class="lux-form-group">
                                        <label class="lux-label">Kategori</label>
                                        <select name="kategori_id" id="kategori_id" class="lux-select">
                                            <option value="">Tanpa Kategori</option>
                                            @foreach ($kategoris as $kategori)
                                                <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="lux-form-group">
                                        <label class="lux-label">Event (Opsional)</label>
                                        <select name="event_id" id="event_id" class="lux-select">
                                            <option value="">Tidak Ada</option>
                                            @foreach ($events as $event)
                                                <option value="{{ $event->id }}">{{ $event->nama_event }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                                    <div class="lux-form-group">
                                        <label class="lux-label">Fotografer</label>
                                        <input type="text" name="fotografer" id="fotografer" class="lux-input">
                                    </div>
                                    <div class="lux-form-group">
                                        <label class="lux-label">Tanggal Album *</label>
                                        <input type="date" name="tanggal_album" id="tanggal_album" class="lux-input" required>
                                    </div>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Lokasi</label>
                                    <input type="text" name="lokasi" id="lokasi" class="lux-input">
                                </div>
                            </div>

                            <!-- Kolom Kanan -->
                            <div class="lux-card">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">Upload Foto</h3>
                                    <i class="ri-gallery-upload-line" style="color: var(--accent);"></i>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Foto Album *</label>
                                    <input type="file" name="photos[]" id="photos" class="lux-input" accept="image/*" multiple required>
                                    <p class="lux-form-help mt-1"><i class="ri-information-line"></i> Pilih multiple files. Max 10MB per file</p>
                                    <div id="photoPreview" class="row g-2 mt-3" style="display:none;"></div>
                                </div>

                                <div class="lux-form-group mt-4 p-3" style="background:#f8fafc; border-radius:var(--radius);">
                                    <label class="lux-label mb-3">Opsi Tampilan</label>
                                    <div style="display:flex; flex-direction:column; gap:0.75rem;">
                                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                                            <input type="checkbox" name="status" value="published" id="chkPublished">
                                            <span>Published (Langsung tampil)</span>
                                        </label>
                                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                                            <input type="checkbox" name="is_featured" value="1" id="chkFeatured">
                                            <span>Featured Album</span>
                                        </label>
                                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                                            <input type="checkbox" name="allow_download" value="1" id="chkDownload">
                                            <span>Allow Download</span>
                                        </label>
                                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                                            <input type="checkbox" name="show_in_website" value="1" id="chkShowWeb" checked>
                                            <span>Tampilkan di Website</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer" style="position: sticky; bottom: 0; background: white; z-index: 10; border-top: 1px solid #e2e8f0; box-shadow: 0 -2px 10px rgba(0,0,0,0.08);">
                    <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="lux-btn lux-btn-primary" id="btnSubmitAlbum" form="formAlbum">
                        <i class="ri-save-line"></i> Simpan Album
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            const modalAlbum = new bootstrap.Modal(document.getElementById('modalAlbumForm'));

            const $form = $('#formAlbum');
            const $btnSubmit = $('#btnSubmitAlbum');

            // Tombol Tambah Album
            $(document).on('click', '.btn-tambah-album', function () {
                resetForm('create');
                $('#modalAlbumLabel').text('Buat Album Dokumentasi');
                modalAlbum.show();
            });

            // Tombol Edit Album (dari partial grid)
            $(document).on('click', '.btn-edit-album', function () {
                const id = $(this).data('id');
                loadDataForEdit(id);
            });

            function resetForm(mode = 'create') {
                $form[0].reset();
                $form.attr('action', '{{ route('admin.dokumentasi.store') }}');
                $('#formMethod').val('POST');
                $('#albumId').val('');
                $('#photoPreview').empty().hide();

                $('#chkPublished').prop('checked', false);
                $('#chkFeatured').prop('checked', false);
                $('#chkDownload').prop('checked', false);
                $('#chkShowWeb').prop('checked', true);

                if (mode === 'create') {
                    $('#modalAlbumLabel').text('Buat Album Dokumentasi');
                } else {
                    $('#modalAlbumLabel').text('Edit Album');
                }
            }

            function loadDataForEdit(id) {
                $.ajax({
                    url: '{{ route('admin.dokumentasi.edit', ':id') }}'.replace(':id', id) + '?ajax=1',
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Memuat...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function (data) {
                        Swal.close();

                        if (!data.success) {
                            Swal.fire('Gagal', data.message || 'Data album tidak ditemukan', 'error');
                            return;
                        }

                        resetForm('edit');

                        $form.attr('action', '{{ route('admin.dokumentasi.update', ':id') }}'.replace(':id', id));
                        $('#formMethod').val('PUT');
                        $('#albumId').val(id);

                        // Isi field teks
                        $('#judul_album').val(data.judul_album || '');
                        $('#deskripsi').val(data.deskripsi || '');
                        $('#kategori_id').val(data.kategori_id || '');
                        $('#event_id').val(data.event_id || '');
                        $('#fotografer').val(data.fotografer || '');
                        $('#tanggal_album').val(data.tanggal_album || '');
                        $('#lokasi').val(data.lokasi || '');

                        // Checkbox
                        $('#chkPublished').prop('checked', data.status === 'published');
                        $('#chkFeatured').prop('checked', !!data.is_featured);
                        $('#chkDownload').prop('checked', !!data.allow_download);
                        $('#chkShowWeb').prop('checked', !!data.show_in_website);

                        // Foto existing (preview)
                        $('#photoPreview').empty();
                        if (data.photos && data.photos.length > 0) {
                            data.photos.forEach(photo => {
                                $('#photoPreview').append(`
                                    <div class="col-3">
                                        <img src="{{ asset('storage') }}/${photo}" class="img-fluid rounded shadow-sm" style="aspect-ratio:1; object-fit:cover;">
                                    </div>
                                `);
                            });
                            $('#photoPreview').show();
                        }

                        $('#modalAlbumLabel').text('Edit Album: ' + (data.judul_album || ''));
                        modalAlbum.show();
                    },
                    error: function (xhr, status, error) {
                        Swal.close();
                        console.error('Error load edit:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Memuat',
                            text: 'Terjadi kesalahan saat memuat data album. Silakan coba lagi atau refresh halaman.',
                            footer: 'Cek console browser untuk detail error'
                        });
                    }
                });
            }

            // Submit Form
            $btnSubmit.on('click', function (e) {
                e.preventDefault();
                $form.submit();
            });

            $form.on('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                $btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        modalAlbum.hide();
                        reloadGrid();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Album berhasil disimpan',
                            timer: 2200,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    },
                    error: function (xhr) {
                        let msg = 'Terjadi kesalahan server';
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            let html = '';
                            $.each(xhr.responseJSON.errors, function (field, msgs) {
                                html += `<li>${field}: ${msgs.join(', ')}</li>`;
                            });
                            $('#errorList').html(html);
                            $('#formErrors').show();
                            msg = 'Ada kesalahan validasi. Periksa input Anda.';
                        }
                        Swal.fire('Gagal', msg, 'error');
                    },
                    complete: function () {
                        $btnSubmit.prop('disabled', false).html('<i class="ri-save-line"></i> Simpan Album');
                    }
                });
            });

            // Filter & Reload
            $('#btnFilter').on('click', reloadGrid);

            function reloadGrid() {
                const params = {
                    kategori_id: $('#kategori_id').val() || '',
                    event_id: $('#event_id').val() || '',
                    status: $('#status').val() || '',
                    search: $('#search').val().trim(),
                    ajax: 1
                };

                $.ajax({
                    url: '{{ route('admin.dokumentasi.index') }}',
                    type: 'GET',
                    data: params,
                    success: function (html) {
                        const $html = $(html);
                        $('#albumsGrid').html($html.find('#albumsGrid').html() || '');
                        $('#paginationContainer').html($html.find('#paginationContainer').html() || '');
                    },
                    error: function (xhr) {
                        console.error('Reload grid error:', xhr);
                        Swal.fire('Gagal', 'Tidak bisa memuat ulang daftar album', 'error');
                    }
                });
            }

            // Preview foto baru (multiple)
            $('#photos').on('change', function () {
                $('#photoPreview').empty().show();
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = e => {
                        $('#photoPreview').append(`
                            <div class="col-3">
                                <img src="${e.target.result}" class="img-fluid rounded shadow-sm" style="aspect-ratio:1; object-fit:cover;">
                            </div>
                        `);
                    };
                    reader.readAsDataURL(file);
                });
            });
        });
    </script>
@endpush