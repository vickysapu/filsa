@extends('layouts.admin')

@section('title', 'Data Inventaris')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Data Inventaris</h1>
                <p class="text-muted-custom mb-0">Kelola data inventaris Gereja Filadelfia</p>
            </div>
            <button type="button" class="btn btn-custom btn-tambah-inventaris">
                <i class="ri-add-line me-1"></i> Tambah Inventaris
            </button>
        </div>

        <!-- Success Toast -->
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080">
            <div id="successToast" class="toast align-items-center border-0" role="alert" aria-live="assertive"
                aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fw-semibold d-flex align-items-center gap-2">
                        <i class="ri-checkbox-circle-fill text-success"></i>
                        <span id="toastMessage">Operasi berhasil!</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <form id="formFilter">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-semibold text-muted-custom mb-2">Cari Inventaris</label>
                            <div class="position-relative">
                                <i
                                    class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted-custom"></i>
                                <input type="text" id="search" name="search"
                                    class="form-control form-control-custom ps-5" value="{{ request('search') }}"
                                    placeholder="Nama barang...">
                            </div>
                        </div>
                        <div class="col-12 col-md-4 d-flex gap-2">
                            <button type="button" id="btnFilter" class="btn btn-custom flex-fill">
                                <i class="ri-filter-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.inventaris.index') }}" class="btn btn-outline-custom" title="Reset">
                                <i class="ri-refresh-line"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card-custom">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" id="tabel-inventaris">
                    <thead class="bg-body-tertiary">
                        <tr>
                            @if (!Auth::user()?->roles->first()?->branch_id)
                                <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Cabang
                                </th>
                            @endif
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Nama Barang
                            </th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Kategori</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Kondisi</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="inventarisBody">
                        @include('features.inventaris._list')
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ================== MODAL FORM ================== -->
    <div class="modal fade" id="modalInventarisForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-custom">

                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold" id="modalInventarisLabel">Tambah Inventaris Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formInventaris" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="inventarisId" value="">

                    <div class="modal-body p-4">

                        <div class="alert alert-danger d-none" id="formErrors">
                            <i class="ri-error-warning-line me-2"></i>
                            <ul class="mb-0 small" id="errorList"></ul>
                        </div>

                        <div class="card card-custom">
                            <div class="card-header-custom border-bottom border-custom py-3 px-4">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="ri-archive-line me-2 text-primary"></i>Data Inventaris
                                </h6>
                            </div>
                            <div class="card-body p-4">

                                @if (!Auth::user()?->roles->first()?->branch_id)
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold text-muted-custom">Cabang <span
                                                class="text-danger">*</span></label>
                                        <select name="branch_id" id="branch_id" class="form-select form-select-custom"
                                            required>
                                            <option value="">Pilih Cabang</option>
                                            @foreach (\App\Models\Branch::all() as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <input type="hidden" name="branch_id"
                                        value="{{ Auth::user()->roles->first()->branch_id }}">
                                @endif

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted-custom">Nama Barang <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="nama_barang" id="nama_barang"
                                        class="form-control form-control-custom" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted-custom">Kategori <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="kategori" id="kategori"
                                        class="form-control form-control-custom" required
                                        placeholder="Contoh: Elektronik, Furniture, dll">
                                </div>

                                <div>
                                    <label class="form-label small fw-semibold text-muted-custom">Kondisi <span
                                            class="text-danger">*</span></label>
                                    <select name="kondisi" id="kondisi" class="form-select form-select-custom"
                                        required>
                                        <option value="">Pilih Kondisi</option>
                                        <option value="Baik">Baik</option>
                                        <option value="Rusak Ringan">Rusak Ringan</option>
                                        <option value="Rusak Berat">Rusak Berat</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom" id="btnSubmitInventaris">
                            <i class="ri-save-line me-1"></i> Simpan Inventaris
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

        /* ===== FORM ELEMENTS ===== */
        .form-control-custom,
        .form-select-custom {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.625rem 0.875rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        .form-control-custom::placeholder {
            color: var(--text-secondary);
        }

        /* ===== CONDITION BADGES ===== */
        .badge-condition-baik {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-condition-rusak-ringan {
            background: rgba(245, 158, 11, 0.15);
            color: #b45309;
            border: 1px solid rgba(245, 158, 11, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-condition-rusak-berat {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        /* ===== ACTION BUTTONS ===== */
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-primary);
            transition: var(--transition);
            font-size: 1rem;
            padding: 0;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .btn-action-primary {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .btn-action-primary:hover {
            background: var(--accent-blue);
            color: white;
        }

        .btn-action-danger {
            border-color: #ef4444;
            color: #ef4444;
        }

        .btn-action-danger:hover {
            background: #ef4444;
            color: white;
        }

        /* ===== TOAST CUSTOM ===== */
        .toast {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-lg);
        }

        .toast .toast-body {
            color: var(--text-primary);
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

        /* ===== DARK MODE ===== */
        [data-theme="dark"] .bg-body-tertiary {
            background: var(--bg-sidebar) !important;
        }

        [data-theme="dark"] .toast {
            background: var(--bg-surface);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .empty-state-card {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .table-responsive table {
                min-width: 600px;
            }

            .d-flex.flex-wrap.gap-2 {
                flex-direction: column;
            }

            .d-flex.flex-wrap.gap-2 .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== MODAL INSTANCE =====
            let modalInventaris;
            const modalEl = document.getElementById('modalInventarisForm');
            if (modalEl && bootstrap.Modal) {
                modalInventaris = new bootstrap.Modal(modalEl);
            }

            // ===== TOAST INSTANCE =====
            const toastEl = document.getElementById('successToast');
            let toast;
            if (toastEl && bootstrap.Toast) {
                toast = new bootstrap.Toast(toastEl, {
                    delay: 2000
                });
            }

            // ===== CONSTANTS =====
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            // ===== EVENT: Tambah Inventaris =====
            document.querySelector('.btn-tambah-inventaris')?.addEventListener('click', function() {
                resetForm('create');
                document.getElementById('modalInventarisLabel').textContent = 'Tambah Inventaris Baru';
                if (modalInventaris) modalInventaris.show();
            });

            // ===== EVENT: Edit Inventaris (delegation) =====
            document.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.btn-edit-inventaris');
                if (editBtn) {
                    e.preventDefault();
                    loadInventarisForEdit(editBtn.dataset.id);
                }
            });

            // ===== EVENT: Delete Inventaris (delegation) =====
            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.btn-delete-inventaris');
                if (deleteBtn) {
                    e.preventDefault();
                    const nama = deleteBtn.closest('tr')?.querySelector('td:nth-child(2)')?.textContent
                        ?.trim() || 'Inventaris ini';

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Yakin hapus?',
                            text: `Inventaris: ${nama}`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            confirmButtonText: 'Ya, Hapus',
                            cancelButtonText: 'Batal'
                        }).then(result => {
                            if (result.isConfirmed) deleteInventaris(deleteBtn.dataset.id);
                        });
                    } else {
                        if (confirm(`Yakin ingin menghapus "${nama}"?`)) {
                            deleteInventaris(deleteBtn.dataset.id);
                        }
                    }
                }
            });

            // ===== EVENT: Filter =====
            document.getElementById('btnFilter')?.addEventListener('click', function() {
                reloadTable();
            });

            // ===== EVENT: Form Submit =====
            const formInventaris = document.getElementById('formInventaris');
            const btnSubmit = document.getElementById('btnSubmitInventaris');

            formInventaris?.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!formInventaris.checkValidity()) {
                    formInventaris.classList.add('was-validated');
                    return;
                }

                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
                }

                const formData = new FormData(formInventaris);

                try {
                    const response = await fetch(formInventaris.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        showToast('success', data.message || 'Data berhasil disimpan');
                        if (modalInventaris) modalInventaris.hide();
                        reloadTable();
                    } else {
                        if (data.errors) {
                            showErrors(data.errors);
                        } else {
                            showToast('error', data.message || 'Gagal menyimpan');
                        }
                    }
                } catch (err) {
                    console.error(err);
                    showToast('error', 'Terjadi kesalahan koneksi');
                } finally {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="ri-save-line me-1"></i> Simpan Inventaris';
                    }
                }
            });

            // ===== FUNCTION: Reset Form =====
            function resetForm(mode = 'create') {
                const form = document.getElementById('formInventaris');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated');
                    form.action = '{{ route('admin.inventaris.store') }}';
                }

                document.getElementById('formMethod').value = 'POST';
                document.getElementById('inventarisId').value = '';

                const errorBox = document.getElementById('formErrors');
                if (errorBox) errorBox.classList.add('d-none');
            }

            // ===== FUNCTION: Load Inventaris for Edit =====
            function loadInventarisForEdit(id) {
                fetch(`{{ url('admin/inventaris') }}/${id}/edit?ajax=1`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (!res.success) {
                            showToast('error', res.message || 'Data tidak ditemukan');
                            return;
                        }

                        resetForm('edit');

                        const form = document.getElementById('formInventaris');
                        if (form) {
                            form.action = `{{ url('admin/inventaris') }}/${id}`;
                        }
                        document.getElementById('formMethod').value = 'PUT';
                        document.getElementById('inventarisId').value = id;

                        document.getElementById('modalInventarisLabel').textContent = 'Edit Inventaris';

                        @if (!Auth::user()?->roles->first()?->branch_id)
                            document.getElementById('branch_id').value = res.data.branch_id || '';
                        @endif

                        document.getElementById('nama_barang').value = res.data.nama_barang || '';
                        document.getElementById('kategori').value = res.data.kategori || '';
                        document.getElementById('kondisi').value = res.data.kondisi || '';

                        if (modalInventaris) modalInventaris.show();
                    })
                    .catch(() => showToast('error', 'Gagal memuat data'));
            }

            // ===== FUNCTION: Delete Inventaris =====
            function deleteInventaris(id) {
                fetch(`{{ url('admin/inventaris/delete') }}/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            showToast('success', res.message || 'Data berhasil dihapus');
                            reloadTable();
                        } else {
                            showToast('error', res.message || 'Gagal menghapus');
                        }
                    })
                    .catch(() => showToast('error', 'Terjadi kesalahan saat menghapus'));
            }

            // ===== FUNCTION: Reload Table =====
            function reloadTable() {
                const search = document.getElementById('search')?.value?.trim() || '';
                const params = new URLSearchParams({
                    search: search,
                    ajax: 1
                });

                const tableBody = document.getElementById('inventarisBody');
                if (tableBody) {
                    tableBody.innerHTML = `
                <tr>
                    <td colspan="{{ !Auth::user()?->roles->first()?->branch_id ? '6' : '5' }}" class="py-5 text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted-custom mt-3 mb-0">Memuat data inventaris...</p>
                    </td>
                </tr>`;
                }

                fetch(`{{ route('admin.inventaris.index') }}?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    })
                    .then(r => r.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        const newBody = doc.querySelector('#inventarisBody');
                        if (newBody && tableBody) {
                            tableBody.innerHTML = newBody.innerHTML;
                        }
                    })
                    .catch(err => {
                        console.error('Reload table failed:', err);
                        showToast('error', 'Gagal memuat ulang daftar inventaris');
                    });
            }

            // ===== FUNCTION: Show Toast =====
            function showToast(type, message) {
                const toastMessage = document.getElementById('toastMessage');
                const toastElement = document.getElementById('successToast');

                if (toastMessage && toastElement) {
                    toastMessage.textContent = message;

                    if (type === 'success') {
                        toastElement.classList.add('text-bg-success');
                        toastElement.classList.remove('text-bg-danger');
                    } else {
                        toastElement.classList.add('text-bg-danger');
                        toastElement.classList.remove('text-bg-success');
                    }

                    if (toast) {
                        toast.show();
                    }
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: type,
                        title: type === 'success' ? 'Berhasil' : 'Gagal',
                        text: message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    alert(`${type === 'success' ? '✅' : '❌'} ${message}`);
                }
            }

            // ===== FUNCTION: Show Validation Errors =====
            function showErrors(errors) {
                const errorBox = document.getElementById('formErrors');
                const errorList = document.getElementById('errorList');
                if (!errorBox || !errorList) return;

                let html = '';
                for (let field in errors) {
                    const messages = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                    messages.forEach(msg => {
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
        });
    </script>
@endsection
