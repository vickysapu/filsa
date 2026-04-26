@extends('layouts.admin')

@section('title', 'Laporan Diterima')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Laporan Diterima</h1>
                <p class="text-muted-custom mb-0">
                    <i class="ri-building-line me-1"></i> Pusat Cabang:
                    {{ auth()->user()->roles->first()?->branch->name ?? '' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                @if (auth()->user()->hasPermission('finance.report.consolidate'))
                    <button class="btn btn-outline-custom" onclick="showConsolidate()">
                        <i class="ri-calculator-line me-1"></i> Konsolidasi
                    </button>
                @endif
                <a href="{{ route('admin.finance.reports.index') }}" class="btn btn-outline-custom">
                    <i class="ri-arrow-left-line me-1"></i> Laporan Saya
                </a>
            </div>
        </div>

        <!-- Consolidate Box (Hidden by default) -->
        @if (auth()->user()->hasPermission('finance.report.consolidate'))
            <div class="card-custom mb-4" id="consolidateBox" style="display: none;">
                <div class="card-header-custom border-bottom border-custom py-3 px-4">
                    <h6 class="mb-0 fw-semibold">
                        <i class="ri-calculator-line me-2 text-primary"></i>Konsolidasi Laporan
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted-custom">Tahun</label>
                            <input type="number" id="consYear" class="form-control form-control-custom"
                                value="{{ date('Y') }}" min="2020" max="2030">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted-custom">Bulan (opsional)</label>
                            <select id="consMonth" class="form-select form-select-custom">
                                <option value="">Semua Bulan (Tahunan)</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-custom w-100" onclick="runConsolidate()">
                                <i class="ri-play-line me-1"></i> Jalankan
                            </button>
                        </div>
                    </div>

                    <!-- Consolidate Results -->
                    <div id="consolidateResult" class="row g-3 mb-4" style="display: none;">
                        <div class="col-md-4">
                            <div class="summary-card text-center p-3 border rounded">
                                <div class="text-muted-custom small">Total Laporan</div>
                                <div class="fw-bold fs-4" id="consCount">0</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card text-center p-3 border rounded">
                                <div class="text-success small">Total Pemasukan</div>
                                <div class="fw-bold fs-4 text-success" id="consIncome">Rp 0</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card text-center p-3 border rounded">
                                <div class="text-danger small">Total Pengeluaran</div>
                                <div class="fw-bold fs-4 text-danger" id="consExpense">Rp 0</div>
                            </div>
                        </div>
                    </div>

                    <!-- Consolidate Details Table -->
                    <div id="consolidateDetails"></div>
                </div>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-3 align-items-end">
                    <div style="min-width: 180px;">
                        <label class="form-label small fw-semibold text-muted-custom mb-2">Status</label>
                        <select id="filterStatus" class="form-select form-select-custom" onchange="loadReports()">
                            <option value="">Semua Status</option>
                            <option value="submitted">Menunggu Persetujuan</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <div style="min-width: 120px;">
                        <label class="form-label small fw-semibold text-muted-custom mb-2">Tahun</label>
                        <input type="number" id="filterYear" class="form-control form-control-custom" placeholder="Tahun"
                            min="2020" max="2030" onchange="loadReports()">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold text-muted-custom mb-2">&nbsp;</label>
                        <button class="btn btn-outline-custom" onclick="resetFilters()">
                            <i class="ri-refresh-line me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card-custom">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Cabang</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Periode</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Tipe</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">
                                Pemasukan</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">
                                Pengeluaran</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">Saldo
                            </th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Status</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr class="border-bottom border-custom">
                                <td class="py-3 px-4">
                                    <div class="fw-semibold">{{ $report->branch->name }}</div>
                                    <small class="text-muted-custom">{{ $report->branch->code ?? '' }}</small>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($report->report_type === 'monthly')
                                        {{ \Carbon\Carbon::create()->month($report->report_month)->year($report->report_year)->format('F Y') }}
                                    @else
                                        {{ $report->report_year }}
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge badge-secondary-custom">
                                        {{ $report->report_type === 'monthly' ? 'Bulanan' : 'Tahunan' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <span class="text-success fw-medium">
                                        Rp {{ number_format($report->total_income, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <span class="text-danger fw-medium">
                                        Rp {{ number_format($report->total_expense, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <span class="fw-bold text-primary">
                                        Rp {{ number_format($report->closing_balance, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge badge-status-{{ $report->status }}">
                                        {{ ucfirst($report->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('admin.finance.reports.show', $report) }}"
                                            class="btn-action btn-action-info" title="Detail">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @if ($report->status === 'submitted' && auth()->user()->hasPermission('finance.report.approve'))
                                            <button type="button" class="btn-action btn-action-success"
                                                onclick="approveReport('{{ $report->id }}')" title="Setujui">
                                                <i class="ri-check-line"></i>
                                            </button>
                                            <button type="button" class="btn-action btn-action-danger"
                                                onclick="openRejectModal('{{ $report->id }}')" title="Tolak">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-5">
                                    <div class="text-center">
                                        <div class="empty-state-icon mb-3">
                                            <i class="ri-inbox-line"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-2">Belum ada laporan yang diterima</h6>
                                        <p class="text-muted-custom small mb-0">Laporan dari cabang akan muncul disini
                                            setelah dikirim</p>
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

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="rejectForm">
                    <input type="hidden" id="rejectReportId">
                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold">
                            <i class="ri-close-circle-line me-2 text-danger"></i>Tolak Laporan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted-custom">Alasan Penolakan <span
                                    class="text-danger">*</span></label>
                            <textarea id="rejectReason" class="form-control form-control-custom textarea-custom" rows="4" required
                                minlength="10" placeholder="Jelaskan alasan penolakan..."></textarea>
                            <small class="text-muted-custom">Minimal 10 karakter</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="ri-close-line me-1"></i> Tolak
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

        /* ===== SUMMARY CARD ===== */
        .summary-card {
            background: var(--bg-body);
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .summary-card:hover {
            border-color: var(--accent-blue);
            box-shadow: var(--shadow-sm);
        }

        /* ===== STATUS BADGES (Consistent with system) ===== */
        .badge-status-submitted {
            background: rgba(59, 130, 246, 0.15);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-approved {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-rejected {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
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

        .btn-action-success {
            border-color: #22c55e;
            color: #22c55e;
        }

        .btn-action-success:hover {
            background: #22c55e;
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

        /* ===== CONSOLIDATE DETAILS TABLE ===== */
        #consolidateDetails .table {
            margin: 0;
        }

        #consolidateDetails .table> :not(caption)>*>* {
            background: var(--bg-surface);
            border-color: var(--border-color);
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

        [data-theme="dark"] .summary-card {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        [data-theme="dark"] #consolidateDetails .table> :not(caption)>*>* {
            background: var(--bg-surface);
        }

        [data-theme="dark"] .empty-state-icon {
            background: var(--bg-sidebar);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .table-responsive table {
                min-width: 1000px;
            }

            .d-flex.flex-wrap.gap-3 {
                gap: 1rem !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== MODAL INSTANCE =====
            let rejectModal;
            const modalEl = document.getElementById('rejectModal');
            if (modalEl && bootstrap.Modal) {
                rejectModal = new bootstrap.Modal(modalEl);
            }

            // ===== TOOLTIP INIT =====
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });

            // ===== URLS =====
            const urls = {
                consolidate: "{{ route('admin.finance.reports.consolidate') }}",
                approve: "{{ route('admin.finance.reports.approve', ':id') }}",
                reject: "{{ route('admin.finance.reports.reject', ':id') }}"
            };

            // ===== FILTER FUNCTIONS =====
            window.loadReports = function() {
                const params = new URLSearchParams();
                const s = document.getElementById('filterStatus')?.value;
                const y = document.getElementById('filterYear')?.value;
                if (s) params.append('status', s);
                if (y) params.append('year', y);
                window.location.href = '{{ route('admin.finance.reports.received') }}?' + params.toString();
            }

            window.resetFilters = function() {
                document.getElementById('filterStatus').value = '';
                document.getElementById('filterYear').value = '';
                window.location.href = '{{ route('admin.finance.reports.received') }}';
            }

            // ===== CONSOLIDATE FUNCTIONS =====
            window.showConsolidate = function() {
                const box = document.getElementById('consolidateBox');
                if (box) {
                    box.style.display = box.style.display === 'none' ? 'block' : 'none';
                    if (box.style.display === 'block') {
                        box.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            }

            window.runConsolidate = function() {
                const y = document.getElementById('consYear')?.value;
                const m = document.getElementById('consMonth')?.value;
                const btn = document.querySelector('#consolidateBox .btn-custom');

                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
                }

                fetch(urls.consolidate, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            year: y,
                            month: m || null
                        })
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            const resultEl = document.getElementById('consolidateResult');
                            if (resultEl) resultEl.style.display = 'grid';

                            document.getElementById('consCount').textContent = res.data.reports_count;

                            const fmt = n => new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(n);

                            document.getElementById('consIncome').textContent = fmt(res.data.total_income);
                            document.getElementById('consExpense').textContent = fmt(res.data
                            .total_expense);

                            let html =
                                '<div class="table-responsive mt-4"><table class="table table-hover mb-0 align-middle"><thead class="bg-body-tertiary"><tr><th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Kategori</th><th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Tipe</th><th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">Total</th><th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Rincian per Cabang</th></tr></thead><tbody>';

                            res.data.details.forEach(d => {
                                const branches = Object.entries(d.branches).map(([n, a]) =>
                                    `${n}: ${new Intl.NumberFormat('id-ID').format(a)}`).join(
                                    ', ');
                                const amountClass = d.type === 'income' ? 'text-success' :
                                    'text-danger';
                                const typeLabel = d.type === 'income' ? 'Pemasukan' : 'Pengeluaran';
                                html +=
                                    `<tr class="border-bottom border-custom"><td class="py-3 px-4 fw-medium">${d.category_name}</td><td class="py-3 px-4"><span class="badge badge-secondary-custom">${typeLabel}</span></td><td class="py-3 px-4 text-end"><span class="fw-bold ${amountClass}">${fmt(d.amount)}</span></td><td class="py-3 px-4"><small class="text-muted-custom">${branches}</small></td></tr>`;
                            });

                            html += '</tbody></table></div>';
                            document.getElementById('consolidateDetails').innerHTML = html;

                            showToast('success', 'Berhasil', 'Konsolidasi berhasil dijalankan');
                        } else {
                            showToast('error', 'Gagal', res.message || 'Gagal konsolidasi');
                        }
                    })
                    .catch(() => showToast('error', 'Error', 'Terjadi kesalahan saat konsolidasi'))
                    .finally(() => {
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="ri-play-line me-1"></i> Jalankan';
                        }
                    });
            }

            // ===== APPROVE REPORT =====
            window.approveReport = function(id) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Setujui laporan?',
                        text: 'Laporan ini akan ditandai sebagai disetujui dan tidak dapat diubah.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#22c55e',
                        confirmButtonText: 'Ya, Setujui',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) performApprove(id);
                    });
                } else {
                    if (confirm('Setujui laporan ini?')) {
                        performApprove(id);
                    }
                }
            }

            function performApprove(id) {
                const url = urls.approve.replace(':id', id);

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            showToast('success', 'Disetujui', res.message);
                            setTimeout(() => location.reload(), 500);
                        } else {
                            showToast('error', 'Gagal', res.message);
                        }
                    })
                    .catch(() => showToast('error', 'Error', 'Terjadi kesalahan saat menyetujui'));
            }

            // ===== REJECT MODAL =====
            window.openRejectModal = function(id) {
                document.getElementById('rejectReportId').value = id;
                document.getElementById('rejectReason').value = '';
                if (rejectModal) rejectModal.show();
            }

            window.closeModal = function() {
                if (rejectModal) rejectModal.hide();
            }

            // ===== REJECT FORM SUBMIT =====
            const rejectForm = document.getElementById('rejectForm');
            if (rejectForm) {
                rejectForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const id = document.getElementById('rejectReportId').value;
                    const reason = document.getElementById('rejectReason').value;
                    const url = urls.reject.replace(':id', id);
                    const btn = this.querySelector('button[type="submit"]');

                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
                    }

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    ?.content || ''
                            },
                            body: JSON.stringify({
                                rejection_reason: reason
                            })
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                showToast('success', 'Ditolak', res.message);
                                if (rejectModal) rejectModal.hide();
                                setTimeout(() => location.reload(), 500);
                            } else {
                                showToast('error', 'Gagal', res.message);
                            }
                        })
                        .catch(() => showToast('error', 'Error', 'Terjadi kesalahan saat menolak'))
                        .finally(() => {
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = '<i class="ri-close-line me-1"></i> Tolak';
                            }
                        });
                });
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
