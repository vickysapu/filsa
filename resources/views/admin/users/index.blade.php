@extends('layouts.admin')

@section('title', 'Manajemen Users')

@section('content')
    <div class="page-wrapper">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Manajemen Users</h1>
                <p class="text-muted-custom mb-0">Kelola akun pengguna sistem</p>
            </div>
            @if (auth()->user()->hasPermission('users.create'))
                <button type="button" class="btn btn-custom" id="btn-tambah-user">
                    <i class="ri-add-line me-1"></i> Tambah User
                </button>
            @endif
        </div>

        <div id="alertContainer" class="mb-4"></div>

        <input type="hidden" id="baseUrlStore" value="{{ route('admin.users.store') }}">
        <input type="hidden" id="baseUrlUpdate" value="{{ route('admin.users.update', '__ID__') }}">
        <input type="hidden" id="baseUrlEdit" value="{{ route('admin.users.edit', '__ID__') }}">
        <input type="hidden" id="baseUrlDelete" value="{{ route('admin.users.destroy', '__ID__') }}">

        <div class="card-custom">
            {{-- DataTable Toolbar --}}
            <div class="dt-toolbar">
                <div class="dt-entries">
                    <label>Tampilkan</label>
                    <select onchange="var p=new URLSearchParams(window.location.search);p.set('per_page',this.value);p.set('page',1);window.location.href='{{ url()->current() }}?'+p.toString()">
                        @foreach([10,25,50,100] as $pp)
                            <option value="{{ $pp }}" {{ request('per_page', 10) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                        @endforeach
                    </select>
                    <label>entri</label>
                </div>
                <div class="dt-search">
                    <div class="dt-search-wrap">
                        <i class="ri-search-line"></i>
                        <input type="text" placeholder="Cari nama, email..." value="{{ request('search') }}"
                            onkeydown="if(event.key==='Enter'){var p=new URLSearchParams(window.location.search);p.set('search',this.value);p.set('page',1);window.location.href='{{ url()->current() }}?'+p.toString();}">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" id="tabel-users">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">User</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Role</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Cabang</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Status</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Last Login
                            </th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="border-bottom border-custom">
                                <td class="py-3 px-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="user-avatar">{{ substr($user->name, 0, 1) }}</div>
                                        <div>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            <small class="text-muted-custom">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($user->roles as $role)
                                            <span class="badge badge-role">{{ $role->display_name }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-muted-custom small">{{ $user->branch?->name ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        class="badge {{ $user->is_active ? 'badge-status-active' : 'badge-status-inactive' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-muted-custom small">
                                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        @if (auth()->user()->hasPermission('users.edit'))
                                            <button type="button" class="btn-action btn-action-primary btn-edit"
                                                data-id="{{ $user->id }}" title="Edit">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                        @endif
                                        @if (auth()->user()->hasPermission('users.delete') && $user->id !== auth()->id())
                                            <button type="button" class="btn-action btn-action-danger btn-delete"
                                                data-id="{{ $user->id }}" title="Hapus">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-5">
                                    <div class="text-center">
                                        <div class="empty-state-icon mb-3">
                                            <i class="ri-user-line"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-2">Belum ada data user</h6>
                                        <p class="text-muted-custom small mb-0">Mulai tambahkan user pertama untuk sistem
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="dt-footer">
                    <span class="dt-info">
                        Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} data
                    </span>
                    <div>
                        {{ $users->withQueryString()->links('pagination::simple-bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="dt-footer">
                    <span class="dt-info">Menampilkan {{ $users->count() }} data</span>
                </div>
            @endif
        </div>

    </div>

    <div class="modal fade" id="modalUserForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-content-custom">
                <form id="formUser">
                    @csrf
                    <input type="hidden" name="user_id" id="userId" value="">
                    <input type="hidden" name="name" id="hiddenName" value="">
                    <input type="hidden" name="email" id="hiddenEmail" value="">

                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold" id="modalUserLabel">Tambah User Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">

                        <div class="alert alert-danger d-none" id="formErrors">
                            <i class="ri-error-warning-line me-2"></i>
                            <ul class="mb-0 small" id="errorList"></ul>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="card card-custom">
                                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="ri-user-line me-2 text-primary"></i>Informasi Akun
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">

                                        <div class="mb-3" id="jemaatSection">
                                            <label class="form-label small fw-semibold text-muted-custom">Pilih Jemaat
                                                <span class="text-danger">*</span></label>
                                            <select name="jemaat_id" id="jemaat_id"
                                                class="form-control form-control-custom">
                                                <option selected disabled>Pilih Jemaat</option>
                                                @forelse($jemaats as $jemaat)
                                                    <option value="{{ $jemaat->id }}"
                                                        data-email="{{ $jemaat->email ?? '' }}"
                                                        data-name="{{ $jemaat->nama_lengkap }}">
                                                        {{ $jemaat->nama_lengkap }} - {{ $jemaat->NIK }}
                                                    </option>
                                                @empty
                                                    <option value="" disabled selected>Tidak ada data jemaat tersedia
                                                    </option>
                                                @endforelse
                                            </select>
                                            @if ($jemaats->isEmpty())
                                                <small class="text-danger">Semua jemaat sudah memiliki akun atau belum ada
                                                    data jemaat.</small>
                                            @endif
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Nama
                                                Lengkap</label>
                                            <input type="text" id="displayName"
                                                class="form-control form-control-custom" readonly
                                                placeholder="Dipilih dari jemaat">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom">Email</label>
                                            <input type="email" id="displayEmail"
                                                class="form-control form-control-custom" readonly
                                                placeholder="Dipilih dari jemaat">
                                            <small class="text-muted-custom">Email otomatis dari data jemaat</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted-custom"
                                                id="passwordLabel">
                                                Password <span class="text-danger">*</span>
                                            </label>
                                            <input type="password" name="password" id="password"
                                                class="form-control form-control-custom" placeholder="••••••••"
                                                autocomplete="new-password">
                                        </div>

                                        <div class="mb-0">
                                            <label class="form-label small fw-semibold text-muted-custom">Konfirmasi
                                                Password</label>
                                            <input type="password" name="password_confirmation"
                                                id="password_confirmation" class="form-control form-control-custom"
                                                placeholder="••••••••">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card card-custom mb-4">
                                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="ri-shield-line me-2 text-primary"></i>Hak Akses (Roles)
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="roles-list">
                                            @forelse($roles as $role)
                                                <label class="role-item">
                                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                                        class="form-check-input-custom">
                                                    <div class="role-info">
                                                        <span
                                                            class="role-name">{{ $role->display_name ?? $role->name }}</span>
                                                        @if ($role->description)
                                                            <small
                                                                class="role-description text-muted-custom">{{ $role->description }}</small>
                                                        @endif
                                                    </div>
                                                </label>
                                            @empty
                                                <p class="text-muted-custom small text-center mb-0">Tidak ada role tersedia
                                                </p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <div class="card card-custom">
                                    <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="ri-settings-line me-2 text-primary"></i>Pengaturan
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <label class="form-check form-switch-custom">
                                            <input type="checkbox" name="is_active" id="is_active"
                                                class="form-check-input-custom" value="1" checked>
                                            <span class="form-check-label-custom">Akun Aktif</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSubmit">
                            <i class="ri-save-line me-1"></i> Simpan
                        </button>
                    </div>
                </form>
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

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent-blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .badge-role {
            background: var(--bg-body);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 0.25em 0.5em;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-status-active {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-inactive {
            background: rgba(100, 116, 139, 0.15);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
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

        .roles-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-height: 280px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .role-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.625rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            background: var(--bg-surface);
            cursor: pointer;
            transition: var(--transition);
        }

        .role-item:hover {
            border-color: var(--accent-blue);
            background: var(--bg-body);
        }

        .role-info {
            flex: 1;
            min-width: 0;
        }

        .role-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-primary);
            display: block;
        }

        .role-description {
            font-size: 0.8rem;
            display: block;
            margin-top: 0.15rem;
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

        .form-check-input-custom {
            width: 18px;
            height: 18px;
            border: 2px solid var(--border-color);
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
            accent-color: var(--accent-blue);
        }

        .form-check-input-custom:checked {
            background-color: var(--accent-blue);
            border-color: var(--accent-blue);
        }

        .form-check-input-custom[type="checkbox"] {
            border-radius: 4px;
        }

        .form-check-label-custom {
            font-size: 0.9rem;
            color: var(--text-primary);
            cursor: pointer;
            margin-left: 0.35rem;
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

        .alert-custom {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.25rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            animation: slideIn 0.3s ease;
        }

        .alert-custom.success {
            background: rgba(34, 197, 94, 0.1);
            border-left: 4px solid #22c55e;
            color: #166534;
        }

        .alert-custom.error {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .alert-custom i {
            font-size: 1.25rem;
        }

        @keyframes slideIn {
            from {
                transform: translateX(-20px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
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
            opacity: 0.6;
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

        .modal-dialog-scrollable .modal-content {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            flex: 1 1 auto;
        }

        .modal-dialog-scrollable .modal-header,
        .modal-dialog-scrollable .modal-footer {
            flex-shrink: 0;
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
            background: var(--bg-sidebar) !important;
        }

        [data-theme="dark"] .role-item {
            background: var(--bg-surface);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .role-item:hover {
            background: var(--bg-sidebar);
        }

        [data-theme="dark"] .empty-state-icon {
            background: var(--bg-sidebar);
        }

        [data-theme="dark"] .modal-content-custom {
            background: var(--bg-surface);
            border-color: var(--border-color);
        }

        @media (max-width: 991px) {
            .roles-list {
                max-height: 200px;
            }
        }

        @media (max-width: 768px) {
            .table-responsive table {
                min-width: 700px;
            }

            .d-flex.flex-wrap.gap-1 {
                flex-direction: column;
            }

            .d-flex.flex-wrap.gap-1 .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let modalUser;
            const modalEl = document.getElementById('modalUserForm');
            if (modalEl && bootstrap.Modal) {
                modalUser = new bootstrap.Modal(modalEl);
            }

            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });

            const currentToken = '{{ request()->attributes->get('page_token') ?? '' }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let isEditMode = false;

            document.getElementById('btn-tambah-user')?.addEventListener('click', function() {
                resetForm();
                isEditMode = false;
                document.getElementById('modalUserLabel').textContent = 'Tambah User Baru';
                document.getElementById('passwordLabel').innerHTML =
                    'Password <span class="text-danger">*</span>';
                document.getElementById('password').required = true;
                document.getElementById('password_confirmation').required = true;
                document.getElementById('jemaatSection').style.display = 'block';
                document.getElementById('displayName').closest('.mb-3').style.display = 'block';
                document.getElementById('displayEmail').closest('.mb-3').style.display = 'block';
                if (modalUser) modalUser.show();
            });

            document.getElementById('jemaat_id')?.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                const email = selected.dataset.email || this.value + '@system.local';
                const name = selected.dataset.name || '';
                document.getElementById('displayEmail').value = email;
                document.getElementById('displayName').value = name;
                document.getElementById('hiddenEmail').value = email;
                document.getElementById('hiddenName').value = name;
            });

            document.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.btn-edit');
                if (editBtn) {
                    e.preventDefault();
                    loadUserForEdit(editBtn.dataset.id);
                }
            });

            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.btn-delete');
                if (deleteBtn) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Konfirmasi',
                            text: 'Hapus user ini?',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            confirmButtonText: 'Ya',
                            cancelButtonText: 'Batal'
                        }).then(result => {
                            if (result.isConfirmed) deleteUser(deleteBtn.dataset.id);
                        });
                    } else {
                        if (confirm('Yakin ingin menghapus user ini?')) {
                            deleteUser(deleteBtn.dataset.id);
                        }
                    }
                }
            });

            const formUser = document.getElementById('formUser');
            const btnSave = document.getElementById('btnSubmit');

            formUser?.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!isEditMode) {
                    const jemaatId = document.getElementById('jemaat_id').value;
                    const password = document.getElementById('password').value;
                    const passwordConfirm = document.getElementById('password_confirmation').value;

                    if (!jemaatId) {
                        showAlert('error', 'Silakan pilih jemaat terlebih dahulu');
                        return;
                    }
                    if (!password || !passwordConfirm) {
                        showAlert('error', 'Password dan konfirmasi password harus diisi');
                        return;
                    }
                    if (password !== passwordConfirm) {
                        showAlert('error', 'Konfirmasi password tidak cocok');
                        return;
                    }
                }

                if (!formUser.checkValidity()) {
                    formUser.classList.add('was-validated');
                    return;
                }

                const userId = document.getElementById('userId').value;
                let baseUrl = isEditMode ?
                    document.getElementById('baseUrlUpdate').value.replace('__ID__', userId) :
                    document.getElementById('baseUrlStore').value;

                const separator = baseUrl.includes('?') ? '&' : '?';
                const url =
                    `${baseUrl}${separator}_token_nav=${currentToken}${isEditMode ? '&_method=PUT' : ''}`;

                if (btnSave) {
                    btnSave.disabled = true;
                    btnSave.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
                }

                const formData = new FormData(formUser);

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        showAlert('success', data.message);
                        if (modalUser) modalUser.hide();
                        loadUsers();
                    } else {
                        if (data.errors) {
                            showErrors(data.errors);
                        } else {
                            showAlert('error', data.message || 'Gagal menyimpan user');
                        }
                    }
                } catch (err) {
                    console.error(err);
                    showAlert('error', 'Terjadi kesalahan koneksi');
                } finally {
                    if (btnSave) {
                        btnSave.disabled = false;
                        btnSave.innerHTML = '<i class="ri-save-line me-1"></i> Simpan';
                    }
                }
            });

            function loadUsers() {
                const url = '{{ route('admin.users.index') }}?ajax=1&_token_nav=' + currentToken;
                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.html) {
                            document.querySelector('#tabel-users tbody').innerHTML = data.html;
                            bindEvents();
                        }
                    })
                    .catch(() => showAlert('error', 'Gagal memuat data'));
            }

            function loadUserForEdit(id) {
                let baseUrl = document.getElementById('baseUrlEdit').value.replace('__ID__', id);
                const separator = baseUrl.includes('?') ? '&' : '?';
                const url = `${baseUrl}${separator}_token_nav=${currentToken}&ajax=1`;

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        resetForm();
                        isEditMode = true;
                        document.getElementById('modalUserLabel').textContent = 'Edit User';
                        document.getElementById('userId').value = data.user.id;
                        document.getElementById('hiddenName').value = data.user.name || '';
                        document.getElementById('hiddenEmail').value = data.user.email || '';
                        document.getElementById('displayName').value = data.user.name || '';
                        document.getElementById('displayEmail').value = data.user.email || '';
                        document.getElementById('is_active').checked = !!data.user.is_active;
                        document.getElementById('passwordLabel').innerHTML =
                            'Password Baru <span class="text-muted-custom">(kosongkan jika tidak ingin ubah)</span>';
                        document.getElementById('password').required = false;
                        document.getElementById('password_confirmation').required = false;
                        document.getElementById('jemaatSection').style.display = 'none';
                        document.getElementById('displayName').closest('.mb-3').style.display = 'block';
                        document.getElementById('displayEmail').closest('.mb-3').style.display = 'block';

                        document.querySelectorAll('input[name="roles[]"]').forEach(cb => cb.checked = false);
                        if (data.userRoles?.length) {
                            data.userRoles.forEach(rid => {
                                const cb = document.querySelector(
                                    `input[name="roles[]"][value="${rid}"]`);
                                if (cb) cb.checked = true;
                            });
                        }

                        if (modalUser) modalUser.show();
                    })
                    .catch(() => showAlert('error', 'Gagal memuat data user'));
            }

            function deleteUser(id) {
                let baseUrl = document.getElementById('baseUrlDelete').value.replace('__ID__', id);
                const separator = baseUrl.includes('?') ? '&' : '?';
                const url = `${baseUrl}${separator}_token_nav=${currentToken}`;

                fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', data.message);
                            loadUsers();
                        } else {
                            showAlert('error', data.message || 'Gagal menghapus user');
                        }
                    })
                    .catch(() => showAlert('error', 'Terjadi kesalahan koneksi'));
            }

            function resetForm() {
                const form = document.getElementById('formUser');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated');
                }
                const errorBox = document.getElementById('formErrors');
                if (errorBox) errorBox.classList.add('d-none');
                document.getElementById('userId').value = '';
                document.getElementById('hiddenName').value = '';
                document.getElementById('hiddenEmail').value = '';
                document.getElementById('displayName').value = '';
                document.getElementById('displayEmail').value = '';
                document.getElementById('password').value = '';
                document.getElementById('password_confirmation').value = '';
                document.querySelectorAll('input[name="roles[]"]').forEach(cb => cb.checked = false);
            }

            function showAlert(type, message) {
                const container = document.getElementById('alertContainer');
                if (!container) return;
                const alertClass = type === 'success' ? 'success' : 'error';
                const icon = type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill';
                container.innerHTML = `
                    <div class="alert-custom ${alertClass}" data-auto-close>
                        <i class="${icon}"></i>
                        <span>${message}</span>
                        <button class="alert-close">&times;</button>
                    </div>`;
                const alert = container.firstElementChild;
                setTimeout(() => {
                    if (alert) {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateX(-20px)';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);
                alert?.querySelector('.alert-close')?.addEventListener('click', () => alert.remove());
            }

            function showErrors(errors) {
                const errorBox = document.getElementById('formErrors');
                const errorList = document.getElementById('errorList');
                if (!errorBox || !errorList) return;
                let html = '';
                for (let field in errors) {
                    const messages = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                    messages.forEach(msg => {
                        html += `<li><strong>${field}:</strong> ${msg}</li>`;
                    });
                }
                errorList.innerHTML = html;
                errorBox.classList.remove('d-none');
                errorBox.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            function bindEvents() {
                document.querySelectorAll('.btn-edit').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        loadUserForEdit(id);
                    });
                });
                document.querySelectorAll('.btn-delete').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Konfirmasi',
                                text: 'Hapus user ini?',
                                showCancelButton: true,
                                confirmButtonColor: '#ef4444',
                                confirmButtonText: 'Ya',
                                cancelButtonText: 'Batal'
                            }).then(result => {
                                if (result.isConfirmed) deleteUser(id);
                            });
                        } else {
                            if (confirm('Yakin ingin menghapus user ini?')) {
                                deleteUser(id);
                            }
                        }
                    });
                });
            }

            document.querySelectorAll('[data-auto-close]').forEach(alert => {
                const closeBtn = alert.querySelector('.alert-close');
                closeBtn?.addEventListener('click', () => alert.remove());
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modalUser?._isShown) {
                    modalUser.hide();
                }
            });

            bindEvents();
        });
    </script>
@endsection
