@extends('layouts.admin')
@section('title', 'Manajemen Roles')

@section('content')
    <div class="lux-container">
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Manajemen Roles</h1>
                <p class="text-muted">Kelola hak akses pengguna dan cabang</p>
            </div>
            @if (auth()->user()->hasPermission('roles.create'))
                <button class="lux-btn lux-btn-primary" id="btnOpenCreateModal">
                    <i class="ri-add-line"></i> Tambah Role
                </button>
            @endif
        </div>

        <div id="alertContainer" class="mb-4"></div>

        <div class="lux-card">
            <div class="lux-card-body">
                <div class="table-responsive">
                    <table class="lux-table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Deskripsi</th>
                                <th>Cabang</th>
                                <th>Users</th>
                                <th>Permissions</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr>
                                    <td>
                                        <strong>{{ $role->display_name }}</strong>
                                        <br><small class="text-muted">{{ $role->name }}</small>
                                    </td>
                                    <td>{{ $role->description ?? '-' }}</td>
                                    <td>
                                        @if ($role->isGlobal())
                                            <span class="lux-badge lux-badge-primary">Global</span>
                                        @else
                                            <span
                                                class="lux-badge lux-badge-secondary">{{ $role->branch?->name ?? 'Unknown' }}</span>
                                        @endif
                                    </td>
                                    <td><span class="lux-badge lux-badge-info">{{ $role->users_count }}</span></td>
                                    <td><span class="lux-badge lux-badge-success">{{ $role->permissions_count }}</span></td>
                                    <td>
                                        @if ($role->is_active)
                                            <span class="lux-badge lux-badge-success">Aktif</span>
                                        @else
                                            <span class="lux-badge lux-badge-danger">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            @if (auth()->user()->hasPermission('roles.edit'))
                                                <button type="button"
                                                    class="lux-btn lux-btn-outline lux-btn-sm btn-edit-trigger"
                                                    data-id="{{ $role->id }}" data-name="{{ e($role->name) }}"
                                                    data-display-name="{{ e($role->display_name) }}"
                                                    data-description="{{ e($role->description ?? '') }}"
                                                    data-branch-id="{{ $role->branch_id }}"
                                                    data-is-active="{{ $role->is_active ? 1 : 0 }}"
                                                    data-permissions='@json($role->permissions->pluck('id'))'>
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                            @endif
                                            @if (auth()->user()->hasPermission('roles.delete') && !in_array($role->name, ['administrator']))
                                                <button type="button"
                                                    class="lux-btn lux-btn-danger lux-btn-sm btn-delete-trigger"
                                                    data-id="{{ $role->id }}"
                                                    data-name="{{ e($role->display_name) }}">
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
                <div class="mt-4">{{ $roles->links() }}</div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRole" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content lux-card">
                <form id="formRole">
                    @csrf
                    <input type="hidden" name="role_id" id="roleIdField">
                    <div class="modal-header lux-modal-header">
                        <h3 class="modal-title lux-modal-title" id="modalTitle">Tambah Role</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body lux-modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Nama Role (System) *</label>
                                    <input type="text" name="name" id="inputName" class="lux-input form-control"
                                        required maxlength="50" placeholder="contoh: admin_cabang">
                                    <small class="text-muted">Unik, lowercase, tanpa spasi</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Display Name *</label>
                                    <input type="text" name="display_name" id="inputDisplayName"
                                        class="lux-input form-control" required maxlength="100"
                                        placeholder="contoh: Admin Cabang">
                                </div>
                            </div>
                        </div>
                        <div class="lux-form-group mb-3">
                            <label class="lux-label">Deskripsi</label>
                            <textarea name="description" id="inputDescription" class="lux-textarea form-control" rows="2" maxlength="255"></textarea>
                        </div>
                        @if (auth()->user()->isSuperAdmin())
                            <div class="lux-form-group mb-3">
                                <label class="lux-label">Tipe Role</label>
                                <select name="branch_id" id="inputBranchId" class="lux-input form-select">
                                    <option value="">Global (Semua Cabang)</option>
                                    @foreach (\App\Models\Branch::all() as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->code }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih cabang untuk role spesifik, atau kosongkan untuk role
                                    global</small>
                            </div>
                        @endif
                        <div class="lux-form-group mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" id="inputActive" class="form-check-input"
                                    value="1" checked>
                                <span class="form-check-label">Role Aktif</span>
                            </label>
                        </div>
                        <hr>
                        <h5 class="mb-3">Permissions</h5>
                        <input type="text" id="permissionSearch" class="lux-input form-control mb-3"
                            placeholder="🔍 Cari permission...">
                        <div id="permissionList" style="max-height: 300px; overflow-y: auto;">
                            @foreach ($permissionGroups as $group => $permissions)
                                <div class="permission-group mb-3" data-group="{{ strtolower($group) }}">
                                    <div
                                        class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                        <strong>{{ ucfirst($group) }}</strong>
                                        <label class="form-check mb-0">
                                            <input type="checkbox" class="select-all-group"
                                                data-group="{{ strtolower($group) }}">
                                            <small>Pilih Semua</small>
                                        </label>
                                    </div>
                                    <div class="row">
                                        @foreach ($permissions as $permission)
                                            <div class="col-md-6 mb-1">
                                                <label class="form-check permission-item"
                                                    data-group="{{ strtolower($group) }}">
                                                    <input type="checkbox" name="permissions[]"
                                                        value="{{ $permission->id }}" class="permission-checkbox"
                                                        data-group="{{ strtolower($group) }}">
                                                    {{ $permission->display_name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="formErrors" class="alert alert-danger d-none"></div>
                    </div>
                    <div class="modal-footer lux-modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-primary" id="btnSaveRole">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDelete" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content lux-card">
                <form id="formDelete">
                    @csrf @method('DELETE')
                    <input type="hidden" name="role_id" id="deleteRoleId">
                    <div class="modal-header lux-modal-header">
                        <h3 class="modal-title lux-modal-title text-danger">Konfirmasi Hapus</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body lux-modal-body">
                        <p>Yakin menghapus role <strong id="deleteRoleName"></strong>?</p>
                        <div class="alert alert-danger">
                            <i class="ri-error-warning-line"></i> <strong>Peringatan:</strong> Semua user dengan role ini
                            akan kehilangan hak akses terkait.
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmDelete">
                            <label class="form-check-label" for="confirmDelete">Saya mengerti dan ingin
                                melanjutkan</label>
                        </div>
                    </div>
                    <div class="modal-footer lux-modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-danger" id="btnConfirmDelete" disabled>Ya,
                            Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <input type="hidden" id="baseUrlStore" value="{{ route('admin.roles.store') }}">
    <input type="hidden" id="baseUrlUpdate" value="{{ route('admin.roles.update', ['role' => '__ID__']) }}">
    <input type="hidden" id="baseUrlDelete" value="{{ route('admin.roles.destroy', ['role' => '__ID__']) }}">
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalRole = new bootstrap.Modal(document.getElementById('modalRole'));
            const modalDelete = new bootstrap.Modal(document.getElementById('modalDelete'));
            const formRole = document.getElementById('formRole');
            const formDelete = document.getElementById('formDelete');
            const btnSave = document.getElementById('btnSaveRole');
            const btnConfirmDelete = document.getElementById('btnConfirmDelete');
            const confirmDelete = document.getElementById('confirmDelete');
            const currentToken = '{{ request()->attributes->get('page_token') ?? '' }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let isEditMode = false;

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
                document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
                updateSelectAllStates();
                modalRole.show();
            });

            document.querySelectorAll('.btn-edit-trigger').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('modalTitle').textContent = 'Edit Role';
                    document.getElementById('roleIdField').value = this.dataset.id || '';
                    isEditMode = true;
                    document.getElementById('inputName').value = this.dataset.name || '';
                    document.getElementById('inputName').readOnly = true;
                    document.getElementById('inputDisplayName').value = this.dataset.displayName ||
                        '';
                    document.getElementById('inputDescription').value = this.dataset.description ||
                        '';
                    document.getElementById('inputActive').checked = this.dataset.isActive === '1';
                    if (document.getElementById('inputBranchId')) document.getElementById(
                        'inputBranchId').value = this.dataset.branchId || '';
                    const perms = JSON.parse(this.dataset.permissions || '[]');
                    document.querySelectorAll('.permission-checkbox').forEach(cb => {
                        cb.checked = perms.includes(parseInt(cb.value));
                        updatePermissionItemStyle(cb);
                    });
                    updateSelectAllStates();
                    document.getElementById('formErrors')?.classList.add('d-none');
                    modalRole.show();
                });
            });

            document.querySelectorAll('.btn-delete-trigger').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('deleteRoleId').value = this.dataset.id || '';
                    document.getElementById('deleteRoleName').textContent = this.dataset.name || '';
                    confirmDelete.checked = false;
                    btnConfirmDelete.disabled = true;
                    modalDelete.show();
                });
            });

            confirmDelete?.addEventListener('change', function() {
                btnConfirmDelete.disabled = !this.checked;
            });

            document.getElementById('modalRole')?.addEventListener('hidden.bs.modal', resetForm);

            formRole?.addEventListener('submit', async function(e) {
                e.preventDefault();
                const roleId = document.getElementById('roleIdField').value;
                let baseUrl = isEditMode ? document.getElementById('baseUrlUpdate').value.replace(
                    '__ID__', roleId) : document.getElementById('baseUrlStore').value;
                const separator = baseUrl.includes('?') ? '&' : '?';
                const url =
                    `${baseUrl}${separator}_token_nav=${currentToken}${isEditMode ? '&_method=PUT' : ''}`;
                btnSave.disabled = true;
                btnSave.innerHTML =
                    '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
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
                        modalRole.hide();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        if (data.errors) {
                            let html = '<strong>Validasi gagal:</strong><ul class="mb-0">';
                            for (let f in data.errors) {
                                (Array.isArray(data.errors[f]) ? data.errors[f] : [data.errors[f]])
                                .forEach(m => {
                                    html += `<li>${m}</li>`;
                                });
                            }
                            html += '</ul>';
                            const errorBox = document.getElementById('formErrors');
                            if (errorBox) {
                                errorBox.innerHTML = html;
                                errorBox.classList.remove('d-none');
                                errorBox.scrollIntoView({
                                    behavior: 'smooth'
                                });
                            }
                        } else {
                            showAlert('error', data.message || 'Gagal menyimpan role');
                        }
                    }
                } catch (err) {
                    showAlert('error', 'Terjadi kesalahan koneksi');
                } finally {
                    btnSave.disabled = false;
                    btnSave.textContent = 'Simpan';
                }
            });

            formDelete?.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (!confirmDelete.checked) return;
                const id = document.getElementById('deleteRoleId').value;
                let baseUrl = document.getElementById('baseUrlDelete').value.replace('__ID__', id);
                const separator = baseUrl.includes('?') ? '&' : '?';
                const url = `${baseUrl}${separator}_token_nav=${currentToken}`;
                btnConfirmDelete.disabled = true;
                btnConfirmDelete.innerHTML =
                    '<span class="spinner-border spinner-border-sm"></span> Menghapus...';
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
                        modalDelete.hide();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert('error', data.message || 'Gagal menghapus role');
                        btnConfirmDelete.disabled = false;
                        btnConfirmDelete.textContent = 'Ya, Hapus';
                    }
                } catch (err) {
                    showAlert('error', 'Terjadi kesalahan koneksi');
                    btnConfirmDelete.disabled = false;
                    btnConfirmDelete.textContent = 'Ya, Hapus';
                }
            });

            document.getElementById('permissionSearch')?.addEventListener('keyup', function(e) {
                const keyword = e.target.value.toLowerCase();
                document.querySelectorAll('.permission-item').forEach(item => {
                    const text = item.textContent.toLowerCase();
                    const cb = item.querySelector('.permission-checkbox');
                    const group = item.closest('.permission-group');
                    if (text.includes(keyword)) {
                        item.style.display = '';
                        if (cb) cb.disabled = false;
                    } else {
                        item.style.display = 'none';
                        if (cb) cb.disabled = true;
                    }
                    if (group) {
                        const visible = Array.from(group.querySelectorAll('.permission-item')).some(
                            i => i.style.display !== 'none');
                        group.style.display = visible || keyword === '' ? '' : 'none';
                    }
                });
                updateSelectAllStates();
            });

            document.querySelectorAll('.select-all-group').forEach(chk => {
                chk.addEventListener('change', function() {
                    const grp = this.dataset.group;
                    document.querySelectorAll(`.permission-checkbox[data-group="${grp}"]`).forEach(
                        cb => {
                            if (cb.closest('.permission-item').style.display !== 'none') {
                                cb.checked = this.checked;
                                updatePermissionItemStyle(cb);
                            }
                        });
                });
            });

            document.querySelectorAll('.permission-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    updatePermissionItemStyle(this);
                    updateSelectAllState(this.dataset.group);
                });
                updatePermissionItemStyle(cb);
            });

            function resetForm() {
                formRole?.reset();
                formRole?.classList.remove('was-validated');
                document.getElementById('formErrors')?.classList.add('d-none');
                document.getElementById('inputName').readOnly = false;
            }

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

            function updatePermissionItemStyle(cb) {
                const label = cb.closest('.permission-item');
                if (!label) return;
                if (cb.checked) {
                    label.style.background = '#f0f9ff';
                    label.style.borderLeft = '3px solid var(--primary)';
                } else {
                    label.style.background = '';
                    label.style.borderLeft = '';
                }
            }

            function updateSelectAllState(group) {
                const cbs = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
                const visible = Array.from(cbs).filter(cb => cb.closest('.permission-item').style.display !==
                    'none');
                const selectAll = document.querySelector(`.select-all-group[data-group="${group}"]`);
                if (selectAll && visible.length > 0) {
                    const all = visible.every(cb => cb.checked);
                    const some = visible.some(cb => cb.checked);
                    selectAll.checked = all;
                    selectAll.indeterminate = some && !all;
                }
            }

            function updateSelectAllStates() {
                document.querySelectorAll('.select-all-group').forEach(chk => updateSelectAllState(chk.dataset
                    .group));
            }
        });
    </script>
@endpush
