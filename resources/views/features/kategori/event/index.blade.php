@extends('layouts.admin')
@section('title', 'Kategori Event')

@section('content')
    <div class="lux-container">
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Kategori Event</h1>
                <p class="text-muted">Kelola kategori event untuk website</p>
            </div>
            <button class="lux-btn lux-btn-primary" id="btnAddKategori">
                <i class="ri-add-line"></i> Tambah Kategori
            </button>
        </div>

        <div id="alertContainer" class="mb-4"></div>

        @if ($kategoris->count() > 0)
            <div class="lux-grid lux-grid-3">
                @foreach ($kategoris as $kategori)
                    <div class="lux-card" style="border-top: 4px solid {{ $kategori->warna }};">
                        <div class="flex-between mb-3">
                            <h3 class="lux-card-title" style="margin: 0;">{{ $kategori->nama_kategori }}</h3>
                            <span class="lux-badge {{ $kategori->is_active ? 'lux-badge-success' : 'lux-badge-danger' }}">
                                {{ $kategori->is_active ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </div>
                        @if ($kategori->deskripsi)
                            <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 1rem;">{{ $kategori->deskripsi }}
                            </p>
                        @endif
                        <div class="text-muted" style="font-size: 0.85rem; margin-bottom: 1rem;">
                            <i class="ri-calendar-line"></i> {{ $kategori->events->count() }} Events
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="lux-btn lux-btn-outline lux-btn-sm btn-edit-kategori"
                                data-id="{{ $kategori->id }}" data-nama="{{ e($kategori->nama_kategori) }}"
                                data-deskripsi="{{ e($kategori->deskripsi ?? '') }}" data-warna="{{ $kategori->warna }}"
                                data-is-active="{{ $kategori->is_active ? 1 : 0 }}">
                                <i class="ri-pencil-line"></i> Edit
                            </button>
                            @if ($kategori->events->count() == 0)
                                <button class="lux-btn lux-btn-outline lux-btn-sm btn-delete-kategori"
                                    data-id="{{ $kategori->id }}" data-nama="{{ e($kategori->nama_kategori) }}"
                                    style="border-color: var(--danger); color: var(--danger);">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 4rem; background: #f8fafc; border-radius: var(--radius);">
                <i class="ri-folder-off-line"
                    style="font-size: 4rem; color: var(--text-muted); opacity: 0.3; margin-bottom: 1rem;"></i>
                <h3>Belum Ada Kategori</h3>
                <p class="text-muted">Mulai dengan menambahkan kategori event pertama</p>
                <button class="lux-btn lux-btn-primary mt-3" id="btnAddKategori2">
                    <i class="ri-add-line"></i> Tambah Kategori
                </button>
            </div>
        @endif
    </div>

    <div class="modal fade" id="modalKategori" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content lux-card">
                <form id="formKategori">
                    @csrf
                    <input type="hidden" name="kategori_id" id="kategoriId">
                    <div class="modal-header lux-modal-header">
                        <h3 class="modal-title lux-modal-title" id="modalTitle">Tambah Kategori</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body lux-modal-body">
                        <div class="lux-form-group mb-3">
                            <label class="lux-label">Nama Kategori *</label>
                            <input type="text" name="nama_kategori" id="namaKategori" class="lux-input form-control"
                                required maxlength="100">
                        </div>
                        <div class="lux-form-group mb-3">
                            <label class="lux-label">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsiKategori" class="lux-textarea form-control" rows="2" maxlength="500"></textarea>
                        </div>
                        <div class="lux-form-group mb-3">
                            <label class="lux-label">Warna</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="warna" id="warnaKategori"
                                    class="form-control form-control-color" value="#4f46e5"
                                    style="width: 60px; height: 40px; padding: 0;">
                                <span id="warnaPreview" class="text-muted">#4f46e5</span>
                            </div>
                        </div>
                        <div class="lux-form-group mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" id="isActiveKategori" class="form-check-input"
                                    value="1" checked>
                                <span class="form-check-label">Aktif</span>
                            </label>
                        </div>
                        <div id="formErrors" class="alert alert-danger d-none"></div>
                    </div>
                    <div class="modal-footer lux-modal-footer">
                        <button type="button" class="lux-btn lux-btn-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="lux-btn lux-btn-primary" id="btnSubmitKategori">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <input type="hidden" id="baseUrlStore" value="{{ route('admin.event-kategori.store') }}">
    <input type="hidden" id="baseUrlUpdate"
        value="{{ route('admin.event-kategori.update', ['kategori' => '__ID__']) }}">
    <input type="hidden" id="baseUrlDelete"
        value="{{ route('admin.event-kategori.destroy', ['kategori' => '__ID__']) }}">
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalKategori'));
            const form = document.getElementById('formKategori');
            const btnSave = document.getElementById('btnSubmitKategori');
            const currentToken = '{{ request()->attributes->get('page_token') ?? '' }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let isEditMode = false;

            function openModal(mode = 'create', data = null) {
                resetForm();
                isEditMode = mode === 'edit';
                document.getElementById('modalTitle').textContent = mode === 'edit' ? 'Edit Kategori' :
                    'Tambah Kategori';
                if (mode === 'edit' && data) {
                    document.getElementById('kategoriId').value = data.id;
                    document.getElementById('namaKategori').value = data.nama || '';
                    document.getElementById('deskripsiKategori').value = data.deskripsi || '';
                    document.getElementById('warnaKategori').value = data.warna || '#4f46e5';
                    document.getElementById('warnaPreview').textContent = data.warna || '#4f46e5';
                    document.getElementById('isActiveKategori').checked = data.isActive === 1;
                }
                modal.show();
            }

            document.getElementById('btnAddKategori')?.addEventListener('click', () => openModal('create'));
            document.getElementById('btnAddKategori2')?.addEventListener('click', () => openModal('create'));

            document.querySelectorAll('.btn-edit-kategori').forEach(btn => {
                btn.addEventListener('click', function() {
                    const data = {
                        id: this.dataset.id,
                        nama: this.dataset.nama,
                        deskripsi: this.dataset.deskripsi,
                        warna: this.dataset.warna,
                        isActive: this.dataset.isActive
                    };
                    openModal('edit', data);
                });
            });

            document.querySelectorAll('.btn-delete-kategori').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const nama = this.dataset.nama;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Konfirmasi Hapus',
                        text: `Hapus kategori "${nama}"?`,
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) deleteKategori(id);
                    });
                });
            });

            form?.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }

                const kategoriId = document.getElementById('kategoriId').value;
                let baseUrl = isEditMode ? document.getElementById('baseUrlUpdate').value.replace(
                    '__ID__', kategoriId) : document.getElementById('baseUrlStore').value;
                const separator = baseUrl.includes('?') ? '&' : '?';
                const url =
                    `${baseUrl}${separator}_token_nav=${currentToken}${isEditMode ? '&_method=PUT' : ''}`;

                btnSave.disabled = true;
                btnSave.innerHTML =
                    '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
                const formData = new FormData(form);

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
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        if (data.errors) showErrors(data.errors);
                        else showAlert('error', data.message || 'Gagal menyimpan kategori');
                    }
                } catch (err) {
                    showAlert('error', 'Terjadi kesalahan koneksi');
                } finally {
                    btnSave.disabled = false;
                    btnSave.textContent = 'Simpan';
                }
            });

            function deleteKategori(id) {
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
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showAlert('error', data.message || 'Gagal menghapus kategori');
                        }
                    })
                    .catch(() => showAlert('error', 'Terjadi kesalahan koneksi'));
            }

            function resetForm() {
                form?.reset();
                form?.classList.remove('was-validated');
                document.getElementById('formErrors')?.classList.add('d-none');
                document.getElementById('kategoriId').value = '';
                document.getElementById('warnaKategori').value = '#4f46e5';
                document.getElementById('warnaPreview').textContent = '#4f46e5';
                document.getElementById('isActiveKategori').checked = true;
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
                        html += `<li>${f}: ${m}</li>`;
                    });
                }
                html += '</ul>';
                errorBox.innerHTML = html;
                errorBox.classList.remove('d-none');
                errorBox.scrollIntoView({
                    behavior: 'smooth'
                });
            }

            document.getElementById('warnaKategori')?.addEventListener('input', function() {
                document.getElementById('warnaPreview').textContent = this.value;
            });

            document.getElementById('modalKategori')?.addEventListener('hidden.bs.modal', resetForm);
        });
    </script>
@endpush
