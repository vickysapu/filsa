@extends('layouts.admin')

@section('title', 'Manajemen Roles')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Manajemen Roles</h1>
                <p class="text-muted-custom mb-0">Kelola hak akses pengguna dan cabang</p>
            </div>
            @if (auth()->user()->hasPermission('roles.create'))
                <button type="button" class="btn btn-custom" id="btnOpenCreateModal">
                    <i class="ri-add-line me-1"></i> Tambah Role
                </button>
            @endif
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="mb-4"></div>

        <!-- Roles Table -->
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
                        <input type="text" placeholder="Cari nama role..." value="{{ request('search') }}"
                            onkeydown="if(event.key==='Enter'){var p=new URLSearchParams(window.location.search);p.set('search',this.value);p.set('page',1);window.location.href='{{ url()->current() }}?'+p.toString();}">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Role</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Deskripsi</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Cabang</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                Users</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                Permissions</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Status</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr class="border-bottom border-custom">
                                <td class="py-3 px-4">
                                    <div class="fw-semibold">{{ $role->display_name }}</div>
                                    <small class="text-muted-custom">{{ $role->name }}</small>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-muted-custom small">{{ $role->description ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($role->isGlobal())
                                        <span class="badge badge-scope-global">
                                            <i class="ri-global-line me-1"></i> Global
                                        </span>
                                    @else
                                        <span class="badge badge-scope-branch">
                                            <i class="ri-building-line me-1"></i> {{ $role->branch?->name ?? 'Unknown' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="badge badge-count">{{ $role->users_count }}</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="badge badge-count">{{ $role->permissions_count }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        class="badge {{ $role->is_active ? 'badge-status-active' : 'badge-status-inactive' }}">
                                        {{ $role->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        @if (auth()->user()->hasPermission('roles.edit'))
                                            <button type="button" class="btn-action btn-action-primary btn-edit-trigger"
                                                data-id="{{ $role->id }}" data-name="{{ e($role->name) }}"
                                                data-display-name="{{ e($role->display_name) }}"
                                                data-description="{{ e($role->description ?? '') }}"
                                                data-branch-id="{{ $role->branch_id }}"
                                                data-is-active="{{ $role->is_active ? 1 : 0 }}"
                                                data-permissions='@json($role->permissions->pluck('id'))' title="Edit">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                        @endif
                                        @if (auth()->user()->hasPermission('roles.delete') && !in_array($role->name, ['administrator']))
                                            <button type="button" class="btn-action btn-action-danger btn-delete-trigger"
                                                data-id="{{ $role->id }}" data-name="{{ e($role->display_name) }}"
                                                title="Hapus">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($roles->hasPages())
                <div class="dt-footer">
                    <span class="dt-info">
                        Menampilkan {{ $roles->firstItem() }}–{{ $roles->lastItem() }} dari {{ $roles->total() }} data
                    </span>
                    <div>
                        {{ $roles->withQueryString()->links('pagination::simple-bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="dt-footer">
                    <span class="dt-info">Menampilkan {{ $roles->count() }} data</span>
                </div>
            @endif
        </div>

    </div>

    <!-- Modal Create/Edit Role -->
    <div class="modal fade" id="modalRole" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-content-custom">
                <form id="formRole">
                    @csrf
                    <input type="hidden" name="role_id" id="roleIdField">

                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold" id="modalTitle">Tambah Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">

                        <div class="alert alert-danger d-none" id="formErrors">
                            <i class="ri-error-warning-line me-2"></i>
                            <ul class="mb-0 small" id="errorList"></ul>
                        </div>

                        <!-- Basic Info -->
                        <div class="card card-custom mb-4">
                            <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="ri-information-line me-2 text-primary"></i>Informasi Role
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted-custom">Nama Role (System)
                                            <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="inputName"
                                            class="form-control form-control-custom" required maxlength="50"
                                            placeholder="contoh: admin_cabang">
                                        <small class="text-muted-custom">Unik, lowercase, tanpa spasi</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted-custom">Display Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="display_name" id="inputDisplayName"
                                            class="form-control form-control-custom" required maxlength="100"
                                            placeholder="contoh: Admin Cabang">
                                    </div>
                                </div>
                                <div class="mb-0 mt-3">
                                    <label class="form-label small fw-semibold text-muted-custom">Deskripsi</label>
                                    <textarea name="description" id="inputDescription" class="form-control form-control-custom textarea-custom"
                                        rows="2" maxlength="255"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Scope & Settings -->
                        @if (auth()->user()->isSuperAdmin())
                            <div class="card card-custom mb-4">
                                <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                    <h6 class="mb-0 fw-semibold">
                                        <i class="ri-shield-line me-2 text-primary"></i>Cakupan Role
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold text-muted-custom">Tipe Role</label>
                                        <select name="branch_id" id="inputBranchId"
                                            class="form-select form-select-custom">
                                            <option value="">Global (Semua Cabang)</option>
                                            @foreach (\App\Models\Branch::all() as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}
                                                    ({{ $branch->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted-custom">Pilih cabang untuk role spesifik, atau kosongkan
                                            untuk role global</small>
                                    </div>
                                    <label class="form-check form-switch-custom">
                                        <input type="checkbox" name="is_active" id="inputActive"
                                            class="form-check-input-custom" value="1" checked>
                                        <span class="form-check-label-custom">Role Aktif</span>
                                    </label>
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="is_active" id="inputActive" value="1">
                        @endif

                        <!-- Permissions -->
                        <div class="card card-custom">
                            <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="ri-key-line me-2 text-primary"></i>Permissions
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted-custom">Cari Permission</label>
                                    <div class="position-relative">
                                        <i
                                            class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted-custom"></i>
                                        <input type="text" id="permissionSearch"
                                            class="form-control form-control-custom ps-5"
                                            placeholder="Cari permission...">
                                    </div>
                                </div>

                                <div class="permissions-list" id="permissionList">
                                    @foreach ($permissionGroups as $group => $permissions)
                                        <div class="permission-group mb-3" data-group="{{ strtolower($group) }}">
                                            <div
                                                class="permission-group-header d-flex justify-content-between align-items-center mb-2 p-3 rounded border">
                                                <span class="fw-semibold">{{ ucfirst($group) }}</span>
                                                <label class="form-check form-check-inline mb-0">
                                                    <input type="checkbox"
                                                        class="form-check-input-custom select-all-group"
                                                        data-group="{{ strtolower($group) }}">
                                                    <span class="form-check-label-custom small">Pilih Semua</span>
                                                </label>
                                            </div>
                                            <div class="row g-2">
                                                @foreach ($permissions as $permission)
                                                    <div class="col-md-6">
                                                        <label class="permission-item form-check">
                                                            <input type="checkbox" name="permissions[]"
                                                                value="{{ $permission->id }}"
                                                                class="form-check-input-custom permission-checkbox"
                                                                data-group="{{ strtolower($group) }}">
                                                            <span
                                                                class="form-check-label-custom">{{ $permission->display_name }}</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSaveRole">
                            <i class="ri-save-line me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Delete Confirmation -->
    <div class="modal fade" id="modalDelete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="formDelete">
                    @csrf @method('DELETE')
                    <input type="hidden" name="role_id" id="deleteRoleId">

                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold text-danger">
                            <i class="ri-delete-bin-line me-2"></i>Konfirmasi Hapus
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <p class="mb-3">Yakin menghapus role <strong id="deleteRoleName"
                                class="text-primary"></strong>?</p>

                        <div class="alert-custom error mb-3">
                            <i class="ri-error-warning-fill"></i>
                            <span><strong>Peringatan:</strong> Semua user dengan role ini akan kehilangan hak akses
                                terkait.</span>
                        </div>

                        <label class="form-check">
                            <input type="checkbox" id="confirmDelete" class="form-check-input-custom">
                            <span class="form-check-label-custom">Saya mengerti dan ingin melanjutkan</span>
                        </label>
                    </div>

                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger" id="btnConfirmDelete" disabled>
                            <i class="ri-delete-bin-line me-1"></i> Ya, Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden Base URLs -->
    <input type="hidden" id="baseUrlStore" value="{{ route('admin.roles.store') }}">
    <input type="hidden" id="baseUrlUpdate" value="{{ route('admin.roles.update', ['role' => '__ID__']) }}">
    <input type="hidden" id="baseUrlDelete" value="{{ route('admin.roles.destroy', ['role' => '__ID__']) }}">

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

        /* ===== BADGES ===== */
        .badge-scope-global {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-scope-branch {
            background: var(--bg-body);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-count {
            background: var(--bg-body);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 0.25em 0.6em;
            font-weight: 600;
            font-size: 0.75rem;
            min-width: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
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

        /* ===== FORM ELEMENTS ===== */
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

        .textarea-custom {
            resize: vertical;
            min-height: 80px;
            line-height: 1.5;
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

        /* ===== PERMISSIONS LIST ===== */
        .permissions-list {
            max-height: 320px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .permission-group {
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            overflow: hidden;
        }

        .permission-group-header {
            background: var(--bg-body);
            border-bottom: 1px solid var(--border-color);
        }

        .permission-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
            border-left: 3px solid transparent;
        }

        .permission-item:hover {
            background: var(--bg-body);
        }

        .permission-item.selected {
            background: rgba(37, 99, 235, 0.08);
            border-left-color: var(--accent-blue);
        }

        .permission-item .form-check-label-custom {
            font-size: 0.85rem;
        }

        /* ===== ALERT CUSTOM ===== */
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

        .modal-dialog-scrollable .modal-content {
            max-height: 90vh;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
        }

        .modal-body {
            max-height: calc(90vh - 120px);
            overflow-y: auto;
        }

        .modal-dialog-scrollable .modal-header,
        .modal-dialog-scrollable .modal-footer {
            flex-shrink: 0;
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

        /* ===== DARK MODE ===== */
        [data-theme="dark"] .bg-body-tertiary {
            background: var(--bg-sidebar) !important;
        }

        [data-theme="dark"] .permission-group-header {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .permission-item:hover {
            background: var(--bg-sidebar);
        }

        [data-theme="dark"] .permission-item.selected {
            background: rgba(37, 99, 235, 0.15);
        }

        [data-theme="dark"] .modal-content-custom {
            background: var(--bg-surface);
            border-color: var(--border-color);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991px) {
            .permissions-list {
                max-height: 240px;
            }
        }

        @media (max-width: 768px) {
            .table-responsive table {
                min-width: 900px;
            }

            .d-flex.flex-wrap.gap-1 {
                flex-direction: column;
            }

            .d-flex.flex-wrap.gap-1 .btn {
                width: 100%;
                justify-content: center;
            }

            .permission-group .row {
                gap: 0.25rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== MODAL INSTANCES =====
            let modalRole, modalDelete;
            const modalRoleEl = document.getElementById('modalRole');
            const modalDeleteEl = document.getElementById('modalDelete');

            if (modalRoleEl && bootstrap.Modal) modalRole = new bootstrap.Modal(modalRoleEl);
            if (modalDeleteEl && bootstrap.Modal) modalDelete = new bootstrap.Modal(modalDeleteEl);

            // ===== TOOLTIP INIT =====
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });

            // ===== CONSTANTS =====
            const currentToken = '{{ request()->attributes->get('page_token') ?? '' }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let isEditMode = false;

            // ===== EVENT: Open Create Modal =====
            document.getElementById('btnOpenCreateModal')?.addEventListener('click', function() {
                resetForm();
                document.getElementById('modalTitle').textContent = 'Tambah Role';
                document.getElementById('roleIdField').value = '';
                isEditMode = false;
                document.getElementById('inputName').readOnly = false;
                document.getElementById('inputName').value = '';
                document.getElementById('inputDisplayName').value = '';
                document.getElementById('inputDescription').value = '';
                document.getElementById('inputActive').checked = true;
                if (document.getElementById('inputBranchId')) document.getElementById('inputBranchId')
                    .value = '';
                document.querySelectorAll('.permission-checkbox').forEach(cb => {
                    cb.checked = false;
                    updatePermissionItemStyle(cb);
                });
                updateSelectAllStates();
                if (modalRole) modalRole.show();
            });

            // ===== EVENT: Edit Role (delegation) =====
            document.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.btn-edit-trigger');
                if (editBtn) {
                    e.preventDefault();
                    document.getElementById('modalTitle').textContent = 'Edit Role';
                    document.getElementById('roleIdField').value = editBtn.dataset.id || '';
                    isEditMode = true;
                    document.getElementById('inputName').value = editBtn.dataset.name || '';
                    document.getElementById('inputName').readOnly = true;
                    document.getElementById('inputDisplayName').value = editBtn.dataset.displayName || '';
                    document.getElementById('inputDescription').value = editBtn.dataset.description || '';
                    document.getElementById('inputActive').checked = editBtn.dataset.isActive === '1';
                    if (document.getElementById('inputBranchId')) {
                        document.getElementById('inputBranchId').value = editBtn.dataset.branchId || '';
                    }

                    const perms = JSON.parse(editBtn.dataset.permissions || '[]');
                    document.querySelectorAll('.permission-checkbox').forEach(cb => {
                        cb.checked = perms.includes(parseInt(cb.value));
                        updatePermissionItemStyle(cb);
                    });
                    updateSelectAllStates();

                    const errorBox = document.getElementById('formErrors');
                    if (errorBox) errorBox.classList.add('d-none');

                    if (modalRole) modalRole.show();
                }
            });

            // ===== EVENT: Delete Role (delegation) =====
            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.btn-delete-trigger');
                if (deleteBtn) {
                    e.preventDefault();
                    document.getElementById('deleteRoleId').value = deleteBtn.dataset.id || '';
                    document.getElementById('deleteRoleName').textContent = deleteBtn.dataset.name || '';
                    document.getElementById('confirmDelete').checked = false;
                    document.getElementById('btnConfirmDelete').disabled = true;
                    if (modalDelete) modalDelete.show();
                }
            });

            // ===== EVENT: Confirm Delete Checkbox =====
            document.getElementById('confirmDelete')?.addEventListener('change', function() {
                document.getElementById('btnConfirmDelete').disabled = !this.checked;
            });

            // ===== EVENT: Form Role Submit =====
            const formRole = document.getElementById('formRole');
            const btnSave = document.getElementById('btnSaveRole');

            formRole?.addEventListener('submit', async function(e) {
                e.preventDefault();

                const roleId = document.getElementById('roleIdField').value;
                let baseUrl = isEditMode ?
                    document.getElementById('baseUrlUpdate').value.replace('__ID__', roleId) :
                    document.getElementById('baseUrlStore').value;

                const separator = baseUrl.includes('?') ? '&' : '?';
                const url =
                    `${baseUrl}${separator}_token_nav=${currentToken}${isEditMode ? '&_method=PUT' : ''}`;

                if (btnSave) {
                    btnSave.disabled = true;
                    btnSave.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
                }

                const formData = new FormData(formRole);

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
                        if (modalRole) modalRole.hide();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        if (data.errors) {
                            showErrors(data.errors);
                        } else {
                            showAlert('error', data.message || 'Gagal menyimpan role');
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

            // ===== EVENT: Form Delete Submit =====
            const formDelete = document.getElementById('formDelete');
            const btnConfirmDelete = document.getElementById('btnConfirmDelete');

            formDelete?.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (!document.getElementById('confirmDelete').checked) return;

                const id = document.getElementById('deleteRoleId').value;
                let baseUrl = document.getElementById('baseUrlDelete').value.replace('__ID__', id);
                const separator = baseUrl.includes('?') ? '&' : '?';
                const url = `${baseUrl}${separator}_token_nav=${currentToken}`;

                if (btnConfirmDelete) {
                    btnConfirmDelete.disabled = true;
                    btnConfirmDelete.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span> Menghapus...';
                }

                try {
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        showAlert('success', data.message);
                        if (modalDelete) modalDelete.hide();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert('error', data.message || 'Gagal menghapus role');
                        if (btnConfirmDelete) {
                            btnConfirmDelete.disabled = false;
                            btnConfirmDelete.innerHTML =
                                '<i class="ri-delete-bin-line me-1"></i> Ya, Hapus';
                        }
                    }
                } catch (err) {
                    console.error(err);
                    showAlert('error', 'Terjadi kesalahan koneksi');
                    if (btnConfirmDelete) {
                        btnConfirmDelete.disabled = false;
                        btnConfirmDelete.innerHTML =
                            '<i class="ri-delete-bin-line me-1"></i> Ya, Hapus';
                    }
                }
            });

            // ===== EVENT: Permission Search =====
            document.getElementById('permissionSearch')?.addEventListener('keyup', function(e) {
                const keyword = e.target.value.toLowerCase().trim();

                document.querySelectorAll('.permission-item').forEach(item => {
                    const text = item.textContent.toLowerCase();
                    const cb = item.querySelector('.permission-checkbox');
                    const group = item.closest('.permission-group');

                    if (text.includes(keyword)) {
                        item.style.display = '';
                        if (cb) cb.disabled = false;
                    } else {
                        item.style.display = 'none';
                        if (cb) {
                            cb.disabled = true;
                            cb.checked = false;
                            updatePermissionItemStyle(cb);
                        }
                    }

                    if (group) {
                        const visible = Array.from(group.querySelectorAll('.permission-item'))
                            .some(i => i.style.display !== 'none');
                        group.style.display = visible || keyword === '' ? '' : 'none';
                    }
                });

                updateSelectAllStates();
            });

            // ===== EVENT: Select All Group =====
            document.querySelectorAll('.select-all-group').forEach(chk => {
                chk.addEventListener('change', function() {
                    const grp = this.dataset.group;
                    document.querySelectorAll(`.permission-checkbox[data-group="${grp}"]`).forEach(
                        cb => {
                            const item = cb.closest('.permission-item');
                            if (item && item.style.display !== 'none') {
                                cb.checked = this.checked;
                                updatePermissionItemStyle(cb);
                            }
                        });
                    updateSelectAllState(grp);
                });
            });

            // ===== EVENT: Permission Checkbox Change =====
            document.querySelectorAll('.permission-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    updatePermissionItemStyle(this);
                    updateSelectAllState(this.dataset.group);
                });
                updatePermissionItemStyle(cb);
            });

            // ===== FUNCTION: Reset Form =====
            function resetForm() {
                const form = document.getElementById('formRole');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated');
                }

                const errorBox = document.getElementById('formErrors');
                if (errorBox) errorBox.classList.add('d-none');

                document.getElementById('inputName').readOnly = false;
                document.getElementById('inputName').value = '';
                document.getElementById('roleIdField').value = '';

                document.querySelectorAll('.permission-checkbox').forEach(cb => {
                    cb.checked = false;
                    updatePermissionItemStyle(cb);
                });
                updateSelectAllStates();
            }

            // ===== FUNCTION: Show Alert =====
            function showAlert(type, message) {
                const container = document.getElementById('alertContainer');
                if (!container) return;

                const alertClass = type === 'success' ? 'success' : 'error';
                const icon = type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill';
                const borderColor = type === 'success' ? '#22c55e' : '#ef4444';
                const textColor = type === 'success' ? '#166534' : '#991b1b';

                container.innerHTML = `
            <div class="alert-custom ${alertClass}" data-auto-close>
                <i class="${icon}"></i>
                <span>${message}</span>
                <button class="alert-close">&times;</button>
            </div>`;

                // Auto close after 5 seconds
                const alert = container.firstElementChild;
                setTimeout(() => {
                    if (alert) {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateX(-20px)';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);

                // Close button handler
                alert?.querySelector('.alert-close')?.addEventListener('click', () => alert.remove());
            }

            // ===== FUNCTION: Show Validation Errors =====
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

            // ===== FUNCTION: Update Permission Item Style =====
            function updatePermissionItemStyle(cb) {
                const label = cb.closest('.permission-item');
                if (!label) return;
                label.classList.toggle('selected', cb.checked);
            }

            // ===== FUNCTION: Update Select All State =====
            function updateSelectAllState(group) {
                const cbs = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
                const visible = Array.from(cbs).filter(cb => {
                    const item = cb.closest('.permission-item');
                    return item && item.style.display !== 'none';
                });
                const selectAll = document.querySelector(`.select-all-group[data-group="${group}"]`);

                if (selectAll && visible.length > 0) {
                    const all = visible.every(cb => cb.checked);
                    const some = visible.some(cb => cb.checked);
                    selectAll.checked = all;
                    selectAll.indeterminate = some && !all;
                }
            }

            // ===== FUNCTION: Update All Select All States =====
            function updateSelectAllStates() {
                document.querySelectorAll('.select-all-group').forEach(chk => {
                    updateSelectAllState(chk.dataset.group);
                });
            }

            // ===== AUTO-CLOSE ALERTS =====
            document.querySelectorAll('[data-auto-close]').forEach(alert => {
                const closeBtn = alert.querySelector('.alert-close');
                closeBtn?.addEventListener('click', () => alert.remove());
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

            // ===== ESC KEY TO CLOSE MODALS =====
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (modalRole?._isShown) modalRole.hide();
                    if (modalDelete?._isShown) modalDelete.hide();
                }
            });

            // ===== INIT =====
            updateSelectAllStates();
        });
    </script>
@endsection
