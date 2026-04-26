@extends('layouts.admin')

@section('title', 'Jadwal Latihan Pelayanan')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Jadwal Latihan Pelayanan</h1>
                <p class="text-muted-custom mb-0">Kelola jadwal latihan untuk pelayanan yang sudah ada</p>
            </div>
            <button type="button" class="btn btn-custom btn-tambah-latihan">
                <i class="ri-calendar-add-line me-1"></i> Buat Jadwal Latihan Baru
            </button>
        </div>

        <!-- Success Alert -->
        @if (session('success'))
            <div class="alert-custom success mb-4" data-auto-close>
                <i class="ri-checkbox-circle-fill"></i>
                <span>{{ session('success') }}</span>
                <button class="alert-close">&times;</button>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <form id="formFilter">
                    <div class="row g-3">
                        <div class="col-6 col-md-4 col-lg-3">
                            <label class="form-label small fw-semibold mb-2">Pelayanan</label>
                            <select id="pelayanan_id" class="form-select form-select-custom">
                                <option value="">Semua Pelayanan</option>
                                @foreach ($pelayanans as $p)
                                    <option value="{{ $p->id }}"
                                        {{ request('pelayanan_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_pelayanan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-semibold mb-2">Dari Tanggal</label>
                            <input type="date" id="tanggal_start" class="form-control form-control-custom"
                                value="{{ request('tanggal_start') }}">
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-semibold mb-2">Sampai Tanggal</label>
                            <input type="date" id="tanggal_end" class="form-control form-control-custom"
                                value="{{ request('tanggal_end') }}">
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-semibold mb-2">Status</label>
                            <select id="status" class="form-select form-select-custom">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                    Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                            <label class="form-label small fw-semibold mb-2">Cari</label>
                            <div class="position-relative">
                                <i
                                    class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted-custom"></i>
                                <input type="text" id="search" class="form-control form-control-custom ps-5"
                                    value="{{ request('search') }}" placeholder="Nama latihan...">
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 d-flex align-items-end">
                            <button type="button" id="btnFilter" class="btn btn-custom w-100">
                                <i class="ri-filter-line me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Latihan List Grid -->
        <div class="row g-4" id="latihanList">
            @include('features.latihan-pelayanan._list')
        </div>

        <!-- Pagination -->
        @if ($latihans->hasPages())
            <div class="d-flex justify-content-center mt-4" id="paginationContainer">
                {{ $latihans->links('pagination::simple-bootstrap-4') }}
            </div>
        @endif

    </div>

    @include('features.latihan-pelayanan._modal_form', ['pelayanans' => $pelayanans ?? collect()])

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

        /* ===== LATIHAN CARD ===== */
        .latihan-card {
            transition: var(--transition);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            background: var(--bg-surface);
            height: 100%;
            display: flex;
            flex-direction: column;
            border-left: 4px solid var(--status-color, var(--accent-blue));
        }

        .latihan-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .latihan-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .latihan-card-title {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            line-height: 1.4;
            color: var(--text-primary);
        }

        .latihan-card-status {
            font-size: 0.75rem;
            padding: 0.25em 0.6em;
            border-radius: 20px;
            font-weight: 500;
            white-space: nowrap;
        }

        .latihan-card-status.active {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .latihan-card-status.cancelled {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .latihan-card-body {
            padding: 1.25rem;
            flex: 1;
        }

        .latihan-meta {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .latihan-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-primary);
        }

        .latihan-meta-item i {
            color: var(--accent-blue);
            font-size: 1rem;
            width: 16px;
        }

        .latihan-location {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .latihan-location i {
            color: var(--text-secondary);
        }

        .latihan-keterangan {
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .latihan-pelayanan-badge {
            background: var(--bg-body);
            color: var(--text-primary);
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            font-weight: 500;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .latihan-footer {
            padding: 0.75rem 1.25rem 1.25rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
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

        /* ===== FORM ELEMENTS (Consistent) ===== */
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

        /* ===== PAGINATION ===== */
        .pagination .page-link {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.5rem 0.875rem;
            margin: 0 2px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .pagination .page-link:hover,
        .pagination .page-item.active .page-link {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        .pagination .page-item.disabled .page-link {
            background: var(--bg-body);
            color: var(--text-secondary);
        }

        /* ===== DARK MODE ===== */
        [data-theme="dark"] .bg-body-tertiary {
            background-color: var(--bg-sidebar) !important;
        }

        [data-theme="dark"] .latihan-card:hover {
            background: var(--bg-sidebar);
            border-color: var(--accent-blue);
        }

        [data-theme="dark"] .empty-state-card {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .latihan-card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .latihan-footer {
                flex-wrap: wrap;
            }

            .latihan-footer .btn {
                flex: 1 1 auto;
                justify-content: center;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== MODAL INSTANCE =====
            const modal = document.getElementById('modalLatihanForm');
            let modalInstance;
            if (bootstrap.Modal) {
                modalInstance = new bootstrap.Modal(modal);
            }

            // ===== TOOLTIP INIT =====
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // ===== EVENT: Tambah Latihan =====
            document.querySelector('.btn-tambah-latihan')?.addEventListener('click', function() {
                resetForm();
                if (modalInstance) modalInstance.show();
            });

            // ===== EVENT: Filter =====
            document.getElementById('btnFilter')?.addEventListener('click', reloadList);

            // ===== FUNCTION: Reload List =====
            function reloadList() {
                const params = new URLSearchParams({
                    pelayanan_id: document.getElementById('pelayanan_id')?.value || '',
                    tanggal_start: document.getElementById('tanggal_start')?.value || '',
                    tanggal_end: document.getElementById('tanggal_end')?.value || '',
                    status: document.getElementById('status')?.value || '',
                    search: document.getElementById('search')?.value?.trim() || '',
                    ajax: 1
                });

                const url = '{{ route('admin.latihan-pelayanan.index') }}?' + params.toString();
                const listEl = document.getElementById('latihanList');

                // Show loading
                if (listEl) {
                    listEl.innerHTML = `
                <div class="col-12">
                    <div class="empty-state-card">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted-custom mb-0">Memuat jadwal latihan...</p>
                    </div>
                </div>`;
                }

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.text())
                    .then(html => {
                        if (listEl) {
                            // Parse HTML to extract just the grid content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newList = doc.querySelector('#latihanList');

                            if (newList) {
                                listEl.innerHTML = newList.innerHTML;
                            } else {
                                listEl.innerHTML = html;
                            }
                        }
                        attachListeners();

                        // Update pagination if exists
                        const newPagination = doc?.querySelector('#paginationContainer');
                        const paginationEl = document.getElementById('paginationContainer');
                        if (newPagination && paginationEl) {
                            paginationEl.innerHTML = newPagination.innerHTML;
                        }
                    })
                    .catch(() => {
                        if (listEl) {
                            listEl.innerHTML = `
                        <div class="col-12">
                            <div class="empty-state-card">
                                <i class="ri-error-warning-line fs-1 d-block mb-2 text-danger"></i>
                                <p class="text-danger mb-0">Gagal memuat data</p>
                            </div>
                        </div>`;
                        }
                    });
            }

            // ===== FUNCTION: Attach Event Listeners =====
            function attachListeners() {
                // Edit buttons
                document.querySelectorAll('.btn-edit-latihan').forEach(btn => {
                    btn.addEventListener('click', function() {
                        editLatihan(this.dataset.id);
                    });
                });

                // Delete buttons
                document.querySelectorAll('.btn-delete-latihan').forEach(btn => {
                    btn.addEventListener('click', function() {
                        confirmDelete(this.dataset.id, this.dataset.nama);
                    });
                });
            }

            // ===== FUNCTION: Reset Form =====
            function resetForm() {
                const form = document.getElementById('formLatihan');
                if (form) {
                    form.reset();
                    document.getElementById('formMethod').value = 'POST';
                    document.getElementById('latihanId').value = '';
                    document.getElementById('modalTitle').textContent = 'Buat Jadwal Latihan Pelayanan Baru';

                    // Clear validation errors
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    form.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');
                }
            }

            // ===== FUNCTION: Edit Latihan =====
            function editLatihan(id) {
                fetch(`{{ route('admin.latihan-pelayanan.index') }}/${id}/edit?ajax=1`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            const l = data.latihan;

                            document.getElementById('modalTitle').textContent = 'Edit Jadwal Latihan';
                            document.getElementById('formMethod').value = 'PUT';
                            document.getElementById('latihanId').value = l.id;

                            // Isi form
                            document.getElementById('pelayanan_id').value = l.pelayanan_id || '';
                            document.getElementById('nama_latihan').value = l.nama_latihan || '';

                            // Format tanggal Y-m-d
                            if (l.tanggal) {
                                let tanggalFormatted = l.tanggal.includes(' ') ? l.tanggal.split(' ')[0] : l
                                    .tanggal;
                                document.getElementById('tanggal').value = tanggalFormatted;
                            } else {
                                document.getElementById('tanggal').value = '';
                            }

                            // Format waktu HH:mm
                            if (l.waktu) {
                                let waktuFormatted = l.waktu.toString().substring(0, 5);
                                document.getElementById('waktu').value = waktuFormatted;
                            } else {
                                document.getElementById('waktu').value = '';
                            }

                            document.getElementById('lokasi').value = l.lokasi || 'Gedung Utama';
                            document.getElementById('keterangan').value = l.keterangan || '';

                            // Set status radio
                            const statusRadio = document.querySelector(
                                `input[name="status"][value="${l.status}"]`);
                            if (statusRadio) statusRadio.checked = true;

                            if (modalInstance) modalInstance.show();
                        }
                    })
                    .catch(err => {
                        console.error('Edit error:', err);
                        showToast('error', 'Error', 'Gagal memuat data untuk edit');
                    });
            }

            // ===== FUNCTION: Confirm Delete =====
            function confirmDelete(id, nama) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Yakin hapus?',
                        text: `"${nama}" akan dihapus permanen`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) deleteLatihan(id);
                    });
                } else {
                    if (confirm(`Yakin ingin menghapus "${nama}"?`)) {
                        deleteLatihan(id);
                    }
                }
            }

            // ===== FUNCTION: Delete Latihan =====
            function deleteLatihan(id) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

                fetch(`{{ route('admin.latihan-pelayanan.destroy', ':id') }}`.replace(':id', id), {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showToast('success', 'Terhapus!', data.message);
                            reloadList();
                        } else {
                            showToast('error', 'Gagal', data.message);
                        }
                    })
                    .catch(() => showToast('error', 'Error', 'Gagal menghapus data'));
            }

            // ===== FUNCTION: Show Toast =====
            function showToast(type, title, message) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: type,
                        title: title,
                        text: message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                } else {
                    alert(`${title}: ${message}`);
                }
            }

            // ===== FORM SUBMIT =====
            const formLatihan = document.getElementById('formLatihan');
            if (formLatihan) {
                formLatihan.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    let url = '{{ route('admin.latihan-pelayanan.index') }}';
                    const method = document.getElementById('formMethod').value;

                    if (method === 'PUT') {
                        const id = document.getElementById('latihanId').value;
                        url = `{{ route('admin.latihan-pelayanan.index') }}/${id}`;
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const btnSubmit = document.getElementById('btnSubmitLatihan');

                    // Show loading
                    if (btnSubmit) {
                        btnSubmit.disabled = true;
                        btnSubmit.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
                    }

                    fetch(url, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                showToast('success', 'Berhasil!', data.message);
                                if (modalInstance) modalInstance.hide();
                                reloadList();
                            } else {
                                // Show validation errors
                                if (data.errors) {
                                    showValidationErrors(data.errors);
                                } else {
                                    showToast('error', 'Gagal', data.message || 'Validasi gagal');
                                }
                            }
                        })
                        .catch(() => showToast('error', 'Error', 'Gagal mengirim data'))
                        .finally(() => {
                            if (btnSubmit) {
                                btnSubmit.disabled = false;
                                btnSubmit.innerHTML = '<i class="ri-save-line me-1"></i> Simpan';
                            }
                        });
                });
            }

            // ===== FUNCTION: Show Validation Errors =====
            function showValidationErrors(errors) {
                const form = document.getElementById('formLatihan');
                if (!form) return;

                // Clear previous errors
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                // Add new errors
                Object.entries(errors).forEach(([field, messages]) => {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback d-block';
                        feedback.textContent = Array.isArray(messages) ? messages[0] : messages;
                        input.parentNode.appendChild(feedback);
                    }
                });

                // Scroll to first error
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstError.focus?.();
                }
            }

            // ===== INIT =====
            attachListeners();

            // Auto-close alerts
            document.querySelectorAll('[data-auto-close]').forEach(alert => {
                const closeBtn = alert.querySelector('.alert-close');
                closeBtn?.addEventListener('click', () => alert.remove());
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>
@endsection
