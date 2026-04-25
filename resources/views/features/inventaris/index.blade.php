@extends('layouts.admin')

@section('title', 'Data Inventaris')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="lux-container">

        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Data Inventaris</h1>
                <p class="text-muted">Kelola data inventaris Gereja Filadelfia</p>
            </div>
            <button type="button" class="lux-btn lux-btn-primary btn-tambah-inventaris">
                <i class="ri-add-line"></i> Tambah Inventaris
            </button>
        </div>

        <!-- Toast -->
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 2000">
            <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fw-bold" id="toastMessage">Operasi berhasil!</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="lux-card mb-4">
            <form id="formFilter" style="display: grid; grid-template-columns: {{ !Auth::user()?->roles->first()?->branch_id ? '2fr 1fr 1fr 1fr 1fr auto' : '2fr 1fr 1fr 1fr auto' }}; gap: 1rem; align-items: end;">
                <div>
                    <label class="lux-label">Cari Inventaris</label>
                    <div style="position: relative;">
                        <i class="ri-search-line" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="text" id="search" name="search" class="lux-input" value="{{ request('search') }}" 
                               placeholder="Nama barang..." style="padding-left: 36px;">
                    </div>
                </div>
                <button type="button" id="btnFilter" class="lux-btn lux-btn-primary">
                    <i class="ri-filter-line"></i> Filter
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="lux-card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table class="table table-hover mb-0" id="tabel-inventaris">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 15px; text-align: left; color: var(--text-muted); font-weight: 700;">Cabang</th>
                            <th style="padding: 15px; text-align: left; color: var(--text-muted); font-weight: 700;">Nama Barang</th>
                            <th style="padding: 15px; text-align: left; color: var(--text-muted); font-weight: 700;">Kategori</th>
                            <th style="padding: 15px; text-align: left; color: var(--text-muted); font-weight: 700;">Kondisi</th>
                            <th style="padding: 15px; text-align: center; color: var(--text-muted); font-weight: 700;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="inventarisBody">
                        @include('features.inventaris._list')   {{-- Partial grid/list --}}
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ================== MODAL FORM ================== -->
    <div class="modal fade" id="modalInventarisForm" tabindex="-1" aria-labelledby="modalInventarisLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: var(--radius-lg); border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">

                <div class="modal-header lux-card-header">
                    <h5 class="modal-title" id="modalInventarisLabel">Tambah Inventaris Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formInventaris" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="inventarisId" value="">

                    <div class="modal-body lux-container">

                        <div class="alert alert-danger" id="formErrors" style="display:none;">
                            <ul class="mb-0" id="errorList"></ul>
                        </div>

                        <div class="lux-grid lux-grid-1">

                            <div class="lux-card">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">Data Inventaris</h3>
                                </div>

                                @if (!Auth::user()?->roles->first()?->branch_id)
                                    <div class="lux-form-group">
                                        <label class="lux-label">Cabang *</label>
                                        <select name="branch_id" id="branch_id" class="lux-select" required>
                                            <option value="">Pilih Cabang</option>
                                            @foreach (\App\Models\Branch::all() as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <input type="hidden" name="branch_id" value="{{ Auth::user()->roles->first()->branch_id }}">
                                @endif

                                <div class="lux-form-group">
                                    <label class="lux-label">Nama Barang *</label>
                                    <input type="text" name="nama_barang" id="nama_barang" class="lux-input" required>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Kategori *</label>
                                    <input type="text" name="kategori" id="kategori" class="lux-input" required>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Kondisi *</label>
                                    <select name="kondisi" id="kondisi" class="lux-select" required>
                                        <option value="">Pilih Kondisi</option>
                                        <option value="Baik">Baik</option>
                                        <option value="Rusak Ringan">Rusak Ringan</option>
                                        <option value="Rusak Berat">Rusak Berat</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-primary" id="btnSubmitInventaris">
                            <i class="ri-save-line"></i> Simpan Inventaris
                        </button>
                    </div>
                </form>
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

            const modalInventaris = new bootstrap.Modal('#modalInventarisForm');
            const $form = $('#formInventaris');
            const $btnSubmit = $('#btnSubmitInventaris');
            const $inventarisBody = $('#inventarisBody');

            // ==================== TAMBAH ====================
            $('.btn-tambah-inventaris').on('click', function () {
                resetForm('create');
                $('#modalInventarisLabel').text('Tambah Inventaris Baru');
                modalInventaris.show();
            });

            // ==================== EDIT ====================
            $(document).on('click', '.btn-edit-inventaris', function () {
                const id = $(this).data('id');
                loadDataForEdit(id);
            });

            // ==================== HAPUS ====================
            $(document).on('click', '.btn-delete-inventaris', function (e) {
                e.preventDefault();
                const id = $(this).data('id');
                const nama = $(this).closest('tr').find('td:eq(1)').text().trim();

                Swal.fire({
                    title: 'Yakin hapus?',
                    text: `Inventaris: ${nama}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteInventaris(id);
                    }
                });
            });

            // ==================== RESET FORM ====================
            function resetForm(mode = 'create') {
                $form[0].reset();
                $form.attr('action', '{{ route("admin.inventaris.store") }}');
                $('#formMethod').val('POST');
                $('#inventarisId').val('');
                $('#formErrors').hide();
            }

            // ==================== LOAD EDIT ====================
            function loadDataForEdit(id) {
                $.ajax({
                    url: '{{ url("admin/inventaris") }}/' + id + '/edit?ajax=1',
                    type: 'GET',
                    success: function (res) {
                        if (res.success) {
                            resetForm('edit');
                            $form.attr('action', '{{ url("admin/inventaris") }}/' + id);
                            $('#formMethod').val('PUT');
                            $('#inventarisId').val(id);

                            $('#modalInventarisLabel').text('Edit Inventaris');

                            @if (!Auth::user()?->roles->first()?->branch_id)
                                $('#branch_id').val(res.data.branch_id || '');
                            @endif

                            $('#nama_barang').val(res.data.nama_barang || '');
                            $('#kategori').val(res.data.kategori || '');
                            $('#kondisi').val(res.data.kondisi || '');

                            modalInventaris.show();
                        } else {
                            Swal.fire('Gagal', res.message || 'Data tidak ditemukan', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Gagal', 'Gagal memuat data', 'error');
                    }
                });
            }

            // ==================== SUBMIT FORM (Create & Update) ====================
            $form.on('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                $btnSubmit.prop('disabled', true)
                          .html('<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.success) {
                            modalInventaris.hide();
                            showSuccess(res.message || 'Data berhasil disimpan');
                            reloadTable();   // Refresh list
                        } else {
                            showError(res.message || 'Gagal menyimpan');
                        }
                    },
                    error: function () {
                        showError('Terjadi kesalahan saat menyimpan data');
                    },
                    complete: function () {
                        $btnSubmit.prop('disabled', false)
                                  .html('<i class="ri-save-line"></i> Simpan Inventaris');
                    }
                });
            });

            // ==================== DELETE ====================
            function deleteInventaris(id) {
                $.ajax({
                    url: '{{ url("admin/inventaris/delete") }}/' + id,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        if (res.success) {
                            showSuccess(res.message || 'Data berhasil dihapus');
                            reloadTable();
                        } else {
                            showError(res.message || 'Gagal menghapus');
                        }
                    },
                    error: function () {
                        showError('Terjadi kesalahan saat menghapus');
                    }
                });
            }

            // ==================== RELOAD TABLE (VERSI OPTIMAL) ====================
            function reloadTable() {
                $.ajax({
                    url: '{{ route("admin.inventaris.index") }}?ajax=1',
                    type: 'GET',
                    success: function (html) {
                        // Cara paling aman: replace seluruh tbody
                        const $newBody = $(html).find('#inventarisBody');
                        if ($newBody.length) {
                            $inventarisBody.html($newBody.html());
                        } else {
                            // Fallback jika struktur berbeda
                            $inventarisBody.html(html);
                        }
                    },
                    error: function (xhr) {
                        console.error('Reload table failed:', xhr.responseText);
                        showError('Gagal memuat ulang daftar inventaris');
                    }
                });
            }

            // ==================== HELPER ====================
            function showSuccess(message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: message,
                    timer: 1800,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }

            function showError(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message
                });
            }

            // Filter
            $('#btnFilter').on('click', function () {
                $('#formFilter').submit();
            });

            // Inisialisasi
            console.log('Inventaris Script initialized with auto refresh');
        });
    </script>
@endpush