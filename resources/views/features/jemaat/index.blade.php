@extends('layouts.admin')

@section('title', 'Data Jemaat')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="lux-container">

        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Data Jemaat</h1>
                <p class="text-muted">Kelola data anggota jemaat Gereja Filadelfia</p>
            </div>
            <button type="button" class="lux-btn lux-btn-primary btn-tambah-jemaat">
                <i class="ri-user-add-line"></i> Tambah Jemaat
            </button>
        </div>

        @if (session('success'))
            <div class="lux-card mb-4"
                style="border-left: 4px solid var(--success); background: #f0fdf4; display: flex; align-items: center; gap: 10px;">
                <i class="ri-checkbox-circle-line" style="color: var(--success); font-size: 1.5rem;"></i>
                <span style="color: #166534; font-weight: 600;">{{ session('success') }}</span>
            </div>
        @endif

        <div id="successToast"></div>

        <div class="lux-card mb-4">
            <form id="formFilter"
                style="display: grid; grid-template-columns: {{ !Auth::user()?->roles->first()?->branch_id ? '2fr 1fr 1fr 1fr 1fr auto' : '2fr 1fr 1fr 1fr auto' }}; gap: 1rem; align-items: end;">

                @if (!Auth::user()?->roles->first()?->branch_id)
                    <div>
                        <label class="lux-label">Filter Cabang</label>
                        <select id="branch_id" name="branch_id" class="lux-select">
                            <option value="">Semua Cabang</option>
                            @foreach (\App\Models\Branch::all() as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

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
                        <option value="belum menikah" {{ request('status_pernikahan') == 'belum menikah' ? 'selected' : '' }}>Belum Menikah</option>
                        <option value="menikah" {{ request('status_pernikahan') == 'menikah' ? 'selected' : '' }}>Menikah</option>
                        <option value="janda" {{ request('status_pernikahan') == 'janda' ? 'selected' : '' }}>Janda</option>
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

        <div class="lux-card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;" id="tabel-jemaat">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Jemaat
                            </th>
                            <th style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Kontak
                            </th>
                            <th style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Status
                            </th>
                            <th style="padding: 15px; text-align: center; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tabel-body">
                        @forelse($jemaats as $jemaat)
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: 0.2s;"
                                onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                <td style="padding: 15px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        @if ($jemaat->foto)
                                            <img src="{{ asset('storage/' . $jemaat->foto) }}"
                                                alt="{{ $jemaat->nama_lengkap }}"
                                                style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid white; box-shadow: var(--shadow-sm);">
                                        @else
                                            <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); 
                                                 display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.2rem;">
                                                {{ substr($jemaat->nama_lengkap, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-main);">
                                                {{ $jemaat->nama_lengkap }}</div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                NIK: {{ number_format($jemaat->NIK, 0, ',', '.') }} •
                                                {{ $jemaat->jenis_kelamin == 1 ? 'L' : 'P' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 15px;">
                                    <div style="font-size: 0.9rem; font-weight: 500;">{{ $jemaat->no_hp }}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $jemaat->email ?? '-' }}</div>
                                </td>
                                <td style="padding: 15px;">
                                    <div style="margin-bottom: 5px;">
                                        @if ($jemaat->status_jemaat)
                                            <span class="lux-badge lux-badge-success">Aktif</span>
                                        @else
                                            <span class="lux-badge lux-badge-danger">Non-Aktif</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="lux-badge"
                                            style="background: #f1f5f9; color: var(--text-muted); font-size: 0.7rem;">
                                            {{ ucfirst($jemaat->status_pernikahan ?? '-') }}
                                        </span>
                                    </div>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <div style="display: flex; gap: 8px; justify-content: center;">
                                        <button type="button" class="lux-btn lux-btn-outline lux-btn-sm btn-show-jemaat"
                                            data-id="{{ $jemaat->id }}" title="Detail">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        <button type="button" class="lux-btn lux-btn-primary lux-btn-sm btn-edit-jemaat"
                                            data-id="{{ $jemaat->id }}" title="Edit">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                        <button type="button"
                                            class="lux-btn lux-btn-outline lux-btn-sm btn-delete-jemaat"
                                            data-id="{{ $jemaat->id }}" data-nama="{{ $jemaat->nama_lengkap }}"
                                            style="border-color: var(--danger); color: var(--danger);" title="Hapus">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                                    <i class="ri-inbox-line" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p style="margin-top: 1rem;">Belum ada data jemaat</p>
                                    <button type="button" class="lux-btn lux-btn-primary btn-tambah-jemaat"
                                        style="margin-top: 1rem;">
                                        <i class="ri-user-add-line"></i> Tambah Jemaat Pertama
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($jemaats->hasPages())
                <div style="padding: 15px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                    <span class="text-muted" style="font-size: 0.85rem;">
                        Menampilkan {{ $jemaats->firstItem() }} - {{ $jemaats->lastItem() }} dari {{ $jemaats->total() }} data
                    </span>
                    <div>
                        {{ $jemaats->links('pagination::simple-bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- ====================== MODAL FORM ====================== -->
    <div class="modal fade" id="modalJemaatForm" tabindex="-1" aria-labelledby="modalJemaatLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content" style="border-radius: var(--radius-lg); border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">

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
                            <!-- IDENTITAS DIRI -->
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
                                        <input type="text" name="tempat_lahir" id="tempat_lahir" class="lux-input" required>
                                    </div>
                                    <div class="lux-form-group">
                                        <label class="lux-label">Tanggal Lahir *</label>
                                        <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="lux-input" required>
                                    </div>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Jenis Kelamin *</label>
                                    <div style="display: flex; gap: 1.5rem; margin-top: 8px;">
                                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500;">
                                            <input type="radio" name="jenis_kelamin" value="1" required> Laki-laki
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500;">
                                            <input type="radio" name="jenis_kelamin" value="0" required> Perempuan
                                        </label>
                                    </div>
                                </div>

                                <div class="lux-form-group">
                                    <label class="lux-label">Foto Profil</label>
                                    <input type="file" name="foto" id="foto" class="lux-input" accept="image/*">
                                    <div id="fotoPreview" class="mt-2" style="max-width: 180px; display: none;">
                                        <img id="imgPreview" style="width: 100%; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    </div>
                                    <p class="lux-form-help"><i class="ri-information-line"></i> Format: JPG, PNG. Max: 2MB</p>
                                </div>
                            </div>

                            <!-- KONTAK & DATA -->
                            <div class="lux-card">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">Kontak & Data</h3>
                                </div>

                                <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                                    <div class="lux-form-group">
                                        <label class="lux-label">Email</label>
                                        <input type="email" name="email" id="email" class="lux-input" placeholder="email@contoh.com">
                                    </div>
                                    <div class="lux-form-group">
                                        <label class="lux-label">No. HP *</label>
                                        <input type="tel" name="no_hp" id="no_hp" class="lux-input" required placeholder="08123456789">
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
                                        <input type="text" name="pekerjaan" id="pekerjaan" class="lux-input" placeholder="Contoh: Wiraswasta">
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

                                <div class="lux-form-group mt-4 p-3 bg-light rounded">
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600;">
                                        <input type="hidden" name="status_jemaat" value="0">
                                        <input type="checkbox" name="status_jemaat" id="status_jemaat" value="1" checked>
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

    <div class="modal fade" id="modalShowJemaat" tabindex="-1" aria-labelledby="modalShowLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content"
                style="border-radius: var(--radius-lg); border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">

                <div class="modal-header lux-card-header"
                    style="border-bottom: 1px solid #e2e8f0; justify-content: space-between;">
                    <h5 class="modal-title" id="modalShowLabel">Detail Jemaat</h5>
                    <div>
                        <button type="button" class="lux-btn lux-btn-primary btn-edit-from-show" style="display:none;"
                            id="btnEditFromShow">
                            <i class="ri-pencil-line"></i> Edit
                        </button>
                        <button type="button" class="lux-btn lux-btn-outline btn-delete-from-show"
                            style="display:none; border-color: var(--danger); color: var(--danger);"
                            id="btnDeleteFromShow">
                            <i class="ri-delete-bin-line"></i> Hapus
                        </button>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body lux-container" id="showJemaatContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-3 text-muted">Memuat data...</p>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

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

            const modalForm = new bootstrap.Modal('#modalJemaatForm');
            const modalShow = new bootstrap.Modal('#modalShowJemaat');
            const modalDelete = new bootstrap.Modal('#modalConfirmDelete');

            const $form = $('#formJemaat');
            const $btnSubmit = $('#btnSubmitJemaat');
            const $errorBox = $('#formErrors');
            const $errorList = $('#errorList');

            let currentJemaatId = null;

            $('.btn-tambah-jemaat').on('click', function() {
                resetForm('create');
                $('#modalJemaatLabel').text('Tambah Jemaat Baru');
                modalForm.show();
            });

            $('#tabel-jemaat tbody').on('click', '.btn-edit-jemaat', function() {
                const id = $(this).data('id');
                loadDataForEdit(id);
            });

            $('#tabel-jemaat tbody').on('click', '.btn-show-jemaat', function() {
                const id = $(this).data('id');
                loadShowJemaat(id);
            });

            $('#btnEditFromShow').on('click', function() {
                if (currentJemaatId) {
                    modalShow.hide();
                    setTimeout(() => loadDataForEdit(currentJemaatId), 300);
                }
            });

            $('#btnDeleteFromShow').on('click', function() {
                if (currentJemaatId) {
                    modalShow.hide();
                    setTimeout(() => {
                        const nama = $('#deleteNamaJemaat').data('nama') || 'Jemaat ini';
                        $('#deleteNamaJemaat').text(nama).data('nama', nama);
                        $('#btnConfirmDelete').data('id', currentJemaatId);
                        modalDelete.show();
                    }, 300);
                }
            });

            $('#tabel-jemaat tbody').on('click', '.btn-delete-jemaat', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const nama = $(this).data('nama') || 'Jemaat ini';

                $('#deleteNamaJemaat').text(nama).data('nama', nama);
                $('#btnConfirmDelete').data('id', id);

                modalDelete.show();
            });

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

            function loadShowJemaat(id) {
                currentJemaatId = id;
                $('#showJemaatContent').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-3 text-muted">Memuat data...</p>
                    </div>
                `);
                $('#btnEditFromShow').hide();
                $('#btnDeleteFromShow').hide();
                modalShow.show();

                $.ajax({
                    url: '{{ route('admin.jemaat.show', ':id') }}'.replace(':id', id) + '?ajax=1',
                    type: 'GET',
                    success: function(data) {
                        const j = data;
                        const usia = j.tanggal_lahir ? new Date().getFullYear() - new Date(j
                            .tanggal_lahir).getFullYear() : '-';
                        const fotoHtml = j.foto ?
                            `<img src="{{ asset('storage') }}/${j.foto}" class="img-fluid rounded-circle" style="max-width: 180px; border: 5px solid white; box-shadow: var(--shadow-lg);">` :
                            `<div style="width: 180px; height: 180px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); 
                               display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 4rem; margin: 0 auto; border: 5px solid white; box-shadow: var(--shadow-lg);">
                               ${j.nama_lengkap ? j.nama_lengkap.charAt(0).toUpperCase() : '?'}</div>`;

                        const statusBadge = j.status_jemaat ?
                            '<span class="lux-badge lux-badge-success" style="border: 3px solid white; font-size: 0.75rem; padding: 5px 12px;">Aktif</span>' :
                            '<span class="lux-badge lux-badge-danger" style="border: 3px solid white; font-size: 0.75rem; padding: 5px 12px;">Non-Aktif</span>';

                        const noHpWa = j.no_hp ? j.no_hp.replace(/^0/, '62') : '';

                        $('#showJemaatContent').html(`
                            <div class="text-center mb-4">
                                ${fotoHtml}
                                <h2 class="mt-3 mb-1" style="font-size: 1.5rem; font-weight: 700;">${j.nama_lengkap || '-'}</h2>
                                <p class="text-muted">NIK: ${j.NIK ? Number(j.NIK).toLocaleString('id-ID') : '-'}</p>
                                <div style="margin-top: 10px;">${statusBadge}</div>
                            </div>
                            
                            <div class="lux-grid lux-grid-2 mb-4" style="gap: 1rem; padding: 1rem; background: #f8fafc; border-radius: var(--radius);">
                                <div>
                                    <p class="lux-label mb-1">Jenis Kelamin</p>
                                    <p class="fw-bold">${j.jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan'}</p>
                                </div>
                                <div>
                                    <p class="lux-label mb-1">Usia</p>
                                    <p class="fw-bold">${usia} tahun</p>
                                </div>
                                <div>
                                    <p class="lux-label mb-1">Status Pernikahan</p>
                                    <p class="fw-bold">${j.status_pernikahan ? j.status_pernikahan.charAt(0).toUpperCase() + j.status_pernikahan.slice(1) : '-'}</p>
                                </div>
                                <div>
                                    <p class="lux-label mb-1">Bergabung</p>
                                    <p class="fw-bold">${j.tanggal_bergabung ? new Date(j.tanggal_bergabung).toLocaleDateString('id-ID', {month:'short', year:'numeric'}) : '-'}</p>
                                </div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem;">
                                ${j.no_hp ? `<a href="https://wa.me/${noHpWa}" target="_blank" class="lux-btn lux-btn-outline" style="border-color: #25D366; color: #25D366; justify-content: center;">
                                        <i class="ri-whatsapp-line"></i> Chat WhatsApp
                                    </a>` : ''}
                                ${j.email ? `<a href="mailto:${j.email}" class="lux-btn lux-btn-outline" style="justify-content: center;">
                                        <i class="ri-mail-line"></i> Kirim Email
                                    </a>` : ''}
                            </div>

                            <hr>

                            <h6 class="text-muted mb-3">Informasi Pribadi</h6>
                            <div class="lux-grid lux-grid-2 mb-4">
                                <div>
                                    <p class="lux-label mb-1">Tempat, Tanggal Lahir</p>
                                    <p class="fw-bold">${j.tempat_lahir || '-'}, ${j.tanggal_lahir ? new Date(j.tanggal_lahir).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}) : '-'}</p>
                                </div>
                                <div>
                                    <p class="lux-label mb-1">Usia</p>
                                    <p class="fw-bold">${usia} tahun</p>
                                </div>
                                <div>
                                    <p class="lux-label mb-1">Jenis Kelamin</p>
                                    <p class="fw-bold">${j.jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan'}</p>
                                </div>
                                <div>
                                    <p class="lux-label mb-1">Status Pernikahan</p>
                                    <p class="fw-bold">${j.status_pernikahan ? j.status_pernikahan.charAt(0).toUpperCase() + j.status_pernikahan.slice(1) : '-'}</p>
                                </div>
                            </div>

                            <hr>

                            <h6 class="text-muted mb-3">Informasi Kontak</h6>
                            <div class="lux-grid lux-grid-2 mb-4">
                                <div>
                                    <p class="lux-label mb-1">Nomor Telepon</p>
                                    <p class="fw-bold">${j.no_hp || '-'}</p>
                                </div>
                                <div>
                                    <p class="lux-label mb-1">Email</p>
                                    <p class="fw-bold">${j.email || '-'}</p>
                                </div>
                                <div style="grid-column: span 2;">
                                    <p class="lux-label mb-1">Alamat Lengkap</p>
                                    <p class="fw-bold" style="line-height: 1.6;">${j.alamat || '-'}</p>
                                </div>
                            </div>

                            <hr>

                            <h6 class="text-muted mb-3">Pendidikan & Pekerjaan</h6>
                            <div class="lux-grid lux-grid-2 mb-4">
                                <div>
                                    <p class="lux-label mb-1">Pendidikan Terakhir</p>
                                    <p class="fw-bold">${j.pendidikan || '-'}</p>
                                </div>
                                <div>
                                    <p class="lux-label mb-1">Pekerjaan</p>
                                    <p class="fw-bold">${j.pekerjaan || '-'}</p>
                                </div>
                            </div>

                            <hr>

                            <h6 class="text-muted mb-3">Data Gerejawi</h6>
                            <div class="lux-grid lux-grid-2">
                                <div>
                                    <p class="lux-label mb-1">Tanggal Bergabung</p>
                                    <p class="fw-bold">${j.tanggal_bergabung ? new Date(j.tanggal_bergabung).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}) : '-'}</p>
                                </div>
                                <div>
                                    <p class="lux-label mb-1">Status Jemaat</p>
                                    <p class="fw-bold">${j.status_jemaat ? '<span class="lux-badge lux-badge-success">Aktif</span>' : '<span class="lux-badge lux-badge-danger">Non-Aktif</span>'}</p>
                                </div>
                            </div>
                        `);

                        $('#btnEditFromShow').show();
                        $('#btnDeleteFromShow').show();
                        $('#deleteNamaJemaat').data('nama', j.nama_lengkap);
                    },
                    error: function() {
                        $('#showJemaatContent').html(
                            '<p class="text-danger text-center">Gagal memuat data jemaat</p>');
                    }
                });
            }

            function loadDataForEdit(id) {
                $.ajax({
                    url: '{{ route('admin.jemaat.edit', ':id') }}'.replace(':id', id) + '?ajax=1',
                    type: 'GET',
                    success: function(data) {
                        resetForm('edit');

                        $form.attr('action', '{{ route('admin.jemaat.update', ':id') }}'.replace(':id', id));
                        $('#formMethod').val('PUT');
                        $('#jemaatId').val(id);

                        $('#nama_lengkap').val(data.nama_lengkap || '');
                        $('#NIK').val(data.NIK || '').prop('readonly', false);   // ← Tidak readonly
                        $('#tempat_lahir').val(data.tempat_lahir || '');
                        $('#tanggal_lahir').val(data.tanggal_lahir ? data.tanggal_lahir.split('T')[0] : '');
                        $('#alamat').val(data.alamat || '');
                        $('#no_hp').val(data.no_hp || '');
                        $('#email').val(data.email || '');
                        $('#pekerjaan').val(data.pekerjaan || '');
                        $('#pendidikan').val(data.pendidikan || '');
                        $('#status_pernikahan').val(data.status_pernikahan || '');   // ← Fix status pernikahan

                        if (data.jenis_kelamin !== null) {
                            $('input[name="jenis_kelamin"][value="' + data.jenis_kelamin + '"]').prop('checked', true);
                        }

                        const isActive = data.status_jemaat == 1 || data.status_jemaat === true;
                        $('#status_jemaat').prop('checked', isActive);

                        if (data.foto && data.foto.trim() !== '') {
                            $('#imgPreview').attr('src', '{{ asset('storage') }}/' + data.foto);
                            $('#fotoPreview').show();
                        } else {
                            $('#fotoPreview').hide();
                        }

                        $('#modalJemaatLabel').text('Edit Data Jemaat');
                        modalForm.show();
                    },
                    error: function() {
                        alert('Gagal memuat data untuk diedit');
                    }
                });
            }

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
                $('input[name="jenis_kelamin"]').prop('checked', false);
                $('#status_jemaat').prop('checked', true);
                $('#status_pernikahan').val('');
            }

            $form.on('submit', function(e) {
                e.preventDefault();

                $btnSubmit.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...');

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
                            if (modalShow._isShown) {
                                loadShowJemaat(currentJemaatId);
                            } else {
                                reloadTable();
                            }
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
                        $btnSubmit.prop('disabled', false).html(
                            '<i class="ri-save-line"></i> Simpan');
                    }
                });
            });

            $('#btnFilter').on('click', reloadTable);

            function getFilterParams() {
                const params = {
                    search: $('#search').val().trim(),
                    jenis_kelamin: $('#jenis_kelamin').val(),
                    status_pernikahan: $('#status_pernikahan').val(),
                    status_jemaat: $('#status_jemaat').val(),
                    ajax: 1
                };
                @if (!Auth::user()?->roles->first()?->branch_id)
                    if ($('#branch_id').val()) params.branch_id = $('#branch_id').val();
                @endif
                return params;
            }

            function reloadTable() {
                const params = getFilterParams();
                const queryString = $.param(params);

                $.get('{{ route('admin.jemaat.index') }}?' + queryString, function(html) {
                    $('#tabel-body').html(html);
                });
            }

            reloadTable();

            @if ($errors->any())
                modalForm.show();
            @endif

            $('input[type="number"], input[type="tel"]').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
@endpush