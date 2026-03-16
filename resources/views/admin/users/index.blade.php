@extends('layouts.admin')
@section('title', 'Manajemen Users')

@section('content')
    <div class="lux-container">
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Manajemen Users</h1>
                <p class="text-muted">Kelola akun pengguna sistem</p>
            </div>
            @if (auth()->user()->hasPermission('users.create'))
                <button type="button" class="lux-btn lux-btn-primary" id="btn-tambah-user">
                    <i class="ri-add-line"></i> Tambah User
                </button>
            @endif
        </div>

        <div id="alertContainer" class="mb-4"></div>

        <div class="lux-card">
            <div class="lux-card-body">
                <div class="table-responsive">
                    <table class="lux-table" id="tabel-users">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Cabang</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="lux-avatar lux-avatar-sm">{{ substr($user->name, 0, 1) }}</div>
                                            <div>
                                                <p class="text-bold">{{ $user->name }}</p>
                                                <p class="text-muted small">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @foreach ($user->roles as $role)
                                            <span class="lux-badge lux-badge-primary">{{ $role->display_name }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $user->branch?->name ?? '-' }}</td>
                                    <td>
                                        @if ($user->is_active)
                                            <span class="lux-badge lux-badge-success">Aktif</span>
                                        @else
                                            <span class="lux-badge lux-badge-danger">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            @if (auth()->user()->hasPermission('users.edit'))
                                                <button class="lux-btn lux-btn-outline lux-btn-sm btn-edit"
                                                    data-id="{{ $user->id }}">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                            @endif
                                            @if (auth()->user()->hasPermission('users.delete') && $user->id !== auth()->id())
                                                <button class="lux-btn lux-btn-danger lux-btn-sm btn-delete"
                                                    data-id="{{ $user->id }}">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada data user</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $users->links() }}</div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalUserForm" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content lux-card">
                <form id="formUser">
                    @csrf
                    <input type="hidden" name="user_id" id="userId" value="">
                    <div class="modal-header lux-modal-header">
                        <h3 class="modal-title lux-modal-title" id="modalUserLabel">Tambah User Baru</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body lux-modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Nama Lengkap *</label>
                                    <input type="text" name="name" id="name" class="lux-input form-control"
                                        required maxlength="100" placeholder="Contoh: Budi Santoso">
                                </div>
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Email *</label>
                                    <input type="email" name="email" id="email" class="lux-input form-control"
                                        required maxlength="100" placeholder="email@perusahaan.com">
                                </div>
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label" id="passwordLabel">Password *</label>
                                    <input type="password" name="password" id="password" class="lux-input form-control"
                                        placeholder="••••••••" autocomplete="new-password">
                                </div>
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Konfirmasi Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="lux-input form-control" placeholder="••••••••">
                                </div>
                                <div class="lux-form-group mb-3">
                                    <label class="form-check">
                                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input"
                                            value="1" checked>
                                        <span class="form-check-label">Akun Aktif</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="lux-label mb-2">Hak Akses (Roles)</label>
                                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                    @forelse($roles as $role)
                                        <label class="form-check d-block mb-2">
                                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                                class="form-check-input me-2">
                                            <strong>{{ $role->display_name ?? $role->name }}</strong>
                                            @if ($role->description)
                                                <br><small class="text-muted">{{ $role->description }}</small>
                                            @endif
                                        </label>
                                    @empty
                                        <p class="text-muted text-center">Tidak ada role tersedia</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div id="formErrors" class="alert alert-danger d-none"></div>
                    </div>
                    <div class="modal-footer lux-modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-primary" id="btnSubmit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <input type="hidden" id="baseUrlStore" value="{{ route('admin.users.store') }}">
    <input type="hidden" id="baseUrlUpdate" value="{{ route('admin.users.update', ['user' => '__ID__']) }}">
    <input type="hidden" id="baseUrlDelete" value="{{ route('admin.users.destroy', ['user' => '__ID__']) }}">
    <input type="hidden" id="baseUrlEdit" value="{{ route('admin.users.edit', ['user' => '__ID__']) }}">
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalUserForm'));
            const formUser = document.getElementById('formUser');
            const btnSave = document.getElementById('btnSubmit');
            const currentToken = '{{ request()->attributes->get('page_token') ?? '' }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let isEditMode = false;

            document.getElementById('btn-tambah-user')?.addEventListener('click', function() {
                resetForm();
                isEditMode = false;
                document.getElementById('modalUserLabel').textContent = 'Tambah User Baru';
                document.getElementById('passwordLabel').textContent = 'Password *';
                document.getElementById('password').required = true;
                document.getElementById('password_confirmation').required = true;
                modal.show();
            });

            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    let baseUrl = document.getElementById('baseUrlEdit').value.replace('__ID__',
                    id);
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
                            document.getElementById('name').value = data.user.name || '';
                            document.getElementById('email').value = data.user.email || '';
                            document.getElementById('is_active').checked = !!data.user
                            .is_active;
                            document.getElementById('passwordLabel').textContent =
                                'Password Baru (kosongkan jika tidak ingin ubah)';
                            document.getElementById('password').required = false;
                            document.getElementById('password_confirmation').required = false;
                            document.querySelectorAll('input[name="roles[]"]').forEach(cb => cb
                                .checked = false);
                            if (data.userRoles?.length) {
                                data.userRoles.forEach(rid => {
                                    const cb = document.querySelector(
                                        `input[name="roles[]"][value="${rid}"]`);
                                    if (cb) cb.checked = true;
                                });
                            }
                            modal.show();
                        })
                        .catch(() => showAlert('error', 'Gagal memuat data user'));
                });
            });

            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Konfirmasi',
                        text: 'Hapus user ini?',
                        showCancelButton: true,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) deleteUser(id);
                    });
                });
            });

            formUser?.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (!formUser.checkValidity()) {
                    formUser.classList.add('was-validated');
                    return;
                }

                const roleId = document.getElementById('userId').value;
                let baseUrl = isEditMode ? document.getElementById('baseUrlUpdate').value.replace(
                    '__ID__', roleId) : document.getElementById('baseUrlStore').value;
                const separator = baseUrl.includes('?') ? '&' : '?';
                const url =
                    `${baseUrl}${separator}_token_nav=${currentToken}${isEditMode ? '&_method=PUT' : ''}`;

                btnSave.disabled = true;
                btnSave.innerHTML =
                    '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
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
                        modal.hide();
                        loadUsers();
                    } else {
                        if (data.errors) showErrors(data.errors);
                        else showAlert('error', data.message || 'Gagal menyimpan user');
                    }
                } catch (err) {
                    showAlert('error', 'Terjadi kesalahan koneksi');
                } finally {
                    btnSave.disabled = false;
                    btnSave.textContent = 'Simpan';
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
                        if (data.html) document.querySelector('#tabel-users tbody').innerHTML = data.html;
                        bindEvents();
                    })
                    .catch(() => showAlert('error', 'Gagal memuat data'));
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
                        } else showAlert('error', data.message || 'Gagal menghapus user');
                    })
                    .catch(() => showAlert('error', 'Terjadi kesalahan koneksi'));
            }

            function resetForm() {
                formUser?.reset();
                formUser?.classList.remove('was-validated');
                document.getElementById('formErrors')?.classList.add('d-none');
                document.getElementById('userId').value = '';
                document.getElementById('password').value = '';
                document.getElementById('password_confirmation').value = '';
                document.querySelectorAll('input[name="roles[]"]').forEach(cb => cb.checked = false);
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

            function showErrors(errors) {
                const errorBox = document.getElementById('formErrors');
                if (!errorBox) return;
                let html = '<strong>Validasi gagal:</strong><ul class="mb-0">';
                for (let f in errors) {
                    (Array.isArray(errors[f]) ? errors[f] : [errors[f]]).forEach(m => {
                        html += `<li>${m}</li>`;
                    });
                }
                html += '</ul>';
                errorBox.innerHTML = html;
                errorBox.classList.remove('d-none');
                errorBox.scrollIntoView({
                    behavior: 'smooth'
                });
            }

            function bindEvents() {
                document.querySelectorAll('.btn-edit').forEach(btn => {
                    btn.onclick = null;
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        let baseUrl = document.getElementById('baseUrlEdit').value.replace('__ID__',
                            id);
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
                                document.getElementById('modalUserLabel').textContent =
                                    'Edit User';
                                document.getElementById('userId').value = data.user.id;
                                document.getElementById('name').value = data.user.name || '';
                                document.getElementById('email').value = data.user.email || '';
                                document.getElementById('is_active').checked = !!data.user
                                    .is_active;
                                document.getElementById('passwordLabel').textContent =
                                    'Password Baru (kosongkan jika tidak ingin ubah)';
                                document.getElementById('password').required = false;
                                document.getElementById('password_confirmation').required =
                                    false;
                                document.querySelectorAll('input[name="roles[]"]').forEach(cb =>
                                    cb.checked = false);
                                if (data.userRoles?.length) {
                                    data.userRoles.forEach(rid => {
                                        const cb = document.querySelector(
                                            `input[name="roles[]"][value="${rid}"]`);
                                        if (cb) cb.checked = true;
                                    });
                                }
                                modal.show();
                            })
                            .catch(() => showAlert('error', 'Gagal memuat data user'));
                    });
                });
                document.querySelectorAll('.btn-delete').forEach(btn => {
                    btn.onclick = null;
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        Swal.fire({
                            icon: 'warning',
                            title: 'Konfirmasi',
                            text: 'Hapus user ini?',
                            showCancelButton: true,
                            confirmButtonText: 'Ya',
                            cancelButtonText: 'Batal'
                        }).then(result => {
                            if (result.isConfirmed) deleteUser(id);
                        });
                    });
                });
            }

            bindEvents();
        });
    </script>
@endpush
