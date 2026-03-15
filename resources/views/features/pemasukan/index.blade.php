@extends('layouts.admin')

@section('title', 'Data Pemasukan')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Data Pemasukan</h1>
                <p class="text-muted">Kelola semua pemasukan keuangan gereja</p>
            </div>
            <button type="button" class="lux-btn lux-btn-primary btn-tambah-pemasukan">
                <i class="ri-add-line"></i> Tambah Pemasukan
            </button>
        </div>

        <!-- Alert Success -->
        @if (session('success'))
            <div class="lux-card mb-4"
                style="border-left: 4px solid var(--success); background: #f0fdf4; display: flex; align-items: center; gap: 10px;">
                <i class="ri-checkbox-circle-line" style="color: var(--success); font-size: 1.5rem;"></i>
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

        <!-- Statistics Cards -->
        <div class="lux-grid lux-grid-4 mb-5">
            <div class="lux-card" style="border-left: 5px solid var(--primary);">
                <p class="text-muted mb-1">Total Pemasukan</p>
                <h3 class="fw-bold text-primary">Rp {{ number_format($stats['total'] ?? 0, 0, ',', '.') }}</h3>
                <small class="text-muted">{{ $stats['count'] ?? 0 }} transaksi</small>
            </div>
            <div class="lux-card" style="border-left: 5px solid var(--success);">
                <p class="text-muted mb-1">Terverifikasi</p>
                <h3 class="fw-bold text-success">Rp {{ number_format($stats['verified'] ?? 0, 0, ',', '.') }}</h3>
                <small class="text-muted">Sudah diverifikasi</small>
            </div>
            <div class="lux-card" style="border-left: 5px solid var(--warning);">
                <p class="text-muted mb-1">Pending</p>
                <h3 class="fw-bold text-warning">{{ $stats['pending'] ?? 0 }}</h3>
                <small class="text-muted">Menunggu verifikasi</small>
            </div>
            <div class="lux-card" style="border-left: 5px solid var(--accent);">
                <p class="text-muted mb-1">Export Data</p>
                <button onclick="window.location.href='{{ route('admin.pemasukan.export', request()->query()) }}'"
                    class="lux-btn lux-btn-outline w-100 mt-2">
                    <i class="ri-file-excel-line"></i> Download Excel
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="lux-card mb-5">
            <form id="formFilter"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div>
                    <label class="lux-label">Tanggal Mulai</label>
                    <input type="date" id="tanggal_start" name="tanggal_start" class="lux-input"
                        value="{{ request('tanggal_start') }}">
                </div>
                <div>
                    <label class="lux-label">Tanggal Akhir</label>
                    <input type="date" id="tanggal_end" name="tanggal_end" class="lux-input"
                        value="{{ request('tanggal_end') }}">
                </div>
                <div>
                    <label class="lux-label">Kategori</label>
                    <select id="kategori" name="kategori" class="lux-select">
                        <option value="">Semua Kategori</option>
                        <option value="persembahan_minggu"
                            {{ request('kategori') == 'persembahan_minggu' ? 'selected' : '' }}>Persembahan Minggu</option>
                        <option value="persembahan_khusus"
                            {{ request('kategori') == 'persembahan_khusus' ? 'selected' : '' }}>Persembahan Khusus</option>
                        <option value="donasi" {{ request('kategori') == 'donasi' ? 'selected' : '' }}>Donasi</option>
                        <option value="kegiatan" {{ request('kategori') == 'kegiatan' ? 'selected' : '' }}>Kegiatan
                        </option>
                        <option value="usaha_mandiri" {{ request('kategori') == 'usaha_mandiri' ? 'selected' : '' }}>Usaha
                            Mandiri</option>
                        <option value="lainnya" {{ request('kategori') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="lux-label">Status</label>
                    <select id="status" name="status" class="lux-select">
                        <option value="">Semua</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div>
                    <label class="lux-label">Cari</label>
                    <input type="text" id="search" name="search" class="lux-input" value="{{ request('search') }}"
                        placeholder="No. Transaksi, Keterangan...">
                </div>
                <div style="align-self: end;">
                    <button type="button" id="btnFilter" class="lux-btn lux-btn-primary">
                        <i class="ri-filter-line"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="lux-card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table class="table table-hover mb-0" id="tablePemasukan">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 15px; font-weight: 700; color: var(--text-muted);">Tanggal</th>
                            <th style="padding: 15px; font-weight: 700; color: var(--text-muted);">No. Transaksi</th>
                            <th style="padding: 15px; font-weight: 700; color: var(--text-muted);">Kategori</th>
                            <th style="padding: 15px; font-weight: 700; color: var(--text-muted);">Sumber Dana</th>
                            <th style="padding: 15px; text-align: right; font-weight: 700; color: var(--text-muted);">
                                Jumlah</th>
                            <th style="padding: 15px; text-align: center; font-weight: 700; color: var(--text-muted);">
                                Status</th>
                            <th style="padding: 15px; text-align: center; font-weight: 700; color: var(--text-muted);">Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody id="pemasukanList">
                        @include('features.pemasukan._list')
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="paginationContainer"
                style="padding: 15px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <span class="text-muted" style="font-size: 0.85rem;">
                    Menampilkan {{ $pemasukans->firstItem() }} - {{ $pemasukans->lastItem() }} dari
                    {{ $pemasukans->total() }} data
                </span>
                {{ $pemasukans->links('pagination::simple-bootstrap-4') }}
            </div>
        </div>
    </div>

    <!-- ================== MODAL FORM (Tambah & Edit) ================== -->
    <div class="modal fade" id="modalPemasukanForm" tabindex="-1" aria-labelledby="modalPemasukanLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content"
                style="border-radius: var(--radius-lg); border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">

                <div class="modal-header lux-card-header" style="border-bottom: 1px solid #e2e8f0;">
                    <h5 class="modal-title" id="modalPemasukanLabel">Tambah Pemasukan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formPemasukan" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="pemasukanId" value="">

                    <div class="modal-body lux-container">

                        <div class="alert alert-danger" id="formErrors" style="display:none;">
                            <ul class="mb-0" id="errorList"></ul>
                        </div>

                        <div class="lux-grid lux-grid-2">

                            <!-- Kolom Kiri -->
                            <div class="lux-card">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">Informasi Transaksi</h3>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Tanggal Transaksi *</label>
                                    <input type="date" name="tanggal" id="tanggal" class="lux-input" required
                                        value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Kategori Pemasukan *</label>
                                    <select name="kategori" id="kategori" class="lux-select" required>
                                        <option value="">Pilih Kategori...</option>
                                        <option value="persembahan_minggu">Persembahan Minggu</option>
                                        <option value="persembahan_khusus">Persembahan Khusus</option>
                                        <option value="donasi">Donasi</option>
                                        <option value="kegiatan">Kegiatan</option>
                                        <option value="usaha_mandiri">Usaha Mandiri</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Sumber Dana *</label>
                                    <input type="text" name="sumber_dana" id="sumber_dana" class="lux-input" required
                                        placeholder="Contoh: Kolekte Minggu I, Donasi Bapak John">
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Jumlah (Rp) *</label>
                                    <input type="number" name="jumlah" id="jumlah" class="lux-input" required
                                        min="1000" step="1000" placeholder="1000000">
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Status Awal *</label>
                                    <div style="display: flex; gap: 1.5rem; margin-top: 8px;">
                                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                            <input type="radio" name="status" value="pending" checked> Pending
                                        </label>
                                        @if (auth()->user()->hasPermission('pemasukan.verify'))
                                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                                <input type="radio" name="status" value="verified"> Verified
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Kanan -->
                            <div class="lux-card">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">Detail & Bukti</h3>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Keterangan</label>
                                    <textarea name="keterangan" id="keterangan" class="lux-textarea" rows="4"
                                        placeholder="Deskripsi tambahan..."></textarea>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Bukti Foto / Document</label>
                                    <input type="file" name="bukti_foto" id="bukti_foto" class="lux-input"
                                        accept="image/*,.pdf">
                                    <div id="buktiPreview" class="mt-2" style="display: none;">
                                        <img id="previewImg"
                                            style="max-width: 100%; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    </div>
                                    <p class="lux-form-help mt-2"><i class="ri-information-line"></i> JPG, PNG, PDF. Max
                                        5MB</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-primary" id="btnSubmitPemasukan">
                            <i class="ri-save-line"></i> Simpan Pemasukan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-labelledby="modalConfirmDeleteLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
                style="border-radius: var(--radius-lg); border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <div class="modal-header lux-card-header" style="border-bottom: 1px solid #e2e8f0;">
                    <h5 class="modal-title" id="modalConfirmDeleteLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center lux-container">
                    <i class="ri-delete-bin-6-line"
                        style="font-size: 3.5rem; color: var(--danger); margin-bottom: 1rem;"></i>
                    <p class="fw-bold mb-2" style="font-size: 1.25rem;">Yakin ingin menghapus pemasukan ini?</p>
                    <p class="text-muted">Aksi ini tidak dapat dibatalkan.</p>
                    <p id="deleteKeterangan" class="fw-bold text-primary mt-3"></p>
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

            const modalForm   = new bootstrap.Modal('#modalPemasukanForm');
            const modalDelete = new bootstrap.Modal('#modalConfirmDelete');

            const $form       = $('#formPemasukan');
            const $btnSubmit  = $('#btnSubmitPemasukan');
            const $errorBox   = $('#formErrors');
            const $errorList  = $('#errorList');

            // Tombol Tambah Pemasukan
            $('.btn-tambah-pemasukan').on('click', function () {
                resetForm('create');
                $('#modalPemasukanLabel').text('Tambah Pemasukan Baru');
                modalForm.show();
            });

            // Event delegation untuk tombol Edit & Hapus
            $(document).on('click', '.btn-edit-pemasukan', function () {
                const id = $(this).data('id');
                loadDataForEdit(id);
            });

            $(document).on('click', '.btn-delete-pemasukan', function (e) {
                e.preventDefault();
                const id          = $(this).data('id');
                const keterangan = $(this).data('keterangan') || 'Pemasukan ini';

                $('#deleteKeterangan').text(keterangan);
                $('#btnConfirmDelete').data('id', id);
                modalDelete.show();
            });

            // Konfirmasi Hapus
            $('#btnConfirmDelete').on('click', function () {
                const id = $(this).data('id');
                $.ajax({
                    url: '{{ route("admin.pemasukan.destroy", ":id") }}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message || 'Pemasukan berhasil dihapus',
                                timer: 2200,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                            modalDelete.hide();
                            reloadList();
                        }
                    },
                    error: function () {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus', 'error');
                    }
                });
            });

            // Load data untuk Edit
            function loadDataForEdit(id) {
                $.ajax({
                    url: '{{ route("admin.pemasukan.edit", ":id") }}'.replace(':id', id) + '?ajax=1',
                    type: 'GET',
                    success: function (data) {
                        if (data.success) {
                            resetForm('edit');

                            $form.attr('action', '{{ route("admin.pemasukan.update", ":id") }}'.replace(':id', id));
                            $('#formMethod').val('PUT');
                            $('#pemasukanId').val(id);

                            // Isi field
                            $('#tanggal').val(data.tanggal || '{{ date("Y-m-d") }}');
                            $('#kategori').val(data.kategori || '');
                            $('#sumber_dana').val(data.sumber_dana || '');
                            $('#jumlah').val(data.jumlah || '');
                            $('#keterangan').val(data.keterangan || '');
                            $('input[name="status"][value="' + (data.status || 'pending') + '"]').prop('checked', true);

                            // Preview bukti jika ada
                            if (data.bukti_foto) {
                                $('#previewImg').attr('src', '{{ asset("storage") }}/' + data.bukti_foto);
                                $('#buktiPreview').show();
                            } else {
                                $('#buktiPreview').hide();
                            }

                            $('#modalPemasukanLabel').text('Edit Pemasukan');
                            modalForm.show();
                        } else {
                            Swal.fire('Gagal', data.message || 'Data tidak ditemukan', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Gagal', 'Gagal memuat data pemasukan', 'error');
                    }
                });
            }

            // Reset Form
            function resetForm(mode = 'create') {
                $form[0].reset();
                $form.attr('action', '{{ route("admin.pemasukan.store") }}');
                $('#formMethod').val('POST');
                $('#pemasukanId').val('');
                $errorBox.hide();
                $errorList.empty();
                $('#buktiPreview').hide();

                if (mode === 'create') {
                    $('#modalPemasukanLabel').text('Tambah Pemasukan Baru');
                    $('#tanggal').val('{{ date("Y-m-d") }}');
                    $('input[name="status"][value="pending"]').prop('checked', true);
                }
            }

            // Submit Form (Create & Update) - Selalu tutup modal & reload table saat sukses
            $form.on('submit', function (e) {
                e.preventDefault();

                $btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...');

                const formData = new FormData(this);

                // Pastikan CSRF token ada
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function (res) {
                        // Selalu tutup modal & reload table
                        modalForm.hide();
                        reloadList();

                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sukses',
                                text: res.message || 'Pemasukan berhasil disimpan',
                                timer: 2200,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.message || 'Operasi gagal',
                                timer: 3000
                            });
                        }
                    },
                    error: function (xhr) {
                        modalForm.hide();
                        reloadList();

                        let errorMsg = 'Terjadi kesalahan server';

                        if (xhr.status === 422) {
                            let html = '';
                            $.each(xhr.responseJSON.errors || {}, function (field, msgs) {
                                html += `<li>${field}: ${msgs.join(', ')}</li>`;
                            });
                            $errorList.html(html);
                            $errorBox.show();
                            errorMsg = 'Validasi gagal, periksa input Anda.';
                        } else if (xhr.status === 419) {
                            errorMsg = 'CSRF token mismatch. Silakan refresh halaman.';
                        } else if (xhr.status === 500) {
                            errorMsg = 'Server error (500). Cek log Laravel.';
                            console.error('Server 500:', xhr.responseText);
                        } else {
                            errorMsg = xhr.responseJSON?.message || `Error ${xhr.status}`;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Operasi Gagal',
                            text: errorMsg,
                            timer: 3500
                        });
                    },
                    complete: function () {
                        $btnSubmit.prop('disabled', false).html('<i class="ri-save-line"></i> Simpan Pemasukan');
                    }
                });
            });

            // Filter & Reload
            $('#btnFilter').on('click', reloadList);

            function getFilterParams() {
                return {
                    tanggal_start: $('#tanggal_start').val(),
                    tanggal_end: $('#tanggal_end').val(),
                    kategori: $('#kategori').val(),
                    status: $('#status').val(),
                    search: $('#search').val().trim(),
                    ajax: 1
                };
            }

            function reloadList() {
                const params = getFilterParams();
                $.ajax({
                    url: '{{ route("admin.pemasukan.index") }}?' + $.param(params),
                    type: 'GET',
                    success: function (html) {
                        $('#pemasukanList').html($(html).find('#pemasukanList').html() || html);
                        $('#paginationContainer').html($(html).find('#paginationContainer').html() || '');
                    },
                    error: function () {
                        Swal.fire('Gagal', 'Gagal memuat data pemasukan', 'error');
                    }
                });
            }

            reloadList(); // Load awal

            // Preview bukti foto
            $('#bukti_foto').on('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        $('#previewImg').attr('src', e.target.result);
                        $('#buktiPreview').show();
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endpush