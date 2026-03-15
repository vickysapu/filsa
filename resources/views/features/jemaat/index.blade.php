@extends('layouts.admin')

@section('title', 'Data Jemaat')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Data Jemaat</h1>
                <p class="text-muted">Kelola data anggota jemaat Gereja Filadelfia</p>
            </div>
            <button type="button" class="lux-btn lux-btn-primary btn-tambah-jemaat">
                <i class="ri-user-add-line"></i> Tambah Jemaat
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

        <div id="successToast">
        </div>

        <!-- Filter & Search Card -->
        <div class="lux-card mb-4">
            <form id="formFilter"
                style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                <div>
                    <label class="lux-label">Cari Jemaat</label>
                    <div style="position: relative;">
                        <i class="ri-search-line"
                            style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="text" id="search" name="search" class="lux-input"
                            value="{{ request('search') }}" placeholder="Nama, NIK, atau Email..."
                            style="padding-left: 36px;">
                    </div>
                </div>
                <div>
                    <label class="lux-label">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" class="lux-select">
                        <option value="">Semua</option>
                        <option value="1" {{ request('jenis_kelamin') == '1' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="0" {{ request('jenis_kelamin') == '0' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="lux-label">Status Pernikahan</label>
                    <select id="status_pernikahan" name="status_pernikahan" class="lux-select">
                        <option value="">Semua</option>
                        <option value="belum menikah"
                            {{ request('status_pernikahan') == 'belum menikah' ? 'selected' : '' }}>Belum Menikah</option>
                        <option value="menikah" {{ request('status_pernikahan') == 'menikah' ? 'selected' : '' }}>Menikah
                        </option>
                        <option value="janda" {{ request('status_pernikahan') == 'janda' ? 'selected' : '' }}>Janda
                        </option>
                        <option value="duda" {{ request('status_pernikahan') == 'duda' ? 'selected' : '' }}>Duda</option>
                    </select>
                </div>
                <div>
                    <label class="lux-label">Status Jemaat</label>
                    <select id="status_jemaat" name="status_jemaat" class="lux-select">
                        <option value="">Semua</option>
                        <option value="1" {{ request('status_jemaat') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('status_jemaat') == '0' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>
                <button type="button" id="btnFilter" class="lux-btn lux-btn-primary">
                    <i class="ri-filter-line"></i> Filter
                </button>
            </form>
        </div>

        <!-- Table Card -->
        <div class="lux-card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;" id="tabel-jemaat">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th
                                style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Jemaat</th>
                            <th
                                style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Kontak</th>
                            <th
                                style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Status</th>
                            <th
                                style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Bergabung</th>
                            <th
                                style="padding: 15px; text-align: center; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @include('features.jemaat._table')
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($jemaats->hasPages())
                <div
                    style="padding: 15px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                    <span class="text-muted" style="font-size: 0.85rem;">Menampilkan {{ $jemaats->firstItem() }} -
                        {{ $jemaats->lastItem() }} dari {{ $jemaats->total() }} data</span>
                    <div>
                        {{ $jemaats->links('pagination::simple-bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- ================== MODAL FORM (Tambah & Edit) ================== -->
    <div class="modal fade" id="modalJemaatForm" tabindex="-1" aria-labelledby="modalJemaatLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content"
                style="border-radius: var(--radius-lg); border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">

                <div class="modal-header lux-card-header" style="border-bottom: 1px solid #e2e8f0;">
                    <h5 class="modal-title" id="modalJemaatLabel">Tambah Jemaat Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formJemaat" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="jemaatId" value="">

                    <div class="modal-body lux-container">

                        <div class="alert alert-danger" id="formErrors" style="display:none;">
                            <ul class="mb-0" id="errorList"></ul>
                        </div>

                        <div class="lux-grid lux-grid-2">
                            <!-- Kolom Kiri: Identitas Diri -->
                            <div class="lux-card">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">Identitas Diri</h3>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Nama Lengkap *</label>
                                    <input type="text" name="nama_lengkap" id="nama_lengkap" class="lux-input"
                                        required placeholder="Sesuai KTP">
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">NIK *</label>
                                    <input type="number" name="NIK" id="NIK" class="lux-input" required
                                        placeholder="16 digit NIK" min="0">
                                </div>

                                <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                                    <div class="lux-form-group">
                                        <label class="lux-label">Tempat Lahir *</label>
                                        <input type="text" name="tempat_lahir" id="tempat_lahir" class="lux-input"
                                            required>
                                    </div>
                                    <div class="lux-form-group">
                                        <label class="lux-label">Tanggal Lahir *</label>
                                        <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="lux-input"
                                            required>
                                    </div>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Jenis Kelamin *</label>
                                    <div style="display: flex; gap: 1.5rem; margin-top: 8px;">
                                        <label
                                            style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500;">
                                            <input type="radio" name="jenis_kelamin" value="1" required> Laki-laki
                                        </label>
                                        <label
                                            style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500;">
                                            <input type="radio" name="jenis_kelamin" value="0" required> Perempuan
                                        </label>
                                    </div>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Foto Profil</label>
                                    <input type="file" name="foto" id="foto" class="lux-input"
                                        accept="image/*">
                                    <div id="fotoPreview" class="mt-2" style="max-width: 180px; display: none;">
                                        <img id="imgPreview"
                                            style="width: 100%; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    </div>
                                    <p class="lux-form-help"><i class="ri-information-line"></i> Format: JPG, PNG. Max:
                                        2MB</p>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Kontak & Data Tambahan -->
                            <div class="lux-card">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">Kontak & Data</h3>
                                </div>

                                <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                                    <div class="lux-form-group">
                                        <label class="lux-label">Email</label>
                                        <input type="email" name="email" id="email" class="lux-input"
                                            placeholder="email@contoh.com">
                                    </div>
                                    <div class="lux-form-group">
                                        <label class="lux-label">No. HP *</label>
                                        <input type="tel" name="no_hp" id="no_hp" class="lux-input" required
                                            placeholder="08123456789">
                                    </div>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Alamat Lengkap *</label>
                                    <textarea name="alamat" id="alamat" class="lux-textarea" rows="3" required
                                        placeholder="Nama jalan, RT/RW, Kelurahan, Kecamatan"></textarea>
                                </div>

                                <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 1.5rem 0;">

                                <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                                    <div class="lux-form-group">
                                        <label class="lux-label">Pendidikan Terakhir</label>
                                        <select name="pendidikan" id="pendidikan" class="lux-select">
                                            <option value="">Pilih...</option>
                                            <option value="SD">SD / Sederajat</option>
                                            <option value="SMP">SMP / Sederajat</option>
                                            <option value="SMA">SMA / SMK / Sederajat</option>
                                            <option value="D3">Diploma (D3)</option>
                                            <option value="S1">Sarjana (S1)</option>
                                            <option value="S2">Magister (S2)</option>
                                            <option value="S3">Doktor (S3)</option>
                                        </select>
                                    </div>
                                    <div class="lux-form-group">
                                        <label class="lux-label">Pekerjaan</label>
                                        <input type="text" name="pekerjaan" id="pekerjaan" class="lux-input"
                                            placeholder="Contoh: Wiraswasta">
                                    </div>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Status Pernikahan *</label>
                                    <select name="status_pernikahan" id="status_pernikahan" class="lux-select" required>
                                        <option value="">Pilih...</option>
                                        <option value="belum menikah">Belum Menikah</option>
                                        <option value="menikah">Menikah</option>
                                        <option value="janda">Janda</option>
                                        <option value="duda">Duda</option>
                                    </select>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Tanggal Bergabung *</label>
                                    <input type="date" name="tanggal_bergabung" id="tanggal_bergabung"
                                        class="lux-input" required value="{{ date('Y-m-d') }}">
                                </div>

                                <div class="lux-form-group mt-4 p-3 bg-light rounded">
                                    <label
                                        style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600;">
                                        <input type="checkbox" name="status_jemaat" id="status_jemaat" value="1"
                                            checked>
                                        <span>Status Jemaat Aktif</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-primary" id="btnSubmitJemaat">
                            <i class="ri-save-line"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================== MODAL KONFIRMASI HAPUS ================== -->
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
                    <p class="fw-bold mb-2" style="font-size: 1.25rem;">Yakin ingin menghapus data jemaat ini?</p>
                    <p class="text-muted">Aksi ini tidak dapat dibatalkan.</p>
                    <p id="deleteNamaJemaat" class="fw-bold text-primary mt-3"></p>
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
        $(document).ready(function() {

            // Inisialisasi modal Bootstrap
            const modalForm = new bootstrap.Modal('#modalJemaatForm');
            const modalDelete = new bootstrap.Modal('#modalConfirmDelete');

            const $form = $('#formJemaat');
            const $btnSubmit = $('#btnSubmitJemaat');
            const $errorBox = $('#formErrors');
            const $errorList = $('#errorList');

            // --------------------------------------------------
            // Tombol Tambah Jemaat
            // --------------------------------------------------
            $('.btn-tambah-jemaat').on('click', function() {
                resetForm('create');
                $('#modalJemaatLabel').text('Tambah Jemaat Baru');
                modalForm.show();
            });

            // --------------------------------------------------
            // Event delegation untuk tombol Edit & Delete di tabel
            // --------------------------------------------------
            $('#tabel-jemaat tbody').on('click', '.btn-edit-jemaat', function() {
                const id = $(this).data('id');
                loadDataForEdit(id);
            });

            $('#tabel-jemaat tbody').on('click', '.btn-delete-jemaat', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const nama = $(this).data('nama') || 'Jemaat ini';

                $('#deleteNamaJemaat').text(nama);
                $('#btnConfirmDelete').data('id', id);

                modalDelete.show();
            });

            // --------------------------------------------------
            // Konfirmasi Hapus via AJAX
            // --------------------------------------------------
            $('#btnConfirmDelete').on('click', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: '{{ route('admin.jemaat.destroy', ':id') }}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message || 'Data jemaat berhasil dihapus',
                                timer: 2500,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                            modalDelete.hide();
                            reloadTable();
                        } else {
                            alert(res.message || 'Gagal menghapus data');
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan server saat menghapus');
                    }
                });
            });

            // --------------------------------------------------
            // Load data untuk Edit (AJAX GET)
            // --------------------------------------------------
            function loadDataForEdit(id) {
                $.ajax({
                    url: '{{ route('admin.jemaat.edit', ':id') }}'.replace(':id', id) + '?ajax=1',
                    type: 'GET',
                    success: function(data) {
                        resetForm('edit');

                        // Set form untuk UPDATE
                        $form.attr('action', '{{ route('admin.jemaat.update', ':id') }}'.replace(':id',
                            id));
                        $('#formMethod').val('PUT');
                        $('#jemaatId').val(id);

                        // Isi field identitas
                        $('#nama_lengkap').val(data.nama_lengkap || '');
                        $('#NIK').val(data.NIK || '').prop('readonly', true);
                        $('#tempat_lahir').val(data.tempat_lahir || '');
                        $('#tanggal_lahir').val(data.tanggal_lahir ? data.tanggal_lahir.split('T')[0] :
                            '');
                        $('#alamat').val(data.alamat || '');
                        $('#no_hp').val(data.no_hp || '');
                        $('#email').val(data.email || '');
                        $('#status_pernikahan').val(data.status_pernikahan || '');
                        $('#pekerjaan').val(data.pekerjaan || '');
                        $('#pendidikan').val(data.pendidikan || '');
                        $('#tanggal_bergabung').val(data.tanggal_bergabung ? data.tanggal_bergabung
                            .split('T')[0] : '');

                        // =============================================
                        // Tambahan yang diminta: Jenis Kelamin
                        // =============================================
                        if (data.jenis_kelamin !== null && data.jenis_kelamin !== undefined) {
                            $('input[name="jenis_kelamin"][value="' + data.jenis_kelamin + '"]').prop(
                                'checked', true);
                        } else {
                            // Jika null, kosongkan semua radio (opsional)
                            $('input[name="jenis_kelamin"]').prop('checked', false);
                        }

                        // =============================================
                        // Tambahan: Status Jemaat (checkbox)
                        // =============================================
                        const isActive = data.status_jemaat == 1 || data.status_jemaat === true || data
                            .status_jemaat === '1';
                        $('#status_jemaat').prop('checked', isActive);

                        // =============================================
                        // Tambahan: Foto Profil
                        // =============================================
                        if (data.foto && data.foto.trim() !== '') {
                            $('#imgPreview').attr('src', '{{ asset('storage') }}/' + data.foto);
                            $('#fotoPreview').show();
                        } else {
                            $('#fotoPreview').hide();
                            $('#imgPreview').attr('src', ''); // clear src agar tidak tampil gambar lama
                        }

                        // Debug (bisa dihapus setelah testing)
                        console.log('Data edit loaded:', {
                            id: id,
                            nama: data.nama_lengkap,
                            jenis_kelamin: data.jenis_kelamin,
                            status_jemaat: data.status_jemaat,
                            foto: data.foto
                        });

                        $('#modalJemaatLabel').text('Edit Data Jemaat');
                        modalForm.show();
                    },
                    error: function(xhr) {
                        console.error('Error load edit:', xhr);
                        alert('Gagal memuat data jemaat untuk diedit');
                    }
                });
            }

            // --------------------------------------------------
            // Reset Form (create / edit)
            // --------------------------------------------------
            function resetForm(mode = 'create') {
                $form[0].reset();
                $form.attr('action', '{{ route('admin.jemaat.store') }}');
                $('#formMethod').val('POST');
                $('#jemaatId').val('');
                $('#NIK').prop('readonly', false);
                $('#fotoPreview').hide();
                $('#imgPreview').attr('src', '');
                $errorBox.hide();
                $errorList.empty();

                if (mode === 'create') {
                    $('#modalJemaatLabel').text('Tambah Jemaat Baru');
                    // Optional: set default tanggal bergabung hari ini
                    $('#tanggal_bergabung').val('{{ date('Y-m-d') }}');
                }
            }

            // --------------------------------------------------
            // Submit Form via AJAX (Create & Update)
            // --------------------------------------------------
            $form.on('submit', function(e) {
                e.preventDefault();

                $btnSubmit
                    .prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...');

                const formData = new FormData(this);

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sukses',
                                text: res.message || 'Data berhasil disimpan',
                                timer: 2500,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                            modalForm.hide();
                            reloadTable();
                        } else {
                            alert(res.message || 'Operasi gagal');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors || {};
                            let html = '';
                            $.each(errors, function(field, msgs) {
                                html +=
                                    `<li><strong>${field}:</strong> ${msgs.join(', ')}</li>`;
                            });
                            $errorList.html(html);
                            $errorBox.show();
                        } else {
                            alert('Terjadi kesalahan server (status ' + xhr.status + ')');
                        }
                    },
                    complete: function() {
                        $btnSubmit
                            .prop('disabled', false)
                            .html('<i class="ri-save-line"></i> Simpan');
                    }
                });
            });

            // --------------------------------------------------
            // Filter & Reload Table
            // --------------------------------------------------
            $('#btnFilter').on('click', reloadTable);

            function getFilterParams() {
                return {
                    search: $('#search').val().trim(),
                    jenis_kelamin: $('#jenis_kelamin').val(),
                    status_pernikahan: $('#status_pernikahan').val(),
                    status_jemaat: $('#status_jemaat').val(),
                    ajax: 1
                };
            }

            function reloadTable() {
                const params = getFilterParams();
                const queryString = $.param(params);

                $.get('{{ route('admin.jemaat.index') }}?' + queryString, function(html) {
                    $('#tabel-jemaat tbody').html(html);
                }).fail(function() {
                    alert('Gagal memuat tabel');
                });
            }

            // Load awal
            reloadTable();

            // Auto buka modal jika ada error validasi dari server (redirect back)
            @if ($errors->any())
                modalForm.show();
            @endif

            // Tambahkan di akhir $(document).ready(...)
            function showSuccessToast(message) {
                $('#toastMessage').text(message);
                const toastEl = document.getElementById('successToast');
                if (toastEl) {
                    const toast = new bootstrap.Toast(toastEl);
                    toast.show();
                } else {
                    // fallback jika toast HTML belum ada
                    alert(message);
                }
            }
        });
    </script>
@endpush
