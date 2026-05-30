@extends('layouts.admin')

@section('title', 'Laporan Masuk')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Laporan Masuk</h1>
                <p class="text-muted-custom mb-0">
                    <i class="ri-building-line me-1"></i> {{ auth()->user()->roles->first()?->branch->name ?? '' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                @if (auth()->user()->hasPermission('finance.report.consolidate'))
                    <button type="button" class="btn btn-outline-custom" onclick="toggleConsolidate()">
                        <i class="ri-calculator-line me-1"></i> Konsolidasi
                    </button>
                @endif
                <a href="{{ route('admin.finance.reports.index') }}" class="btn btn-outline-custom">
                    <i class="ri-arrow-left-line me-1"></i> Laporan Saya
                </a>
            </div>
        </div>

        <!-- Stat Cards -->
        @php
            $allReceived = $reports->getCollection();
            $statPending = $allReceived->whereIn('status', ['submitted', 'reviewed'])->count();
            $statApproved = $allReceived->where('status', 'approved')->count();
            $statRejected = $allReceived->where('status', 'rejected')->count();
        @endphp
        <div class="row g-4 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card text-center p-4 border rounded">
                    <div class="text-muted-custom small text-uppercase fw-semibold">Menunggu Review</div>
                    <div class="fw-bold display-6 text-warning">{{ $statPending }}</div>
                    <small class="text-muted-custom">Laporan pending</small>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card text-center p-4 border rounded">
                    <div class="text-muted-custom small text-uppercase fw-semibold">Disetujui</div>
                    <div class="fw-bold display-6 text-success">{{ $statApproved }}</div>
                    <small class="text-muted-custom">Laporan approved</small>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card text-center p-4 border rounded">
                    <div class="text-muted-custom small text-uppercase fw-semibold">Ditolak</div>
                    <div class="fw-bold display-6 text-danger">{{ $statRejected }}</div>
                    <small class="text-muted-custom">Laporan rejected</small>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card text-center p-4 border rounded">
                    <div class="text-muted-custom small text-uppercase fw-semibold">Total</div>
                    <div class="fw-bold display-6 text-primary">{{ $reports->total() }}</div>
                    <small class="text-muted-custom">Semua laporan</small>
                </div>
            </div>
        </div>

        <!-- Consolidate Box -->
        @if (auth()->user()->hasPermission('finance.report.consolidate'))
            <div class="card-custom mb-4" id="consolidateBox" style="display:none">
                <div class="card-header-custom border-bottom border-custom py-3 px-4">
                    <h6 class="mb-0 fw-semibold">
                        <i class="ri-calculator-line me-2 text-primary"></i>Konsolidasi Laporan Cabang
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
                            <button type="button" class="btn btn-custom w-100" onclick="runConsolidate()">
                                <i class="ri-play-line me-1"></i> Jalankan
                            </button>
                        </div>
                    </div>

                    <!-- Consolidate Results -->
                    <div id="consolidateResult" style="display:none">
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-lg-3">
                                <div class="summary-card text-center p-3 border rounded">
                                    <div class="text-muted-custom small">Laporan</div>
                                    <div class="fw-bold fs-4" id="consCount">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="summary-card text-center p-3 border rounded">
                                    <div class="text-muted-custom small">Total Pemasukan</div>
                                    <div class="fw-bold fs-5 text-success" id="consIncome">Rp 0</div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="summary-card text-center p-3 border rounded">
                                    <div class="text-muted-custom small">Total Pengeluaran</div>
                                    <div class="fw-bold fs-5 text-danger" id="consExpense">Rp 0</div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="summary-card text-center p-3 border rounded">
                                    <div class="text-muted-custom small">Selisih Bersih</div>
                                    <div class="fw-bold fs-5" id="consNet">Rp 0</div>
                                </div>
                            </div>
                        </div>
                        <div id="consolidateDetails"></div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted-custom mb-2">Status</label>
                        <select id="filterStatus" class="form-select form-select-custom" onchange="loadReports()">
                            <option value="">Semua Status</option>
                            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Menunggu
                                Review</option>
                            <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Sedang
                                Direview</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui
                            </option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak
                            </option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted-custom mb-2">Tipe</label>
                        <select id="filterType" class="form-select form-select-custom" onchange="loadReports()">
                            <option value="">Semua Tipe</option>
                            <option value="monthly" {{ request('type') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            <option value="annual" {{ request('type') === 'annual' ? 'selected' : '' }}>Tahunan</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted-custom mb-2">Tahun</label>
                        <input type="number" id="filterYear" class="form-control form-control-custom"
                            placeholder="Tahun" value="{{ request('year') }}" min="2020" max="2030"
                            onchange="loadReports()">
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
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Cabang
                                Pengirim</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Periode</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Tipe</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">
                                Pemasukan</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">
                                Pengeluaran</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">
                                Saldo Akhir</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Rantai
                                Pengiriman</th>
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
                                    <div class="fw-medium">
                                        @if ($report->report_type === 'monthly')
                                            {{ \Carbon\Carbon::create()->month($report->report_month)->year($report->report_year)->format('F Y') }}
                                        @else
                                            {{ $report->report_year }}
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
                                    <div class="forward-chain d-flex flex-wrap align-items-center gap-1">
                                        <span class="forward-node">{{ $report->branch->name }}</span>
                                        @foreach ($report->forwards as $fwd)
                                            <span class="forward-arrow text-muted-custom small">
                                                @if ($fwd->action === 'approve')
                                                    <i class="ri-check-line"></i>
                                                @elseif($fwd->action === 'reject')
                                                    <i class="ri-close-line"></i>
                                                @else
                                                    <i class="ri-arrow-right-line"></i>
                                                @endif
                                            </span>
                                            <span
                                                class="forward-node {{ $fwd->action === 'approve' ? 'node-approved' : ($fwd->action === 'reject' ? 'node-rejected' : '') }}">
                                                {{ $fwd->toBranch->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge badge-status-{{ $report->status }}">
                                        {{ $report->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="d-flex gap-1 justify-content-center flex-wrap">
                                        <a href="{{ route('admin.finance.reports.show', $report) }}"
                                            class="btn-action btn-action-info" title="Detail">
                                            <i class="ri-eye-line"></i>
                                        </a>

                                        @if (in_array($report->status, ['submitted', 'reviewed']) && auth()->user()->hasPermission('finance.report.approve'))
                                            <button type="button" class="btn-action btn-action-success"
                                                onclick="approveReport('{{ $report->id }}')" title="Setujui">
                                                <i class="ri-checkbox-circle-line"></i>
                                            </button>
                                            <button type="button" class="btn-action btn-action-danger"
                                                onclick="openRejectModal('{{ $report->id }}')" title="Tolak">
                                                <i class="ri-close-circle-line"></i>
                                            </button>
                                        @endif

                                        @if (in_array($report->status, ['submitted', 'reviewed']) && auth()->user()->hasPermission('finance.report.forward'))
                                            <button type="button" class="btn-action btn-action-warning"
                                                onclick="openForwardModal('{{ $report->id }}')"
                                                title="Teruskan ke Atasan">
                                                <i class="ri-share-forward-line"></i>
                                            </button>
                                        @endif

                                        @if (auth()->user()->hasPermission('finance.report.export'))
                                            <a href="{{ route('admin.finance.export.annual-report', $report) }}"
                                                class="btn-action btn-action-success" title="Export Excel">
                                                <i class="ri-file-excel-2-line"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-5">
                                    <div class="text-center">
                                        <div class="empty-state-icon mb-3">
                                            <i class="ri-inbox-line"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-2">Belum ada laporan yang masuk</h6>
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
                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold">
                        <i class="ri-close-circle-line me-2 text-danger"></i>Tolak Laporan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="rejectReportId">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted-custom">Alasan Penolakan <span
                                class="text-danger">*</span></label>
                        <textarea id="rejectReason" class="form-control form-control-custom textarea-custom" rows="4" required
                            minlength="10" placeholder="Jelaskan alasan penolakan laporan ini (minimal 10 karakter)..."></textarea>
                        <small class="text-muted-custom">Minimal 10 karakter</small>
                    </div>
                </div>
                <div class="modal-footer border-top border-custom py-3 px-4">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" onclick="doReject()">
                        <i class="ri-close-circle-line me-1"></i> Tolak Laporan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Forward Modal -->
    <div class="modal fade" id="forwardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header border-bottom border-custom">
                    <h5 class="modal-title fw-semibold">
                        <i class="ri-share-forward-line me-2 text-primary"></i>Teruskan ke Atasan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="forwardReportId">
                    <div class="alert-info-custom mb-4">
                        <i class="ri-information-line me-2"></i>
                        <span class="small">Laporan akan diteruskan ke tingkat berikutnya untuk review lebih
                            lanjut.</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted-custom">Tujuan Berikutnya <span
                                class="text-danger">*</span></label>
                        <select id="forwardTo" class="form-select form-select-custom" required>
                            <option value="">— Pilih Cabang / Pusat —</option>
                            @foreach ($availableBranches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small fw-semibold text-muted-custom">Catatan untuk Penerima
                            (opsional)</label>
                        <textarea id="forwardNotes" class="form-control form-control-custom textarea-custom" rows="3"
                            placeholder="Tambahkan catatan atau instruksi khusus..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-custom py-3 px-4">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-custom" onclick="doForward()">
                        <i class="ri-share-forward-line me-1"></i> Teruskan
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

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent-blue);
        }

        .stat-card .display-6 {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        /* ===== SUMMARY CARD (for consolidate) ===== */
        .summary-card {
            background: var(--bg-body);
            border: 1px solid var(--border-color);
        }

        /* ===== STATUS BADGES ===== */
        .badge-status-submitted {
            background: rgba(59, 130, 246, 0.15);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-status-reviewed {
            background: rgba(139, 92, 246, 0.15);
            color: #7c3aed;
            border: 1px solid rgba(139, 92, 246, 0.3);
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

        /* ===== FORWARD CHAIN ===== */
        .forward-chain {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .forward-node {
            padding: 0.2em 0.5em;
            border-radius: var(--radius-sm);
            background: var(--bg-body);
            border: 1px solid var(--border-color);
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        .forward-node.node-approved {
            background: rgba(34, 197, 94, 0.15);
            border-color: rgba(34, 197, 94, 0.3);
            color: #22c55e;
        }

        .forward-node.node-rejected {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .forward-arrow {
            font-size: 0.7rem;
            color: var(--text-secondary);
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

        .btn-action-warning {
            border-color: #f59e0b;
            color: #f59e0b;
        }

        .btn-action-warning:hover {
            background: #f59e0b;
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

        [data-theme="dark"] .stat-card,
        [data-theme="dark"] .summary-card {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .forward-node {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
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
            .table-responsive table {
                min-width: 1200px;
            }

            .stat-card .display-6 {
                font-size: 1.5rem;
            }

            .forward-chain {
                max-width: 200px;
            }

            .d-flex.flex-wrap.gap-1 {
                gap: 0.25rem !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== MODAL INSTANCES =====
            let rejectModal, forwardModal;
            const rejectModalEl = document.getElementById('rejectModal');
            const forwardModalEl = document.getElementById('forwardModal');

            if (rejectModalEl && bootstrap.Modal) rejectModal = new bootstrap.Modal(rejectModalEl);
            if (forwardModalEl && bootstrap.Modal) forwardModal = new bootstrap.Modal(forwardModalEl);

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
                reject: "{{ route('admin.finance.reports.reject', ':id') }}",
                forward: "{{ route('admin.finance.reports.forward', ':id') }}"
            };

            // ===== CURRENCY FORMATTER =====
            const fmt = n => new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(n);

            // ===== FILTER FUNCTIONS =====
            window.loadReports = function() {
                const params = new URLSearchParams();
                const s = document.getElementById('filterStatus')?.value;
                const t = document.getElementById('filterType')?.value;
                const y = document.getElementById('filterYear')?.value;

                if (s) params.append('status', s);
                if (t) params.append('type', t);
                if (y) params.append('year', y);

                window.location.href = '{{ route('admin.finance.reports.received') }}?' + params.toString();
            }

            window.resetFilters = function() {
                window.location.href = '{{ route('admin.finance.reports.received') }}';
            }

            // ===== CONSOLIDATE FUNCTIONS =====
            window.toggleConsolidate = function() {
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
                        if (!res.success) {
                            showToast('error', 'Gagal', res.message || 'Gagal konsolidasi');
                            return;
                        }

                        const resultEl = document.getElementById('consolidateResult');
                        if (resultEl) resultEl.style.display = 'block';

                        document.getElementById('consCount').textContent = res.data.reports_count;
                        document.getElementById('consIncome').textContent = fmt(res.data.total_income);
                        document.getElementById('consExpense').textContent = fmt(res.data.total_expense);

                        const net = res.data.net_balance ?? (res.data.total_income - res.data
                        .total_expense);
                        const netEl = document.getElementById('consNet');
                        netEl.textContent = fmt(net);
                        netEl.className = `fw-bold fs-5 ${net >= 0 ? 'text-success' : 'text-danger'}`;

                        let html =
                            '<div class="table-responsive mt-4"><table class="table table-hover mb-0 align-middle"><thead class="bg-body-tertiary"><tr><th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Kategori</th><th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Tipe</th><th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">Total</th><th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Rincian per Cabang</th></tr></thead><tbody>';

                        res.data.details.forEach(d => {
                            const branches = Object.entries(d.branches).map(([n, a]) =>
                                `${n}: ${new Intl.NumberFormat('id-ID').format(a)}`).join(' • ');
                            const typeBadge = d.type === 'income' ?
                                '<span class="badge badge-status-approved">Pemasukan</span>' :
                                '<span class="badge badge-status-rejected">Pengeluaran</span>';
                            const amountClass = d.type === 'income' ? 'text-success' :
                            'text-danger';

                            html += `<tr class="border-bottom border-custom">
                    <td class="py-3 px-4 fw-medium">${d.category_name}</td>
                    <td class="py-3 px-4">${typeBadge}</td>
                    <td class="py-3 px-4 text-end"><span class="fw-bold ${amountClass}">${fmt(d.amount)}</span></td>
                    <td class="py-3 px-4"><small class="text-muted-custom">${branches}</small></td>
                </tr>`;
                        });

                        html += '</tbody></table></div>';
                        document.getElementById('consolidateDetails').innerHTML = html;

                        showToast('success', 'Berhasil', 'Konsolidasi berhasil dijalankan');
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
                        text: 'Laporan ini akan ditandai sebagai disetujui.',
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
                fetch(urls.approve.replace(':id', id), {
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

            window.doReject = function() {
                const id = document.getElementById('rejectReportId').value;
                const reason = document.getElementById('rejectReason').value.trim();

                if (reason.length < 10) {
                    showToast('error', 'Validasi', 'Alasan penolakan minimal 10 karakter');
                    return;
                }

                const btn = document.querySelector('#rejectModal .btn-danger');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
                }

                fetch(urls.reject.replace(':id', id), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
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
                            btn.innerHTML = '<i class="ri-close-circle-line me-1"></i> Tolak Laporan';
                        }
                    });
            }

            // ===== FORWARD MODAL =====
            window.openForwardModal = function(id) {
                document.getElementById('forwardReportId').value = id;
                document.getElementById('forwardTo').value = '';
                document.getElementById('forwardNotes').value = '';
                if (forwardModal) forwardModal.show();
            }

            window.doForward = function() {
                const id = document.getElementById('forwardReportId').value;
                const to = document.getElementById('forwardTo').value;
                const notes = document.getElementById('forwardNotes').value;

                if (!to) {
                    showToast('error', 'Peringatan', 'Pilih tujuan cabang terlebih dahulu');
                    return;
                }

                const btn = document.querySelector('#forwardModal .btn-custom');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Meneruskan...';
                }

                fetch(urls.forward.replace(':id', id), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            to_branch_id: to,
                            notes: notes
                        })
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            showToast('success', 'Berhasil', res.message);
                            if (forwardModal) forwardModal.hide();
                            setTimeout(() => location.reload(), 500);
                        } else {
                            showToast('error', 'Gagal', res.message);
                        }
                    })
                    .catch(() => showToast('error', 'Error', 'Terjadi kesalahan saat meneruskan'))
                    .finally(() => {
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="ri-share-forward-line me-1"></i> Teruskan';
                        }
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
