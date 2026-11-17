@extends('layouts.admin')

@section('title', 'Data Jemaat')

@section('content')
    @php
        if (!isset($jemaat)) {
            $jemaat = null;
        }
    @endphp
    <div class="page-wrapper">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Data Jemaat</h1>
                <p class="text-muted-custom mb-0">Kelola data anggota jemaat Gereja Filadelfia</p>
            </div>
            <button type="button" class="btn btn-custom btn-tambah-jemaat" data-bs-toggle="modal"
                data-bs-target="#modalJemaatForm">
                <i class="ri-user-add-line me-1"></i> Tambah Jemaat
            </button>
        </div>

        @if (session('success'))
            <div class="alert-custom success mb-4" data-auto-close>
                <i class="ri-checkbox-circle-fill"></i>
                <span>{{ session('success') }}</span>
                <button class="alert-close">&times;</button>
            </div>
        @endif

        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <form id="formFilter">
                    <div class="row g-3">
                        @if ($isSuperAdmin)
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label small fw-semibold mb-2">Filter Cabang</label>
                                <select id="filter_branch_id" name="branch_id" class="form-select form-select-custom">
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

                        <div class="col-md-6 col-lg-3">
                            <label class="form-label small fw-semibold mb-2">Cari Jemaat</label>
                            <div class="position-relative">
                                <i
                                    class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted-custom"></i>
                                <input type="text" id="search" name="search"
                                    class="form-control form-control-custom ps-5" value="{{ request('search') }}"
                                    placeholder="Nama, Email, atau No HP...">
                            </div>
                        </div>

                        <div class="col-6 col-lg-2">
                            <label class="form-label small fw-semibold mb-2">Jenis Kelamin</label>
                            <select id="jenis_kelamin" name="jenis_kelamin" class="form-select form-select-custom">
                                <option value="">Semua</option>
                                <option value="1" {{ request('jenis_kelamin') == '1' ? 'selected' : '' }}>Laki-laki
                                </option>
                                <option value="0" {{ request('jenis_kelamin') == '0' ? 'selected' : '' }}>Perempuan
                                </option>
                            </select>
                        </div>

                        <div class="col-6 col-lg-2">
                            <label class="form-label small fw-semibold mb-2">Status Jemaat</label>
                            <select id="status_jemaat" name="status_jemaat" class="form-select form-select-custom">
                                <option value="">Semua</option>
                                <option value="1" {{ request('status_jemaat') == '1' ? 'selected' : '' }}>Aktif
                                </option>
                                <option value="0" {{ request('status_jemaat') == '0' ? 'selected' : '' }}>Non-Aktif
                                </option>
                            </select>
                        </div>

                        <div class="col-6 col-lg-{{ $isSuperAdmin ? '3' : '4' }} d-flex align-items-end">
                            <button type="button" id="btnFilter" class="btn btn-custom w-100">
                                <i class="ri-filter-line me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" id="tabel-jemaat">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Jemaat</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Kontak</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Status</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabel-body">
                        @forelse($jemaats as $jemaat)
                            <tr class="border-bottom border-custom" data-id="{{ $jemaat->id }}">
                                <td class="py-3 px-4">
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($jemaat->foto)
                                            <img src="{{ asset('storage/' . $jemaat->foto) }}"
                                                alt="{{ $jemaat->nama_lengkap }}" class="avatar-table">
                                        @else
                                            <div class="avatar-table avatar-placeholder">
                                                {{ substr($jemaat->nama_lengkap, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $jemaat->nama_lengkap }}</div>
                                            <div class="text-muted-custom small">
                                                {{ $jemaat->jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="fw-medium">{{ $jemaat->no_hp }}</div>
                                    <div class="text-muted-custom small">{{ $jemaat->email ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex flex-column gap-2">
                                        @if ($jemaat->status_jemaat)
                                            <span class="badge badge-status-success">
                                                <i class="ri-check-line me-1"></i> Aktif
                                            </span>
                                        @else
                                            <span class="badge badge-status-danger">
                                                <i class="ri-close-line me-1"></i> Non-Aktif
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn-action btn-action-info btn-show-jemaat"
                                            data-id="{{ $jemaat->id }}" title="Detail">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        <button type="button" class="btn-action btn-action-primary btn-edit-jemaat"
                                            data-id="{{ $jemaat->id }}" title="Edit">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                        <button type="button" class="btn-action btn-action-danger btn-delete-jemaat"
                                            data-id="{{ $jemaat->id }}" data-nama="{{ $jemaat->nama_lengkap }}"
                                            title="Hapus">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-5">
                                    <div class="text-center">
                                        <div class="empty-state-icon mb-3">
                                            <i class="ri-user-3-line"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-2">Belum ada data jemaat</h6>
                                        <p class="text-muted-custom small mb-3">Mulai tambahkan data jemaat pertama Anda
                                        </p>
                                        <button type="button" class="btn btn-custom btn-tambah-jemaat">
                                            <i class="ri-user-add-line me-1"></i> Tambah Jemaat Pertama
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($jemaats->hasPages())
                <div
                    class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 p-4 border-top border-custom">
                    <span class="text-muted-custom small">
                        Menampilkan {{ $jemaats->firstItem() }} - {{ $jemaats->lastItem() }} dari {{ $jemaats->total() }}
                        data
                    </span>
                    <div>
                        {{ $jemaats->links('pagination::simple-bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>

    </div>

    <div class="modal fade" id="modalJemaatForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-height: 90vh;">
            <div class="modal-content modal-content-custom">

                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold" id="modalJemaatLabel">Tambah Jemaat Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formJemaat" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="jemaatId" value="">

                    <div class="modal-body p-4" style="overflow-y: auto; max-height: 70vh;">

                        <div class="alert alert-danger alert-custom-error d-none" id="formErrors">
                            <i class="ri-error-warning-line me-2"></i>
                            <ul class="mb-0 small" id="errorList"></ul>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="card card-custom h-100">
                                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                        <h6 class="mb-0 fw-semibold"><i
                                                class="ri-user-line me-2 text-primary"></i>Identitas Diri</h6>
                                    </div>
                                    <div class="card-body p-4">

                                        @if ($isSuperAdmin)
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted-custom">Cabang <span
                                                        class="text-danger">*</span></label>
                                                <select name="branch_id" id="branch_id"
                                                    class="form-select form-select-custom" required>
                                                    <option value="">Pilih Cabang...</option>
                                                    @foreach ($branches as $branch)
                                                        <option value="{{ $branch->id }}"
                                                            {{ (old('branch_id') ?? ($jemaat?->branch_id ?? ($defaultBranchId ?? ''))) == $branch->id ? 'selected' : '' }}>
                                                            {{ $branch->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @else
                                            <input type="hidden" name="branch_id" value="{{ $defaultBranchId }}">
                                        @endif

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Nama Lengkap
                                                <span class="text-danger">*</span></label>
                                            <input type="text" name="nama_lengkap" id="nama_lengkap"
                                                class="form-control form-control-custom" required placeholder="Sesuai KTP"
                                                value="{{ old('nama_lengkap', $jemaat?->nama_lengkap ?? '') }}">
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted-custom">Tempat Lahir
                                                    <span class="text-danger">*</span></label>
                                                <input type="text" name="tempat_lahir" id="tempat_lahir"
                                                    class="form-control form-control-custom" required
                                                    value="{{ old('tempat_lahir', $jemaat?->tempat_lahir ?? '') }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted-custom">Tanggal Lahir
                                                    <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                                                    class="form-control form-control-custom" required
                                                    value="{{ old('tanggal_lahir', $jemaat?->tanggal_lahir ?? '') }}">
                                            </div>
                                        </div>

                                        <div class="mb-3 mt-4">
                                            <label
                                                class="form-label small fw-semibold text-muted-custom d-block mb-2">Jenis
                                                Kelamin <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-4">
                                                <label class="form-check-radio">
                                                    <input type="radio" name="jenis_kelamin" value="1"
                                                        class="form-check-input-custom"
                                                        {{ old('jenis_kelamin', $jemaat?->jenis_kelamin ?? '') == 1 ? 'checked' : '' }}>
                                                    <span class="form-check-label-custom">Laki-laki</span>
                                                </label>
                                                <label class="form-check-radio">
                                                    <input type="radio" name="jenis_kelamin" value="0"
                                                        class="form-check-input-custom"
                                                        {{ old('jenis_kelamin', $jemaat?->jenis_kelamin ?? '') == 0 ? 'checked' : '' }}>
                                                    <span class="form-check-label-custom">Perempuan</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mb-0">
                                            <label class="form-label small fw-semibold text-muted-custom">Foto
                                                Profil</label>
                                            <input type="file" name="foto" id="foto"
                                                class="form-control form-control-custom" accept="image/*">
                                            <div id="fotoPreview"
                                                class="mt-3 {{ $jemaat?->foto ?? false ? '' : 'd-none' }}">
                                                <img id="imgPreview" class="img-preview"
                                                    src="{{ $jemaat?->foto ? asset('storage/' . $jemaat->foto) : '' }}">
                                            </div>
                                            <small class="text-muted-custom"><i
                                                    class="ri-information-line me-1"></i>Format: JPG, PNG. Max: 2MB</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card card-custom h-100">
                                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                        <h6 class="mb-0 fw-semibold"><i
                                                class="ri-contact-line me-2 text-primary"></i>Kontak & Data</h6>
                                    </div>
                                    <div class="card-body p-4">

                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted-custom">Email</label>
                                                <input type="email" name="email" id="email"
                                                    class="form-control form-control-custom"
                                                    placeholder="email@contoh.com"
                                                    value="{{ old('email', $jemaat?->email ?? '') }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted-custom">No. HP <span
                                                        class="text-danger">*</span></label>
                                                <input type="tel" name="no_hp" id="no_hp"
                                                    class="form-control form-control-custom" required
                                                    placeholder="08123456789"
                                                    value="{{ old('no_hp', $jemaat?->no_hp ?? '') }}">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Alamat Lengkap
                                                <span class="text-danger">*</span></label>
                                            <textarea name="alamat" id="alamat" class="form-control form-control-custom textarea-custom" rows="3"
                                                required placeholder="Nama jalan, RT/RW, Kelurahan, Kecamatan">{{ old('alamat', $jemaat?->alamat ?? '') }}</textarea>
                                        </div>

                                        <hr class="border-custom my-4">

                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted-custom">Pendidikan
                                                    Terakhir</label>
                                                <select name="pendidikan" id="pendidikan"
                                                    class="form-select form-select-custom">
                                                    <option value="">Pilih...</option>
                                                    <option value="SD"
                                                        {{ old('pendidikan', $jemaat?->pendidikan ?? '') == 'SD' ? 'selected' : '' }}>
                                                        SD / Sederajat</option>
                                                    <option value="SMP"
                                                        {{ old('pendidikan', $jemaat?->pendidikan ?? '') == 'SMP' ? 'selected' : '' }}>
                                                        SMP / Sederajat</option>
                                                    <option value="SMA"
                                                        {{ old('pendidikan', $jemaat?->pendidikan ?? '') == 'SMA' ? 'selected' : '' }}>
                                                        SMA / SMK / Sederajat</option>
                                                    <option value="D3"
                                                        {{ old('pendidikan', $jemaat?->pendidikan ?? '') == 'D3' ? 'selected' : '' }}>
                                                        Diploma (D3)</option>
                                                    <option value="S1"
                                                        {{ old('pendidikan', $jemaat?->pendidikan ?? '') == 'S1' ? 'selected' : '' }}>
                                                        Sarjana (S1)</option>
                                                    <option value="S2"
                                                        {{ old('pendidikan', $jemaat?->pendidikan ?? '') == 'S2' ? 'selected' : '' }}>
                                                        Magister (S2)</option>
                                                    <option value="S3"
                                                        {{ old('pendidikan', $jemaat?->pendidikan ?? '') == 'S3' ? 'selected' : '' }}>
                                                        Doktor (S3)</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label
                                                    class="form-label small fw-semibold text-muted-custom">Pekerjaan</label>
                                                <input type="text" name="pekerjaan" id="pekerjaan"
                                                    class="form-control form-control-custom"
                                                    placeholder="Contoh: Wiraswasta"
                                                    value="{{ old('pekerjaan', $jemaat?->pekerjaan ?? '') }}">
                                            </div>
                                        </div>

                                        <div class="form-check form-switch-custom mt-4 p-3 bg-body-tertiary rounded">
                                            <input type="hidden" name="status_jemaat" value="0">
                                            <input type="checkbox" name="status_jemaat" id="status_jemaat"
                                                class="form-check-input-custom" value="1"
                                                {{ old('status_jemaat', $jemaat?->status_jemaat ?? true) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label-custom fw-semibold" for="status_jemaat">Status
                                                Jemaat Aktif</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSubmitJemaat">
                            <i class="ri-save-line me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalShowJemaat" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-custom">

                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold" id="modalShowLabel">Detail Jemaat</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-custom btn-sm btn-edit-from-show d-none"
                            id="btnEditFromShow">
                            <i class="ri-pencil-line me-1"></i> Edit
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete-from-show d-none"
                            id="btnDeleteFromShow">
                            <i class="ri-delete-bin-line me-1"></i> Hapus
                        </button>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4" id="showJemaatContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted-custom mb-0">Memuat data...</p>
                    </div>
                </div>

                <div class="modal-footer border-top border-custom py-3 px-4">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="icon-delete mb-3">
                        <i class="ri-delete-bin-6-line"></i>
                    </div>
                    <h6 class="fw-semibold mb-2">Yakin ingin menghapus data jemaat ini?</h6>
                    <p class="text-muted-custom small mb-3">Aksi ini tidak dapat dibatalkan.</p>
                    <p id="deleteNamaJemaat" class="fw-bold text-primary mb-0"></p>
                </div>
                <div class="modal-footer border-top border-custom py-3 px-4 justify-content-center">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDelete">
                        <i class="ri-delete-bin-line me-1"></i> Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
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

        .avatar-table {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--bg-surface);
            box-shadow: var(--shadow-sm);
        }

        .avatar-placeholder {
            background: var(--accent-blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .badge-status-success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.25);
            padding: 0.4em 0.75em;
            font-weight: 500;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
        }

        .badge-status-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.25);
            padding: 0.4em 0.75em;
            font-weight: 500;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
        }

        .badge-secondary-custom {
            background: var(--bg-body);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            padding: 0.35em 0.65em;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-primary);
            transition: var(--transition);
            font-size: 1rem;
            padding: 0;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .btn-action-info {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .btn-action-info:hover {
            background: var(--accent-blue);
            color: white;
        }

        .btn-action-primary {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .btn-action-primary:hover {
            background: var(--accent-blue);
            color: white;
        }

        .btn-action-danger {
            border-color: #ef4444;
            color: #ef4444;
        }

        .btn-action-danger:hover {
            background: #ef4444;
            color: white;
        }

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

        .form-check-radio {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .form-check-input-custom {
            width: 18px;
            height: 18px;
            border: 2px solid var(--border-color);
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
        }

        .form-check-input-custom:checked {
            background-color: var(--accent-blue);
            border-color: var(--accent-blue);
        }

        .form-check-input-custom[type="radio"] {
            border-radius: 50%;
        }

        .form-check-label-custom {
            font-size: 0.9rem;
            color: var(--text-primary);
            cursor: pointer;
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

        .textarea-custom {
            resize: vertical;
            min-height: 80px;
            line-height: 1.5;
        }

        .modal-content-custom {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            background: var(--bg-surface);
            padding: 1rem 1.5rem;
        }

        .modal-body {
            background: var(--bg-body);
        }

        .modal-footer {
            background: var(--bg-surface);
        }

        .img-preview {
            width: 100%;
            max-width: 180px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .alert-custom-error {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            color: #ef4444;
            padding: 0.75rem 1rem;
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
        }

        .alert-custom-error ul {
            padding-left: 1.25rem;
            margin: 0;
        }

        .icon-delete {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 2rem;
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--bg-body);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 2.5rem;
            opacity: 0.5;
        }

        .pagination .page-link {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.5rem 0.875rem;
            margin: 0 2px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .pagination .page-link:hover,
        .pagination .page-item.active .page-link {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        .pagination .page-item.disabled .page-link {
            background: var(--bg-body);
            color: var(--text-secondary);
        }

        [data-theme="dark"] .bg-body-tertiary {
            background-color: var(--bg-sidebar) !important;
        }

        [data-theme="dark"] .avatar-table {
            border-color: var(--bg-sidebar);
        }

        @media (max-width: 768px) {
            .avatar-table {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .btn-action {
                width: 28px;
                height: 28px;
                font-size: 0.9rem;
            }

            .modal-dialog-scrollable .modal-content {
                max-height: calc(100vh - 2rem);
            }
        }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            const modalForm = new bootstrap.Modal(document.getElementById('modalJemaatForm'));
            const modalShow = new bootstrap.Modal(document.getElementById('modalShowJemaat'));
            const modalDelete = new bootstrap.Modal(document.getElementById('modalConfirmDelete'));

            const form = document.getElementById('formJemaat');
            const formMethod = document.getElementById('formMethod');
            const jemaatId = document.getElementById('jemaatId');
            const errorAlert = document.getElementById('formErrors');
            const errorList = document.getElementById('errorList');

            document.getElementById('foto')?.addEventListener('change', function(e) {
                const preview = document.getElementById('fotoPreview');
                const img = document.getElementById('imgPreview');
                const file = e.target.files[0];

                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        img.src = event.target.result;
                        preview.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.classList.add('d-none');
                }
            });

            document.getElementById('no_hp')?.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9\+\-\s\(\)]/g, '').slice(0, 20);
            });

            document.getElementById('btnFilter')?.addEventListener('click', function() {
                const form = document.getElementById('formFilter');
                const params = new URLSearchParams(new FormData(form));
                const url = `${window.location.pathname}?${params.toString()}`;

                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memuat...';

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        document.getElementById('tabel-body').innerHTML =
                            doc.getElementById('tabel-body')?.innerHTML ||
                            '<tr><td colspan="4" class="text-center py-4">Tidak ada data</td></tr>';

                        const newPagination = doc.querySelector('.pagination');
                        const oldPagination = document.querySelector(
                            '.card-custom > div:last-child .pagination');
                        if (newPagination && oldPagination) {
                            oldPagination.replaceWith(newPagination);
                        }
                    })
                    .catch(err => {
                        console.error('Filter error:', err);
                        alert('Gagal memuat data. Silakan refresh halaman.');
                    })
                    .finally(() => {
                        this.disabled = false;
                        this.innerHTML = '<i class="ri-filter-line me-1"></i> Filter';
                    });
            });

            document.querySelector('#formFilter button[type="reset"]')?.addEventListener('click', () => {
                window.location = window.location.pathname;
            });

            document.querySelectorAll('.btn-tambah-jemaat').forEach(btn => {
                btn.addEventListener('click', () => {

                    form.reset();

                    formMethod.value = 'POST';
                    jemaatId.value = '';

                    document.getElementById('nama_lengkap').value = '';
                    document.getElementById('tempat_lahir').value = '';
                    document.getElementById('tanggal_lahir').value = '';
                    document.getElementById('email').value = '';
                    document.getElementById('no_hp').value = '';
                    document.getElementById('alamat').value = '';
                    document.getElementById('pendidikan').value = '';
                    document.getElementById('pekerjaan').value = '';

                    document.querySelectorAll('input[name="jenis_kelamin"]')
                        .forEach(r => r.checked = false);

                    document.getElementById('status_jemaat').checked = true;

                    document.getElementById('foto').value = '';
                    document.getElementById('imgPreview').src = '';
                    document.getElementById('fotoPreview')
                        ?.classList.add('d-none');

                    const branchInput = document.getElementById('branch_id');
                    if (branchInput) {
                        branchInput.value = '';
                    }

                    document.getElementById('modalJemaatLabel')
                        .textContent = 'Tambah Jemaat Baru';

                    errorAlert?.classList.add('d-none');

                    modalForm.show();
                });
            });

            document.querySelectorAll('.btn-edit-jemaat').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const id = this.dataset.id;
                    errorAlert?.classList.add('d-none');

                    try {
                        const res = await fetch(`/admin/jemaat/${id}/api`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        if (!res.ok) throw new Error('Gagal mengambil data');

                        const data = await res.json();

                        formMethod.value = 'PUT';
                        jemaatId.value = data.id;

                        document.getElementById('nama_lengkap').value = data.nama_lengkap || '';
                        document.getElementById('tempat_lahir').value = data.tempat_lahir || '';
                        document.getElementById('tanggal_lahir').value =
                            data.tanggal_lahir ?
                            data.tanggal_lahir.split('T')[0] :
                            '';
                        document.getElementById('email').value = data.email || '';
                        document.getElementById('no_hp').value = data.no_hp || '';
                        document.getElementById('alamat').value = data.alamat || '';
                        document.getElementById('pendidikan').value = data.pendidikan || '';
                        document.getElementById('pekerjaan').value = data.pekerjaan || '';
                        if (document.getElementById('branch_id')) {
                            document.getElementById('branch_id').value = data.branch_id || '';
                        }

                        document.querySelectorAll('input[name="jenis_kelamin"]').forEach(
                            radio => {
                                radio.checked = (radio.value == data.jenis_kelamin);
                            });

                        document.getElementById('status_jemaat').checked = data.status_jemaat ==
                            1;

                        const preview = document.getElementById('fotoPreview');
                        const img = document.getElementById('imgPreview');
                        if (data.foto) {
                            img.src = `/storage/${data.foto}`;
                            preview?.classList.remove('d-none');
                        } else {
                            preview?.classList.add('d-none');
                        }

                        document.getElementById('modalJemaatLabel').textContent = 'Edit Jemaat';
                        modalForm.show();

                    } catch (err) {
                        console.error('Edit error:', err);
                        alert('Gagal memuat data jemaat. Silakan coba lagi.');
                    }
                });
            });

            document.querySelectorAll('.btn-show-jemaat').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const id = this.dataset.id;
                    const content = document.getElementById('showJemaatContent');

                    modalShow.show();
                    content.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="text-muted">Memuat data...</p>
                </div>
            `;

                    try {
                        const res = await fetch(`/admin/jemaat/${id}/api`);
                        if (!res.ok) throw new Error('Gagal mengambil data');

                        const data = await res.json();

                        const canEdit =
                            {{ Auth::user()?->can('jemaat.edit') ? 'true' : 'false' }};
                        const canDelete =
                            {{ Auth::user()?->can('jemaat.delete') ? 'true' : 'false' }};

                        document.getElementById('btnEditFromShow').classList.toggle('d-none', !
                            canEdit);
                        document.getElementById('btnDeleteFromShow').classList.toggle('d-none',
                            !canDelete);

                        if (canEdit) {
                            document.getElementById('btnEditFromShow').onclick = () => {
                                modalShow.hide();
                                document.querySelector(`.btn-edit-jemaat[data-id="${id}"]`)
                                    ?.click();
                            };
                        }
                        if (canDelete) {
                            document.getElementById('btnDeleteFromShow').onclick = () => {
                                modalShow.hide();
                                document.querySelector(
                                    `.btn-delete-jemaat[data-id="${id}"]`)?.click();
                            };
                        }

                        content.innerHTML = `
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            ${data.foto 
                                ? `<img src="/storage/${data.foto}" class="img-fluid rounded mb-3" style="max-height:200px">`
                                : `<div class="avatar-placeholder d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:120px;height:120px;background:var(--accent-blue);color:white;font-size:2rem;font-weight:600">${(data.nama_lengkap?.[0] || '?').toUpperCase()}</div>`
                            }
                            <h5 class="fw-bold mb-1">${data.nama_lengkap}</h5>
                            <span class="badge ${data.status_jemaat ? 'badge-status-success' : 'badge-status-danger'}">
                                ${data.status_jemaat ? 'Aktif' : 'Non-Aktif'}
                            </span>
                            ${data.branch ? `<p class="text-muted-custom small mt-2">${data.branch.name}</p>` : ''}
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-6"><strong>Tempat, Tgl Lahir</strong></div>
                                <div class="col-6">${data.tempat_lahir || '-'}, ${data.tanggal_lahir ? new Date(data.tanggal_lahir).toLocaleDateString('id-ID') : '-'}</div>
                                
                                <div class="col-6"><strong>Jenis Kelamin</strong></div>
                                <div class="col-6">${data.jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan'}</div>
                                
                                <div class="col-6"><strong>No. HP</strong></div>
                                <div class="col-6">${data.no_hp || '-'}</div>
                                
                                <div class="col-6"><strong>Email</strong></div>
                                <div class="col-6">${data.email || '-'}</div>
                                
                                <div class="col-6"><strong>Pendidikan</strong></div>
                                <div class="col-6">${data.pendidikan || '-'}</div>
                                
                                <div class="col-6"><strong>Pekerjaan</strong></div>
                                <div class="col-6">${data.pekerjaan || '-'}</div>
                                
                                <div class="col-12"><strong>Alamat</strong></div>
                                <div class="col-12">${data.alamat || '-'}</div>
                            </div>
                        </div>
                    </div>
                `;

                    } catch (err) {
                        console.error('Show error:', err);
                        content.innerHTML =
                            `<div class="alert alert-danger">❌ Gagal memuat detail jemaat.</div>`;
                    }
                });
            });

            let deleteId = null;

            document.querySelectorAll('.btn-delete-jemaat').forEach(btn => {
                btn.addEventListener('click', function() {
                    deleteId = this.dataset.id;
                    const nama = this.dataset.nama;
                    document.getElementById('deleteNamaJemaat').textContent = nama;
                    modalDelete.show();
                });
            });

            document.getElementById('btnConfirmDelete')?.addEventListener('click', async function() {
                if (!deleteId) return;

                this.disabled = true;
                this.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...';

                try {
                    const res = await fetch(`/admin/jemaat/${deleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const result = await res.json();

                    if (result.success) {
                        modalDelete.hide();
                        const row = document.querySelector(`#tabel-body tr[data-id="${deleteId}"]`);
                        if (row) {
                            row.style.transition = 'all 0.3s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(20px)';
                            setTimeout(() => row.remove(), 300);
                        }
                        showTemporaryAlert('success', result.message);
                    } else {
                        alert(result.message || 'Gagal menghapus data.');
                    }
                } catch (err) {
                    console.error('Delete error:', err);
                    alert('Terjadi kesalahan saat menghapus data.');
                } finally {
                    this.disabled = false;
                    this.innerHTML = '<i class="ri-delete-bin-line me-1"></i> Ya, Hapus';
                }
            });

            form?.addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = document.getElementById('btnSubmitJemaat');
                const originalBtnText = submitBtn.innerHTML;

                errorAlert?.classList.add('d-none');
                errorList.innerHTML = '';

                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

                const formData = new FormData(form);
                const id = jemaatId.value;
                const method = formMethod.value;
                const url = id ? `/admin/jemaat/${id}` : '/admin/jemaat';

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const result = await res.json();

                    if (res.ok && result.success) {
                        modalForm.hide();
                        showTemporaryAlert('success', result.message);

                        if (id) {
                            const row = document.querySelector(`#tabel-body tr[data-id="${id}"]`);
                            if (row && result.data) {
                                window.location.reload();
                            } else {
                                window.location.reload();
                            }
                        } else {
                            window.location.reload();
                        }
                    } else {
                        if (result.errors) {
                            errorList.innerHTML = Object.values(result.errors)
                                .flat()
                                .map(err => `<li>${err}</li>`)
                                .join('');
                            errorAlert?.classList.remove('d-none');
                            errorAlert?.scrollIntoView({
                                behavior: 'smooth'
                            });
                        } else {
                            alert(result.message || 'Gagal menyimpan data.');
                        }
                    }
                } catch (err) {
                    console.error('Submit error:', err);
                    alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });

            function showTemporaryAlert(type, message) {
                document.querySelectorAll('[data-auto-close]').forEach(el => el.remove());

                const alert = document.createElement('div');
                alert.className = `alert-custom ${type === 'success' ? 'success' : 'danger'} mb-4`;
                alert.setAttribute('data-auto-close', '');
                alert.innerHTML = `
            <i class="ri-${type === 'success' ? 'checkbox-circle-fill' : 'error-warning-fill'}"></i>
            <span>${message}</span>
            <button class="alert-close">&times;</button>
        `;

                const header = document.querySelector('.page-wrapper > .d-flex');
                if (header) {
                    header.after(alert);

                    setTimeout(() => {
                        alert.style.transition = 'opacity 0.3s ease';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 300);
                    }, 5000);

                    alert.querySelector('.alert-close')?.addEventListener('click', () => {
                        alert.remove();
                    });
                }
            }

            document.addEventListener('click', function(e) {
                if (e.target.closest('.alert-close')) {
                    e.target.closest('.alert-custom').remove();
                }
            });

            document.querySelectorAll('[data-auto-close]').forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.3s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>
@endsection
