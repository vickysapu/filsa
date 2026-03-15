@extends('layouts.admin')

@section('title', 'Data Pengeluaran')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Data Pengeluaran</h1>
                <p class="text-muted">Kelola semua pengeluaran keuangan gereja</p>
            </div>
            <button type="button" class="lux-btn lux-btn-danger btn-tambah-pengeluaran">
                <i class="ri-add-line"></i> Tambah Pengeluaran
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

        <!-- Statistics Cards -->
        <div class="lux-grid lux-grid-4 mb-5">
            <div class="lux-card" style="border-left: 5px solid var(--danger);">
                <p class="text-muted mb-1">Total Pengeluaran</p>
                <h3 class="fw-bold text-danger">Rp {{ number_format($stats['total'] ?? 0, 0, ',', '.') }}</h3>
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
                <button onclick="window.location.href='{{ route('admin.pengeluaran.export', request()->query()) }}'"
                    class="lux-btn lux-btn-outline w-100 mt-2">
                    <i class="ri-file-excel-line"></i> Download Excel
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="lux-card mb-5">
            <form id="formFilter" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div>
                    <label class="lux-label">Tanggal Mulai</label>
                    <input type="date" id="tanggal_start" name="tanggal_start" class="lux-input" value="{{ request('tanggal_start') }}">
                </div>
                <div>
                    <label class="lux-label">Tanggal Akhir</label>
                    <input type="date" id="tanggal_end" name="tanggal_end" class="lux-input" value="{{ request('tanggal_end') }}">
                </div>
                <div>
                    <label class="lux-label">Kategori</label>
                    <select id="kategori" name="kategori" class="lux-select">
                        <option value="">Semua Kategori</option>
                        <option value="operasional" {{ request('kategori') == 'operasional' ? 'selected' : '' }}>Operasional</option>
                        <option value="listrik_air" {{ request('kategori') == 'listrik_air' ? 'selected' : '' }}>Listrik & Air</option>
                        <option value="gaji_karyawan" {{ request('kategori') == 'gaji_karyawan' ? 'selected' : '' }}>Gaji Karyawan</option>
                        <option value="kegiatan" {{ request('kategori') == 'kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                        <option value="pemeliharaan" {{ request('kategori') == 'pemeliharaan' ? 'selected' : '' }}>Pemeliharaan</option>
                        <option value="amal_sosial" {{ request('kategori') == 'amal_sosial' ? 'selected' : '' }}>Amal & Sosial</option>
                        <option value="konsumsi" {{ request('kategori') == 'konsumsi' ? 'selected' : '' }}>Konsumsi</option>
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
                    <input type="text" id="search" name="search" class="lux-input" value="{{ request('search') }}" placeholder="No. Transaksi, Keperluan...">
                </div>
                <div style="align-self: end;">
                    <button type="button" id="btnFilter" class="lux-btn lux-btn-danger">
                        <i class="ri-filter-line"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="lux-card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table class="table table-hover mb-0" id="tablePengeluaran">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 15px; font-weight: 700; color: var(--text-muted);">Tanggal</th>
                            <th style="padding: 15px; font-weight: 700; color: var(--text-muted);">No. Transaksi</th>
                            <th style="padding: 15px; font-weight: 700; color: var(--text-muted);">Kategori</th>
                            <th style="padding: 15px; font-weight: 700; color: var(--text-muted);">Keperluan</th>
                            <th style="padding: 15px; text-align: right; font-weight: 700; color: var(--text-muted);">Jumlah</th>
                            <th style="padding: 15px; text-align: center; font-weight: 700; color: var(--text-muted);">Status</th>
                            <th style="padding: 15px; text-align: center; font-weight: 700; color: var(--text-muted);">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pengeluaranList">
                        @include('features.pengeluaran._list')
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="paginationContainer" style="padding: 15px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <span class="text-muted" style="font-size: 0.85rem;">
                    Menampilkan {{ $pengeluarans->firstItem() }} - {{ $pengeluarans->lastItem() }} dari {{ $pengeluarans->total() }} data
                </span>
                {{ $pengeluarans->links('pagination::simple-bootstrap-4') }}
            </div>
        </div>
    </div>

    <!-- ================== MODAL FORM (Tambah & Edit) ================== -->
    <div class="modal fade" id="modalPengeluaranForm" tabindex="-1" aria-labelledby="modalPengeluaranLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: var(--radius-lg); border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">

                <div class="modal-header lux-card-header" style="border-bottom: 1px solid #e2e8f0;">
                    <h5 class="modal-title" id="modalPengeluaranLabel">Tambah Pengeluaran Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formPengeluaran" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="pengeluaranId" value="">

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
                                    <input type="date" name="tanggal" id="tanggal" class="lux-input" required value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Kategori Pengeluaran *</label>
                                    <select name="kategori" id="kategori" class="lux-select" required>
                                        <option value="">Pilih Kategori...</option>
                                        <option value="operasional">Operasional</option>
                                        <option value="listrik_air">Listrik & Air</option>
                                        <option value="gaji_karyawan">Gaji Karyawan</option>
                                        <option value="kegiatan">Kegiatan</option>
                                        <option value="pemeliharaan">Pemeliharaan</option>
                                        <option value="amal_sosial">Amal & Sosial</option>
                                        <option value="konsumsi">Konsumsi</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Keperluan *</label>
                                    <input type="text" name="keperluan" id="keperluan" class="lux-input" required placeholder="Contoh: Bayar listrik, Gaji karyawan Januari">
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Penerima Dana *</label>
                                    <input type="text" name="penerima" id="penerima" class="lux-input" required placeholder="Nama vendor/toko/pegawai">
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Jumlah (Rp) *</label>
                                    <input type="number" name="jumlah" id="jumlah" class="lux-input" required min="1000" step="1000" placeholder="1000000">
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Status Awal *</label>
                                    <div style="display: flex; gap: 1.5rem; margin-top: 8px;">
                                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                            <input type="radio" name="status" value="pending" checked> Pending
                                        </label>
                                        @if (auth()->user()->hasPermission('pengeluaran.verify'))
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
                                    <textarea name="keterangan" id="keterangan" class="lux-textarea" rows="4" placeholder="Deskripsi tambahan..."></textarea>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Bukti Foto / Document</label>
                                    <input type="file" name="bukti_foto" id="bukti_foto" class="lux-input" accept="image/*,.pdf">
                                    <div id="buktiPreview" class="mt-2" style="display: none;">
                                        <img id="previewImg" style="max-width: 100%; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    </div>
                                    <p class="lux-form-help mt-2"><i class="ri-information-line"></i> JPG, PNG, PDF. Max 5MB</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-danger" id="btnSubmitPengeluaran">
                            <i class="ri-save-line"></i> Simpan Pengeluaran
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
                    <p class="fw-bold mb-2" style="font-size: 1.25rem;">Yakin ingin menghapus pengeluaran ini?</p>
                    <p class="text-muted">Aksi ini tidak dapat dibatalkan.</p>
                    <p id="deleteKeterangan" class="fw-bold text-danger mt-3"></p>
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

            const modalForm   = new bootstrap.Modal('#modalPengeluaranForm');
            const modalDelete = new bootstrap.Modal('#modalConfirmDelete');

            const $form       = $('#formPengeluaran');
            const $btnSubmit  = $('#btnSubmitPengeluaran');
            const $errorBox   = $('#formErrors');
            const $errorList  = $('#errorList');

            // Tombol Tambah Pengeluaran
            $('.btn-tambah-pengeluaran').on('click', function () {
                resetForm('create');
                $('#modalPengeluaranLabel').text('Tambah Pengeluaran Baru');
                modalForm.show();
            });

            // Event delegation tombol Edit & Hapus
            $(document).on('click', '.btn-edit-pengeluaran', function () {
                const id = $(this).data('id');
                loadDataForEdit(id);
            });

            $(document).on('click', '.btn-delete-pengeluaran', function (e) {
                e.preventDefault();
                const id          = $(this).data('id');
                const keterangan = $(this).data('keterangan') || 'Pengeluaran ini';

                $('#deleteKeterangan').text(keterangan);
                $('#btnConfirmDelete').data('id', id);
                modalDelete.show();
            });

            // Konfirmasi Hapus
            $('#btnConfirmDelete').on('click', function () {
                const id = $(this).data('id');
                $.ajax({
                    url: '{{ route("admin.pengeluaran.destroy", ":id") }}'.replace(':id', id),
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
                                text: res.message || 'Pengeluaran berhasil dihapus',
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
                    url: '{{ route("admin.pengeluaran.edit", ":id") }}'.replace(':id', id) + '?ajax=1',
                    type: 'GET',
                    success: function (data) {
                        if (data.success) {
                            resetForm('edit');

                            $form.attr('action', '{{ route("admin.pengeluaran.update", ":id") }}'.replace(':id', id));
                            $('#formMethod').val('PUT');
                            $('#pengeluaranId').val(id);

                            // Isi field
                            $('#tanggal').val(data.tanggal || '{{ date("Y-m-d") }}');
                            $('#kategori').val(data.kategori || '');
                            $('#keperluan').val(data.keperluan || '');
                            $('#penerima').val(data.penerima || '');
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

                            $('#modalPengeluaranLabel').text('Edit Pengeluaran');
                            modalForm.show();
                        } else {
                            Swal.fire('Gagal', data.message || 'Data tidak ditemukan', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Gagal', 'Gagal memuat data pengeluaran', 'error');
                    }
                });
            }

            // Reset Form
            function resetForm(mode = 'create') {
                $form[0].reset();
                $form.attr('action', '{{ route("admin.pengeluaran.store") }}');
                $('#formMethod').val('POST');
                $('#pengeluaranId').val('');
                $errorBox.hide();
                $errorList.empty();
                $('#buktiPreview').hide();

                if (mode === 'create') {
                    $('#modalPengeluaranLabel').text('Tambah Pengeluaran Baru');
                    $('#tanggal').val('{{ date("Y-m-d") }}');
                    $('input[name="status"][value="pending"]').prop('checked', true);
                }
            }

            // Submit Form (Create & Update)
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Sukses',
                                text: res.message || 'Pengeluaran berhasil disimpan',
                                timer: 2200,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        } else {
                            Swal.fire('Gagal', res.message || 'Operasi gagal', 'error');
                        }
                    },
                    error: function (xhr) {
                        modalForm.hide();
                        reloadList();

                        console.log('AJAX Error:', xhr.status, xhr.responseText); // <-- tambahkan ini untuk debug

                        let errorMsg = 'Terjadi kesalahan server';

                        if (xhr.status === 422) {
                            // ... (sudah ada)
                        } else if (xhr.status === 500) {
                            errorMsg = 'Error server 500. Cek console atau log Laravel.';
                            console.error('Response 500:', xhr.responseText);
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
                        $btnSubmit.prop('disabled', false).html('<i class="ri-save-line"></i> Simpan Pengeluaran');
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
                $.get('{{ route("admin.pengeluaran.index") }}?' + $.param(params), function (html) {
                    $('#pengeluaranList').html($(html).find('#pengeluaranList').html() || html);
                    $('#paginationContainer').html($(html).find('#paginationContainer').html() || '');
                }).fail(function () {
                    Swal.fire('Gagal', 'Gagal memuat data pengeluaran', 'error');
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