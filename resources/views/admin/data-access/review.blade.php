@extends('layouts.admin')

@section('title', 'Review Data Access Request')

@section('content')
    <div class="page-wrapper">

        <!-- Warning Banner -->
        <div class="alert-custom warning mb-4" role="alert">
            <i class="ri-shield-warning-line"></i>
            <div>
                <strong class="d-block mb-1">Penting</strong>
                <span>Token akses hanya akan ditampilkan <strong>SEKALI</strong> setelah approval. Pastikan requester
                    menyimpan token dengan aman.</span>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="mb-4"></div>

        <div class="row g-4">

            <!-- Left Column: Requester & Project Info -->
            <div class="col-lg-4">

                <!-- Requester Info Card -->
                <div class="card-custom mb-4">
                    <div class="card-header-custom bg-primary text-white py-3 px-4">
                        <h6 class="mb-0 fw-semibold">
                            <i class="ri-user-line me-2"></i>Requester Information
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="info-item mb-3">
                            <span class="info-label">Name</span>
                            <span class="info-value fw-semibold">{{ $request->requester_name }}</span>
                        </div>
                        <div class="info-item mb-3">
                            <span class="info-label">Email</span>
                            <span class="info-value fw-semibold">{{ $request->requester_email }}</span>
                            @if ($request->isVerified())
                                <span class="badge badge-status-verified mt-2">
                                    <i class="ri-check-line me-1"></i>Verified
                                </span>
                            @else
                                <span class="badge badge-status-pending mt-2">
                                    <i class="ri-error-warning-line me-1"></i>Not Verified
                                </span>
                            @endif
                        </div>
                        <div class="info-item mb-3">
                            <span class="info-label">IP Address</span>
                            <code class="info-value d-block mt-1">{{ $request->ip_address }}</code>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Submitted</span>
                            <span class="info-value d-block mt-1">{{ $request->created_at->format('d M Y, H:i') }}</span>
                            <small class="text-muted-custom">{{ $request->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>

                <!-- Project Details Card -->
                <div class="card-custom">
                    <div class="card-header-custom bg-success text-white py-3 px-4">
                        <h6 class="mb-0 fw-semibold">
                            <i class="ri-projector-line me-2"></i>Project Details
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="info-item mb-3">
                            <span class="info-label">Project Name</span>
                            <span class="info-value fw-semibold">{{ $request->project_name ?: 'Not specified' }}</span>
                        </div>
                        <div>
                            <span class="info-label">Purpose</span>
                            <div class="purpose-box mt-2 p-3 rounded border bg-body">
                                {{ $request->purpose }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Access Configuration Form -->
            <div class="col-lg-8">
                <div class="card-custom">
                    <div class="card-header-custom bg-warning text-white py-3 px-4">
                        <h6 class="mb-0 fw-semibold">
                            <i class="ri-building-line me-2"></i>Konfigurasi Akses
                        </h6>
                    </div>
                    <div class="card-body p-4">

                        <!-- Branch Selection -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-muted-custom">
                                <i class="ri-map-pin-line me-1 text-danger"></i>
                                Branch untuk Filter Data <span class="text-danger">*</span>
                            </label>
                            <select id="branchSelect" class="form-select form-select-custom" required>
                                <option value="">-- Pilih Branch --</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }} ({{ $branch->code }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted-custom d-block mt-2">
                                <i class="ri-information-line me-1"></i>Semua data yang diakses akan difilter berdasarkan
                                branch ini (row-level security)
                            </small>
                        </div>

                        <!-- Tables & Columns Selection -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-muted-custom mb-3">
                                <i class="ri-database-line me-1"></i>Pilih Tabel & Kolom
                            </label>

                            <div class="position-relative mb-3" style="max-width: 400px;">
                                <i
                                    class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted-custom"></i>
                                <input type="text" id="tableSearch" class="form-control form-control-custom ps-5"
                                    placeholder="Cari tabel...">
                            </div>

                            <div id="tablesContainer">
                                @php $tableIndex = 0; @endphp
                                @foreach ($allTables as $tableName => $tableData)
                                    @if (in_array($tableName, $request->requested_tables))
                                        <div class="card card-custom mb-3 table-card"
                                            data-table-name="{{ $tableName }}">
                                            <div
                                                class="card-header-custom bg-body py-3 px-4 d-flex align-items-center justify-content-between">
                                                <label
                                                    class="d-flex align-items-center gap-2 mb-0 cursor-pointer fw-semibold">
                                                    <input type="checkbox" class="form-check-input-custom table-selector"
                                                        data-index="{{ $tableIndex }}">
                                                    <i class="ri-table-line text-primary"></i>
                                                    {{ $tableName }}
                                                    <span
                                                        class="badge badge-secondary-custom ms-2">{{ count($tableData['columns']) }}
                                                        kolom</span>
                                                </label>
                                            </div>

                                            <div id="columns_{{ $tableIndex }}" class="card-body p-4"
                                                style="display: none;">
                                                <input type="hidden" name="grants[{{ $tableIndex }}][table_name]"
                                                    class="grant-table-name" value="{{ $tableName }}">

                                                <p class="small fw-semibold text-success mb-3">
                                                    <i class="ri-checkbox-circle-line me-1"></i>Centang kolom yang boleh
                                                    diakses:
                                                </p>

                                                <div class="row g-2">
                                                    @foreach ($tableData['columns'] as $column)
                                                        <div class="col-md-6">
                                                            <label
                                                                class="form-check d-flex align-items-center gap-2 p-2 rounded hover-bg cursor-pointer">
                                                                <input type="checkbox"
                                                                    class="form-check-input-custom column-checkbox"
                                                                    name="grants[{{ $tableIndex }}][allowed_columns][]"
                                                                    value="{{ $column }}"
                                                                    data-index="{{ $tableIndex }}">
                                                                <code class="small">{{ $column }}</code>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <p class="small text-muted-custom mt-3 mb-0">
                                                    <i class="ri-information-line me-1"></i>
                                                    <span class="selected-count" data-index="{{ $tableIndex }}">0</span>
                                                    kolom dipilih
                                                </p>
                                            </div>
                                        </div>
                                        @php $tableIndex++; @endphp
                                    @endif
                                @endforeach
                            </div>

                            <small class="text-muted-custom d-block mt-3">
                                <i class="ri-shield-line me-1"></i>Kolom sensitif (password, token, secret, key) sudah
                                otomatis disembunyikan.
                            </small>
                        </div>

                        <!-- Token Configuration -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-muted-custom mb-3">
                                <i class="ri-key-line me-1"></i>Konfigurasi Token
                            </label>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted-custom mb-2">
                                        <i class="ri-time-line me-1"></i>Token Expired
                                    </label>
                                    <input type="datetime-local" id="expiresAt" class="form-control form-control-custom"
                                        value="{{ now()->addDays(30)->format('Y-m-d\TH:i') }}">
                                    <small class="text-muted-custom d-block mt-1">Kosongkan jika tidak ada batas
                                        waktu</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted-custom mb-2">
                                        <i class="ri-global-line me-1"></i>IP Whitelist (Opsional)
                                    </label>
                                    <input type="text" id="allowedIps" class="form-control form-control-custom"
                                        placeholder="192.168.1.1, 10.0.0.0/24">
                                    <small class="text-muted-custom d-block mt-1">Pisahkan dengan koma atau kosongkan untuk
                                        semua IP</small>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-wrap justify-content-end gap-3 pt-4 border-top border-custom">
                            <a href="{{ route('admin.data-access.requests') }}" class="btn btn-outline-custom">
                                <i class="ri-arrow-left-line me-1"></i> Back
                            </a>
                            <button type="button" class="btn btn-outline-danger" id="btnReject">
                                <i class="ri-close-line me-1"></i> Reject Request
                            </button>
                            <button type="button" class="btn btn-custom btn-gradient" id="btnApprove">
                                <i class="ri-check-line me-1"></i> Approve & Generate Tokens
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="rejectForm">
                    @csrf
                    <input type="hidden" name="_method" value="POST">

                    <div class="modal-header border-bottom border-custom bg-danger text-white">
                        <h5 class="modal-title fw-semibold">
                            <i class="ri-error-warning-line me-2"></i>Reject Request
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">
                                Alasan Penolakan <span class="text-danger">*</span>
                            </label>
                            <textarea id="rejectionReason" class="form-control form-control-custom textarea-custom" rows="4" required
                                minlength="10" placeholder="Jelaskan mengapa permintaan ini ditolak..."></textarea>
                            <small class="text-muted-custom d-block mt-1">Minimal 10 karakter</small>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="btnConfirmReject" disabled>
                            <i class="ri-close-line me-1"></i> Reject Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden Inputs for URLs -->
    <input type="hidden" id="baseUrlApprove"
        value="{{ route('admin.data-access.requests.approve', ['dataRequest' => '__ID__']) }}">
    <input type="hidden" id="baseUrlReject"
        value="{{ route('admin.data-access.requests.reject', ['dataRequest' => '__ID__']) }}">
    <input type="hidden" id="requestId" value="{{ $request->id }}">

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

        /* ===== ALERT CUSTOM ===== */
        .alert-custom {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            animation: slideIn 0.3s ease;
        }

        .alert-custom.warning {
            background: rgba(245, 158, 11, 0.1);
            border-left: 4px solid #f59e0b;
            color: #92400e;
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
            margin-top: 2px;
        }

        /* ===== INFO ITEMS ===== */
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-value {
            font-size: 0.95rem;
            color: var(--text-primary);
            word-break: break-word;
        }

        .info-value code {
            background: var(--bg-body);
            padding: 0.2em 0.4em;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
        }

        /* ===== PURPOSE BOX ===== */
        .purpose-box {
            background: var(--bg-body);
            border-color: var(--border-color);
            font-size: 0.9rem;
            line-height: 1.5;
            color: var(--text-primary);
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

        .textarea-custom {
            resize: vertical;
            min-height: 80px;
            line-height: 1.5;
        }

        .form-check-input-custom {
            width: 18px;
            height: 18px;
            border: 2px solid var(--border-color);
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
            accent-color: var(--accent-blue);
        }

        .form-check-input-custom:checked {
            background-color: var(--accent-blue);
            border-color: var(--accent-blue);
        }

        .form-check-input-custom[type="checkbox"] {
            border-radius: 4px;
        }

        /* ===== TABLE CARD ===== */
        .table-card {
            transition: var(--transition);
        }

        .table-card:hover {
            border-color: var(--accent-blue);
            box-shadow: var(--shadow-sm);
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .hover-bg:hover {
            background: var(--bg-body);
        }

        /* ===== BADGES ===== */
        .badge-status-verified {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.25em 0.5em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-pending {
            background: rgba(245, 158, 11, 0.15);
            color: #b45309;
            border: 1px solid rgba(245, 158, 11, 0.3);
            padding: 0.25em 0.5em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-secondary-custom {
            background: var(--bg-body);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            padding: 0.2em 0.4em;
            font-size: 0.7rem;
        }

        /* ===== BUTTON GRADIENT ===== */
        .btn-gradient {
            background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-blue-hover) 100%);
            color: white;
            border: none;
        }

        .btn-gradient:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
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

        /* ===== ANIMATIONS ===== */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* ===== DARK MODE ===== */
        [data-theme="dark"] .alert-custom.warning {
            background: rgba(245, 158, 11, 0.15);
        }

        [data-theme="dark"] .purpose-box {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .table-card {
            background: var(--bg-surface);
        }

        [data-theme="dark"] .modal-content-custom {
            background: var(--bg-surface);
            border-color: var(--border-color);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991px) {
            .row.g-4 {
                --bs-gutter-y: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .d-flex.flex-wrap.gap-3 {
                flex-direction: column;
            }

            .d-flex.flex-wrap.gap-3 .btn {
                width: 100%;
                justify-content: center;
            }

            .table-card .card-header-custom {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== CONSTANTS =====
            const requestId = document.getElementById('requestId')?.value;
            const currentToken =
            '{{ request()->attributes->get('page_token') ?? request()->query('_token_nav') }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            const baseUrlApprove = document.getElementById('baseUrlApprove')?.value.replace('__ID__', requestId);
            const baseUrlReject = document.getElementById('baseUrlReject')?.value.replace('__ID__', requestId);

            // ===== MODAL INSTANCE =====
            let modalReject;
            const modalRejectEl = document.getElementById('rejectModal');
            if (modalRejectEl && bootstrap.Modal) {
                modalReject = new bootstrap.Modal(modalRejectEl);
            }

            // ===== TOOLTIP INIT =====
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });

            // ===== TABLE SELECTOR TOGGLE =====
            document.querySelectorAll('.table-selector').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const index = this.dataset.index;
                    const columnsDiv = document.getElementById('columns_' + index);
                    if (!columnsDiv) return;

                    const columnCheckboxes = columnsDiv.querySelectorAll('.column-checkbox');
                    columnsDiv.style.display = this.checked ? 'block' : 'none';

                    columnCheckboxes.forEach(cb => {
                        cb.disabled = !this.checked;
                        if (!this.checked) cb.checked = false;
                    });

                    updateCount(index);
                });
            });

            // ===== COLUMN CHECKBOX COUNTER =====
            document.querySelectorAll('.column-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    const index = this.dataset.index;
                    updateCount(index);
                });
            });

            function updateCount(index) {
                const checked = document.querySelectorAll(`#columns_${index} .column-checkbox:checked`).length;
                const counter = document.querySelector(`.selected-count[data-index="${index}"]`);
                if (counter) counter.textContent = checked;
            }

            // ===== TABLE SEARCH FILTER =====
            document.getElementById('tableSearch')?.addEventListener('input', function(e) {
                const search = e.target.value.toLowerCase().trim();
                document.querySelectorAll('[data-table-name]').forEach(card => {
                    const name = card.dataset.tableName.toLowerCase();
                    card.style.display = name.includes(search) ? 'block' : 'none';
                });
            });

            // ===== REJECT MODAL: Enable button when reason filled =====
            const rejectionReason = document.getElementById('rejectionReason');
            const btnConfirmReject = document.getElementById('btnConfirmReject');

            rejectionReason?.addEventListener('input', function() {
                if (btnConfirmReject) {
                    btnConfirmReject.disabled = this.value.trim().length < 10;
                }
            });

            // ===== REJECT BUTTON CLICK =====
            document.getElementById('btnReject')?.addEventListener('click', function() {
                if (modalReject) modalReject.show();
            });

            // ===== REJECT FORM SUBMIT =====
            const rejectForm = document.getElementById('rejectForm');
            rejectForm?.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!rejectionReason || rejectionReason.value.trim().length < 10) {
                    showAlert('error', 'Alasan penolakan minimal 10 karakter');
                    return;
                }

                if (btnConfirmReject) {
                    btnConfirmReject.disabled = true;
                    btnConfirmReject.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
                }

                const url = baseUrlReject + (baseUrlReject.includes('?') ? '&' : '?') + '_token_nav=' +
                    currentToken;

                try {
                    const formData = new FormData();
                    formData.append('_token', csrfToken);
                    formData.append('rejection_reason', rejectionReason.value.trim());

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
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
                        if (modalReject) modalReject.hide();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('error', data.message || 'Gagal menolak permintaan');
                        if (btnConfirmReject) {
                            btnConfirmReject.disabled = false;
                            btnConfirmReject.innerHTML =
                                '<i class="ri-close-line me-1"></i> Reject Request';
                        }
                    }
                } catch (error) {
                    console.error('Reject error:', error);
                    showAlert('error', 'Terjadi kesalahan koneksi');
                    if (btnConfirmReject) {
                        btnConfirmReject.disabled = false;
                        btnConfirmReject.innerHTML =
                        '<i class="ri-close-line me-1"></i> Reject Request';
                    }
                }
            });

            // ===== APPROVE BUTTON CLICK =====
            document.getElementById('btnApprove')?.addEventListener('click', async function() {
                // Validate branch
                const branchSelect = document.getElementById('branchSelect');
                const branchId = branchSelect?.value;

                if (!branchId) {
                    showAlert('error', 'Pilih branch terlebih dahulu!');
                    if (branchSelect) branchSelect.focus();
                    return;
                }

                // Validate at least 1 table selected
                const selectedTables = document.querySelectorAll('.table-selector:checked');
                if (selectedTables.length === 0) {
                    showAlert('error', 'Pilih minimal 1 tabel!');
                    return;
                }

                // Validate at least 1 column per selected table
                let valid = true;
                selectedTables.forEach(checkbox => {
                    const index = checkbox.dataset.index;
                    const columnsDiv = document.getElementById('columns_' + index);
                    if (!columnsDiv) return;

                    const columns = columnsDiv.querySelectorAll('.column-checkbox:checked');
                    if (columns.length === 0) {
                        valid = false;
                        const tableName = checkbox.closest('[data-table-name]')?.dataset
                            .tableName || 'unknown';
                        showAlert('error', `Pilih minimal 1 kolom untuk tabel: ${tableName}`);
                        checkbox.closest('[data-table-name]')?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                });

                if (!valid) return;

                // Prepare approve data
                const btnApprove = document.getElementById('btnApprove');
                if (btnApprove) {
                    btnApprove.disabled = true;
                    btnApprove.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
                }

                const url = baseUrlApprove + (baseUrlApprove.includes('?') ? '&' : '?') +
                    '_token_nav=' + currentToken;
                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('branch_id', branchId);

                // Build grants array
                selectedTables.forEach(checkbox => {
                    const index = checkbox.dataset.index;
                    const tableCard = checkbox.closest('[data-table-name]');
                    if (!tableCard) return;

                    const tableNameInput = tableCard.querySelector('.grant-table-name');
                    const tableName = tableNameInput?.value || tableCard.dataset.tableName;

                    formData.append(`grants[${index}][table_name]`, tableName);

                    // Allowed columns
                    const columnsDiv = document.getElementById('columns_' + index);
                    if (columnsDiv) {
                        columnsDiv.querySelectorAll('.column-checkbox:checked').forEach(
                        colCb => {
                            formData.append(`grants[${index}][allowed_columns][]`, colCb
                                .value);
                        });
                    }

                    // Rate limit
                    formData.append(`grants[${index}][rate_limit_per_minute]`, '60');
                });

                // Token config
                const expiresAt = document.getElementById('expiresAt')?.value;
                if (expiresAt) formData.append('expires_at', expiresAt);

                const allowedIps = document.getElementById('allowedIps')?.value;
                if (allowedIps) {
                    allowedIps.split(',').map(ip => ip.trim()).filter(ip => ip).forEach(ip => {
                        formData.append('allowed_ips[]', ip);
                    });
                }

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
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
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('error', data.message || 'Gagal menyetujui permintaan');
                        if (btnApprove) {
                            btnApprove.disabled = false;
                            btnApprove.innerHTML =
                                '<i class="ri-check-line me-1"></i> Approve & Generate Tokens';
                        }
                    }
                } catch (error) {
                    console.error('Approve error:', error);
                    showAlert('error', 'Terjadi kesalahan koneksi');
                    if (btnApprove) {
                        btnApprove.disabled = false;
                        btnApprove.innerHTML =
                            '<i class="ri-check-line me-1"></i> Approve & Generate Tokens';
                    }
                }
            });

            // ===== ALERT HELPER =====
            function showAlert(type, message) {
                const container = document.getElementById('alertContainer');
                if (!container) {
                    alert(`${type.toUpperCase()}: ${message}`);
                    return;
                }

                const colors = {
                    success: {
                        bg: 'rgba(34,197,94,0.1)',
                        border: '#22c55e',
                        color: '#166534',
                        icon: 'ri-checkbox-circle-fill'
                    },
                    error: {
                        bg: 'rgba(239,68,68,0.1)',
                        border: '#ef4444',
                        color: '#991b1b',
                        icon: 'ri-error-warning-fill'
                    },
                    warning: {
                        bg: 'rgba(245,158,11,0.1)',
                        border: '#f59e0b',
                        color: '#92400e',
                        icon: 'ri-alert-fill'
                    }
                };
                const c = colors[type] || colors.error;

                container.innerHTML = `
            <div class="alert-custom ${type}" data-auto-close>
                <i class="${c.icon}"></i>
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

            // ===== AUTO-CLOSE ALERTS =====
            document.querySelectorAll('[data-auto-close]').forEach(alert => {
                const closeBtn = alert.querySelector('.alert-close');
                closeBtn?.addEventListener('click', () => alert.remove());
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

            // ===== ESC KEY TO CLOSE MODAL =====
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modalReject?._isShown) {
                    modalReject.hide();
                }
            });
        });
    </script>
@endsection
