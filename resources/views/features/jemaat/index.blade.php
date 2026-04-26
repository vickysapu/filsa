@extends('layouts.admin')

@section('title', 'Data Jemaat')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Data Jemaat</h1>
                <p class="text-muted-custom mb-0">Kelola data anggota jemaat Gereja Filadelfia</p>
            </div>
            <button type="button" class="btn btn-custom btn-tambah-jemaat">
                <i class="ri-user-add-line me-1"></i> Tambah Jemaat
            </button>
        </div>

        <!-- Success Alert -->
        @if (session('success'))
            <div class="alert-custom success mb-4" data-auto-close>
                <i class="ri-checkbox-circle-fill"></i>
                <span>{{ session('success') }}</span>
                <button class="alert-close">&times;</button>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <form id="formFilter">
                    <div class="row g-3">
                        @if (!Auth::user()?->roles->first()?->branch_id)
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label small fw-semibold mb-2">Filter Cabang</label>
                                <select id="branch_id" name="branch_id" class="form-select form-select-custom">
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
                                    placeholder="Nama, NIK, atau Email...">
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
                            <label class="form-label small fw-semibold mb-2">Status Pernikahan</label>
                            <select id="status_pernikahan" name="status_pernikahan" class="form-select form-select-custom">
                                <option value="">Semua</option>
                                <option value="belum menikah"
                                    {{ request('status_pernikahan') == 'belum menikah' ? 'selected' : '' }}>Belum Menikah
                                </option>
                                <option value="menikah" {{ request('status_pernikahan') == 'menikah' ? 'selected' : '' }}>
                                    Menikah</option>
                                <option value="janda" {{ request('status_pernikahan') == 'janda' ? 'selected' : '' }}>Janda
                                </option>
                                <option value="duda" {{ request('status_pernikahan') == 'duda' ? 'selected' : '' }}>Duda
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

                        <div
                            class="col-6 col-lg-{{ !Auth::user()?->roles->first()?->branch_id ? '2' : '3' }} d-flex align-items-end">
                            <button type="button" id="btnFilter" class="btn btn-custom w-100">
                                <i class="ri-filter-line me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table Card -->
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
                                                NIK: {{ number_format($jemaat->NIK, 0, ',', '.') }} •
                                                {{ $jemaat->jenis_kelamin == 1 ? 'L' : 'P' }}
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
                                        <span class="badge badge-secondary-custom">
                                            {{ ucfirst($jemaat->status_pernikahan ?? '-') }}
                                        </span>
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

    <!-- Modal Form Jemaat -->
    <div class="modal fade" id="modalJemaatForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-content-custom">

                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold" id="modalJemaatLabel">Tambah Jemaat Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formJemaat" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="jemaatId" value="">

                    <div class="modal-body p-4">

                        <div class="alert alert-danger alert-custom-error d-none" id="formErrors">
                            <i class="ri-error-warning-line me-2"></i>
                            <ul class="mb-0 small" id="errorList"></ul>
                        </div>

                        <div class="row g-4">
                            <!-- IDENTITAS DIRI -->
                            <div class="col-lg-6">
                                <div class="card card-custom h-100">
                                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                        <h6 class="mb-0 fw-semibold"><i
                                                class="ri-user-line me-2 text-primary"></i>Identitas Diri</h6>
                                    </div>
                                    <div class="card-body p-4">

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Nama Lengkap
                                                <span class="text-danger">*</span></label>
                                            <input type="text" name="nama_lengkap" id="nama_lengkap"
                                                class="form-control form-control-custom" required
                                                placeholder="Sesuai KTP">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">NIK <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="NIK" id="NIK"
                                                class="form-control form-control-custom" required
                                                placeholder="16 digit NIK" maxlength="16">
                                            <small class="text-muted-custom">Masukkan 16 digit angka NIK</small>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted-custom">Tempat Lahir
                                                    <span class="text-danger">*</span></label>
                                                <input type="text" name="tempat_lahir" id="tempat_lahir"
                                                    class="form-control form-control-custom" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted-custom">Tanggal Lahir
                                                    <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                                                    class="form-control form-control-custom" required>
                                            </div>
                                        </div>

                                        <div class="mb-3 mt-4">
                                            <label
                                                class="form-label small fw-semibold text-muted-custom d-block mb-2">Jenis
                                                Kelamin <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-4">
                                                <label class="form-check-radio">
                                                    <input type="radio" name="jenis_kelamin" value="1"
                                                        class="form-check-input-custom">
                                                    <span class="form-check-label-custom">Laki-laki</span>
                                                </label>
                                                <label class="form-check-radio">
                                                    <input type="radio" name="jenis_kelamin" value="0"
                                                        class="form-check-input-custom">
                                                    <span class="form-check-label-custom">Perempuan</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mb-0">
                                            <label class="form-label small fw-semibold text-muted-custom">Foto
                                                Profil</label>
                                            <input type="file" name="foto" id="foto"
                                                class="form-control form-control-custom" accept="image/*">
                                            <div id="fotoPreview" class="mt-3 d-none">
                                                <img id="imgPreview" class="img-preview">
                                            </div>
                                            <small class="text-muted-custom"><i
                                                    class="ri-information-line me-1"></i>Format: JPG, PNG. Max: 2MB</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KONTAK & DATA -->
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
                                                    placeholder="email@contoh.com">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted-custom">No. HP <span
                                                        class="text-danger">*</span></label>
                                                <input type="tel" name="no_hp" id="no_hp"
                                                    class="form-control form-control-custom" required
                                                    placeholder="08123456789">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Alamat Lengkap
                                                <span class="text-danger">*</span></label>
                                            <textarea name="alamat" id="alamat" class="form-control form-control-custom textarea-custom" rows="3"
                                                required placeholder="Nama jalan, RT/RW, Kelurahan, Kecamatan"></textarea>
                                        </div>

                                        <hr class="border-custom my-4">

                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted-custom">Pendidikan
                                                    Terakhir</label>
                                                <select name="pendidikan" id="pendidikan"
                                                    class="form-select form-select-custom">
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
                                            <div class="col-6">
                                                <label
                                                    class="form-label small fw-semibold text-muted-custom">Pekerjaan</label>
                                                <input type="text" name="pekerjaan" id="pekerjaan"
                                                    class="form-control form-control-custom"
                                                    placeholder="Contoh: Wiraswasta">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Status Pernikahan
                                                <span class="text-danger">*</span></label>
                                            <select name="status_pernikahan" id="status_pernikahan"
                                                class="form-select form-select-custom" required>
                                                <option value="">Pilih...</option>
                                                <option value="belum menikah">Belum Menikah</option>
                                                <option value="menikah">Menikah</option>
                                                <option value="janda">Janda</option>
                                                <option value="duda">Duda</option>
                                            </select>
                                        </div>

                                        <div class="form-check form-switch-custom mt-4 p-3 bg-body-tertiary rounded">
                                            <input type="hidden" name="status_jemaat" value="0">
                                            <input type="checkbox" name="status_jemaat" id="status_jemaat"
                                                class="form-check-input-custom" value="1" checked>
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

    <!-- Modal Show Jemaat -->
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

    <!-- Modal Confirm Delete -->
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

        /* ===== AVATAR TABLE ===== */
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

        /* ===== BADGES ===== */
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

        /* ===== ACTION BUTTONS ===== */
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

        /* ===== FORM CUSTOM ===== */
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

        /* ===== RADIO & CHECKBOX CUSTOM ===== */
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

        /* ===== TEXTAREA CUSTOM ===== */
        .textarea-custom {
            resize: vertical;
            min-height: 80px;
            line-height: 1.5;
        }

        /* ===== MODAL CUSTOM ===== */
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

        /* ===== IMAGE PREVIEW ===== */
        .img-preview {
            width: 100%;
            max-width: 180px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        /* ===== ALERT ERROR ===== */
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

        /* ===== ICON DELETE ===== */
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

        /* ===== EMPTY STATE ===== */
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

        /* ===== PAGINATION ===== */
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

        /* ===== DARK MODE ADJUSTMENTS ===== */
        [data-theme="dark"] .bg-body-tertiary {
            background-color: var(--bg-sidebar) !important;
        }

        [data-theme="dark"] .avatar-table {
            border-color: var(--bg-sidebar);
        }

        /* ===== RESPONSIVE ===== */
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
@endsection
