{{-- resources/views/admin/branches/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Manajemen Cabang')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Manajemen Cabang</h1>
                <p class="text-muted-custom mb-0">Kelola cabang dan kantor pusat sistem</p>
            </div>
            @if (auth()->user()->hasPermission('branches.create'))
                <button type="button" class="btn btn-custom" id="btnOpenCreateModal">
                    <i class="ri-add-line me-1"></i> Tambah Cabang
                </button>
            @endif
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="mb-4"></div>

        <!-- Branches Table -->
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
                        <input type="text" placeholder="Cari nama cabang..." value="{{ request('search') }}"
                            onkeydown="if(event.key==='Enter'){var p=new URLSearchParams(window.location.search);p.set('search',this.value);p.set('page',1);window.location.href='{{ url()->current() }}?'+p.toString();}">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Nama Cabang
                            </th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Kode</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Tipe</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Status</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                Data Terkait</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($branches as $branch)
                            <tr class="border-bottom border-custom">
                                <td class="py-3 px-4">
                                    <div class="fw-semibold">{{ $branch->name }}</div>
                                    @if ($branch->address)
                                        <small class="text-muted-custom">{{ $branch->address }}</small>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <code class="branch-code">{{ $branch->code }}</code>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($branch->is_headquarter)
                                        <span class="badge badge-type-headquarter">
                                            <i class="ri-building-2-line me-1"></i> Kantor Pusat
                                        </span>
                                    @else
                                        <span class="badge badge-type-branch">
                                            <i class="ri-store-line me-1"></i> Cabang
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        class="badge {{ $branch->is_active ? 'badge-status-active' : 'badge-status-inactive' }}">
                                        {{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="badge badge-count">
                                        <i class="ri-user-line me-1"></i> {{ $branch->users_count }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        @if (!$branch->is_headquarter)
                                            <button type="button"
                                                class="btn-action {{ $branch->is_active ? 'btn-action-success' : 'btn-action-warning' }} btn-toggle-active"
                                                data-id="{{ $branch->id }}" data-active="{{ $branch->is_active ? 1 : 0 }}"
                                                title="{{ $branch->is_active ? 'Nonaktifkan' : 'Aktifkan' }} cabang">
                                                <i class="ri-{{ $branch->is_active ? 'toggle-fill' : 'toggle-line' }}"></i>
                                            </button>
                                            <button type="button" class="btn-action btn-action-danger btn-delete-trigger"
                                                data-id="{{ $branch->id }}" data-name="{{ $branch->name }}"
                                                title="Hapus">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        @else
                                            <span class="badge badge-locked" title="Kantor Pusat">
                                                <i class="ri-lock-line"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($branches->hasPages())
                <div class="dt-footer">
                    <span class="dt-info">
                        Menampilkan {{ $branches->firstItem() }}–{{ $branches->lastItem() }} dari {{ $branches->total() }} data
                    </span>
                    <div>
                        {{ $branches->withQueryString()->links('pagination::simple-bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="dt-footer">
                    <span class="dt-info">Menampilkan {{ $branches->count() }} data</span>
                </div>
            @endif
        </div>

    </div>

    <!-- Modal Create/Edit Branch -->
    <div class="modal fade" id="modalBranch" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="formBranch">
                    @csrf
                    <input type="hidden" name="_method" id="methodField" value="POST">
                    <input type="hidden" name="branch_id" id="branchIdField">

                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold" id="modalTitle">Tambah Cabang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">

                        <div class="alert alert-danger d-none" id="formErrors">
                            <i class="ri-error-warning-line me-2"></i>
                            <ul class="mb-0 small" id="errorList"></ul>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Nama Cabang <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" id="inputName" class="form-control form-control-custom"
                                required maxlength="100" placeholder="Contoh: Gereja Filadelfia Jakarta">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Kode Cabang <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="code" id="inputCode" class="form-control form-control-custom"
                                required maxlength="10" placeholder="Contoh: JKT" style="text-transform: uppercase;">
                            <small class="text-muted-custom">Kode unik untuk identifikasi sistem</small>
                        </div>

                        <label class="form-check form-switch-custom">
                            <input type="checkbox" name="is_active" id="inputActive" class="form-check-input-custom"
                                value="1" checked>
                            <span class="form-check-label-custom">Aktifkan cabang ini</span>
                        </label>
                    </div>

                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSaveBranch">
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
                    <input type="hidden" name="branch_id" id="deleteBranchId">

                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold text-danger">
                            <i class="ri-delete-bin-line me-2"></i>Konfirmasi Hapus
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <p class="mb-3">Yakin menghapus cabang <strong id="deleteBranchName"
                                class="text-primary"></strong>?</p>

                        <div class="alert-custom error mb-3">
                            <i class="ri-error-warning-fill"></i>
                            <span><strong>Peringatan:</strong> Semua data di cabang ini akan terhapus permanen.</span>
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
    <input type="hidden" id="baseUrlStore" value="{{ route('admin.branches.store') }}">
    <input type="hidden" id="baseUrlUpdate" value="{{ route('admin.branches.update', ['branch' => '__ID__']) }}">
    <input type="hidden" id="baseUrlDelete" value="{{ route('admin.branches.destroy', ['branch' => '__ID__']) }}">
    <input type="hidden" id="baseUrlToggle" value="{{ route('admin.branches.toggle', ['branch' => '__ID__']) }}">

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

        /* ===== BRANCH CODE ===== */
        .branch-code {
            background: var(--bg-body);
            color: var(--text-primary);
            padding: 0.25em 0.5em;
            border-radius: var(--radius-sm);
            font-family: ui-monospace, monospace;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid var(--border-color);
        }

        /* ===== TYPE BADGES ===== */
        .badge-type-headquarter {
            background: rgba(37, 99, 235, 0.15);
            color: var(--accent-blue);
            border: 1px solid rgba(37, 99, 235, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-type-branch {
            background: var(--bg-body);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        /* ===== STATUS BADGES ===== */
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

        /* ===== COUNT BADGE ===== */
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

        /* ===== LOCKED BADGE ===== */
        .badge-locked {
            background: rgba(100, 116, 139, 0.15);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
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

        .btn-action-success {
            border-color: #22c55e;
            color: #22c55e;
        }

        .btn-action-success:hover {
            background: #22c55e;
            color: white;
        }

        .btn-action-warning {
            border-color: #f59e0b;
            color: #f59e0b;
        }

        .btn-action-warning:hover {
            background: #f59e0b;
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

        [data-theme="dark"] .branch-code {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .badge-type-branch {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .badge-count {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .modal-content-custom {
            background: var(--bg-surface);
            border-color: var(--border-color);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .table-responsive table {
                min-width: 800px;
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
            // ===== MODAL INSTANCES =====
            let modalBranch, modalDelete;
            const modalBranchEl = document.getElementById('modalBranch');
            const modalDeleteEl = document.getElementById('modalDelete');

            if (modalBranchEl && bootstrap.Modal) modalBranch = new bootstrap.Modal(modalBranchEl);
            if (modalDeleteEl && bootstrap.Modal) modalDelete = new bootstrap.Modal(modalDeleteEl);

            // ===== TOOLTIP INIT =====
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });

            // ===== CONSTANTS =====
            const currentToken = '{{ request()->attributes->get('page_token') ?? '' }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            // ===== EVENT: Open Create Modal =====
            document.getElementById('btnOpenCreateModal')?.addEventListener('click', function() {
                resetForm();
                document.getElementById('modalTitle').textContent = 'Tambah Cabang';
                document.getElementById('methodField').value = 'POST';
                document.getElementById('branchIdField').value = '';
                document.getElementById('inputActive').checked = true;
                if (modalBranch) modalBranch.show();
            });

            // ===== EVENT: Delete Branch (delegation) =====
            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.btn-delete-trigger');
                if (deleteBtn) {
                    e.preventDefault();
                    document.getElementById('deleteBranchId').value = deleteBtn.dataset.id;
                    document.getElementById('deleteBranchName').textContent = deleteBtn.dataset.name;
                    document.getElementById('confirmDelete').checked = false;
                    document.getElementById('btnConfirmDelete').disabled = true;
                    if (modalDelete) modalDelete.show();
                }
            });

            // ===== EVENT: Toggle Active Status =====
            document.addEventListener('click', function(e) {
                const toggleBtn = e.target.closest('.btn-toggle-active');
                if (toggleBtn) {
                    e.preventDefault();
                    toggleBranchStatus(toggleBtn);
                }
            });

            // ===== EVENT: Confirm Delete Checkbox =====
            document.getElementById('confirmDelete')?.addEventListener('change', function() {
                document.getElementById('btnConfirmDelete').disabled = !this.checked;
            });

            // ===== EVENT: Form Branch Submit =====
            const formBranch = document.getElementById('formBranch');
            const btnSave = document.getElementById('btnSaveBranch');

            formBranch?.addEventListener('submit', async function(e) {
                e.preventDefault();

                const isEdit = document.getElementById('methodField').value === 'PUT';
                const branchId = document.getElementById('branchIdField').value;

                let baseUrl = isEdit ?
                    document.getElementById('baseUrlUpdate').value.replace('__ID__', branchId) :
                    document.getElementById('baseUrlStore').value;

                const separator = baseUrl.includes('?') ? '&' : '?';
                const url = `${baseUrl}${separator}_token_nav=${currentToken}`;

                if (btnSave) {
                    btnSave.disabled = true;
                    btnSave.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
                }

                const formData = new FormData(formBranch);

                try {
                    const response = await fetch(url, {
                        method: isEdit ? 'PUT' : 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        showAlert('success', data.message);
                        if (modalBranch) modalBranch.hide();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        if (data.errors) {
                            showErrors(data.errors);
                        } else {
                            showAlert('error', data.message || 'Gagal menyimpan cabang');
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

                const id = document.getElementById('deleteBranchId').value;
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

                    const data = await response.json();

                    if (data.success) {
                        showAlert('success', data.message);
                        if (modalDelete) modalDelete.hide();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert('error', data.message || 'Gagal menghapus cabang');
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

            // ===== FUNCTION: Toggle Branch Status =====
            async function toggleBranchStatus(btn) {
                const id = btn.dataset.id;
                let baseUrl = document.getElementById('baseUrlToggle').value.replace('__ID__', id);
                const separator = baseUrl.includes('?') ? '&' : '?';
                const url = `${baseUrl}${separator}_token_nav=${currentToken}`;

                // Visual feedback
                const originalIcon = btn.querySelector('i')?.className;
                btn.disabled = true;

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        const row = btn.closest('tr');
                        const badge = row?.querySelector('td:nth-child(4) .badge');
                        const icon = btn.querySelector('i');

                        if (data.is_active) {
                            // Switch to Active
                            badge?.classList.remove('badge-status-inactive');
                            badge?.classList.add('badge-status-active');
                            badge.textContent = 'Aktif';
                            icon?.classList.remove('ri-toggle-line');
                            icon?.classList.add('ri-toggle-fill');
                            btn.classList.remove('btn-action-warning');
                            btn.classList.add('btn-action-success');
                            btn.title = 'Nonaktifkan cabang';
                        } else {
                            // Switch to Inactive
                            badge?.classList.remove('badge-status-active');
                            badge?.classList.add('badge-status-inactive');
                            badge.textContent = 'Nonaktif';
                            icon?.classList.remove('ri-toggle-fill');
                            icon?.classList.add('ri-toggle-line');
                            btn.classList.remove('btn-action-success');
                            btn.classList.add('btn-action-warning');
                            btn.title = 'Aktifkan cabang';
                        }

                        btn.dataset.active = data.is_active ? '1' : '0';
                        showAlert('success', data.message);
                    } else {
                        showAlert('error', data.message || 'Gagal mengubah status cabang');
                        // Restore original icon on error
                        if (icon && originalIcon) icon.className = originalIcon;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('error', 'Terjadi kesalahan saat mengubah status cabang');
                    // Restore original icon on error
                    const icon = btn.querySelector('i');
                    if (icon && originalIcon) icon.className = originalIcon;
                } finally {
                    btn.disabled = false;
                }
            }

            // ===== FUNCTION: Reset Form =====
            function resetForm() {
                const form = document.getElementById('formBranch');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated');
                }

                const errorBox = document.getElementById('formErrors');
                if (errorBox) errorBox.classList.add('d-none');

                document.getElementById('branchIdField').value = '';
                document.getElementById('methodField').value = 'POST';
                document.getElementById('inputActive').checked = true;
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
                    if (modalBranch?._isShown) modalBranch.hide();
                    if (modalDelete?._isShown) modalDelete.hide();
                }
            });
        });
    </script>
@endsection
