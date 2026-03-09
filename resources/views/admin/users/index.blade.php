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
    <script>
        $(document).ready(function () {

            const modal = new bootstrap.Modal(document.getElementById('modalUserForm'));
            const form = $('#formUser');
            const tableBody = $('#tabel-users tbody');

            // Load data awal
            loadUsers();

            // Tombol Tambah
            $('#btn-tambah-user').click(function () {
                resetForm();
                $('#modalUserLabel').text('Tambah User Baru');
                $('#passwordLabel').text('Password *');
                $('#password, #password_confirmation').prop('required', true);
                modal.show();
            });

            // Submit form (tambah / edit)
            form.on('submit', function (e) {
                e.preventDefault();
                const url = form.attr('action') || '{{ route("admin.users.store") }}';
                const method = $('#formMethod').val();

                $.ajax({
                    url: url,
                    method: method,
                    data: form.serialize(),
                    success: function (response) {
                        if (response.success) {
                            alert(response.message || 'Operasi berhasil');
                            modal.hide();
                            loadUsers();
                        }
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        let errorHtml = '';
                        $.each(errors, function (key, val) {
                            errorHtml += `<li>${val[0]}</li>`;
                        });
                        $('#errorList').html(errorHtml);
                        $('#formErrors').show();
                    }
                });
            });

            // Event delegation untuk tombol edit & delete
            tableBody.on('click', '.btn-edit', function () {
                const id = $(this).data('id');
                editUser(id);
            });

            tableBody.on('click', '.btn-delete', function () {
                if (!confirm('Hapus user ini? Aksi ini tidak dapat dibatalkan.')) return;

                const id = $(this).data('id');
                $.ajax({
                    url: '{{ route("admin.users.destroy", ":id") }}'.replace(':id', id),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function (response) {
                        if (response.success) {
                            alert(response.message || 'User dihapus');
                            loadUsers();
                        }
                    },
                    error: function () {
                        alert('Gagal menghapus user');
                    }
                });
            });

            function loadUsers() {
                $.get('{{ route("admin.users.index") }}?ajax=1', function (data) {
                    tableBody.html(data.html);
                });
            }

            function editUser(id) {
                $.get('{{ route("admin.users.edit", ":id") }}'.replace(':id', id) + '?ajax=1', function (data) {
                    resetForm();

                    $('#modalUserLabel').text('Edit User');
                    $('#formMethod').val('PUT');
                    $('#formUser').attr('action', data.action);
                    $('#userId').val(data.user.id);
                    $('#name').val(data.user.name);
                    $('#email').val(data.user.email);
                    $('#is_active').prop('checked', data.user.is_active);

                    $('#passwordLabel').text('Password Baru (kosongkan jika tidak ingin ubah)');
                    $('#password, #password_confirmation').prop('required', false);

                    // Set roles
                    $('input[name="roles[]"]').prop('checked', false);
                    if (data.userRoles) {
                        data.userRoles.forEach(roleId => {
                            $(`input[name="roles[]"][value="${roleId}"]`).prop('checked', true);
                        });
                    }

                    modal.show();
                });
            }

            function resetForm() {
                form[0].reset();
                $('#formMethod').val('POST');
                $('#formUser').removeAttr('action');
                $('#userId').val('');
                $('#formErrors').hide();
                $('#errorList').empty();
            }

        });
    </script>
@endpush