@extends('layouts.admin')
@section('title', 'Manajemen Permissions')

@section('content')
    <div class="lux-container">
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Manajemen Permissions</h1>
                <p class="text-muted">Kelola hak akses sistem untuk roles dan users</p>
            </div>
            @if (auth()->user()->hasPermission('permissions.create'))
                <button class="lux-btn lux-btn-primary" id="btnOpenAddModal">
                    <i class="ri-add-line"></i> Tambah Permission
                </button>
            @endif
        </div>

        <div id="alertContainer" class="mb-4"></div>
        <div id="permissionsContainer"></div>
    </div>

    <div class="modal fade" id="modalPermission" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content lux-card">
                <form id="formAddPermission">
                    @csrf
                    <div class="modal-header lux-modal-header">
                        <h3 class="modal-title lux-modal-title">Tambah Permission</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body lux-modal-body">
                        <div class="lux-form-group mb-3">
                            <label class="lux-label form-label">Nama Permission *</label>
                            <input type="text" name="name" class="lux-input form-control"
                                placeholder="contoh: users.create" required>
                            <p class="lux-form-help form-text"><i class="ri-information-line"></i> Format: group.action</p>
                        </div>
                        <div class="lux-form-group mb-3">
                            <label class="lux-label form-label">Display Name *</label>
                            <input type="text" name="display_name" class="lux-input form-control"
                                placeholder="contoh: Buat User" required>
                        </div>
                        <div class="lux-form-group mb-3">
                            <label class="lux-label form-label">Group *</label>
                            <input type="text" name="group" class="lux-input form-control"
                                placeholder="contoh: users, roles" required>
                        </div>
                        <div class="lux-form-group mb-3">
                            <label class="lux-label form-label">Deskripsi</label>
                            <textarea name="description" class="lux-textarea form-control" rows="2"></textarea>
                        </div>
                        <div id="formErrors" class="alert alert-danger d-none"></div>
                    </div>
                    <div class="modal-footer lux-modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-primary" id="btnSavePermission">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDelete" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content lux-card">
                <div class="modal-header lux-modal-header">
                    <h3 class="modal-title lux-modal-title text-danger">Konfirmasi Hapus</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body lux-modal-body">
                    <p>Yakin menghapus permission <strong id="deletePermissionName"></strong>?</p>
                    <div class="alert alert-danger"><i class="ri-alert-line"></i> Tindakan ini tidak dapat dibatalkan.</div>
                </div>
                <div class="modal-footer lux-modal-footer">
                    <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="lux-btn lux-btn-danger" id="btnConfirmDelete"><i
                            class="ri-delete-bin-line"></i> Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="baseUrlPermissionDelete"
        value="{{ route('admin.permissions.destroy', ['permission' => '__ID__']) }}">
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalPermission = new bootstrap.Modal(document.getElementById('modalPermission'));
            const modalDelete = new bootstrap.Modal(document.getElementById('modalDelete'));
            const currentToken = '{{ request()->attributes->get('page_token') ?? '' }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let currentDeleteId = null;

            const ROUTES = {
                index: "@routeToken('admin.permissions.index')",
                store: "@routeToken('admin.permissions.store')",
                delete: function(id) {
                    let baseUrl = document.getElementById('baseUrlPermissionDelete').value.replace('__ID__',
                        id);
                    const separator = baseUrl.includes('?') ? '&' : '?';
                    return baseUrl + separator + '_token_nav=' + currentToken;
                }
            };

            function ajaxGet(url, callback) {
                fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (response.redirected) {
                            window.location.href = response.url;
                            return null;
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && callback) callback(data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showError('Gagal mengambil data');
                    });
            }

            function ajaxPost(url, formData, callback) {
                if (formData instanceof FormData) {
                    formData.append('_token', csrfToken);
                } else {
                    formData._token = csrfToken;
                    formData = new URLSearchParams(formData);
                }
                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (response.redirected) {
                            window.location.href = response.url;
                            return null;
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && callback) callback(data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showError('Gagal mengirim data');
                    });
            }

            function ajaxDelete(url, callback) {
                fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (response.redirected) {
                            window.location.href = response.url;
                            return null;
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && callback) callback(data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showError('Gagal menghapus data');
                    });
            }

            function showSuccess(message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: message,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }

            function showError(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: message
                });
            }

            function showAlert(type, message) {
                const alertContainer = document.getElementById('alertContainer');
                if (!alertContainer) return;
                const bg = type === 'success' ? '#f0fdf4' : '#fef2f2';
                const border = type === 'success' ? 'var(--success)' : 'var(--danger)';
                const color = type === 'success' ? '#166534' : '#991b1b';
                const icon = type === 'success' ? 'ri-checkbox-circle-line' : 'ri-error-warning-line';
                alertContainer.innerHTML =
                    `<div class="lux-card" style="border-left:4px solid ${border};background:${bg};display:flex;align-items:center;gap:10px;padding:12px;"><i class="${icon}" style="color:${border};font-size:1.5rem;"></i><span style="color:${color};font-weight:600;">${message}</span></div>`;
                setTimeout(() => {
                    if (alertContainer.firstElementChild) alertContainer.firstElementChild.style.display =
                        'none';
                }, 5000);
            }

            function showLoading(button, loadingText = 'Menyimpan...') {
                button.disabled = true;
                button.dataset.originalText = button.innerHTML;
                button.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${loadingText}`;
            }

            function hideLoading(button) {
                button.disabled = false;
                button.innerHTML = button.dataset.originalText || 'Simpan';
            }

            function resetForm() {
                const form = document.getElementById('formAddPermission');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated');
                    document.getElementById('formErrors')?.classList.add('d-none');
                }
            }

            function showErrors(errors) {
                const errorBox = document.getElementById('formErrors');
                if (!errorBox) return;
                let html = '<strong>Validasi gagal:</strong><ul class="mb-0">';
                for (let field in errors) {
                    const msgs = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                    msgs.forEach(msg => {
                        html += `<li>${msg}</li>`;
                    });
                }
                html += '</ul>';
                errorBox.innerHTML = html;
                errorBox.classList.remove('d-none');
                errorBox.scrollIntoView({
                    behavior: 'smooth'
                });
            }

            document.getElementById('btnOpenAddModal')?.addEventListener('click', function() {
                resetForm();
                modalPermission.show();
            });
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-delete-trigger')) {
                    const button = e.target.closest('.btn-delete-trigger');
                    currentDeleteId = button.dataset.id;
                    document.getElementById('deletePermissionName').textContent = button.dataset.name;
                    modalDelete.show();
                }
            });
            document.getElementById('btnConfirmDelete')?.addEventListener('click', function() {
                if (currentDeleteId) deletePermission(currentDeleteId);
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    modalPermission.hide();
                    modalDelete.hide();
                }
            });
            document.getElementById('modalPermission')?.addEventListener('hidden.bs.modal', resetForm);

            document.getElementById('formAddPermission')?.addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const button = document.getElementById('btnSavePermission');
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }
                showLoading(button);
                ajaxPost(ROUTES.store, new FormData(form), function(response) {
                    hideLoading(button);
                    if (response?.success) {
                        showSuccess(response.message || 'Permission berhasil ditambahkan');
                        modalPermission.hide();
                        loadPermissions();
                    } else {
                        if (response?.errors) showAlert('error', 'Validasi gagal: ' + Object.values(
                            response.errors)[0][0]);
                        else showAlert('error', response?.message || 'Gagal menyimpan permission');
                    }
                });
            });

            function loadPermissions() {
                ajaxGet(ROUTES.index, function(response) {
                    if (!response?.success) return;
                    let html = '';
                    response.data.forEach(group => {
                        html +=
                            `<div class="lux-card mb-4"><div class="lux-card-header"><h3 class="lux-card-title">${group.group}</h3><span class="lux-badge lux-badge-info">${group.count} Permissions</span></div><div class="lux-grid lux-grid-4">`;
                        group.permissions.forEach(perm => {
                            const canDelete = perm.roles_count === 0;
                            const deleteBtn = canDelete ?
                                `<button class="lux-btn lux-btn-outline lux-btn-sm btn-delete-trigger" data-id="${perm.id}" data-name="${perm.display_name.replace(/"/g,'&quot;')}" style="border-color:var(--danger);color:var(--danger);"><i class="ri-delete-bin-line"></i></button>` :
                                `<button class="lux-btn lux-btn-outline lux-btn-sm" disabled title="Masih digunakan oleh ${perm.roles_count} role" style="border-color:#cbd5e1;color:#94a3b8;cursor:not-allowed;"><i class="ri-lock-line"></i></button>`;
                            html +=
                                `<div class="lux-permission-item"><div><p style="font-weight:600;">${perm.display_name}</p><p class="text-muted" style="font-size:0.8rem;font-family:monospace;">${perm.name}</p>${perm.description ? `<p class="text-muted" style="font-size:0.8rem;margin-top:4px;">${perm.description}</p>` : ''}</div><div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto;"><span class="lux-badge ${perm.roles_count > 0 ? 'lux-badge-warning' : 'lux-badge-success'}">${perm.roles_count} Roles</span>${deleteBtn}</div></div>`;
                        });
                        html += `</div></div>`;
                    });
                    document.getElementById('permissionsContainer').innerHTML = html;
                });
            }

            function deletePermission(id) {
                const button = document.getElementById('btnConfirmDelete');
                showLoading(button, 'Menghapus...');
                ajaxDelete(ROUTES.delete(id), function(response) {
                    hideLoading(button);
                    if (response?.success) {
                        showSuccess(response.message || 'Permission berhasil dihapus');
                        modalDelete.hide();
                        loadPermissions();
                        currentDeleteId = null;
                    } else {
                        showAlert('error', response?.message || 'Gagal menghapus permission');
                    }
                });
            }

            loadPermissions();
        });
    </script>
@endpush
