@extends('layouts.admin')

@section('title', 'Review Data Access Request')

@section('content')
    <div class="page-wrapper">

        <!-- Notice -->
        <div class="notice mb-4" role="alert">
            <i class="ri-shield-warning-line"></i>
            <p class="mb-0">
                Token akses hanya ditampilkan <strong>sekali</strong> setelah approval. Pastikan requester
                menyimpannya dengan aman.
            </p>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <div class="row g-4">

            <!-- LEFT: Requester & Project -->
            <div class="col-lg-4">
                <div class="panel mb-4">
                    <div class="panel-head">
                        <i class="ri-user-line"></i>
                        <h6>Requester</h6>
                    </div>
                    <dl class="panel-body info-list">
                        <div>
                            <dt>Nama</dt>
                            <dd class="fw-semibold">{{ $request->requester_name }}</dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd>
                                <span class="fw-semibold">{{ $request->requester_email }}</span>
                                @if ($request->isVerified())
                                    <span class="chip chip-ok"><i class="ri-check-line"></i>Verified</span>
                                @else
                                    <span class="chip chip-warn"><i class="ri-error-warning-line"></i>Belum verified</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt>IP Address</dt>
                            <dd><code>{{ $request->ip_address }}</code></dd>
                        </div>
                        <div>
                            <dt>Diajukan</dt>
                            <dd>
                                {{ $request->created_at->format('d M Y, H:i') }}
                                <small class="text-muted-custom d-block">{{ $request->created_at->diffForHumans() }}</small>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <i class="ri-projector-line"></i>
                        <h6>Project</h6>
                    </div>
                    <dl class="panel-body info-list">
                        <div>
                            <dt>Nama project</dt>
                            <dd class="fw-semibold">{{ $request->project_name ?: 'Tidak disebutkan' }}</dd>
                        </div>
                        <div>
                            <dt>Tujuan</dt>
                            <dd class="purpose-box">{{ $request->purpose }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- RIGHT: Access configuration -->
            <div class="col-lg-8">
                <div class="panel">
                    <div class="panel-head">
                        <i class="ri-building-line"></i>
                        <h6>Konfigurasi akses</h6>
                    </div>

                    <div class="panel-body">

                        <!-- Branch -->
                        <section class="config-section">
                            <label for="branchSelect" class="section-title">Branch <span class="text-danger">*</span></label>
                            <select id="branchSelect" class="form-select form-select-custom" required>
                                <option value="">Pilih branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }} ({{ $branch->code }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="hint">Semua data yang diakses difilter berdasarkan branch ini (row-level security).</p>
                        </section>

                        <!-- Tables & columns -->
                        <section class="config-section">
                            <div class="section-title">Tabel & kolom</div>

                            <div class="toolbar">
                                <div class="search-box">
                                    <i class="ri-search-line"></i>
                                    <input type="text" id="tableSearch" class="form-control form-control-custom"
                                        placeholder="Cari tabel">
                                </div>

                                <label class="check-inline">
                                    <input type="checkbox" id="selectAllTables" class="form-check-input-custom">
                                    <span>Pilih semua tabel</span>
                                </label>

                                <div class="toolbar-actions">
                                    <button type="button" class="btn-link-sm" id="btnAllColumns">Semua kolom</button>
                                    <button type="button" class="btn-link-sm" id="btnClearColumns">Kosongkan kolom</button>
                                </div>
                            </div>

                            <div id="tablesContainer">
                                @php $tableIndex = 0; @endphp
                                @foreach ($allTables as $tableName => $tableData)
                                    @if (in_array($tableName, $request->requested_tables))
                                        <div class="table-item" data-table-name="{{ $tableName }}">
                                            <div class="table-item-head">
                                                <label class="check-inline table-name-wrap">
                                                    <input type="checkbox" class="form-check-input-custom table-selector"
                                                        data-index="{{ $tableIndex }}">
                                                    <i class="ri-table-line"></i>
                                                    <span class="table-name">{{ $tableName }}</span>
                                                </label>

                                                <span class="table-meta">
                                                    <span class="selected-count"
                                                        data-index="{{ $tableIndex }}">0</span>/{{ count($tableData['columns']) }}
                                                    kolom
                                                </span>

                                                <button type="button" class="panel-toggle" data-index="{{ $tableIndex }}"
                                                    aria-label="Buka atau tutup kolom" disabled>
                                                    <i class="ri-arrow-down-s-line"></i>
                                                </button>
                                            </div>

                                            <div id="columns_{{ $tableIndex }}" class="table-item-body"
                                                style="display: none;">
                                                <input type="hidden" name="grants[{{ $tableIndex }}][table_name]"
                                                    class="grant-table-name" value="{{ $tableName }}">

                                                <label class="check-inline column-select-all-wrap">
                                                    <input type="checkbox" class="form-check-input-custom column-select-all"
                                                        data-index="{{ $tableIndex }}">
                                                    <span>Pilih semua kolom</span>
                                                </label>

                                                <div class="column-grid">
                                                    @foreach ($tableData['columns'] as $column)
                                                        <label class="column-option">
                                                            <input type="checkbox"
                                                                class="form-check-input-custom column-checkbox"
                                                                name="grants[{{ $tableIndex }}][allowed_columns][]"
                                                                value="{{ $column }}"
                                                                data-index="{{ $tableIndex }}" disabled>
                                                            <code>{{ $column }}</code>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @php $tableIndex++; @endphp
                                    @endif
                                @endforeach

                                @if ($tableIndex === 0)
                                    <div class="empty-state">Tidak ada tabel yang diminta.</div>
                                @endif
                                <div id="noTableMatch" class="empty-state d-none">Tidak ada tabel yang cocok dengan pencarian.</div>
                            </div>

                            <p class="hint">Kolom sensitif (password, token, secret, key) sudah disembunyikan otomatis.</p>
                        </section>

                        <!-- Token -->
                        <section class="config-section">
                            <div class="section-title">Token</div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="expiresAt" class="field-label mb-0">Masa Berlaku Token</label>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="noExpirySwitch" checked>
                                            <label class="form-check-label small text-success fw-bold" for="noExpirySwitch">Selamanya (Permanen)</label>
                                        </div>
                                    </div>
                                    <input type="datetime-local" id="expiresAt" class="form-control form-control-custom"
                                        value="" disabled>
                                    <p class="hint" id="expiresAtHint">Token berlaku selamanya tanpa batas waktu kedaluwarsa.</p>
                                </div>
                                <div class="col-md-6">
                                    <label for="allowedIps" class="field-label">IP whitelist (opsional)</label>
                                    <input type="text" id="allowedIps" class="form-control form-control-custom"
                                        placeholder="192.168.1.1, 10.0.0.0/24">
                                    <p class="hint">Pisahkan dengan koma. Kosongkan untuk semua IP.</p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Action bar -->
                    <div class="action-bar">
                        <div class="summary" id="selectionSummary">
                            <strong id="sumTables">0</strong> tabel &middot; <strong id="sumColumns">0</strong> kolom dipilih
                        </div>
                        <div class="actions">
                            <a href="{{ route('admin.data-access.requests') }}" class="btn btn-outline-custom">
                                <i class="ri-arrow-left-line me-1"></i>Kembali
                            </a>
                            <button type="button" class="btn btn-outline-danger" id="btnReject">
                                <i class="ri-close-line me-1"></i>Tolak
                            </button>
                            <button type="button" class="btn btn-custom btn-gradient" id="btnApprove">
                                <i class="ri-check-line me-1"></i>Setujui & buat token
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

                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold">Tolak permintaan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <label for="rejectionReason" class="field-label">
                            Alasan penolakan <span class="text-danger">*</span>
                        </label>
                        <textarea id="rejectionReason" class="form-control form-control-custom textarea-custom" rows="4" required
                            minlength="10" placeholder="Jelaskan kenapa permintaan ini ditolak"></textarea>
                        <p class="hint">Minimal 10 karakter.</p>
                    </div>

                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger" id="btnConfirmReject" disabled>
                            <i class="ri-close-line me-1"></i>Tolak permintaan
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
        /* ===== BASE ===== */
        .page-wrapper {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .hint {
            margin: 0.5rem 0 0;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        /* ===== NOTICE ===== */
        .notice {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #92400e;
            font-size: 0.875rem;
        }

        .notice i {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        [data-theme="dark"] .notice {
            background: rgba(245, 158, 11, 0.12);
            color: #fbbf24;
        }

        /* ===== ALERT (JS) ===== */
        #alertContainer:not(:empty) {
            margin-bottom: 1.5rem;
        }

        .alert-custom {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .alert-custom span { flex: 1; }
        .alert-custom i { font-size: 1.1rem; margin-top: 1px; }

        .alert-custom.success { background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e; color: #166534; }
        .alert-custom.error { background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; color: #991b1b; }
        .alert-custom.warning { background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b; color: #92400e; }

        .alert-close {
            background: none;
            border: 0;
            font-size: 1.25rem;
            line-height: 1;
            color: inherit;
            opacity: 0.6;
            cursor: pointer;
        }

        .alert-close:hover { opacity: 1; }

        /* ===== PANEL ===== */
        .panel {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .panel-head {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .panel-head i { color: var(--text-secondary); font-size: 1.1rem; }
        .panel-head h6 { margin: 0; font-weight: 600; font-size: 0.95rem; }

        .panel-body { padding: 1.25rem; margin: 0; }

        /* ===== INFO LIST ===== */
        .info-list > div {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .info-list > div:first-child { padding-top: 0; }
        .info-list > div:last-child { padding-bottom: 0; border-bottom: 0; }

        .info-list dt {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .info-list dd {
            margin: 0;
            font-size: 0.925rem;
            color: var(--text-primary);
            word-break: break-word;
        }

        .info-list code {
            background: var(--bg-body);
            padding: 0.15em 0.4em;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
        }

        .purpose-box {
            margin-top: 0.25rem !important;
            padding: 0.75rem;
            border-radius: var(--radius-sm);
            background: var(--bg-body);
            border: 1px solid var(--border-color);
            font-size: 0.875rem !important;
            line-height: 1.55;
            white-space: pre-line;
        }

        /* ===== CHIPS ===== */
        .chip {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            margin-left: 0.5rem;
            padding: 0.1rem 0.5rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .chip-ok { background: rgba(34, 197, 94, 0.15); color: #16a34a; }
        .chip-warn { background: rgba(245, 158, 11, 0.15); color: #b45309; }

        /* ===== CONFIG SECTIONS ===== */
        .config-section + .config-section {
            margin-top: 1.75rem;
            padding-top: 1.75rem;
            border-top: 1px solid var(--border-color);
        }

        .section-title {
            display: block;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .field-label {
            display: block;
            margin-bottom: 0.375rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        /* ===== FORM ELEMENTS ===== */
        .form-control-custom,
        .form-select-custom {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.55rem 0.8rem;
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

        .form-control-custom::placeholder { color: var(--text-secondary); }

        .textarea-custom { resize: vertical; min-height: 80px; line-height: 1.5; }

        .form-check-input-custom {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            cursor: pointer;
            accent-color: var(--accent-blue);
        }

        .form-check-input-custom:disabled { cursor: not-allowed; }

        .check-inline {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
            cursor: pointer;
            font-size: 0.875rem;
            color: var(--text-primary);
        }

        /* ===== TOOLBAR ===== */
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem 1.25rem;
            margin-bottom: 0.75rem;
        }

        .search-box {
            position: relative;
            flex: 1 1 220px;
            max-width: 320px;
        }

        .search-box i {
            position: absolute;
            top: 50%;
            left: 0.75rem;
            transform: translateY(-50%);
            color: var(--text-secondary);
            pointer-events: none;
        }

        .search-box input { padding-left: 2.25rem; }

        .toolbar-actions {
            display: flex;
            gap: 1rem;
            margin-left: auto;
        }

        .btn-link-sm {
            background: none;
            border: 0;
            padding: 0;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--accent-blue);
            cursor: pointer;
        }

        .btn-link-sm:hover { text-decoration: underline; }

        /* ===== TABLE ITEMS ===== */
        .table-item {
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            background: var(--bg-surface);
            transition: border-color 0.15s ease;
        }

        .table-item + .table-item { margin-top: 0.5rem; }
        .table-item.is-selected { border-color: var(--accent-blue); }

        .table-item-head {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.875rem;
        }

        .table-name-wrap { flex: 1; min-width: 0; font-weight: 500; }
        .table-name-wrap i { color: var(--text-secondary); }
        .table-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .table-meta {
            font-size: 0.8rem;
            color: var(--text-secondary);
            white-space: nowrap;
        }

        .panel-toggle {
            display: grid;
            place-items: center;
            width: 28px;
            height: 28px;
            border: 0;
            border-radius: var(--radius-sm);
            background: transparent;
            color: var(--text-secondary);
            cursor: pointer;
        }

        .panel-toggle:hover:not(:disabled) { background: var(--bg-body); }
        .panel-toggle:disabled { opacity: 0.35; cursor: default; }
        .panel-toggle i { font-size: 1.25rem; transition: transform 0.2s ease; }
        .panel-toggle.open i { transform: rotate(180deg); }

        .table-item-body {
            padding: 0.75rem 0.875rem 0.875rem;
            border-top: 1px solid var(--border-color);
            background: var(--bg-body);
            border-radius: 0 0 var(--radius-sm) var(--radius-sm);
        }

        .column-select-all-wrap {
            margin-bottom: 0.625rem;
            padding-bottom: 0.625rem;
            border-bottom: 1px dashed var(--border-color);
            display: flex;
            font-weight: 500;
        }

        .column-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 0.125rem 0.75rem;
        }

        .column-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
            padding: 0.3rem 0.4rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            min-width: 0;
        }

        .column-option:hover { background: var(--bg-surface); }
        .column-option code { font-size: 0.8rem; overflow: hidden; text-overflow: ellipsis; }

        .empty-state {
            padding: 1.5rem;
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-secondary);
            border: 1px dashed var(--border-color);
            border-radius: var(--radius-sm);
        }

        /* ===== ACTION BAR ===== */
        .action-bar {
            position: sticky;
            bottom: 0;
            z-index: 5;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem 1rem;
            padding: 0.875rem 1.25rem;
            background: var(--bg-surface);
            border-top: 1px solid var(--border-color);
        }

        .summary { font-size: 0.875rem; color: var(--text-secondary); }
        .summary strong { color: var(--text-primary); }
        .actions { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-left: auto; }

        .btn-gradient {
            background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-blue-hover) 100%);
            color: #fff;
            border: none;
        }

        .btn-gradient:hover:not(:disabled) { opacity: 0.95; color: #fff; }

        /* ===== MODAL ===== */
        .modal-content-custom {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
        }

        .modal-header, .modal-footer { background: var(--bg-surface); }
        .modal-body { background: var(--bg-surface); }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .toolbar-actions { margin-left: 0; }
            .search-box { max-width: none; }
            .actions { width: 100%; margin-left: 0; }
            .actions .btn { flex: 1 1 100%; justify-content: center; }
            .summary { width: 100%; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== CONSTANTS =====
            const requestId = document.getElementById('requestId')?.value;
            const currentToken = @json(request()->attributes->get('page_token') ?? (request()->query('_token_nav') ?? ''));
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            const baseUrlApprove = document.getElementById('baseUrlApprove')?.value.replace('__ID__', requestId);
            const baseUrlReject = document.getElementById('baseUrlReject')?.value.replace('__ID__', requestId);

            const APPROVE_LABEL = '<i class="ri-check-line me-1"></i>Setujui & buat token';
            const REJECT_LABEL = '<i class="ri-close-line me-1"></i>Tolak permintaan';
            const LOADING_LABEL = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

            // ===== ELEMENTS =====
            const selectAllTables = document.getElementById('selectAllTables');
            const tableSelectors = Array.from(document.querySelectorAll('.table-selector'));
            const sumTables = document.getElementById('sumTables');
            const sumColumns = document.getElementById('sumColumns');

            // ===== MODAL =====
            let modalReject;
            const modalRejectEl = document.getElementById('rejectModal');
            if (modalRejectEl && window.bootstrap?.Modal) {
                modalReject = new bootstrap.Modal(modalRejectEl);
            }

            // ===== HELPERS =====
            const colsOf = index => Array.from(document.querySelectorAll(`#columns_${index} .column-checkbox`));
            const panelOf = index => document.getElementById('columns_' + index);
            const toggleBtnOf = index => document.querySelector(`.panel-toggle[data-index="${index}"]`);

            function setPanelOpen(index, open) {
                const panel = panelOf(index);
                const btn = toggleBtnOf(index);
                if (panel) panel.style.display = open ? 'block' : 'none';
                if (btn) btn.classList.toggle('open', open);
            }

            function setTableChecked(selector, checked) {
                const index = selector.dataset.index;
                selector.checked = checked;

                selector.closest('.table-item')?.classList.toggle('is-selected', checked);

                const btn = toggleBtnOf(index);
                if (btn) btn.disabled = !checked;
                setPanelOpen(index, checked);

                colsOf(index).forEach(cb => {
                    cb.disabled = !checked;
                    if (!checked) cb.checked = false;
                });

                updateCount(index);
            }

            function updateCount(index) {
                const cols = colsOf(index);
                const checked = cols.filter(cb => cb.checked).length;

                const counter = document.querySelector(`.selected-count[data-index="${index}"]`);
                if (counter) counter.textContent = checked;

                const all = document.querySelector(`.column-select-all[data-index="${index}"]`);
                if (all) {
                    const tableOn = document.querySelector(`.table-selector[data-index="${index}"]`)?.checked;
                    all.disabled = !tableOn;
                    all.checked = cols.length > 0 && checked === cols.length;
                    all.indeterminate = checked > 0 && checked < cols.length;
                }

                updateSummary();
            }

            function updateSummary() {
                const selected = tableSelectors.filter(s => s.checked);
                const columns = document.querySelectorAll('.column-checkbox:checked').length;

                if (sumTables) sumTables.textContent = selected.length;
                if (sumColumns) sumColumns.textContent = columns;

                if (selectAllTables) {
                    selectAllTables.checked = tableSelectors.length > 0 && selected.length === tableSelectors.length;
                    selectAllTables.indeterminate = selected.length > 0 && selected.length < tableSelectors.length;
                    selectAllTables.disabled = tableSelectors.length === 0;
                }
            }

            const visibleSelectors = () =>
                tableSelectors.filter(s => !s.closest('.table-item').classList.contains('d-none'));

            // ===== SELECT ALL TABLES (only visible/filtered ones) =====
            selectAllTables?.addEventListener('change', function() {
                const checked = this.checked;
                visibleSelectors().forEach(s => setTableChecked(s, checked));
                updateSummary();
            });

            // ===== SINGLE TABLE TOGGLE =====
            tableSelectors.forEach(s => {
                s.addEventListener('change', function() {
                    setTableChecked(this, this.checked);
                });
            });

            // ===== EXPAND / COLLAPSE PANEL =====
            document.querySelectorAll('.panel-toggle').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = this.dataset.index;
                    const panel = panelOf(index);
                    setPanelOpen(index, panel?.style.display === 'none');
                });
            });

            // ===== SELECT ALL COLUMNS (per table) =====
            document.querySelectorAll('.column-select-all').forEach(all => {
                all.addEventListener('change', function() {
                    const index = this.dataset.index;
                    colsOf(index).forEach(cb => (cb.checked = this.checked));
                    updateCount(index);
                });
            });

            // ===== COLUMN CHECKBOX =====
            document.querySelectorAll('.column-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    updateCount(this.dataset.index);
                });
            });

            // ===== SELECT ALL COLUMNS (global, selected tables only) =====
            function setAllColumns(checked) {
                const selected = tableSelectors.filter(s => s.checked);
                if (selected.length === 0) {
                    showAlert('warning', 'Pilih minimal 1 tabel terlebih dahulu.');
                    return;
                }
                selected.forEach(s => {
                    colsOf(s.dataset.index).forEach(cb => (cb.checked = checked));
                    updateCount(s.dataset.index);
                });
            }

            document.getElementById('btnAllColumns')?.addEventListener('click', () => setAllColumns(true));
            document.getElementById('btnClearColumns')?.addEventListener('click', () => setAllColumns(false));

            // ===== TABLE SEARCH =====
            document.getElementById('tableSearch')?.addEventListener('input', function(e) {
                const search = e.target.value.toLowerCase().trim();
                let visible = 0;

                document.querySelectorAll('.table-item').forEach(card => {
                    const match = card.dataset.tableName.toLowerCase().includes(search);
                    card.classList.toggle('d-none', !match);
                    if (match) visible++;
                });

                document.getElementById('noTableMatch')?.classList.toggle('d-none',
                    visible > 0 || tableSelectors.length === 0);
            });

            // ===== NO EXPIRY SWITCH =====
            const noExpirySwitch = document.getElementById('noExpirySwitch');
            const expiresAtInput = document.getElementById('expiresAt');
            const expiresAtHint = document.getElementById('expiresAtHint');

            noExpirySwitch?.addEventListener('change', function() {
                if (this.checked) {
                    expiresAtInput.disabled = true;
                    expiresAtInput.value = '';
                    if (expiresAtHint) expiresAtHint.textContent = 'Token berlaku selamanya tanpa batas waktu kedaluwarsa.';
                } else {
                    expiresAtInput.disabled = false;
                    if (expiresAtHint) expiresAtHint.textContent = 'Tentukan batas tanggal & waktu kedaluwarsa token ini.';
                }
            });

            // ===== REJECT MODAL =====
            const rejectionReason = document.getElementById('rejectionReason');
            const btnConfirmReject = document.getElementById('btnConfirmReject');

            rejectionReason?.addEventListener('input', function() {
                if (btnConfirmReject) btnConfirmReject.disabled = this.value.trim().length < 10;
            });

            document.getElementById('btnReject')?.addEventListener('click', () => modalReject?.show());

            document.getElementById('rejectForm')?.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!rejectionReason || rejectionReason.value.trim().length < 10) {
                    showAlert('error', 'Alasan penolakan minimal 10 karakter.');
                    return;
                }

                btnConfirmReject.disabled = true;
                btnConfirmReject.innerHTML = LOADING_LABEL;

                const url = baseUrlReject + (baseUrlReject.includes('?') ? '&' : '?') + '_token_nav=' +
                    encodeURIComponent(currentToken);

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
                        modalReject?.hide();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('error', data.message || 'Gagal menolak permintaan.');
                        btnConfirmReject.disabled = false;
                        btnConfirmReject.innerHTML = REJECT_LABEL;
                    }
                } catch (error) {
                    console.error('Reject error:', error);
                    showAlert('error', 'Terjadi kesalahan koneksi.');
                    btnConfirmReject.disabled = false;
                    btnConfirmReject.innerHTML = REJECT_LABEL;
                }
            });

            // ===== APPROVE =====
            document.getElementById('btnApprove')?.addEventListener('click', async function() {
                const btnApprove = this;

                // Branch
                const branchSelect = document.getElementById('branchSelect');
                const branchId = branchSelect?.value;

                if (!branchId) {
                    showAlert('error', 'Pilih branch terlebih dahulu.');
                    branchSelect?.focus();
                    return;
                }

                // Tables
                const selectedTables = tableSelectors.filter(s => s.checked);
                if (selectedTables.length === 0) {
                    showAlert('error', 'Pilih minimal 1 tabel.');
                    return;
                }

                // Columns per table
                const invalid = selectedTables.find(s => !colsOf(s.dataset.index).some(cb => cb.checked));
                if (invalid) {
                    const card = invalid.closest('.table-item');
                    showAlert('error', `Pilih minimal 1 kolom untuk tabel: ${card?.dataset.tableName || 'unknown'}`);
                    card?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                btnApprove.disabled = true;
                btnApprove.innerHTML = LOADING_LABEL;

                const url = baseUrlApprove + (baseUrlApprove.includes('?') ? '&' : '?') +
                    '_token_nav=' + encodeURIComponent(currentToken);

                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('branch_id', branchId);

                selectedTables.forEach(selector => {
                    const index = selector.dataset.index;
                    const card = selector.closest('.table-item');
                    const tableName = card.querySelector('.grant-table-name')?.value || card.dataset.tableName;

                    formData.append(`grants[${index}][table_name]`, tableName);

                    colsOf(index).filter(cb => cb.checked).forEach(cb => {
                        formData.append(`grants[${index}][allowed_columns][]`, cb.value);
                    });

                    formData.append(`grants[${index}][rate_limit_per_minute]`, '60');
                });

                const isNoExpiry = document.getElementById('noExpirySwitch')?.checked;
                const expiresAt = isNoExpiry ? '' : (document.getElementById('expiresAt')?.value || '');
                if (expiresAt) formData.append('expires_at', expiresAt);

                const allowedIps = document.getElementById('allowedIps')?.value;
                if (allowedIps) {
                    allowedIps.split(',').map(ip => ip.trim()).filter(Boolean).forEach(ip => {
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
                        showAlert('error', data.message || 'Gagal menyetujui permintaan.');
                        btnApprove.disabled = false;
                        btnApprove.innerHTML = APPROVE_LABEL;
                    }
                } catch (error) {
                    console.error('Approve error:', error);
                    showAlert('error', 'Terjadi kesalahan koneksi.');
                    btnApprove.disabled = false;
                    btnApprove.innerHTML = APPROVE_LABEL;
                }
            });

            // ===== ALERT HELPER =====
            function showAlert(type, message) {
                const container = document.getElementById('alertContainer');
                if (!container) {
                    window.alert(`${type.toUpperCase()}: ${message}`);
                    return;
                }

                const icons = {
                    success: 'ri-checkbox-circle-fill',
                    error: 'ri-error-warning-fill',
                    warning: 'ri-alert-fill'
                };
                const kind = icons[type] ? type : 'error';

                const el = document.createElement('div');
                el.className = `alert-custom ${kind}`;
                el.setAttribute('role', 'alert');

                const icon = document.createElement('i');
                icon.className = icons[kind];

                const text = document.createElement('span');
                text.textContent = message; // textContent: avoids injecting server messages as HTML

                const close = document.createElement('button');
                close.type = 'button';
                close.className = 'alert-close';
                close.setAttribute('aria-label', 'Tutup');
                close.innerHTML = '&times;';
                close.addEventListener('click', () => el.remove());

                el.append(icon, text, close);
                container.replaceChildren(el);
                container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                setTimeout(() => {
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-4px)';
                    setTimeout(() => el.remove(), 300);
                }, 5000);
            }

            // Initial state
            tableSelectors.forEach(s => updateCount(s.dataset.index));
            updateSummary();
        });
    </script>
@endsection