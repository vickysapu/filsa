@extends('layouts.admin')

@section('title', 'Manajemen Events')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .modal-xl {
            max-width: 1100px;
        }

        .modal-content {
            border-radius: var(--radius-lg);
            border: none;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .modal-header,
        .modal-footer {
            position: sticky;
            background: white;
            z-index: 10;
        }

        .modal-header {
            top: 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .modal-footer {
            bottom: 0;
            border-top: 1px solid #e2e8f0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.08);
            padding: 1rem 1.5rem;
        }

        .modal-body {
            padding-bottom: 120px !important;
            /* ruang untuk footer sticky */
        }

        @media (max-width: 576px) {
            .modal-xl {
                max-width: 96%;
                margin: 0.5rem auto;
            }

            .lux-grid-2 {
                grid-template-columns: 1fr !important;
            }

            .modal-body {
                padding: 1rem !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Events</h1>
                <p class="text-muted">Kelola event gereja untuk website public</p>
            </div>
            @if (auth()->user()->hasPermission('event.create'))
                <button type="button" class="lux-btn lux-btn-primary btn-tambah-event">
                    <i class="ri-calendar-add-line"></i> Buat Event Baru
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
            <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert"
                aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fw-bold" id="toastMessage">Operasi berhasil!</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="lux-card mb-5">
            <form id="formFilter"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
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
                    <label class="lux-label">Status</label>
                    <select id="status" name="status" class="lux-select">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published
                        </option>
                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Berlangsung</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan
                        </option>
                    </select>
                </div>

                <div>
                    <label class="lux-label">Cari</label>
                    <input type="text" id="search" name="search" class="lux-input" value="{{ request('search') }}"
                        placeholder="Nama event, lokasi...">
                </div>

                <div style="display: flex; gap: 0.5rem; align-self: end;">
                    <button type="button" id="btnFilter" class="lux-btn lux-btn-primary" style="flex: 1;">
                        <i class="ri-filter-line"></i> Filter
                    </button>
                    <a href="{{ route('admin.events.index') }}" class="lux-btn lux-btn-outline" title="Reset">
                        <i class="ri-refresh-line"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Events Grid -->
        <div class="lux-grid lux-grid-3" id="eventsGrid">
            @include('features.event._grid')
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" style="margin-top: 2rem; display: flex; justify-content: center;">
            {{ $events->links('pagination::simple-bootstrap-4') }}
        </div>

    </div>

    <!-- ================== MODAL FORM CREATE & EDIT ================== -->
    <div class="modal fade" id="modalEventForm" tabindex="-1" aria-labelledby="modalEventLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content"
                style="border-radius: var(--radius-lg); border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">

                <div class="modal-header lux-card-header"
                    style="position: sticky; top: 0; background: white; z-index: 10; border-bottom: 1px solid #e2e8f0;">
                    <h5 class="modal-title" id="modalEventLabel">Edit Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body lux-container" style="padding-bottom: 120px !important;">
                    <form id="formEvent" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod" value="POST">
                        <input type="hidden" name="id" id="eventId" value="">

                        <div class="alert alert-danger" id="formErrors" style="display:none;">
                            <ul class="mb-0" id="errorList"></ul>
                        </div>

                        <div class="lux-grid lux-grid-2">

                            <!-- Kolom Kiri: Informasi Event -->
                            <div class="lux-card">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">Informasi Event</h3>
                                    <i class="ri-calendar-event-line" style="color: var(--primary);"></i>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Nama Event *</label>
                                    <input type="text" name="nama_event" id="nama_event" class="lux-input" required>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Kategori Event *</label>
                                    <select name="kategori_id" id="kategori_id" class="lux-select" required>
                                        <option value="">Pilih Kategori...</option>
                                        @foreach ($kategoris as $kategori)
                                            <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Deskripsi Singkat *</label>
                                    <textarea name="deskripsi" id="deskripsi" class="lux-textarea" rows="3" required></textarea>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Deskripsi Lengkap</label>
                                    <textarea name="deskripsi_lengkap" id="deskripsi_lengkap" class="lux-textarea" rows="5"></textarea>
                                </div>

                                <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                                    <div class="lux-form-group">
                                        <label class="lux-label">Tanggal Mulai *</label>
                                        <input type="datetime-local" name="tanggal_mulai" id="tanggal_mulai"
                                            class="lux-input" required>
                                    </div>
                                    <div class="lux-form-group">
                                        <label class="lux-label">Tanggal Selesai</label>
                                        <input type="datetime-local" name="tanggal_selesai" id="tanggal_selesai"
                                            class="lux-input">
                                    </div>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Lokasi *</label>
                                    <input type="text" name="lokasi" id="lokasi" class="lux-input" required>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Alamat Lengkap</label>
                                    <textarea name="alamat_lengkap" id="alamat_lengkap" class="lux-textarea" rows="2"></textarea>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Detail & Kontak + Gambar + Opsi -->
                            <div class="lux-card">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">Detail & Kontak</h3>
                                    <i class="ri-information-line" style="color: var(--accent);"></i>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Pembicara/Narasumber</label>
                                    <input type="text" name="pembicara" id="pembicara" class="lux-input">
                                </div>

                                <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                                    <div class="lux-form-group">
                                        <label class="lux-label">Kontak Person</label>
                                        <input type="text" name="kontak_person" id="kontak_person" class="lux-input">
                                    </div>
                                    <div class="lux-form-group">
                                        <label class="lux-label">WhatsApp</label>
                                        <input type="text" name="whatsapp_kontak" id="whatsapp_kontak"
                                            class="lux-input">
                                    </div>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Email Kontak</label>
                                    <input type="email" name="email_kontak" id="email_kontak" class="lux-input">
                                </div>

                                <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                                    <div class="lux-form-group">
                                        <label class="lux-label">Harga Tiket (Rp)</label>
                                        <input type="number" name="harga_tiket" id="harga_tiket" class="lux-input"
                                            min="0" step="1000">
                                    </div>
                                    <div class="lux-form-group">
                                        <label class="lux-label">Kuota Peserta</label>
                                        <input type="number" name="kuota_peserta" id="kuota_peserta" class="lux-input"
                                            min="1">
                                    </div>
                                </div>

                                <!-- Gambar Featured Existing + Upload Baru -->
                                <div class="lux-form-group">
                                    <label class="lux-label">Gambar Featured Saat Ini</label>
                                    <div id="currentFeatured" class="mt-2"></div>
                                    <label class="lux-label mt-3">Ganti Gambar Featured</label>
                                    <input type="file" name="gambar_featured" id="gambar_featured" class="lux-input"
                                        accept="image/*">
                                    <div id="featuredPreview" class="mt-2" style="display:none;">
                                        <img id="previewFeatured" style="max-width:100%; border-radius:8px;">
                                    </div>
                                </div>

                                <!-- Galeri Existing + Tambah Baru -->
                                <div class="lux-form-group">
                                    <label class="lux-label">Galeri Saat Ini</label>
                                    <div id="currentGallery" class="lux-grid lux-grid-3 mt-2" style="gap:0.5rem;"></div>
                                    <label class="lux-label mt-3">Tambah Gambar ke Galeri</label>
                                    <input type="file" name="gambar_galeri[]" id="gambar_galeri" class="lux-input"
                                        accept="image/*" multiple>
                                </div>

                                <!-- Opsi Tampilan -->
                                <div class="lux-form-group mt-4 p-3"
                                    style="background:#f8fafc; border-radius:var(--radius);">
                                    <label class="lux-label mb-3">Opsi Tampilan</label>
                                    <div style="display:flex; flex-direction:column; gap:1rem;">
                                        <div>
                                            <label class="lux-label d-block mb-2">Status Event</label>
                                            <select name="status" id="status" class="lux-select">
                                                <option value="draft">Draft</option>
                                                <option value="published">Published</option>
                                                <option value="ongoing">Berlangsung</option>
                                                <option value="completed">Selesai</option>
                                                <option value="cancelled">Dibatalkan</option>
                                            </select>
                                        </div>

                                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                                            <input type="checkbox" name="is_featured" id="chkFeatured" value="1">
                                            <span>Featured Event (tampil di homepage)</span>
                                        </label>

                                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                                            <input type="checkbox" name="show_in_website" id="chkShowWeb"
                                                value="1">
                                            <span>Tampilkan di Website Public</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer"
                    style="position: sticky; bottom: 0; background: white; z-index: 10; border-top: 1px solid #e2e8f0; box-shadow: 0 -2px 10px rgba(0,0,0,0.08);">
                    <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="lux-btn lux-btn-primary" id="btnSubmitEvent" form="formEvent">
                        <i class="ri-save-line"></i> Update Event
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
            const modalEvent = new bootstrap.Modal(document.getElementById('modalEventForm'));

            const $form = $('#formEvent');
            const $btnSubmit = $('#btnSubmitEvent');

            // Tombol Tambah
            $(document).on('click', '.btn-tambah-event', function () {
                resetForm('create');
                $('#modalEventLabel').text('Buat Event Baru');
                modalEvent.show();
            });

            // Tombol Edit
            $(document).on('click', '.btn-edit-event', function (e) {
                e.preventDefault();
                const id = $(this).data('id');
                loadDataForEdit(id);
            });

            function resetForm(mode = 'create') {
                $form[0].reset();
                $form.attr('action', '{{ route('admin.events.store') }}');
                $('#formMethod').val('POST');
                $('#eventId').val('');
                $('#featuredPreview').hide();
                $('#currentFeatured').empty();
                $('#currentGallery').empty();

                // Reset checkbox & select
                $('#chkFeatured').prop('checked', false);
                $('#chkShowWeb').prop('checked', true);
                $('#status').val('draft');

                if (mode === 'create') {
                    $('#modalEventLabel').text('Buat Event Baru');
                }
            }

            function loadDataForEdit(id) {
                $.ajax({
                    url: '{{ route('admin.events.edit', ':id') }}'.replace(':id', id) + '?ajax=1',
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        if (!data || !data.success) {
                            Swal.fire('Gagal', 'Data event tidak ditemukan', 'error');
                            return;
                        }

                        resetForm('edit');

                        $form.attr('action', '{{ route('admin.events.update', ':id') }}'.replace(':id', id));
                        $('#formMethod').val('PUT');
                        $('#eventId').val(id);

                        // Isi field
                        $('#nama_event').val(data.nama_event || '');
                        $('#kategori_id').val(data.kategori_id || '');
                        $('#deskripsi').val(data.deskripsi || '');
                        $('#deskripsi_lengkap').val(data.deskripsi_lengkap || '');
                        $('#tanggal_mulai').val(data.tanggal_mulai ? data.tanggal_mulai.replace(' ', 'T') : '');
                        $('#tanggal_selesai').val(data.tanggal_selesai ? data.tanggal_selesai.replace(' ', 'T') : '');
                        $('#lokasi').val(data.lokasi || '');
                        $('#alamat_lengkap').val(data.alamat_lengkap || '');
                        $('#pembicara').val(data.pembicara || '');
                        $('#kontak_person').val(data.kontak_person || '');
                        $('#whatsapp_kontak').val(data.whatsapp_kontak || '');
                        $('#email_kontak').val(data.email_kontak || '');
                        $('#harga_tiket').val(data.harga_tiket || 0);
                        $('#kuota_peserta').val(data.kuota_peserta || '');

                        // Status & checkbox
                        $('#status').val(data.status || 'draft');
                        $('#chkFeatured').prop('checked', !!data.is_featured);
                        $('#chkShowWeb').prop('checked', !!data.show_in_website);

                        // Gambar featured existing
                        if (data.gambar_featured) {
                            $('#currentFeatured').html(`
                                <img src="{{ asset('storage') }}/${data.gambar_featured}" alt="Featured" 
                                     style="max-width:280px; border-radius:8px; border:1px solid #e2e8f0;">
                                <p class="text-muted small mt-2">Gambar saat ini</p>
                            `);
                        } else {
                            $('#currentFeatured').html('<p class="text-muted small">Belum ada gambar featured</p>');
                        }

                        // Galeri existing
                        if (data.gambar_galeri && data.gambar_galeri.length > 0) {
                            let html = '';
                            data.gambar_galeri.forEach(img => {
                                html += `<img src="{{ asset('storage') }}/${img}" alt="Galeri" style="width:100%; aspect-ratio:1; object-fit:cover; border-radius:6px;">`;
                            });
                            $('#currentGallery').html(html);
                        } else {
                            $('#currentGallery').html('<p class="text-muted small">Belum ada gambar galeri</p>');
                        }

                        $('#modalEventLabel').text('Edit Event: ' + (data.nama_event || ''));
                        modalEvent.show();
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        Swal.fire('Error', 'Gagal memuat data event. Cek console untuk detail.', 'error');
                    }
                });
            }

            // Submit form
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
                        modalEvent.hide();
                        reloadGrid();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Event berhasil disimpan',
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    },
                    error: function (xhr) {
                        let msg = 'Terjadi kesalahan';
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            let html = '';
                            $.each(xhr.responseJSON.errors, function (field, msgs) {
                                html += `<li>${field}: ${msgs.join(', ')}</li>`;
                            });
                            $('#errorList').html(html);
                            $('#formErrors').show();
                            msg = 'Ada kesalahan validasi';
                        }
                        Swal.fire('Gagal', msg, 'error');
                    },
                    complete: function () {
                        $btnSubmit.prop('disabled', false).html('<i class="ri-save-line"></i> Simpan Event');
                    }
                });
            });

            // Filter
            $('#btnFilter').on('click', reloadGrid);

            function reloadGrid() {
                const params = {
                    kategori_id: $('#kategori_id').val() || '',
                    status: $('#status').val() || '',
                    search: $('#search').val().trim(),
                    ajax: 1
                };

                $.ajax({
                    url: '{{ route('admin.events.index') }}',
                    type: 'GET',
                    data: params,
                    success: function (html) {
                        const $html = $(html);
                        $('#eventsGrid').html($html.find('#eventsGrid').html() || '');
                        $('#paginationContainer').html($html.find('#paginationContainer').html() || '');
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        Swal.fire('Gagal', 'Tidak bisa memuat daftar event', 'error');
                    }
                });
            }

            // Preview gambar featured
            $('#gambar_featured').on('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        $('#previewFeatured').attr('src', e.target.result);
                        $('#featuredPreview').show();
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endpush