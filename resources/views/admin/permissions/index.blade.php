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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const DebugLog = {
            prefix: '[PERMISSIONS-AJAX]',
            log: (m, d) => console.log(`${this.prefix} ${m}`, d || ''),
            info: (m, d) => console.info(`${this.prefix} ℹ️ ${m}`, d || ''),
            warn: (m, d) => console.warn(`${this.prefix} ⚠️ ${m}`, d || ''),
            error: (m, d) => console.error(`${this.prefix} ❌ ${m}`, d || '')
        };

        let currentDeleteId = null;

        $(document).ready(function() {
            DebugLog.info('Script AJAX initialized');

            loadPermissions();

            $('#btnOpenAddModal').on('click', function() {
                $('#formAddPermission')[0].reset();
                const myModal = new bootstrap.Modal(document.getElementById('modalPermission'));
                myModal.show();
            });

            $('#formAddPermission').on('submit', function(e) {
                e.preventDefault();
                const $btn = $('#btnSavePermission');
                $btn.prop('disabled', true).text('Menyimpan...');

                $.ajax({
                    url: '{{ route('admin.permissions.store') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            showAlert('success', res.message);
                            loadPermissions();
                            bootstrap.Modal.getInstance(document.getElementById('modalPermission')).hide();
                        }
                    },
                    error: function(xhr) {
                        const resp = xhr.responseJSON || {};
                        if (xhr.status === 422 && resp.errors) {
                            showAlert('error', 'Validasi gagal: ' + Object.values(resp.errors)[
                                0][0]);
                        } else {
                            showAlert('error', resp.message || 'Gagal menyimpan permission');
                        }
                    },
                    complete: () => $btn.prop('disabled', false).text('Simpan')
                });
            });

            $(document).on('click', '.btn-delete-trigger', function() {
                currentDeleteId = $(this).data('id');
                const name = $(this).data('name');

                $('#deletePermissionName').text(name);

                const deleteModal = new bootstrap.Modal(document.getElementById('modalDelete'));
                deleteModal.show();
            });

            $('#btnConfirmDelete').on('click', function() {
                if (!currentDeleteId) return;

                const $btn = $(this);
                $btn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line animate-spin"></i> Menghapus...');

                $.ajax({
                    url: `/admin/permissions/${currentDeleteId}`,
                    method: 'DELETE',
                    success: function(res) {
                        if (res.success) {
                            showAlert('success', res.message);
                            loadPermissions();
                            bootstrap.Modal.getInstance(document.getElementById('modalDelete')).hide();
                        }
                    },
                    error: function(xhr) {
                        const resp = xhr.responseJSON || {};
                        showAlert('error', resp.message || 'Gagal menghapus permission');
                    },
                    complete: () => {
                        $btn.prop('disabled', false).html(
                            '<i class="ri-delete-bin-line"></i> Ya, Hapus');
                        currentDeleteId = null;
                    }
                });
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal('modalPermission');
                    closeModal('modalDelete');
                }
            });
        });

        function loadPermissions() {
            $.ajax({
                url: '{{ route('admin.permissions.index') }}',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (!res.success) return;

                    let html = '';
                    res.data.forEach(group => {
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

                    $('#permissionsContainer').html(html);
                },
                error: function() {
                    showAlert('error', 'Gagal memuat daftar permissions');
                }
            });
        }

        function showAlert(type, message) {
            const bg = type === 'success' ? '#f0fdf4' : '#fef2f2';
            const border = type === 'success' ? 'var(--success)' : 'var(--danger)';
            const color = type === 'success' ? '#166534' : '#991b1b';
            const icon = type === 'success' ? 'ri-checkbox-circle-line' : 'ri-error-warning-line';

            const alertHtml = `
                <div class="lux-card" style="border-left:4px solid ${border};background:${bg};display:flex;align-items:center;gap:10px;">
                    <i class="${icon}" style="color:${border};font-size:1.5rem;"></i>
                    <span style="color:${color};font-weight:600;">${message}</span>
                </div>`;

            $('#alertContainer').html(alertHtml);

            setTimeout(() => $('#alertContainer').fadeOut(400), 5000);
        }

        $('#modalPermission').on('hidden.bs.modal', function() {
            $('#formAddPermission')[0].reset();
        });
    </script>
@endpush
