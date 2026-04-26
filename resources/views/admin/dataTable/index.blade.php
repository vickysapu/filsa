{{-- resources/views/admin/data-tables/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Data Tables')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Data Tables</h1>
                <p class="text-muted-custom mb-0">Kelola tabel data sistem</p>
            </div>
            @if (auth()->user()->hasPermission('data-tables.create'))
                <button type="button" class="btn btn-custom" id="btnAddTable">
                    <i class="ri-add-line me-1"></i> Tambah Tabel
                </button>
            @endif
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="mb-4"></div>

        <!-- Filter Card -->
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <form id="formFilter">
                    <div class="row g-3 align-items-end">
                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-semibold text-muted-custom mb-2">Status</label>
                            <select id="filter_status" class="form-select form-select-custom">
                                <option value="">Semua Status</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-6">
                            <label class="form-label small fw-semibold text-muted-custom mb-2">Cari</label>
                            <div class="position-relative">
                                <i
                                    class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted-custom"></i>
                                <input type="text" id="filter_search" class="form-control form-control-custom ps-5"
                                    value="{{ request('search') }}" placeholder="Nama tabel...">
                            </div>
                        </div>
                        <div class="col-12 col-md-2 d-flex gap-2">
                            <button type="button" id="btnFilter" class="btn btn-custom flex-fill">
                                <i class="ri-filter-line me-1"></i> Filter
                            </button>
                            <button type="button" id="btnReset" class="btn btn-outline-custom" title="Reset">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card-custom">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">ID</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Nama Tabel
                            </th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Status</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Dibuat</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dataTableBody">
                        @forelse($dataTables as $table)
                            <tr class="border-bottom border-custom">
                                <td class="py-3 px-4">
                                    <code class="table-code">{{ Str::limit($table->id, 8) }}</code>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="table-name-display fw-medium">{{ $table->table_name }}</span>
                                        @if (auth()->user()->hasPermission('data-tables.edit'))
                                            <button type="button" class="btn-action btn-action-primary btn-edit-name"
                                                data-id="{{ $table->id }}" data-name="{{ e($table->table_name) }}"
                                                title="Edit nama tabel">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    @if (auth()->user()->hasPermission('data-tables.edit'))
                                        <label class="form-check form-switch-custom mb-0">
                                            <input type="checkbox" class="form-check-input-custom status-toggle"
                                                data-id="{{ $table->id }}" {{ $table->status ? 'checked' : '' }}>
                                            <span class="form-check-label-custom small">
                                                {{ $table->status ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </label>
                                    @else
                                        <span
                                            class="badge {{ $table->status ? 'badge-status-active' : 'badge-status-inactive' }}">
                                            {{ $table->status ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="fw-medium">
                                        <i class="ri-calendar-line me-1"></i> {{ $table->created_at->format('d M Y') }}
                                    </div>
                                    <small class="text-muted-custom">{{ $table->created_at->format('H:i') }}</small>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        @if (auth()->user()->hasPermission('data-tables.delete'))
                                            <button type="button" class="btn-action btn-action-danger btn-delete-table"
                                                data-id="{{ $table->id }}" data-name="{{ e($table->table_name) }}"
                                                title="Hapus tabel">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-5">
                                    <div class="text-center">
                                        <div class="empty-state-icon mb-3">
                                            <i class="ri-database-2-line"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-2">Belum ada data tabel</h6>
                                        <p class="text-muted-custom small mb-4">Mulai tambahkan tabel pertama untuk sistem
                                        </p>
                                        @if (auth()->user()->hasPermission('data-tables.create'))
                                            <button type="button" class="btn btn-custom" id="btnAddTable2">
                                                <i class="ri-add-line me-1"></i> Tambah Tabel Pertama
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($dataTables->hasPages())
                <div class="d-flex justify-content-center p-4 border-top border-custom" id="paginationContainer">
                    {{ $dataTables->links('pagination::simple-bootstrap-4') }}
                </div>
            @endif
        </div>

    </div>

    <!-- Modal Add Table -->
    <div class="modal fade" id="modalTableForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="formTable">
                    @csrf
                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold" id="modalTableLabel">Tambah Tabel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-danger d-none" id="formErrors">
                            <i class="ri-error-warning-line me-2"></i>
                            <ul class="mb-0 small" id="errorList"></ul>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Nama Tabel <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="table_name" id="table_name"
                                class="form-control form-control-custom" required maxlength="100"
                                placeholder="contoh: users, events, meetings" style="text-transform: lowercase;">
                            <small class="text-muted-custom">Gunakan huruf kecil dan underscore, tanpa spasi</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSubmitTable">
                            <i class="ri-save-line me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden Base URLs -->
    <input type="hidden" id="baseUrlStore" value="{{ route('admin.data-tables.store') }}">
    <input type="hidden" id="baseUrlUpdate" value="{{ route('admin.data-tables.update', ['dataTable' => '__ID__']) }}">
    <input type="hidden" id="baseUrlDelete"
        value="{{ route('admin.data-tables.destroy', ['dataTable' => '__ID__']) }}">
    <input type="hidden" id="baseUrlToggle" value="{{ route('admin.data-tables.toggle', ['dataTable' => '__ID__']) }}">

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

        /* ===== TABLE CODE ===== */
        .table-code {
            background: var(--bg-body);
            color: var(--text-primary);
            padding: 0.25em 0.5em;
            border-radius: var(--radius-sm);
            font-family: ui-monospace, monospace;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid var(--border-color);
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

        .form-control-custom[style*="lowercase"] {
            text-transform: lowercase;
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

        /* ===== INLINE EDIT ===== */
        .inline-edit-input {
            border: 2px solid var(--accent-blue) !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
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
            opacity: 0.6;
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

        [data-theme="dark"] .table-code {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .empty-state-icon {
            background: var(--bg-sidebar);
        }

        [data-theme="dark"] .modal-content-custom {
            background: var(--bg-surface);
            border-color: var(--border-color);
        }

        /* ===== RESPONSIVE ===== */
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
            // ===== MODAL INSTANCE =====
            let modalForm;
            const modalEl = document.getElementById('modalTableForm');
            if (modalEl && bootstrap.Modal) {
                modalForm = new bootstrap.Modal(modalEl);
            }

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

            // ===== EVENT: Add Table =====
            document.getElementById('btnAddTable')?.addEventListener('click', openModal);
            document.getElementById('btnAddTable2')?.addEventListener('click', openModal);

            function openModal() {
                const form = document.getElementById('formTable');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated');
                }
                const errorBox = document.getElementById('formErrors');
                if (errorBox) errorBox.classList.add('d-none');
                document.getElementById('modalTableLabel').textContent = 'Tambah Tabel';
                if (modalForm) modalForm.show();
            }

            // ===== EVENT: Inline Edit Table Name =====
            document.addEventListener('click', function(e) {
                // Start edit
                const editBtn = e.target.closest('.btn-edit-name');
                if (editBtn) {
                    e.preventDefault();
                    const cell = editBtn.closest('td');
                    const tableId = editBtn.dataset.id;
                    const currentName = editBtn.dataset.name;

                    cell.innerHTML = `
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <input type="text" class="form-control form-control-custom form-control-sm inline-edit-input" 
                           value="${currentName}" style="min-width: 150px; max-width: 250px;">
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-custom btn-sm btn-save-edit" data-id="${tableId}" title="Simpan">
                            <i class="ri-check-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-custom btn-sm btn-cancel-edit" title="Batal">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>`;
                    cell.querySelector('.inline-edit-input')?.focus();
                }

                // Cancel edit
                const cancelBtn = e.target.closest('.btn-cancel-edit');
                if (cancelBtn) {
                    e.preventDefault();
                    loadTables();
                }

                // Save edit
                const saveBtn = e.target.closest('.btn-save-edit');
                if (saveBtn) {
                    e.preventDefault();
                    const id = saveBtn.dataset.id;
                    const input = saveBtn.closest('td').querySelector('.inline-edit-input');
                    const newName = input?.value.trim();

                    if (!newName) {
                        showAlert('error', 'Nama tabel tidak boleh kosong');
                        return;
                    }

                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                    let baseUrl = document.getElementById('baseUrlUpdate').value.replace('__ID__', id);
                    const separator = baseUrl.includes('?') ? '&' : '?';
                    const url = `${baseUrl}${separator}_token_nav=${currentToken}&_method=PUT`;

                    const formData = new FormData();
                    formData.append('table_name', newName);
                    formData.append('_token', csrfToken);

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                showAlert('success', data.message);
                                loadTables();
                            } else {
                                showAlert('error', data.message || 'Gagal update nama tabel');
                                saveBtn.disabled = false;
                                saveBtn.innerHTML = '<i class="ri-check-line"></i>';
                            }
                        })
                        .catch(() => {
                            showAlert('error', 'Terjadi kesalahan koneksi');
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="ri-check-line"></i>';
                        });
                }
            });

            // ===== EVENT: Toggle Status Switch =====
            document.addEventListener('change', function(e) {
                const toggle = e.target.closest('.status-toggle');
                if (toggle) {
                    const id = toggle.dataset.id;
                    const newStatus = toggle.checked;

                    let baseUrl = document.getElementById('baseUrlToggle').value.replace('__ID__', id);
                    const separator = baseUrl.includes('?') ? '&' : '?';
                    const url = `${baseUrl}${separator}_token_nav=${currentToken}`;

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (!data.success) {
                                toggle.checked = !newStatus;
                                showAlert('error', data.message || 'Gagal mengubah status');
                            } else {
                                showAlert('success', data.message);
                            }
                        })
                        .catch(() => {
                            toggle.checked = !newStatus;
                            showAlert('error', 'Terjadi kesalahan koneksi');
                        });
                }
            });

            // ===== EVENT: Delete Table =====
            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.btn-delete-table');
                if (deleteBtn) {
                    e.preventDefault();
                    const id = deleteBtn.dataset.id;
                    const name = deleteBtn.dataset.name;

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Konfirmasi Hapus',
                            text: `Yakin ingin menghapus tabel "${name}"?`,
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            confirmButtonText: 'Ya, Hapus',
                            cancelButtonText: 'Batal'
                        }).then(result => {
                            if (result.isConfirmed) performDelete(id);
                        });
                    } else {
                        if (confirm(`Yakin ingin menghapus tabel "${name}"?`)) {
                            performDelete(id);
                        }
                    }
                }
            });

            function performDelete(id) {
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
                            loadTables();
                        } else {
                            showAlert('error', data.message || 'Gagal menghapus tabel');
                        }
                    })
                    .catch(() => showAlert('error', 'Terjadi kesalahan koneksi'));
            }

            // ===== EVENT: Filter =====
            document.getElementById('btnFilter')?.addEventListener('click', () => loadTables(1));
            document.getElementById('btnReset')?.addEventListener('click', function() {
                document.getElementById('filter_status').value = '';
                document.getElementById('filter_search').value = '';
                loadTables(1);
            });
            document.getElementById('filter_search')?.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    loadTables(1);
                }
            });

            // ===== EVENT: Form Submit =====
            const formTable = document.getElementById('formTable');
            const btnSave = document.getElementById('btnSubmitTable');

            formTable?.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!formTable.checkValidity()) {
                    formTable.classList.add('was-validated');
                    return;
                }

                const url = document.getElementById('baseUrlStore').value + '?_token_nav=' +
                    currentToken;

                if (btnSave) {
                    btnSave.disabled = true;
                    btnSave.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
                }

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(formTable)
                    });

                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        showAlert('success', data.message);
                        if (modalForm) modalForm.hide();
                        loadTables(1);
                    } else {
                        if (data.errors) {
                            showErrors(data.errors);
                        } else {
                            showAlert('error', data.message || 'Gagal menyimpan tabel');
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

            // ===== FUNCTION: Load Tables =====
            function loadTables(page = 1) {
                const status = document.getElementById('filter_status')?.value || '';
                const search = document.getElementById('filter_search')?.value?.trim() || '';
                const params = new URLSearchParams({
                    page,
                    status,
                    search,
                    ajax: 1,
                    _token_nav: currentToken
                });

                const tableBody = document.getElementById('dataTableBody');
                if (tableBody) {
                    tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="py-5 text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted-custom mt-3 mb-0">Memuat data tabel...</p>
                    </td>
                </tr>`;
                }

                fetch(`{{ route('admin.data-tables.index') }}?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(r => r.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        const newBody = doc.querySelector('#dataTableBody');
                        const newPagination = doc.querySelector('#paginationContainer');

                        if (newBody && tableBody) {
                            tableBody.innerHTML = newBody.innerHTML;
                        }
                        if (newPagination) {
                            const paginationEl = document.getElementById('paginationContainer');
                            if (paginationEl) {
                                paginationEl.innerHTML = newPagination.innerHTML;
                                bindPaginationEvents();
                            }
                        }
                    })
                    .catch(() => showAlert('error', 'Gagal memuat data'));
            }

            // ===== FUNCTION: Bind Pagination Events =====
            function bindPaginationEvents() {
                document.querySelectorAll('#paginationContainer a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const url = new URL(this.href);
                        loadTables(url.searchParams.get('page') || 1);
                    });
                });
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

            // ===== ESC KEY TO CLOSE MODAL =====
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modalForm?._isShown) {
                    modalForm.hide();
                }
            });

            // ===== INIT =====
            bindPaginationEvents();
        });
    </script>
@endsection
