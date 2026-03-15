@extends('layouts.admin')

@section('title', 'Manajemen Users')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
@endpush

@section('content')
<div class="lux-container">

    <div class="flex-between mb-5">
        <div>
            <h1 class="text-xl text-bold">Manajemen Users</h1>
            <p class="text-muted text-sm">Kelola akun pengguna sistem</p>
        </div>
        @if(auth()->user()->hasPermission('users.create'))
        <button type="button" class="lux-btn lux-btn-primary" id="btn-tambah-user">
            <i class="ri-add-line"></i> Tambah User
        </button>
        @endif
    </div>

    <div class="lux-card">
        <div class="lux-table-container">
            <table class="lux-table" id="tabel-users">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Diisi via AJAX -->
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ================== MODAL FORM (Tambah & Edit) ================== -->
<div class="modal fade" id="modalUserForm" tabindex="-1" aria-labelledby="modalUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-lg); border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">

            <div class="modal-header lux-card-header" style="border-bottom: 1px solid #e2e8f0;">
                <h5 class="modal-title" id="modalUserLabel">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formUser" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="user_id" id="userId" value="">

                <div class="modal-body lux-container">

                    @if ($errors->any())
                    <div class="alert alert-danger" id="formErrors" style="display:none;">
                        <ul class="mb-0" id="errorList"></ul>
                    </div>
                    @endif

                    <div class="lux-grid lux-grid-2 gap-4">

                        <!-- Kolom Kiri: Informasi Akun -->
                        <div class="lux-card">
                            <div class="lux-card-header">
                                <h3 class="lux-card-title">Informasi Akun</h3>
                            </div>

                            <div class="lux-form-group">
                                <label class="lux-label">Nama Lengkap *</label>
                                <input type="text" name="name" id="name" class="lux-input" placeholder="Contoh: Budi Santoso" required>
                            </div>

                            <div class="lux-form-group">
                                <label class="lux-label">Email *</label>
                                <input type="email" name="email" id="email" class="lux-input" placeholder="email@perusahaan.com" required>
                            </div>

                            <hr class="my-4" style="border-top: 1px solid #e2e8f0;">

                            <div class="lux-form-group">
                                <label class="lux-label" id="passwordLabel">Password *</label>
                                <input type="password" name="password" id="password" class="lux-input" placeholder="••••••••" autocomplete="new-password">
                            </div>

                            <div class="lux-form-group">
                                <label class="lux-label">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="lux-input" placeholder="••••••••">
                            </div>

                            <div class="lux-form-group mt-4 p-3 bg-light rounded">
                                <label class="d-flex align-items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" style="width:18px;height:18px;accent-color:var(--primary);">
                                    <span class="fw-bold">Akun Aktif</span>
                                </label>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Roles -->
                        <div class="lux-card">
                            <div class="lux-card-header">
                                <h3 class="lux-card-title">Hak Akses (Roles)</h3>
                            </div>

                            <div class="overflow-auto" style="max-height: 420px;">
                                @forelse($roles as $role)
                                <label class="lux-role-option d-flex align-items-start gap-3 p-3 mb-2 border rounded cursor-pointer">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="mt-1" style="width:18px;height:18px;accent-color:var(--primary);">
                                    <div>
                                        <div class="fw-bold">{{ $role->display_name ?? $role->name }}</div>
                                        @if($role->description)
                                        <small class="text-muted">{{ $role->description }}</small>
                                        @endif
                                    </div>
                                </label>
                                @empty
                                <p class="text-center text-muted py-5">Tidak ada role tersedia</p>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="lux-btn lux-btn-primary" id="btnSubmit">
                        <i class="ri-save-line"></i> Simpan
                    </button>
                </div>
            </form>

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
         * Fungsi untuk request PUT (update data)
         */
        function ajaxPut(url, data, callback) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            data._token = csrfToken;
            data._method = 'PUT';

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(data => {
                if (callback) callback(data);
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Gagal mengubah data');
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
            button.innerHTML = button.dataset.originalText || '<i class="ri-save-line"></i> Simpan User';
        }

        /**
         * Reset form ke kondisi awal
         */
        function resetForm(mode = 'create') {
            const form = document.getElementById('formUser');
            const errorBox = document.getElementById('formErrors');

            // Reset form
            form.reset();

            // Reset action URL
            form.action = '{{ route("admin.users.store") }}';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('userId').value = '';

            // Sembunyikan error
            if (errorBox) errorBox.style.display = 'none';

            // Set default values untuk create
            if (mode === 'create') {
                document.getElementById('modalUserLabel').textContent = 'Tambah User Baru';
                document.getElementById('passwordLabel').textContent = 'Password *';
                document.getElementById('password').required = true;
                document.getElementById('password_confirmation').required = true;
            }
        }

        /**
         * Isi form dengan data untuk edit
         */
        function populateForm(data) {
            document.getElementById('name').value = data.user.name || '';
            document.getElementById('email').value = data.user.email || '';
            document.getElementById('is_active').checked = !!data.user.is_active;

            // Set roles
            const roleCheckboxes = document.querySelectorAll('input[name="roles[]"]');
            roleCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            if (data.userRoles && Array.isArray(data.userRoles)) {
                data.userRoles.forEach(roleId => {
                    const checkbox = document.querySelector(`input[name="roles[]"][value="${roleId}"]`);
                    if (checkbox) checkbox.checked = true;
                });
            }
        }

        // ==================== MAIN APPLICATION CODE ====================
        // Kode utama aplikasi

        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi modal
            const modal = new bootstrap.Modal(document.getElementById('modalUserForm'));

            // ==================== EVENT LISTENERS ====================

            // Tombol Tambah User
            document.getElementById('btn-tambah-user').addEventListener('click', function() {
                resetForm('create');
                modal.show();
            });

            // Tombol Edit User
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-edit')) {
                    const button = e.target.closest('.btn-edit');
                    const id = button.dataset.id;
                    loadUserForEdit(id);
                }
            });

            // Tombol Hapus User
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-delete')) {
                    e.preventDefault();
                    const button = e.target.closest('.btn-delete');
                    const id = button.dataset.id;

                    showConfirm('Hapus user ini? Aksi ini tidak dapat dibatalkan.', function() {
                        deleteUser(id);
                    });
                }
            });

            // ==================== FORM SUBMIT ====================

            document.getElementById('formUser').addEventListener('submit', function(e) {
                e.preventDefault();

                const form = this;
                const button = form.querySelector('button[type="submit"]');

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
                ajaxPost(form.action, formData, function(response) {
                    hideLoading(button);

                    if (response.success) {
                        showSuccess(response.message || 'User berhasil disimpan');
                        modal.hide();
                        loadUsers();
                    } else {
                        if (response.errors) {
                            showValidationErrors(response.errors);
                        } else {
                            showError(response.message || 'Gagal menyimpan user');
                        }
                    }
                });
            });

            // ==================== FUNCTIONS ====================

            /**
             * Load daftar users
             */
            function loadUsers() {
                const url = '{{ route("admin.users.index") }}?ajax=1';

                ajaxGet(url, function(data) {
                    if (data.html) {
                        document.querySelector('#tabel-users tbody').innerHTML = data.html;
                    }
                });
            }

            /**
             * Load data user untuk edit
             */
            function loadUserForEdit(id) {
                const url = '{{ route("admin.users.edit", ":id") }}'.replace(':id', id) + '?ajax=1';

                ajaxGet(url, function(data) {
                    if (data) {
                        resetForm('edit');

                        // Set form action untuk update
                        const form = document.getElementById('formUser');
                        form.action = data.action;
                        document.getElementById('formMethod').value = 'PUT';
                        document.getElementById('userId').value = data.user.id;

                        // Isi form dengan data
                        populateForm(data);

                        // Update modal title dan password field
                        document.getElementById('modalUserLabel').textContent = 'Edit User';
                        document.getElementById('passwordLabel').textContent = 'Password Baru (kosongkan jika tidak ingin ubah)';
                        document.getElementById('password').required = false;
                        document.getElementById('password_confirmation').required = false;

                        modal.show();
                    } else {
                        showError('Gagal memuat data user');
                    }
                });
            }

            /**
             * Hapus user
             */
            function deleteUser(id) {
                const url = '{{ route("admin.users.destroy", ":id") }}'.replace(':id', id);

                ajaxDelete(url, function(response) {
                    if (response.success) {
                        showSuccess(response.message || 'User berhasil dihapus');
                        loadUsers();
                    } else {
                        showError(response.message || 'Gagal menghapus user');
                    }
                });
            }

            // Load data awal
            loadUsers();
        });
    </script>
@endpush