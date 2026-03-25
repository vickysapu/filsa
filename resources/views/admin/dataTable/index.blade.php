{{-- resources/views/admin/data-tables/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Data Tables')

@section('content')
    <style>
        .status-toggle {
            position: relative;
            width: 48px;
            height: 26px;
            appearance: none;
            background: #cbd5e1;
            border-radius: 13px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .status-toggle:checked {
            background: #22c55e;
        }

        .status-toggle::before {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s;
        }

        .status-toggle:checked::before {
            transform: translateX(22px);
        }
    </style>
    <div class="lux-container">
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Data Tables</h1>
                <p class="text-muted">Kelola tabel data sistem</p>
            </div>
            @if (auth()->user()->hasPermission('data-tables.create'))
                <button type="button" class="lux-btn lux-btn-primary" id="btnAddTable">
                    <i class="ri-add-line"></i> Tambah Tabel
                </button>
            @endif
        </div>

        <div id="alertContainer" class="mb-4"></div>

        <!-- Filter -->
        <div class="lux-card mb-5">
            <form id="formFilter">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <label class="lux-label">Status</label>
                        <select id="filter_status" class="lux-select">
                            <option value="">Semua Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div>
                        <label class="lux-label">Cari</label>
                        <input type="text" id="filter_search" class="lux-input" value="{{ request('search') }}"
                            placeholder="Nama tabel...">
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-self: end;">
                        <button type="button" id="btnFilter" class="lux-btn lux-btn-primary" style="flex: 1;">
                            <i class="ri-filter-line"></i> Filter
                        </button>
                        <button type="button" id="btnReset" class="lux-btn lux-btn-outline">
                            <i class="ri-refresh-line"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="lux-card">
            <div class="lux-card-body">
                <div class="table-responsive">
                    <table class="lux-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Tabel</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="dataTableBody">
                            @forelse($dataTables as $table)
                                <tr>
                                    <td><code>{{ Str::limit($table->id, 8) }}</code></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <strong class="table-name-display">{{ $table->table_name }}</strong>
                                            @if (auth()->user()->hasPermission('data-tables.edit'))
                                                <button class="lux-btn lux-btn-outline lux-btn-sm btn-edit-name"
                                                    data-id="{{ $table->id }}" data-name="{{ e($table->table_name) }}"
                                                    title="Edit nama tabel">
                                                    <i class="ri-pencil-line"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if (auth()->user()->hasPermission('data-tables.edit'))
                                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                                <input type="checkbox" class="status-toggle" data-id="{{ $table->id }}"
                                                    {{ $table->status ? 'checked' : '' }}>
                                                <span
                                                    class="text-muted small">{{ $table->status ? 'Aktif' : 'Nonaktif' }}</span>
                                            </label>
                                        @else
                                            <span
                                                class="lux-badge {{ $table->status ? 'lux-badge-success' : 'lux-badge-secondary' }}">
                                                {{ $table->status ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div><i class="ri-calendar-line"></i> {{ $table->created_at->format('d M Y') }}
                                        </div>
                                        <small class="text-muted">{{ $table->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if (auth()->user()->hasPermission('data-tables.delete'))
                                                <button class="lux-btn lux-btn-danger lux-btn-sm btn-delete-table"
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
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="ri-database-2-line" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-3">Belum ada data tabel</p>
                                        @if (auth()->user()->hasPermission('data-tables.create'))
                                            <button class="lux-btn lux-btn-primary mt-2" id="btnAddTable2">
                                                <i class="ri-add-line"></i> Tambah Tabel Pertama
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="paginationContainer" class="mt-4">
                    {{ $dataTables->links('pagination::simple-bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add Table -->
    <div class="modal fade" id="modalTableForm" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content lux-card">
                <form id="formTable">
                    @csrf
                    <div class="modal-header lux-modal-header">
                        <h3 class="modal-title lux-modal-title" id="modalTableLabel">Tambah Tabel</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body lux-modal-body">
                        <div id="formErrors" class="alert alert-danger d-none">
                            <ul class="mb-0" id="errorList"></ul>
                        </div>
                        <div class="lux-form-group mb-3">
                            <label class="lux-label">Nama Tabel *</label>
                            <input type="text" name="table_name" id="table_name" class="lux-input form-control" required
                                maxlength="100" placeholder="contoh: users, events, meetings">
                            <small class="text-muted">Gunakan huruf kecil dan underscore, tanpa spasi</small>
                        </div>
                    </div>
                    <div class="modal-footer lux-modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-primary" id="btnSubmitTable">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <input type="hidden" id="baseUrlStore" value="{{ route('admin.data-tables.store') }}">
    <input type="hidden" id="baseUrlUpdate" value="{{ route('admin.data-tables.update', ['dataTable' => '__ID__']) }}">
    <input type="hidden" id="baseUrlDelete"
        value="{{ route('admin.data-tables.destroy', ['dataTable' => '__ID__']) }}">
    <input type="hidden" id="baseUrlToggle" value="{{ route('admin.data-tables.toggle', ['dataTable' => '__ID__']) }}">
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalForm = new bootstrap.Modal(document.getElementById('modalTableForm'));
            const formTable = document.getElementById('formTable');
            const btnSave = document.getElementById('btnSubmitTable');
            const currentToken = '{{ request()->attributes->get('page_token') ?? '' }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            // Add Table
            document.getElementById('btnAddTable')?.addEventListener('click', openModal);
            document.getElementById('btnAddTable2')?.addEventListener('click', openModal);

            function openModal() {
                formTable?.reset();
                formTable?.classList.remove('was-validated');
                document.getElementById('formErrors')?.classList.add('d-none');
                document.getElementById('modalTableLabel').textContent = 'Tambah Tabel';
                modalForm.show();
            }

            // Inline Edit Table Name
            document.addEventListener('click', function(e) {
                // Start edit
                if (e.target.closest('.btn-edit-name')) {
                    const btn = e.target.closest('.btn-edit-name');
                    const cell = btn.closest('td');
                    const tableId = btn.dataset.id;
                    const currentName = btn.dataset.name;

                    cell.innerHTML = `
                <input type="text" class="lux-input form-control form-control-sm inline-edit-input" 
                       value="${currentName}" style="width: 100%; max-width: 250px;">
                <div class="mt-1">
                    <button class="lux-btn lux-btn-primary lux-btn-sm btn-save-edit" data-id="${tableId}">
                        <i class="ri-check-line"></i>
                    </button>
                    <button class="lux-btn lux-btn-outline lux-btn-sm btn-cancel-edit">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            `;
                    cell.querySelector('.inline-edit-input').focus();
                }

                // Cancel edit
                if (e.target.closest('.btn-cancel-edit')) {
                    loadTables(); // Reload to revert changes
                }

                // Save edit
                if (e.target.closest('.btn-save-edit')) {
                    const btn = e.target.closest('.btn-save-edit');
                    const id = btn.dataset.id;
                    const input = btn.closest('td').querySelector('.inline-edit-input');
                    const newName = input.value.trim();

                    if (!newName) {
                        showAlert('error', 'Nama tabel tidak boleh kosong');
                        return;
                    }

                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

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
                                btn.disabled = false;
                                btn.innerHTML = '<i class="ri-check-line"></i>';
                            }
                        })
                        .catch(() => {
                            showAlert('error', 'Terjadi kesalahan koneksi');
                            btn.disabled = false;
                            btn.innerHTML = '<i class="ri-check-line"></i>';
                        });
                }
            });

            // Toggle Status Switch
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('status-toggle')) {
                    const toggle = e.target;
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
                                toggle.checked = !newStatus; // Revert if failed
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

            // Delete Table
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-delete-table')) {
                    const btn = e.target.closest('.btn-delete-table');
                    const id = btn.dataset.id;
                    const name = btn.dataset.name;

                    Swal.fire({
                        icon: 'warning',
                        title: 'Konfirmasi Hapus',
                        text: `Yakin ingin menghapus tabel "${name}"?`,
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) {
                            let baseUrl = document.getElementById('baseUrlDelete').value.replace(
                                '__ID__', id);
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
                                        showAlert('error', data.message ||
                                            'Gagal menghapus tabel');
                                    }
                                })
                                .catch(() => showAlert('error', 'Terjadi kesalahan koneksi'));
                        }
                    });
                }
            });

            // Filter & Pagination
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

            // Form Submit Add
            formTable?.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (!formTable.checkValidity()) {
                    formTable.classList.add('was-validated');
                    return;
                }

                const url = document.getElementById('baseUrlStore').value + '?_token_nav=' +
                    currentToken;
                btnSave.disabled = true;
                btnSave.innerHTML =
                    '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

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
                        modalForm.hide();
                        loadTables(1);
                    } else {
                        if (data.errors) showErrors(data.errors);
                        else showAlert('error', data.message || 'Gagal menyimpan tabel');
                    }
                } catch (err) {
                    showAlert('error', 'Terjadi kesalahan koneksi');
                } finally {
                    btnSave.disabled = false;
                    btnSave.textContent = 'Simpan';
                }
            });

            // Load Tables AJAX
            function loadTables(page = 1) {
                const status = document.getElementById('filter_status').value;
                const search = document.getElementById('filter_search').value.trim();
                const params = new URLSearchParams({
                    page,
                    status,
                    search,
                    ajax: 1,
                    _token_nav: currentToken
                });

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
                        if (newBody) document.getElementById('dataTableBody').innerHTML = newBody.innerHTML;
                        if (newPagination) {
                            document.getElementById('paginationContainer').innerHTML = newPagination.innerHTML;
                            document.querySelectorAll('#paginationContainer a').forEach(link => {
                                link.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const url = new URL(this.href);
                                    loadTables(url.searchParams.get('page') || 1);
                                });
                            });
                        }
                    })
                    .catch(() => showAlert('error', 'Gagal memuat data'));
            }

            // Helpers
            function showAlert(type, message) {
                const container = document.getElementById('alertContainer');
                if (!container) return;
                const bg = type === 'success' ? '#f0fdf4' : '#fef2f2';
                const border = type === 'success' ? 'var(--success)' : 'var(--danger)';
                const color = type === 'success' ? '#166534' : '#991b1b';
                const icon = type === 'success' ? 'ri-checkbox-circle-line' : 'ri-error-warning-line';
                container.innerHTML =
                    `<div class="lux-card" style="border-left:4px solid ${border};background:${bg};display:flex;align-items:center;gap:10px;padding:12px;"><i class="${icon}" style="color:${border};font-size:1.5rem;"></i><span style="color:${color};font-weight:600;">${message}</span></div>`;
                setTimeout(() => {
                    if (container.firstElementChild) container.firstElementChild.style.display = 'none';
                }, 5000);
            }

            function showErrors(errors) {
                const errorBox = document.getElementById('formErrors');
                const errorList = document.getElementById('errorList');
                if (!errorBox || !errorList) return;
                let html = '<strong>Validasi gagal:</strong><ul class="mb-0">';
                for (let f in errors) {
                    (Array.isArray(errors[f]) ? errors[f] : [errors[f]]).forEach(m => {
                        html += `<li>${f}: ${m}</li>`;
                    });
                }
                html += '</ul>';
                errorList.innerHTML = html;
                errorBox.classList.remove('d-none');
                errorBox.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    </script>

    <style>
        /* Toggle Switch Style */
        .status-toggle {
            position: relative;
            width: 48px;
            height: 26px;
            appearance: none;
            background: #cbd5e1;
            border-radius: 13px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .status-toggle:checked {
            background: #22c55e;
        }

        .status-toggle::before {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s;
        }

        .status-toggle:checked::before {
            transform: translateX(22px);
        }

        .status-toggle:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Inline Edit Input */
        .inline-edit-input {
            border: 2px solid var(--primary);
            border-radius: var(--radius);
            padding: 0.4rem 0.75rem;
            font-size: 0.9rem;
        }
    </style>
@endpush
