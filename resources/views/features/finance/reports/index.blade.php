@extends('layouts.admin')

@section('title', 'Laporan Keuangan')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Laporan Keuangan</h1>
                <p class="text-muted-custom mb-0">
                    <i class="ri-building-line me-1"></i> {{ auth()->user()->roles->first()?->branch->name ?? '' }}
                </p>
            </div>
            @can('finance.report.create')
                <a href="{{ route('admin.finance.reports.create') }}" class="btn btn-custom">
                    <i class="ri-add-line me-1"></i> Buat Laporan Baru
                </a>
            @endcan
        </div>

        <!-- Tab Navigation -->
        <div class="nav-tabs-custom mb-4" role="tablist">
            <a href="{{ route('admin.finance.reports.index', array_merge(request()->query(), ['scope' => 'internal'])) }}"
                class="nav-tab-item {{ request('scope', 'internal') === 'internal' ? 'active' : '' }}" role="tab">
                <i class="ri-file-list-3-line me-2"></i>Laporan Saya
            </a>
            <a href="{{ route('admin.finance.reports.index', array_merge(request()->query(), ['scope' => 'external'])) }}"
                class="nav-tab-item {{ request('scope') === 'external' ? 'active' : '' }}" role="tab">
                <i class="ri-send-plane-line me-2"></i>Laporan Terkirim
            </a>
            <a href="{{ route('admin.finance.reports.received') }}"
                class="nav-tab-item {{ request('scope') === 'received' ? 'active' : '' }}" role="tab">
                <i class="ri-inbox-line me-2"></i>Laporan Masuk
                @if (($pendingCount ?? 0) > 0)
                    <span class="badge badge-notification">{{ $pendingCount }}</span>
                @endif
            </a>
        </div>

        <!-- Filter Card -->
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted-custom mb-2">Tipe Laporan</label>
                        <select id="filterType" class="form-select form-select-custom" onchange="applyFilters()">
                            <option value="">Semua Tipe</option>
                            <option value="monthly" {{ request('type') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            <option value="annual" {{ request('type') === 'annual' ? 'selected' : '' }}>Tahunan</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted-custom mb-2">Status</label>
                        <select id="filterStatus" class="form-select form-select-custom" onchange="applyFilters()">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Terkirim
                            </option>
                            <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Sedang
                                Direview</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui
                            </option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak
                            </option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted-custom mb-2">Tahun</label>
                        <input type="number" id="filterYear" class="form-control form-control-custom" placeholder="Tahun"
                            value="{{ request('year') }}" min="2020" max="2030" onchange="applyFilters()">
                    </div>
                    <div class="col-6 col-md-3">
                        <button type="button" class="btn btn-outline-custom w-100" onclick="resetFilters()">
                            <i class="ri-refresh-line me-1"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card-custom">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Periode</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Tipe</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">
                                Pemasukan</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">
                                Pengeluaran</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">Saldo
                                Akhir</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Status</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Tujuan / Dari
                            </th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr class="border-bottom border-custom">
                                <td class="py-3 px-4">
                                    <div class="fw-semibold">
                                        @if ($report->report_type === 'monthly')
                                            {{ \Carbon\Carbon::create()->month($report->report_month)->year($report->report_year)->format('F Y') }}
                                        @else
                                            Tahunan {{ $report->report_year }}
                                        @endif
                                    </div>
                                    <small class="text-muted-custom">
                                        {{ $report->period_start?->format('d M') }} –
                                        {{ $report->period_end?->format('d M Y') }}
                                    </small>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge badge-secondary-custom">
                                        {{ $report->report_type === 'monthly' ? 'Bulanan' : 'Tahunan' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <span class="text-success fw-medium">
                                        Rp {{ number_format((float) $report->total_income, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <span class="text-danger fw-medium">
                                        Rp {{ number_format((float) $report->total_expense, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <span class="fw-bold text-primary">
                                        Rp {{ number_format((float) $report->closing_balance, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge badge-status-{{ $report->status }}">
                                        <i
                                            class="ri-{{ $report->status === 'draft' ? 'draft-line' : ($report->status === 'approved' ? 'checkbox-circle-line' : ($report->status === 'rejected' ? 'close-circle-line' : ($report->status === 'reviewed' ? 'eye-line' : 'send-plane-line'))) }} me-1"></i>
                                        {{ $report->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($report->submittedToBranch)
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="ri-arrow-right-line text-muted-custom small"></i>
                                            <span class="small">{{ $report->submittedToBranch->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted-custom small">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('admin.finance.reports.show', $report) }}"
                                            class="btn-action btn-action-info" title="Detail">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @if ($report->status === 'draft')
                                            <a href="{{ route('admin.finance.reports.edit', $report) }}"
                                                class="btn-action btn-action-primary" title="Edit">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                            @can('finance.report.submit')
                                                <button type="button" class="btn-action btn-action-warning"
                                                    onclick="openSubmitModal('{{ $report->id }}')" title="Kirim">
                                                    <i class="ri-send-plane-line"></i>
                                                </button>
                                            @endcan
                                            <button type="button" class="btn-action btn-action-danger"
                                                onclick="deleteReport('{{ $report->id }}')" title="Hapus">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        @endif
                                        @can('finance.report.export')
                                            <a href="{{ route('admin.finance.export.annual-report', $report) }}"
                                                class="btn-action btn-action-success" title="Export Excel">
                                                <i class="ri-file-excel-2-line"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-5">
                                    <div class="text-center">
                                        <div class="empty-state-icon mb-3">
                                            <i class="ri-file-list-3-line"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-2">Tidak ada laporan</h6>
                                        <p class="text-muted-custom small mb-0">
                                            @can('finance.report.create')
                                                <a href="{{ route('admin.finance.reports.create') }}"
                                                    class="text-primary text-decoration-none">Buat laporan pertama Anda</a>
                                            @else
                                                Hubungi admin untuk akses pembuatan laporan
                                            @endcan
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($reports->hasPages())
                <div class="d-flex justify-content-center p-4 border-top border-custom">
                    {{ $reports->withQueryString()->links('pagination::simple-bootstrap-4') }}
                </div>
            @endif
        </div>

    </div>

    <!-- Modal Submit Report -->
    <div class="modal fade" id="submitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold">
                        <i class="ri-send-plane-line me-2 text-primary"></i>Kirim Laporan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert-info-custom mb-4">
                        <i class="ri-information-line me-2"></i>
                        <span class="small">Laporan akan dikirim ke cabang/pusat tujuan untuk direview. Pastikan data
                            sudah benar.</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted-custom">Kirim ke Cabang / Pusat <span
                                class="text-danger">*</span></label>
                        <select id="submitTo" class="form-select form-select-custom" required>
                            <option value="">— Pilih Tujuan —</option>
                            @foreach (\App\Models\Branch::where('id', '!=', auth()->user()->roles->first()?->branch_id)->where('is_active', true)->orderBy('name')->get() as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small fw-semibold text-muted-custom">Catatan (opsional)</label>
                        <textarea id="submitNotes" class="form-control form-control-custom textarea-custom" rows="3"
                            placeholder="Catatan untuk penerima..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-custom py-3 px-4">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-custom" onclick="doSubmit()">
                        <i class="ri-send-plane-line me-1"></i> Kirim
                    </button>
                </div>
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

        /* ===== TABS CUSTOM ===== */
        .nav-tabs-custom {
            display: flex;
            gap: 0.5rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0;
            flex-wrap: wrap;
        }

        .nav-tab-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border-radius: var(--radius-sm) var(--radius-sm) 0 0;
        }

        .nav-tab-item:hover {
            color: var(--accent-blue);
            background: rgba(37, 99, 235, 0.05);
        }

        .nav-tab-item.active {
            color: var(--accent-blue);
            border-bottom-color: var(--accent-blue);
            font-weight: 600;
            background: rgba(37, 99, 235, 0.08);
        }

        .badge-notification {
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            padding: 0.2em 0.5em;
            border-radius: 20px;
            margin-left: 0.35rem;
            font-weight: 600;
        }

        /* ===== STATUS BADGES ===== */
        .badge-status-draft {
            background: rgba(245, 158, 11, 0.15);
            color: #b45309;
            border: 1px solid rgba(245, 158, 11, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
        }

        .badge-status-submitted {
            background: rgba(59, 130, 246, 0.15);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
        }

        .badge-status-reviewed {
            background: rgba(139, 92, 246, 0.15);
            color: #7c3aed;
            border: 1px solid rgba(139, 92, 246, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
        }

        .badge-status-approved {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
        }

        .badge-status-rejected {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
        }

        .badge-secondary-custom {
            background: var(--bg-body);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            padding: 0.25em 0.5em;
            font-size: 0.75rem;
            font-weight: 500;
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

        .btn-action-info {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .btn-action-info:hover {
            background: var(--accent-blue);
            color: white;
        }

        .btn-action-primary {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .btn-action-primary:hover {
            background: var(--accent-blue);
            color: white;
        }

        .btn-action-warning {
            border-color: #f59e0b;
            color: #f59e0b;
        }

        .btn-action-warning:hover {
            background: #f59e0b;
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

        .btn-action-success {
            border-color: #22c55e;
            color: #22c55e;
        }

        .btn-action-success:hover {
            background: #22c55e;
            color: white;
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

        /* ===== ALERT INFO ===== */
        .alert-info-custom {
            background: rgba(37, 99, 235, 0.08);
            border: 1px solid rgba(37, 99, 235, 0.2);
            border-radius: var(--radius-sm);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            color: var(--text-primary);
        }

        .alert-info-custom i {
            color: var(--accent-blue);
            font-size: 1.1rem;
            margin-top: 2px;
        }

        /* ===== EMPTY STATE ===== */
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
            background: var(--bg-sidebar) !important;
        }

        [data-theme="dark"] .nav-tab-item:hover {
            background: rgba(37, 99, 235, 0.1);
        }

        [data-theme="dark"] .nav-tab-item.active {
            background: rgba(37, 99, 235, 0.15);
        }

        [data-theme="dark"] .alert-info-custom {
            background: rgba(37, 99, 235, 0.12);
            border-color: rgba(37, 99, 235, 0.3);
        }

        [data-theme="dark"] .empty-state-icon {
            background: var(--bg-sidebar);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .nav-tabs-custom {
                flex-wrap: wrap;
            }

            .nav-tab-item {
                padding: 0.625rem 1rem;
                font-size: 0.85rem;
            }

            .table-responsive table {
                min-width: 900px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== MODAL INSTANCE =====
            let submitModal;
            const modalEl = document.getElementById('submitModal');
            if (modalEl && bootstrap.Modal) {
                submitModal = new bootstrap.Modal(modalEl);
            }

            // ===== TOOLTIP INIT =====
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });

            // ===== TAB SWITCHING =====
            window.switchTab = function(scope) {
                if (scope === 'received') {
                    window.location.href = '{{ route('admin.finance.reports.received') }}';
                    return;
                }
                const params = new URLSearchParams(window.location.search);
                params.set('scope', scope);
                window.location.href = '{{ route('admin.finance.reports.index') }}?' + params.toString();
            }

            // ===== FILTER FUNCTIONS =====
            window.applyFilters = function() {
                const params = new URLSearchParams();
                const scope = '{{ request('scope', 'internal') }}';
                const type = document.getElementById('filterType')?.value;
                const status = document.getElementById('filterStatus')?.value;
                const year = document.getElementById('filterYear')?.value;

                params.append('scope', scope);
                if (type) params.append('type', type);
                if (status) params.append('status', status);
                if (year) params.append('year', year);

                window.location.href = '{{ route('admin.finance.reports.index') }}?' + params.toString();
            }

            window.resetFilters = function() {
                window.location.href = '{{ route('admin.finance.reports.index') }}';
            }

            // ===== MODAL FUNCTIONS =====
            let currentReportId = null;

            window.openSubmitModal = function(id) {
                currentReportId = id;
                document.getElementById('submitTo').value = '';
                document.getElementById('submitNotes').value = '';
                if (submitModal) submitModal.show();
            }

            window.closeModal = function() {
                if (submitModal) submitModal.hide();
            }

            // ===== SUBMIT REPORT =====
            window.doSubmit = function() {
                const to = document.getElementById('submitTo').value;
                const notes = document.getElementById('submitNotes').value;

                if (!to) {
                    showToast('error', 'Peringatan', 'Pilih cabang tujuan terlebih dahulu');
                    return;
                }

                const btn = document.querySelector('#submitModal .btn-custom');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mengirim...';
                }

                fetch(`/admin/finance/reports/${currentReportId}/submit`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            submitted_to: to,
                            notes: notes
                        })
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            showToast('success', 'Berhasil', res.message);
                            if (submitModal) submitModal.hide();
                            setTimeout(() => location.reload(), 500);
                        } else {
                            showToast('error', 'Gagal', res.message || 'Gagal mengirim laporan');
                        }
                    })
                    .catch(() => showToast('error', 'Error', 'Terjadi kesalahan saat mengirim'))
                    .finally(() => {
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="ri-send-plane-line me-1"></i> Kirim';
                        }
                    });
            }

            // ===== DELETE REPORT =====
            window.deleteReport = function(id) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Yakin hapus?',
                        text: 'Laporan ini akan dihapus permanen dan tidak dapat dikembalikan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) performDelete(id);
                    });
                } else {
                    if (confirm('Hapus laporan ini? Tindakan ini tidak dapat dibatalkan.')) {
                        performDelete(id);
                    }
                }
            }

            function performDelete(id) {
                fetch(`/admin/finance/reports/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            showToast('success', 'Terhapus', res.message);
                            setTimeout(() => location.reload(), 500);
                        } else {
                            showToast('error', 'Gagal', res.message || 'Gagal menghapus laporan');
                        }
                    })
                    .catch(() => showToast('error', 'Error', 'Terjadi kesalahan saat menghapus'));
            }

            // ===== TOAST NOTIFICATION =====
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
        });
    </script>
@endsection
