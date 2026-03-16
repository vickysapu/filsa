{{-- resources/views/admin/branches/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Manajemen Cabang')

@section('content')
    <div class="lux-container">
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Manajemen Cabang</h1>
                <p class="text-muted">Kelola cabang dan kantor pusat sistem</p>
            </div>
            @if (auth()->user()->hasPermission('branches.create'))
                <button class="lux-btn lux-btn-primary" id="btnOpenCreateModal">
                    <i class="ri-add-line"></i> Tambah Cabang
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
                                <th>Nama Cabang</th>
                                <th>Kode</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th>Data Terkait</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($branches as $branch)
                                <tr>
                                    <td>
                                        <strong>{{ $branch->name }}</strong>
                                        @if ($branch->address)
                                            <br><small class="text-muted">{{ $branch->address }}</small>
                                        @endif
                                    </td>
                                    <td><code>{{ $branch->code }}</code></td>
                                    <td>
                                        @if ($branch->is_headquarter)
                                            <span class="lux-badge lux-badge-primary">Kantor Pusat</span>
                                        @else
                                            <span class="lux-badge lux-badge-secondary">Cabang</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($branch->is_active)
                                            <span class="lux-badge lux-badge-success">Aktif</span>
                                        @else
                                            <span class="lux-badge lux-badge-danger">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td><small>👥 {{ $branch->users_count }} Users</small></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            @if (!$branch->is_headquarter)
                                                <button type="button"
                                                    class="lux-btn lux-btn-outline lux-btn-sm btn-toggle-active"
                                                    data-id="{{ $branch->id }}"
                                                    data-active="{{ $branch->is_active ? 1 : 0 }}"
                                                    title="{{ $branch->is_active ? 'Nonaktifkan' : 'Aktifkan' }} cabang">

                                                    <i
                                                        class="ri-{{ $branch->is_active ? 'toggle-fill' : 'toggle-line' }}"></i>

                                                </button>

                                                <button type="button"
                                                    class="lux-btn lux-btn-danger lux-btn-sm btn-delete-trigger"
                                                    data-id="{{ $branch->id }}" data-name="{{ $branch->name }}">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            @else
                                                <span class="lux-badge lux-badge-primary" title="Kantor Pusat">
                                                    <i class="ri-lock-line"></i> Pusat
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $branches->links() }}</div>
            </div>
        </div>
    </div>

    {{-- Modal Create/Edit --}}
    <div class="modal fade" id="modalBranch" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content lux-card">
                <form id="formBranch">
                    @csrf
                    <input type="hidden" name="_method" id="methodField" value="POST">
                    <input type="hidden" name="branch_id" id="branchIdField">
                    <div class="modal-header lux-modal-header">
                        <h3 class="modal-title lux-modal-title" id="modalTitle">Tambah Cabang</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body lux-modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Nama Cabang *</label>
                                    <input type="text" name="name" id="inputName" class="lux-input form-control"
                                        required maxlength="100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="lux-form-group mb-3">
                                    <label class="lux-label">Kode Cabang *</label>
                                    <input type="text" name="code" id="inputCode" class="lux-input form-control"
                                        required maxlength="10" placeholder="Contoh: JKT">
                                </div>
                            </div>
                        </div>
                        <div class="lux-form-group mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" id="inputActive" class="form-check-input"
                                    value="1" checked>
                                <span class="form-check-label">Aktifkan cabang ini</span>
                            </label>
                        </div>
                        <div id="formErrors" class="alert alert-danger d-none"></div>
                    </div>
                    <div class="modal-footer lux-modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-primary" id="btnSaveBranch">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Delete --}}
    <div class="modal fade" id="modalDelete" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content lux-card">
                <form id="formDelete">
                    @csrf @method('DELETE')
                    <input type="hidden" name="branch_id" id="deleteBranchId">
                    <div class="modal-header lux-modal-header">
                        <h3 class="modal-title lux-modal-title text-danger">Konfirmasi Hapus</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body lux-modal-body">
                        <p>Yakin menghapus cabang <strong id="deleteBranchName"></strong>?</p>
                        <div class="alert alert-danger">
                            <i class="ri-error-warning-line"></i> <strong>Peringatan:</strong> Semua data di cabang ini
                            akan terhapus permanen.
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

    <input type="hidden" id="baseUrlStore" value="{{ route('admin.branches.store') }}">
    <input type="hidden" id="baseUrlUpdate" value="{{ route('admin.branches.update', ['branch' => '__ID__']) }}">
    <input type="hidden" id="baseUrlDelete" value="{{ route('admin.branches.destroy', ['branch' => '__ID__']) }}">
    <input type="hidden" id="baseUrlToggle" value="{{ route('admin.branches.toggle', ['branch' => '__ID__']) }}">
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalBranch = new bootstrap.Modal(document.getElementById('modalBranch'));
            const modalDelete = new bootstrap.Modal(document.getElementById('modalDelete'));
            const formBranch = document.getElementById('formBranch');
            const formDelete = document.getElementById('formDelete');
            const btnSave = document.getElementById('btnSaveBranch');
            const btnConfirmDelete = document.getElementById('btnConfirmDelete');
            const confirmDelete = document.getElementById('confirmDelete');
            const currentToken = '{{ request()->attributes->get('page_token') }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            document.getElementById('btnOpenCreateModal')?.addEventListener('click', function() {
                resetForm();
                document.getElementById('modalTitle').textContent = 'Tambah Cabang';
                document.getElementById('methodField').value = 'POST';
                document.getElementById('branchIdField').value = '';
                document.getElementById('inputActive').checked = true;
                modalBranch.show();
            });

            document.querySelectorAll('.btn-delete-trigger').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('deleteBranchId').value = this.dataset.id;
                    document.getElementById('deleteBranchName').textContent = this.dataset.name;
                    confirmDelete.checked = false;
                    btnConfirmDelete.disabled = true;
                    modalDelete.show();
                });
            });

            document.querySelectorAll('.btn-toggle-active').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const id = this.dataset.id;
                    
                    let baseUrl = document.getElementById('baseUrlToggle').value.replace('__ID__', id);
                    
                    const separator = baseUrl.includes('?') ? '&' : '?';
                    const url = `${baseUrl}${separator}_token_nav=${currentToken}`;
                    
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: { 
                                'X-CSRF-TOKEN': csrfToken, 
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (response.redirected) {
                            window.location.href = response.url;
                            return;
                        }
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            const row = this.closest('tr');
                            const badge = row?.querySelector('td:nth-child(4) .lux-badge');
                            const icon = this.querySelector('i');
                            
                            if (data.is_active) {
                                badge?.classList.remove('lux-badge-danger');
                                badge?.classList.add('lux-badge-success');
                                badge.textContent = 'Aktif';
                                
                                icon?.classList.remove('ri-toggle-line');
                                icon?.classList.add('ri-toggle-fill');
                                this.title = 'Nonaktifkan cabang';
                            } else {
                                badge?.classList.remove('lux-badge-success');
                                badge?.classList.add('lux-badge-danger');
                                badge.textContent = 'Nonaktif';
                                
                                icon?.classList.remove('ri-toggle-fill');
                                icon?.classList.add('ri-toggle-line');
                                this.title = 'Aktifkan cabang';
                            }
                            
                            this.dataset.active = data.is_active ? '1' : '0';
                            
                            showAlert('success', data.message);
                        } else {
                            showAlert('error', data.message || 'Gagal mengubah status cabang');
                        }
                    } catch(error) {
                        console.error('Error:', error);
                        showAlert('error', 'Terjadi kesalahan saat mengubah status cabang');
                    }
                });
            });

            confirmDelete?.addEventListener('change', function() {
                btnConfirmDelete.disabled = !this.checked;
            });

            document.getElementById('modalBranch')?.addEventListener('hidden.bs.modal', resetForm);

            formBranch?.addEventListener('submit', async function(e) {
                e.preventDefault();
                const isEdit = document.getElementById('methodField').value === 'PUT';
                const branchId = document.getElementById('branchIdField').value;

                let baseUrl = isEdit ?
                    document.getElementById('baseUrlUpdate').value.replace('__ID__', branchId) :
                    document.getElementById('baseUrlStore').value;

                const url = baseUrl + (baseUrl.includes('?') ? '&' : '?') + '_token_nav=' +
                    currentToken;

                btnSave.disabled = true;
                btnSave.innerHTML =
                    '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

                const formData = new FormData(formBranch);

                try {
                    const response = await fetch(url, {
                        method: isEdit ? 'PUT' : 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    const data = await response.json();

                    if (data.success) {
                        showAlert('success', data.message);
                        modalBranch.hide();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showErrors(data.errors || {
                            form: [data.message || 'Gagal menyimpan']
                        });
                    }
                } catch (e) {
                    showAlert('error', 'Terjadi kesalahan koneksi');
                } finally {
                    btnSave.disabled = false;
                    btnSave.textContent = 'Simpan';
                }
            });

            formDelete?.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (!confirmDelete.checked) return;

                const id = document.getElementById('deleteBranchId').value;
                let baseUrl = document.getElementById('baseUrlDelete').value.replace('__ID__', id);
                const url = baseUrl + (baseUrl.includes('?') ? '&' : '?') + '_token_nav=' +
                    currentToken;

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
                    const data = await response.json();

                    if (data.success) {
                        showAlert('success', data.message);
                        modalDelete.hide();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert('error', data.message || 'Gagal menghapus');
                        btnConfirmDelete.disabled = false;
                        btnConfirmDelete.textContent = 'Ya, Hapus';
                    }
                } catch (e) {
                    showAlert('error', 'Terjadi kesalahan koneksi');
                    btnConfirmDelete.disabled = false;
                    btnConfirmDelete.textContent = 'Ya, Hapus';
                }
            });

            function resetForm() {
                formBranch?.reset();
                formBranch?.classList.remove('was-validated');
                document.getElementById('formErrors')?.classList.add('d-none');
            }

            function showAlert(type, message) {
                const container = document.getElementById('alertContainer');
                if (!container) return;
                const bg = type === 'success' ? '#f0fdf4' : '#fef2f2';
                const border = type === 'success' ? 'var(--success)' : 'var(--danger)';
                const color = type === 'success' ? '#166534' : '#991b1b';
                const icon = type === 'success' ? 'ri-checkbox-circle-line' : 'ri-error-warning-line';
                container.innerHTML = `<div class="lux-card" style="border-left:4px solid ${border};background:${bg};display:flex;align-items:center;gap:10px;padding:12px;">
                        <i class="${icon}" style="color:${border};font-size:1.5rem;"></i>
                        <span style="color:${color};font-weight:600;">${message}</span>
                    </div>`;
                setTimeout(() => {
                    if (container.firstElementChild) container.firstElementChild.style.display = 'none';
                }, 5000);
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
        });
    </script>
@endpush
