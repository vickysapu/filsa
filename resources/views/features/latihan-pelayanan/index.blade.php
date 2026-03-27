@extends('layouts.admin')

@section('title', 'Jadwal Latihan Pelayanan')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Jadwal Latihan Pelayanan</h1>
                <p class="text-muted">Kelola jadwal latihan persiapan pelayanan gereja</p>
            </div>
            <button type="button" class="lux-btn lux-btn-primary btn-tambah-latihan">
                <i class="ri-calendar-add-line"></i> Buat Jadwal Latihan Baru
            </button>
        </div>

        <!-- Alert Success -->
        @if (session('success'))
            <div class="lux-card mb-4" style="border-left: 4px solid var(--success); background: #f0fdf4; display: flex; align-items: center; gap: 10px;">
                <i class="ri-checkbox-circle-line" style="color: var(--success); font-size: 1.5rem;"></i>
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
                    <label class="lux-label">Dari Tanggal</label>
                    <input type="date" id="tanggal_start" class="lux-input" value="{{ request('tanggal_start') }}">
                </div>
                <div>
                    <label class="lux-label">Sampai Tanggal</label>
                    <input type="date" id="tanggal_end" class="lux-input" value="{{ request('tanggal_end') }}">
                </div>
                <div>
                    <label class="lux-label">Kategori</label>
                    <select id="kategori_id" class="lux-select">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lux-label">Status</label>
                    <select id="status" class="lux-select">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="lux-label">Cari</label>
                    <input type="text" id="search" class="lux-input" value="{{ request('search') }}" placeholder="Nama latihan, lokasi...">
                </div>
                <div style="align-self: end;">
                    <button type="button" id="btnFilter" class="lux-btn lux-btn-primary">
                        <i class="ri-filter-line"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- List Card Grid -->
        <div class="lux-grid lux-grid-3" id="latihanList">
            @include('features.latihan-pelayanan._list')
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" style="margin-top: 2rem; display: flex; justify-content: center;">
            {{ $latihans->links('pagination::simple-bootstrap-4') }}
        </div>
    </div>

    <!-- ================== MODAL FORM (Tambah & Edit) ================== -->
    <div class="modal fade" id="modalLatihanForm" tabindex="-1" aria-labelledby="modalLatihanLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content" style="border-radius: var(--radius-lg); border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">

                <div class="modal-header lux-card-header" style="border-bottom: 1px solid #e2e8f0;">
                    <h5 class="modal-title" id="modalLatihanLabel">Buat Jadwal Latihan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formLatihan" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="latihanId" value="">

                    <div class="modal-body lux-container">

                        <div class="alert alert-danger" id="formErrors" style="display:none;">
                            <ul class="mb-0" id="errorList"></ul>
                        </div>

                        <div class="lux-grid lux-grid-2">

                            <!-- Kolom Kiri: Informasi Latihan -->
                            <div class="lux-card">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">Informasi Latihan</h3>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Kategori Pelayanan *</label>
                                    <select name="kategori_id" id="kategori_id" class="lux-select" required>
                                        <option value="">Pilih Kategori...</option>
                                        @foreach ($kategoris as $kategori)
                                            <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Nama Latihan *</label>
                                    <input type="text" name="nama_latihan" id="nama_latihan" class="lux-input" required
                                        placeholder="Contoh: Latihan Paduan Suara Minggu">
                                </div>

                                <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                                    <div class="lux-form-group">
                                        <label class="lux-label">Tanggal *</label>
                                        <input type="date" name="tanggal" id="tanggal" class="lux-input" required value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="lux-form-group">
                                        <label class="lux-label">Waktu *</label>
                                        <input type="time" name="waktu" id="waktu" class="lux-input" required value="19:00">
                                    </div>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Lokasi</label>
                                    <input type="text" name="lokasi" id="lokasi" class="lux-input" placeholder="Aula Lantai 2 / Ruang Musik">
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Keterangan</label>
                                    <textarea name="keterangan" id="keterangan" class="lux-textarea" rows="3" placeholder="Catatan latihan..."></textarea>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Status *</label>
                                    <div style="display: flex; gap: 1.5rem; margin-top: 8px;">
                                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                            <input type="radio" name="status" value="active" checked> Aktif
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                            <input type="radio" name="status" value="cancelled"> Dibatalkan
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Petugas -->
                            <div class="lux-card">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">Peserta Latihan</h3>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Cari Jemaat</label>
                                    <input type="text" id="searchPetugas" class="lux-input" placeholder="Ketik nama jemaat...">
                                </div>

                                <div id="petugasList" style="max-height: 420px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                    <!-- Di-load via AJAX -->
                                </div>

                                <div class="mt-3 d-flex gap-2 flex-wrap">
                                    <button type="button" id="selectAllPetugas" class="lux-btn lux-btn-outline lux-btn-sm">Pilih Semua</button>
                                    <button type="button" id="deselectAllPetugas" class="lux-btn lux-btn-outline lux-btn-sm">Batal Semua</button>
                                    <span id="selectedPetugasCount" class="lux-badge ms-auto">0 Terpilih</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-primary" id="btnSubmitLatihan">
                            <i class="ri-save-line"></i> Simpan Jadwal Latihan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-labelledby="modalConfirmDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: var(--radius-lg); border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <div class="modal-header lux-card-header" style="border-bottom: 1px solid #e2e8f0;">
                    <h5 class="modal-title" id="modalConfirmDeleteLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center lux-container">
                    <i class="ri-delete-bin-6-line" style="font-size: 3.5rem; color: var(--danger); margin-bottom: 1rem;"></i>
                    <p class="fw-bold mb-2" style="font-size: 1.25rem;">Yakin ingin menghapus jadwal latihan ini?</p>
                    <p class="text-muted">Aksi ini tidak dapat dibatalkan.</p>
                    <p id="deleteNamaLatihan" class="fw-bold text-primary mt-3"></p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="lux-btn lux-btn-danger" id="btnConfirmDelete">
                        <i class="ri-delete-bin-line"></i> Ya, Hapus
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

            const modalForm = new bootstrap.Modal('#modalLatihanForm');
            const modalDelete = new bootstrap.Modal('#modalConfirmDelete');

            const $form = $('#formLatihan');
            const $btnSubmit = $('#btnSubmitLatihan');

            // === Tombol Tambah ===
            $('.btn-tambah-latihan').on('click', function () {
                resetForm('create');
                loadPetugasList();
                modalForm.show();
            });

            // === Tombol Edit ===
            $(document).on('click', '.btn-edit-latihan', function () {
                const id = $(this).data('id');
                loadDataForEdit(id);
            });

            // === Tombol Hapus ===
            $(document).on('click', '.btn-delete-latihan', function (e) {
                e.preventDefault();
                const id = $(this).data('id');
                const nama = $(this).data('nama') || 'Jadwal latihan ini';

                $('#deleteNamaLatihan').text(nama);
                $('#btnConfirmDelete').data('id', id);
                modalDelete.show();
            });

            // Konfirmasi Hapus
            $('#btnConfirmDelete').on('click', function () {
                const id = $(this).data('id');
                $.ajax({
                    url: '{{ route("admin.latihan-pelayanan.destroy", ":id") }}'.replace(':id', id),
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, toast: true, position: 'top-end' });
                            modalDelete.hide();
                            reloadList();
                        }
                    }
                });
            });

            // Load data untuk Edit
            function loadDataForEdit(id) {
                $.get('{{ route("admin.latihan-pelayanan.edit", ":id") }}'.replace(':id', id) + '?ajax=1', function (data) {
                    if (data.success) {
                        resetForm('edit');
                        $form.attr('action', '{{ route("admin.latihan-pelayanan.update", ":id") }}'.replace(':id', id));
                        $('#formMethod').val('PUT');
                        $('#latihanId').val(id);

                        $('#kategori_id').val(data.kategori_id);
                        $('#nama_latihan').val(data.nama_latihan);
                        $('#tanggal').val(data.tanggal);
                        $('#waktu').val(data.waktu);
                        $('#lokasi').val(data.lokasi || '');
                        $('#keterangan').val(data.keterangan || '');
                        $('input[name="status"][value="' + data.status + '"]').prop('checked', true);

                        loadPetugasList(data.selected_jemaat_ids || [], data.roles || {});

                        $('#modalLatihanLabel').text('Edit Jadwal Latihan');
                        modalForm.show();
                    }
                });
            }

            // Reset Form
            function resetForm(mode = 'create') {
                $form[0].reset();
                $form.attr('action', '{{ route("admin.latihan-pelayanan.store") }}');
                $('#formMethod').val('POST');
                $('#latihanId').val('');
                $('#formErrors').hide();

                if (mode === 'create') {
                    $('#modalLatihanLabel').text('Buat Jadwal Latihan Baru');
                    $('#tanggal').val('{{ date("Y-m-d") }}');
                    $('#waktu').val('19:00');
                    $('input[name="status"][value="active"]').prop('checked', true);
                }
            }

            // Load Petugas List (AJAX)
            function loadPetugasList(selectedIds = [], roles = {}) {
                $('#petugasList').html(`
                    <div style="padding: 2rem; text-align: center;">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2">Memuat daftar jemaat...</p>
                    </div>
                `);

                $.get('{{ route("admin.latihan-pelayanan.create") }}?ajax=1&selected=' + JSON.stringify(selectedIds), function (data) {
                    if (data.success) {
                        $('#petugasList').html(data.petugas_html);
                        updateSelectedCount();
                    }
                });
            }

            // Submit Form
            $form.on('submit', function (e) {
                e.preventDefault();
                $btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...');

                const formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        modalForm.hide();
                        reloadList();
                        if (res.success) {
                            Swal.fire({ icon: 'success', title: 'Sukses', text: res.message, timer: 2200, toast: true, position: 'top-end' });
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            let html = '';
                            $.each(xhr.responseJSON.errors || {}, (field, msgs) => html += `<li>${field}: ${msgs.join(', ')}</li>`);
                            $('#errorList').html(html);
                            $('#formErrors').show();
                        } else {
                            Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                        }
                    },
                    complete: function () {
                        $btnSubmit.prop('disabled', false).html('<i class="ri-save-line"></i> Simpan Jadwal Latihan');
                    }
                });
            });

            // Filter & Reload
            $('#btnFilter').on('click', reloadList);

            function reloadList() {
                const params = {
                    tanggal_start: $('#tanggal_start').val(),
                    tanggal_end: $('#tanggal_end').val(),
                    kategori_id: $('#kategori_id').val(),
                    status: $('#status').val(),
                    search: $('#search').val().trim(),
                    ajax: 1
                };

                $.get('{{ route("admin.latihan-pelayanan.index") }}?' + $.param(params), function (html) {
                    $('#latihanList').html($(html).find('#latihanList').html() || html);
                    $('#paginationContainer').html($(html).find('#paginationContainer').html() || '');
                });
            }

            // Select All / Deselect All
            $('#selectAllPetugas').on('click', () => $('#petugasList input[type="checkbox"]').prop('checked', true).trigger('change'));
            $('#deselectAllPetugas').on('click', () => $('#petugasList input[type="checkbox"]').prop('checked', false).trigger('change'));

            // Update count
            function updateSelectedCount() {
                const count = $('#petugasList input[type="checkbox"]:checked').length;
                $('#selectedPetugasCount').text(count + ' Terpilih');
            }

            $(document).on('change', '#petugasList .jemaat-checkbox', updateSelectedCount);

            // Search live
            $('#searchPetugas').on('input', function () {
                const val = $(this).val().toLowerCase();
                $('#petugasList label').each(function () {
                    $(this).toggle($(this).text().toLowerCase().includes(val));
                });
            });

            // Load awal
            reloadList();
        });
    </script>
@endpush