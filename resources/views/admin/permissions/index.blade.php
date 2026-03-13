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

        <div id="permissionsContainer">
        </div>
    </div>

    <div class="modal fade" id="modalPermission" tabindex="-1" aria-labelledby="modalPermissionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content lux-card">
                <form id="formAddPermission">
                    @csrf
                    <div class="modal-header lux-modal-header">
                        <h3 class="modal-title lux-modal-title" id="modalPermissionLabel">Tambah Permission</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    </div>
                    <div class="modal-footer lux-modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline btn btn-secondary"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-primary btn btn-primary"
                            id="btnSavePermission">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDelete" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content lux-card">
                <div class="modal-header lux-modal-header" style="border-bottom: none;">
                    <div class="d-flex align-items-center gap-3">
                        <i class="ri-error-warning-line" style="color:var(--danger);font-size:1.8rem;"></i>
                        <h3 class="modal-title lux-modal-title" id="modalDeleteLabel" style="color:var(--danger);">
                            Konfirmasi Hapus</h3>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body lux-modal-body">
                    <p>Yakin menghapus permission <strong id="deletePermissionName"></strong>?</p>
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-0">
                        <i class="ri-alert-line"></i> Tindakan ini tidak dapat dibatalkan.
                    </div>
                </div>
                <div class="modal-footer lux-modal-footer">
                    <button type="button" class="lux-btn lux-btn-outline btn btn-secondary"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="lux-btn lux-btn-danger btn btn-danger" id="btnConfirmDelete">
                        <i class="ri-delete-bin-line"></i> Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ==================== AJAX HELPER FUNCTIONS ====================
        // Fungsi-fungsi sederhana untuk request AJAX

        /**
         * Fungsi untuk request GET (ambil data)
         */
        function ajaxGet(url, callback) {
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (callback) callback(data);
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Gagal mengambil data');
            });
        }

        /**
         * Fungsi untuk request POST (kirim data)
         */
        function ajaxPost(url, formData, callback) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            // Jika formData adalah FormData object (untuk upload file)
            if (formData instanceof FormData) {
                formData.append('_token', csrfToken);
            } else {
                // Jika object biasa, tambah CSRF token
                formData._token = csrfToken;
                formData = new URLSearchParams(formData);
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (callback) callback(data);
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Gagal mengirim data');
            });
        }

        /**
         * Fungsi untuk request DELETE (hapus data)
         */
        function ajaxDelete(url, callback) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (callback) callback(data);
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Gagal menghapus data');
            });
        }

        // ==================== NOTIFICATION FUNCTIONS ====================
        // Fungsi-fungsi untuk menampilkan pesan

        /**
         * Tampilkan pesan sukses
         */
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

        /**
         * Tampilkan pesan error
         */
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: message
            });
        }

        /**
         * Tampilkan pesan konfirmasi
         */
        function showConfirm(message, onConfirm) {
            Swal.fire({
                icon: 'warning',
                title: 'Konfirmasi',
                text: message,
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed && onConfirm) {
                    onConfirm();
                }
            });
        }

        /**
         * Tampilkan error validasi dari server
         */
        function showValidationErrors(errors) {
            const errorBox = document.getElementById('formErrors');
            const errorList = document.getElementById('errorList');

            if (!errorBox || !errorList) return;

            let html = '';
            for (let field in errors) {
                const messages = errors[field];
                if (Array.isArray(messages)) {
                    html += `<li><strong>${field}:</strong> ${messages.join(', ')}</li>`;
                } else {
                    html += `<li><strong>${field}:</strong> ${messages}</li>`;
                }
            }

            errorList.innerHTML = html;
            errorBox.style.display = 'block';

            // Scroll ke error
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        /**
         * Tampilkan alert custom
         */
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            if (!alertContainer) return;

            const bg = type === 'success' ? '#f0fdf4' : '#fef2f2';
            const border = type === 'success' ? 'var(--success)' : 'var(--danger)';
            const color = type === 'success' ? '#166534' : '#991b1b';
            const icon = type === 'success' ? 'ri-checkbox-circle-line' : 'ri-error-warning-line';

            const alertHtml = `
                <div class="lux-card" style="border-left:4px solid ${border};background:${bg};display:flex;align-items:center;gap:10px;">
                    <i class="${icon}" style="color:${border};font-size:1.5rem;"></i>
                    <span style="color:${color};font-weight:600;">${message}</span>
                </div>`;

            alertContainer.innerHTML = alertHtml;

            // Auto hide after 5 seconds
            setTimeout(() => {
                if (alertContainer.firstElementChild) {
                    alertContainer.firstElementChild.style.display = 'none';
                }
            }, 5000);
        }

        // ==================== FORM FUNCTIONS ====================
        // Fungsi-fungsi untuk mengelola form

        /**
         * Tampilkan loading state pada tombol
         */
        function showLoading(button, loadingText = 'Menyimpan...') {
            button.disabled = true;
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2"></span>
                ${loadingText}
            `;
        }

        /**
         * Sembunyikan loading state dari tombol
         */
        function hideLoading(button) {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText || 'Simpan';
        }

        /**
         * Reset form ke kondisi awal
         */
        function resetForm() {
            const form = document.getElementById('formAddPermission');
            if (form) form.reset();
        }

        // ==================== MAIN APPLICATION CODE ====================
        // Kode utama aplikasi

        let currentDeleteId = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi modal
            const modalPermission = new bootstrap.Modal(document.getElementById('modalPermission'));
            const modalDelete = new bootstrap.Modal(document.getElementById('modalDelete'));

            // ==================== EVENT LISTENERS ====================

            // Tombol Tambah Permission
            document.getElementById('btnOpenAddModal').addEventListener('click', function() {
                resetForm();
                modalPermission.show();
            });

            // Tombol Hapus Permission
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-delete-trigger')) {
                    const button = e.target.closest('.btn-delete-trigger');
                    currentDeleteId = button.dataset.id;
                    const name = button.dataset.name;

                    document.getElementById('deletePermissionName').textContent = name;
                    modalDelete.show();
                }
            });

            // Konfirmasi Hapus
            document.getElementById('btnConfirmDelete').addEventListener('click', function() {
                if (!currentDeleteId) return;

                deletePermission(currentDeleteId);
            });

            // Escape key handler
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    modalPermission.hide();
                    modalDelete.hide();
                }
            });

            // Reset form when modal is hidden
            document.getElementById('modalPermission').addEventListener('hidden.bs.modal', function() {
                resetForm();
            });

            // ==================== FORM SUBMIT ====================

            document.getElementById('formAddPermission').addEventListener('submit', function(e) {
                e.preventDefault();

                const form = this;
                const button = document.getElementById('btnSavePermission');

                // Validasi form
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }

                // Tampilkan loading
                showLoading(button);

                // Buat FormData
                const formData = new FormData(form);

                // Kirim data
                ajaxPost('{{ route("admin.permissions.store") }}', formData, function(response) {
                    hideLoading(button);

                    if (response.success) {
                        showAlert('success', response.message || 'Permission berhasil ditambahkan');
                        modalPermission.hide();
                        loadPermissions();
                    } else {
                        if (response.errors) {
                            showAlert('error', 'Validasi gagal: ' + Object.values(response.errors)[0][0]);
                        } else {
                            showAlert('error', response.message || 'Gagal menyimpan permission');
                        }
                    }
                });
            });

            // ==================== FUNCTIONS ====================

            /**
             * Load daftar permissions
             */
            function loadPermissions() {
                ajaxGet('{{ route("admin.permissions.index") }}', function(response) {
                    if (!response.success) return;

                    let html = '';

                    response.data.forEach(group => {
                        html += `
                            <div class="lux-card mb-4">
                                <div class="lux-card-header">
                                    <h3 class="lux-card-title">${group.group}</h3>
                                    <span class="lux-badge lux-badge-info">${group.count} Permissions</span>
                                </div>
                                <div class="lux-grid lux-grid-4">`;

                        group.permissions.forEach(perm => {
                            const canDelete = perm.roles_count === 0;
                            const deleteBtn = canDelete ?
                                `<button class="lux-btn lux-btn-outline lux-btn-sm btn-delete-trigger"
                                    data-id="${perm.id}"
                                    data-name="${perm.display_name.replace(/"/g,'&quot;')}"
                                    style="border-color:var(--danger);color:var(--danger);">
                                    <i class="ri-delete-bin-line"></i>
                                </button>` :
                                `<button class="lux-btn lux-btn-outline lux-btn-sm" disabled
                                    title="Masih digunakan oleh ${perm.roles_count} role"
                                    style="border-color:#cbd5e1;color:#94a3b8;cursor:not-allowed;">
                                    <i class="ri-lock-line"></i>
                                </button>`;

                            html += `
                                <div class="lux-permission-item">
                                    <div>
                                        <p style="font-weight:600;">${perm.display_name}</p>
                                        <p class="text-muted" style="font-size:0.8rem;font-family:monospace;">${perm.name}</p>
                                        ${perm.description ? `<p class="text-muted" style="font-size:0.8rem;margin-top:4px;">${perm.description}</p>` : ''}
                                    </div>
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto;">
                                        <span class="lux-badge ${perm.roles_count > 0 ? 'lux-badge-warning' : 'lux-badge-success'}">
                                            ${perm.roles_count} Roles
                                        </span>
                                        ${deleteBtn}
                                    </div>
                                </div>`;
                        });

                        html += `</div></div>`;
                    });

                    document.getElementById('permissionsContainer').innerHTML = html;
                });
            }

            /**
             * Hapus permission
             */
            function deletePermission(id) {
                const url = `/admin/permissions/${id}`;
                const button = document.getElementById('btnConfirmDelete');

                showLoading(button, 'Menghapus...');

                ajaxDelete(url, function(response) {
                    hideLoading(button);

                    if (response.success) {
                        showAlert('success', response.message || 'Permission berhasil dihapus');
                        modalDelete.hide();
                        loadPermissions();
                        currentDeleteId = null;
                    } else {
                        showAlert('error', response.message || 'Gagal menghapus permission');
                    }
                });
            }

            // Load data awal
            loadPermissions();
        });
    </script>
@endpush
