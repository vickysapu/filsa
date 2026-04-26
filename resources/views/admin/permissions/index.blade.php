@extends('layouts.admin')

@section('title', 'Manajemen Permissions')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Manajemen Permissions</h1>
                <p class="text-muted-custom mb-0">Kelola hak akses sistem untuk roles dan users</p>
            </div>
            @if (auth()->user()->hasPermission('permissions.create'))
                <button type="button" class="btn btn-custom" id="btnOpenAddModal">
                    <i class="ri-add-line me-1"></i> Tambah Permission
                </button>
            @endif
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="mb-4"></div>

        <!-- Permissions Container (Loaded via AJAX) -->
        <div id="permissionsContainer">
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="text-muted-custom mb-0">Memuat permissions...</p>
            </div>
        </div>

    </div>

    <!-- Modal Add Permission -->
    <div class="modal fade" id="modalPermission" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="formAddPermission">
                    @csrf

                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold">Tambah Permission</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="alert alert-danger d-none" id="formErrors">
                            <i class="ri-error-warning-line me-2"></i>
                            <ul class="mb-0 small" id="errorList"></ul>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Nama Permission <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-custom"
                                placeholder="contoh: users.create" required pattern="^[a-z]+\.[a-z_]+$"
                                title="Format: group.action (contoh: users.create)">
                            <small class="text-muted-custom"><i class="ri-information-line me-1"></i>Format: group.action
                                (huruf kecil, titik pemisah)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Display Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="display_name" class="form-control form-control-custom"
                                placeholder="contoh: Buat User" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Group <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="group" class="form-control form-control-custom"
                                placeholder="contoh: users, roles, finance" required>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-semibold text-muted-custom">Deskripsi</label>
                            <textarea name="description" class="form-control form-control-custom textarea-custom" rows="2"
                                placeholder="Jelaskan fungsi permission ini..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSavePermission">
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
                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold text-danger">
                        <i class="ri-delete-bin-line me-2"></i>Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="icon-delete mb-3">
                        <i class="ri-delete-bin-6-line"></i>
                    </div>
                    <h6 class="fw-semibold mb-2">Yakin menghapus permission ini?</h6>
                    <p id="deletePermissionName" class="fw-bold text-primary mb-3"></p>
                    <div class="alert-custom error">
                        <i class="ri-alert-line"></i>
                        <span class="small">Tindakan ini tidak dapat dibatalkan.</span>
                    </div>
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

    <!-- Hidden Base URL for Delete -->
    <input type="hidden" id="baseUrlPermissionDelete"
        value="{{ route('admin.permissions.destroy', ['permission' => '__ID__']) }}">

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

        /* ===== PERMISSION GROUP CARD ===== */
        .permission-group-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .permission-group-header {
            padding: 1rem 1.25rem;
            background: var(--bg-body);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .permission-group-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            text-transform: capitalize;
        }

        .permission-count-badge {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.3);
            padding: 0.35em 0.65em;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        /* ===== PERMISSION ITEM ===== */
        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
            padding: 1.25rem;
        }

        .permission-item {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 1rem;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .permission-item:hover {
            border-color: var(--accent-blue);
            box-shadow: var(--shadow-sm);
            transform: translateY(-2px);
        }

        .permission-display-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-primary);
            margin: 0;
        }

        .permission-name {
            font-family: ui-monospace, monospace;
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin: 0;
            background: var(--bg-body);
            padding: 0.2em 0.4em;
            border-radius: 4px;
            display: inline-block;
        }

        .permission-description {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin: 0;
            line-height: 1.4;
        }

        .permission-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 0.75rem;
            border-top: 1px solid var(--border-color);
        }

        .permission-roles-badge {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.25em 0.5em;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.7rem;
        }

        .permission-roles-badge.used {
            background: rgba(245, 158, 11, 0.15);
            color: #b45309;
            border-color: rgba(245, 158, 11, 0.3);
        }

        /* ===== ACTION BUTTONS ===== */
        .btn-action {
            width: 28px;
            height: 28px;
            border-radius: var(--radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-primary);
            transition: var(--transition);
            font-size: 0.9rem;
            padding: 0;
        }

        .btn-action:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .btn-action-danger {
            border-color: #ef4444;
            color: #ef4444;
        }

        .btn-action-danger:hover:not(:disabled) {
            background: #ef4444;
            color: white;
        }

        .btn-action:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            border-color: var(--border-color);
            color: var(--text-secondary);
        }

        /* ===== FORM ELEMENTS ===== */
        .form-control-custom {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.625rem 0.875rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .form-control-custom:focus {
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

        /* ===== EMPTY STATE ===== */
        .empty-state-card {
            background: var(--bg-surface);
            border: 1px dashed var(--border-color);
            border-radius: var(--radius);
            padding: 3rem 1.5rem;
            text-align: center;
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

        /* ===== DARK MODE ===== */
        [data-theme="dark"] .permission-group-header {
            background: var(--bg-sidebar);
        }

        [data-theme="dark"] .permission-item {
            background: var(--bg-surface);
        }

        [data-theme="dark"] .permission-name {
            background: var(--bg-body);
        }

        [data-theme="dark"] .empty-state-card {
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
            .permissions-grid {
                grid-template-columns: 1fr;
            }

            .permission-footer {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }

            .permission-footer .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== MODAL INSTANCES =====
            let modalPermission, modalDelete;
            const modalPermissionEl = document.getElementById('modalPermission');
            const modalDeleteEl = document.getElementById('modalDelete');

            if (modalPermissionEl && bootstrap.Modal) modalPermission = new bootstrap.Modal(modalPermissionEl);
            if (modalDeleteEl && bootstrap.Modal) modalDelete = new bootstrap.Modal(modalDeleteEl);

            // ===== CONSTANTS =====
            const currentToken = '{{ request()->attributes->get('page_token') ?? '' }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let currentDeleteId = null;

            // ===== ROUTES =====
            const ROUTES = {
                index: "{{ route('admin.permissions.index') }}?ajax=1&_token_nav=" + currentToken,
                store: "{{ route('admin.permissions.store') }}?_token_nav=" + currentToken,
                delete: function(id) {
                    let baseUrl = document.getElementById('baseUrlPermissionDelete').value.replace('__ID__',
                        id);
                    const separator = baseUrl.includes('?') ? '&' : '?';
                    return baseUrl + separator + '_token_nav=' + currentToken;
                }
            };

            // ===== AJAX HELPERS =====
            function ajaxGet(url, callback) {
                fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
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
                        showAlert('error', 'Gagal mengambil data');
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
                        showAlert('error', 'Gagal mengirim data');
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
                        showAlert('error', 'Gagal menghapus data');
                    });
            }

            // ===== ALERT FUNCTIONS =====
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

            function showSuccess(message) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: message,
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    showAlert('success', message);
                }
            }

            function showError(message) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: message
                    });
                } else {
                    showAlert('error', message);
                }
            }

            // ===== LOADING STATES =====
            function showLoading(button, loadingText = 'Menyimpan...') {
                button.disabled = true;
                button.dataset.originalText = button.innerHTML;
                button.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${loadingText}`;
            }

            function hideLoading(button) {
                button.disabled = false;
                button.innerHTML = button.dataset.originalText || 'Simpan';
            }

            // ===== FORM FUNCTIONS =====
            function resetForm() {
                const form = document.getElementById('formAddPermission');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated');
                }
                const errorBox = document.getElementById('formErrors');
                if (errorBox) errorBox.classList.add('d-none');
            }

            function showErrors(errors) {
                const errorBox = document.getElementById('formErrors');
                const errorList = document.getElementById('errorList');
                if (!errorBox || !errorList) return;

                let html = '';
                for (let field in errors) {
                    const msgs = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                    msgs.forEach(msg => {
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

            // ===== LOAD PERMISSIONS =====
            function loadPermissions() {
                const container = document.getElementById('permissionsContainer');
                if (container) {
                    container.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="text-muted-custom mb-0">Memuat permissions...</p>
                </div>`;
                }

                ajaxGet(ROUTES.index, function(response) {
                    if (!response?.success) {
                        if (container) {
                            container.innerHTML = `
                        <div class="empty-state-card">
                            <div class="empty-state-icon mb-3">
                                <i class="ri-shield-line"></i>
                            </div>
                            <h6 class="fw-semibold mb-2">Gagal memuat permissions</h6>
                            <p class="text-muted-custom small mb-0">Silakan refresh halaman atau coba lagi nanti</p>
                        </div>`;
                        }
                        return;
                    }

                    if (!response.data || response.data.length === 0) {
                        if (container) {
                            container.innerHTML = `
                        <div class="empty-state-card">
                            <div class="empty-state-icon mb-3">
                                <i class="ri-shield-off-line"></i>
                            </div>
                            <h6 class="fw-semibold mb-2">Belum ada permissions</h6>
                            <p class="text-muted-custom small mb-4">Mulai tambahkan permission pertama untuk sistem</p>
                            @if (auth()->user()->hasPermission('permissions.create'))
                                <button type="button" class="btn btn-custom" id="btnOpenAddModal2">
                                    <i class="ri-add-line me-1"></i> Tambah Permission
                                </button>
                            @endif
                        </div>`;
                            // Re-bind button if empty state shown
                            document.getElementById('btnOpenAddModal2')?.addEventListener('click',
                            function() {
                                resetForm();
                                if (modalPermission) modalPermission.show();
                            });
                        }
                        return;
                    }

                    let html = '';
                    response.data.forEach(group => {
                        html += `
                    <div class="permission-group-card">
                        <div class="permission-group-header">
                            <h3 class="permission-group-title">${group.group}</h3>
                            <span class="permission-count-badge">${group.count} Permissions</span>
                        </div>
                        <div class="permissions-grid">`;

                        group.permissions.forEach(perm => {
                            const canDelete = perm.roles_count === 0;
                            const deleteBtn = canDelete ?
                                `<button type="button" class="btn-action btn-action-danger btn-delete-trigger" 
                                  data-id="${perm.id}" data-name="${perm.display_name.replace(/"/g, '&quot;')}" 
                                  title="Hapus permission">
                              <i class="ri-delete-bin-line"></i>
                          </button>` :
                                `<button type="button" class="btn-action" disabled 
                                  title="Masih digunakan oleh ${perm.roles_count} role">
                              <i class="ri-lock-line"></i>
                          </button>`;

                            const rolesBadgeClass = perm.roles_count > 0 ?
                                'permission-roles-badge used' : 'permission-roles-badge';

                            html += `
                        <div class="permission-item">
                            <div>
                                <p class="permission-display-name">${perm.display_name}</p>
                                <code class="permission-name">${perm.name}</code>
                                ${perm.description ? `<p class="permission-description mt-2">${perm.description}</p>` : ''}
                            </div>
                            <div class="permission-footer">
                                <span class="${rolesBadgeClass}">${perm.roles_count} Roles</span>
                                ${deleteBtn}
                            </div>
                        </div>`;
                        });

                        html += `</div></div>`;
                    });

                    if (container) container.innerHTML = html;
                });
            }

            // ===== DELETE PERMISSION =====
            function deletePermission(id) {
                const button = document.getElementById('btnConfirmDelete');
                if (button) showLoading(button, 'Menghapus...');

                ajaxDelete(ROUTES.delete(id), function(response) {
                    if (button) hideLoading(button);

                    if (response?.success) {
                        showSuccess(response.message || 'Permission berhasil dihapus');
                        if (modalDelete) modalDelete.hide();
                        loadPermissions();
                        currentDeleteId = null;
                    } else {
                        showAlert('error', response?.message || 'Gagal menghapus permission');
                    }
                });
            }

            // ===== EVENT LISTENERS =====

            // Open Add Modal
            document.getElementById('btnOpenAddModal')?.addEventListener('click', function() {
                resetForm();
                if (modalPermission) modalPermission.show();
            });

            // Delete Trigger (delegation)
            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.btn-delete-trigger');
                if (deleteBtn) {
                    e.preventDefault();
                    currentDeleteId = deleteBtn.dataset.id;
                    document.getElementById('deletePermissionName').textContent = deleteBtn.dataset.name;
                    if (modalDelete) modalDelete.show();
                }
            });

            // Confirm Delete
            document.getElementById('btnConfirmDelete')?.addEventListener('click', function() {
                if (currentDeleteId) {
                    deletePermission(currentDeleteId);
                }
            });

            // Form Submit
            const formAddPermission = document.getElementById('formAddPermission');
            const btnSave = document.getElementById('btnSavePermission');

            formAddPermission?.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!formAddPermission.checkValidity()) {
                    formAddPermission.classList.add('was-validated');
                    return;
                }

                if (btnSave) showLoading(btnSave);

                ajaxPost(ROUTES.store, new FormData(formAddPermission), function(response) {
                    if (btnSave) hideLoading(btnSave);

                    if (response?.success) {
                        showSuccess(response.message || 'Permission berhasil ditambahkan');
                        if (modalPermission) modalPermission.hide();
                        loadPermissions();
                    } else {
                        if (response?.errors) {
                            showErrors(response.errors);
                        } else {
                            showAlert('error', response?.message || 'Gagal menyimpan permission');
                        }
                    }
                });
            });

            // ESC Key to Close Modals
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (modalPermission?._isShown) modalPermission.hide();
                    if (modalDelete?._isShown) modalDelete.hide();
                }
            });

            // Reset Form on Modal Close
            modalPermissionEl?.addEventListener('hidden.bs.modal', resetForm);

            // Auto-close Alerts
            document.querySelectorAll('[data-auto-close]').forEach(alert => {
                const closeBtn = alert.querySelector('.alert-close');
                closeBtn?.addEventListener('click', () => alert.remove());
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

            // ===== INIT =====
            loadPermissions();
        });
    </script>
@endsection
