@extends('layouts.admin')

@section('title', 'Detail Laporan')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.finance.reports.index') }}"
                                class="text-muted-custom text-decoration-none">
                                <i class="ri-arrow-left-line me-1"></i> Laporan Keuangan
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
                <h1 class="h4 fw-bold mb-1">
                    {{ $report->report_type === 'monthly' ? 'Laporan Bulanan' : 'Laporan Tahunan' }}
                </h1>
                <p class="text-muted-custom mb-0">
                    @if ($report->report_type === 'monthly')
                        {{ \Carbon\Carbon::create()->month($report->report_month)->year($report->report_year)->format('F Y') }}
                    @else
                        {{ $report->report_year }}
                    @endif
                    &bull; {{ $report->branch->name }}
                </p>
            </div>
            <div class="text-md-end">
                <span class="badge badge-status-{{ $report->status }}">
                    {{ ucfirst($report->status) }}
                </span>
                @if ($report->submitted_to && $report->submittedToBranch)
                    <div class="text-muted-custom small mt-1">
                        <i class="ri-arrow-right-line me-1"></i> Dikirim ke: {{ $report->submittedToBranch->name }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Financial Summary Cards -->
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <div class="row g-4 text-center">
                    <div class="col-6 col-lg-3">
                        <div class="summary-item">
                            <div class="summary-label">Saldo Awal</div>
                            <div class="summary-value">
                                Rp {{ number_format($report->opening_balance, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="summary-item">
                            <div class="summary-label text-success">Total Pemasukan</div>
                            <div class="summary-value text-success">
                                + Rp {{ number_format($report->total_income, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="summary-item">
                            <div class="summary-label text-danger">Total Pengeluaran</div>
                            <div class="summary-value text-danger">
                                - Rp {{ number_format($report->total_expense, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="summary-item">
                            <div class="summary-label">Saldo Akhir</div>
                            <div class="summary-value text-primary fw-bold">
                                Rp {{ number_format($report->closing_balance, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejection Alert -->
        @if ($report->status === 'rejected' && $report->rejection_reason)
            <div class="alert-custom error mb-4">
                <i class="ri-error-warning-fill"></i>
                <div>
                    <strong>Alasan Penolakan:</strong> {{ $report->rejection_reason }}
                </div>
            </div>
        @endif

        <!-- Penerimaan Dana Table -->
        <div class="card-custom mb-4">
            <div class="card-header-custom border-bottom border-custom py-3 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="ri-arrow-up-circle-line me-2 text-success"></i>Penerimaan Dana
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Kategori</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">
                                Jumlah (Rp)</th>
                            <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $incomeCats = ['PP', 'IURAN_PEJABAT', 'DANA_MISI', 'PENERIMAAN_LAIN', 'BUNGA_BANK']; @endphp
                        @foreach ($incomeCats as $code)
                            @php $detail = $report->details->where('category_code', $code)->first(); @endphp
                            @if ($detail && $detail->amount > 0)
                                <tr class="border-bottom border-custom">
                                    <td class="py-3 px-4 fw-medium">{{ $categories[$code]['name'] ?? $code }}</td>
                                    <td class="py-3 px-4 text-end text-success fw-medium">
                                        Rp {{ number_format($detail->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-muted-custom small">{{ $detail->notes ?: '-' }}</td>
                                </tr>
                            @endif
                        @endforeach
                        <tr class="border-bottom border-custom" style="background: var(--bg-body);">
                            <td class="py-3 px-4 fw-bold">TOTAL PENERIMAAN</td>
                            <td class="py-3 px-4 text-end text-success fw-bold">
                                + Rp {{ number_format($report->total_income, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expense Groups -->
        @php
            $expenseGroups = [
                'biaya_majelis' => [
                    'title' => 'Biaya Majelis Daerah',
                    'icon' => 'ri-government-line',
                    'cats' => [
                        'SETORAN_PP_MP',
                        'SETORAN_IURAN_MP',
                        'BIAYA_MIKERIA',
                        'BIAYA_PASKAH_NATAL',
                        'BIAYA_RAPAT',
                        'BIAYA_KURIKULUM',
                        'BIAYA_SEKRETARIAT',
                        'BIAYA_LAIN_MAJELIS',
                        'BIAYA_ADMIN_BANK',
                    ],
                ],
                'biaya_penatalayanan' => [
                    'title' => 'Biaya Bidang Penatalayanan',
                    'icon' => 'ri-settings-3-line',
                    'cats' => ['BIAYA_BUKU_PENATALAYANAN', 'BIAYA_PENATALAYANAN_LAIN'],
                ],
                'biaya_penggembalaan' => [
                    'title' => 'Biaya Bidang Penggembalaan',
                    'icon' => 'ri-team-line',
                    'cats' => ['BIAYA_SEKOLAH_MINGGU', 'BIAYA_PEMUDA', 'BIAYA_WANITA', 'BIAYA_CAMP_SEMINAR'],
                ],
                'biaya_misi' => [
                    'title' => 'Biaya Bidang Pendidikan dan Misi',
                    'icon' => 'ri-global-line',
                    'cats' => ['SETORAN_DANA_MISI_MP', 'BIAYA_MISI_SEMINAR', 'BIAYA_PENDIDIKAN_MD'],
                ],
                'biaya_pelayanan' => [
                    'title' => 'Biaya Bidang Pelayanan Masyarakat',
                    'icon' => 'ri-heart-line',
                    'cats' => [
                        'BANTUAN_SAKIT',
                        'BANTUAN_DUKACITA',
                        'BANTUAN_PEMBANGUNAN',
                        'BANTUAN_BENCANA',
                        'BIAYA_MULTIMEDIA',
                    ],
                ],
            ];
        @endphp

        @foreach ($expenseGroups as $groupKey => $group)
            <div class="card-custom mb-4">
                <div class="card-header-custom border-bottom border-custom py-3 px-4">
                    <h6 class="mb-0 fw-semibold">
                        <i class="{{ $group['icon'] }} me-2 text-danger"></i>{{ $group['title'] }}
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-body-tertiary">
                            <tr>
                                <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Kategori
                                </th>
                                <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">
                                    Jumlah (Rp)</th>
                                <th class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">Catatan
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group['cats'] as $code)
                                @php $detail = $report->details->where('category_code', $code)->first(); @endphp
                                @if ($detail && $detail->amount > 0)
                                    <tr class="border-bottom border-custom">
                                        <td class="py-3 px-4 fw-medium">{{ $categories[$code]['name'] ?? $code }}</td>
                                        <td class="py-3 px-4 text-end text-danger fw-medium">
                                            Rp {{ number_format($detail->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-4 text-muted-custom small">{{ $detail->notes ?: '-' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            @if ($report->details->whereIn('category_code', $group['cats'])->sum('amount') > 0)
                                <tr class="border-bottom border-custom" style="background: var(--bg-body);">
                                    <td class="py-3 px-4 fw-bold">Total {{ $group['title'] }}</td>
                                    <td class="py-3 px-4 text-end text-danger fw-bold">
                                        Rp
                                        {{ number_format($report->details->whereIn('category_code', $group['cats'])->sum('amount'), 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4"></td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="3" class="py-3 text-center text-muted-custom small">Tidak ada
                                        pengeluaran</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <!-- Additional Info -->
        <div class="card-custom mb-4">
            <div class="card-header-custom border-bottom border-custom py-3 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="ri-information-line me-2 text-primary"></i>Informasi Tambahan
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="info-item">
                            <i class="ri-calendar-line text-primary"></i>
                            <div>
                                <p class="text-muted-custom small mb-1">Dibuat Pada</p>
                                <p class="fw-semibold mb-0">{{ $report->created_at->format('d F Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <i class="ri-building-line text-primary"></i>
                            <div>
                                <p class="text-muted-custom small mb-1">Dibuat Oleh</p>
                                <p class="fw-semibold mb-0">{{ $report->branch->name }}</p>
                            </div>
                        </div>
                    </div>
                    @if ($report->submitted_at)
                        <div class="col-md-6">
                            <div class="info-item">
                                <i class="ri-send-plane-line text-primary"></i>
                                <div>
                                    <p class="text-muted-custom small mb-1">Dikirim Pada</p>
                                    <p class="fw-semibold mb-0">{{ $report->submitted_at->format('d F Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($report->approved_at)
                        <div class="col-md-6">
                            <div class="info-item">
                                <i class="ri-checkbox-circle-line text-primary"></i>
                                <div>
                                    <p class="text-muted-custom small mb-1">Disetujui Oleh</p>
                                    <p class="fw-semibold mb-0">{{ $report->approver?->name ?: '-' }}</p>
                                    <small
                                        class="text-muted-custom">{{ $report->approved_at->format('d F Y H:i') }}</small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                @if ($report->notes)
                    <hr class="border-custom my-4">
                    <div>
                        <p class="text-muted-custom small mb-2">Catatan</p>
                        <p class="mb-0">{{ $report->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4 pt-4 border-top border-custom">
            <a href="{{ route('admin.finance.reports.index') }}" class="btn btn-outline-custom">
                <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
            <div class="d-flex flex-wrap gap-2">
                @if ($report->status === 'draft')
                    @if (auth()->user()->hasPermission('finance.report.edit'))
                        <a href="{{ route('admin.finance.reports.edit', $report) }}" class="btn btn-outline-custom">
                            <i class="ri-pencil-line me-1"></i> Edit
                        </a>
                    @endif
                    @if (auth()->user()->hasPermission('finance.report.submit'))
                        <button class="btn btn-custom" onclick="openSubmitModal()">
                            <i class="ri-send-plane-line me-1"></i> Kirim ke Pusat Cabang
                        </button>
                    @endif
                @endif
                @if (auth()->user()->hasPermission('finance.report.export'))
                    <a href="{{ route('admin.finance.export.annual-report', $report) }}" class="btn btn-outline-success">
                        <i class="ri-file-excel-2-line me-1"></i> Export Excel
                    </a>
                @endif
                @if ($report->status === 'submitted' && auth()->user()->hasPermission('finance.report.approve'))
                    <button class="btn btn-success" onclick="approveReport()">
                        <i class="ri-check-line me-1"></i> Setujui
                    </button>
                    <button class="btn btn-danger" onclick="openRejectModal()">
                        <i class="ri-close-line me-1"></i> Tolak
                    </button>
                @endif
            </div>
        </div>

    </div>

    <!-- Submit Modal -->
    <div class="modal fade" id="submitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="submitForm">
                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold">
                            <i class="ri-send-plane-line me-2 text-primary"></i>Kirim Laporan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert-info-custom mb-3">
                            <i class="ri-information-line me-2"></i>
                            <span class="small">Laporan akan dikirim ke cabang/pusat tujuan untuk direview.</span>
                        </div>
                        <div>
                            <label class="form-label small fw-semibold text-muted-custom">Kirim ke Cabang <span
                                    class="text-danger">*</span></label>
                            <select id="submitTo" class="form-select form-select-custom" required>
                                <option value="">Pilih Cabang Tujuan</option>
                                @foreach (\App\Models\Branch::where('id', '!=', $report->branch_id)->where('is_active', true)->get() as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-custom py-3 px-4">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-custom">
                            <i class="ri-send-plane-line me-1"></i> Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="rejectForm">
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

        /* ===== SUMMARY ITEMS ===== */
        .summary-item {
            padding: 1rem;
            border-radius: var(--radius-sm);
            background: var(--bg-body);
            transition: var(--transition);
        }

        .summary-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .summary-label {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .summary-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* ===== INFO ITEMS ===== */
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .info-item i {
            font-size: 1.25rem;
            margin-top: 2px;
        }

        /* ===== STATUS BADGES ===== */
        .badge-status-draft {
            background: rgba(245, 158, 11, 0.15);
            color: #b45309;
            border: 1px solid rgba(245, 158, 11, 0.3);
            padding: 0.4em 0.75em;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .badge-status-submitted {
            background: rgba(59, 130, 246, 0.15);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.3);
            padding: 0.4em 0.75em;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .badge-status-approved {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.4em 0.75em;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .badge-status-rejected {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 0.4em 0.75em;
            font-weight: 500;
            font-size: 0.8rem;
        }

        /* ===== ALERT CUSTOM ===== */
        .alert-custom {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
        }

        .alert-custom.error {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            color: #ef4444;
        }

        .alert-custom i {
            font-size: 1.25rem;
            margin-top: 2px;
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

        [data-theme="dark"] .summary-item {
            background: var(--bg-sidebar);
        }

        [data-theme="dark"] .alert-info-custom {
            background: rgba(37, 99, 235, 0.12);
            border-color: rgba(37, 99, 235, 0.3);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .summary-value {
                font-size: 0.95rem;
            }

            .d-flex.flex-wrap.gap-3 {
                flex-direction: column;
                align-items: stretch;
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
            // ===== MODAL INSTANCES =====
            let submitModal, rejectModal;
            const submitModalEl = document.getElementById('submitModal');
            const rejectModalEl = document.getElementById('rejectModal');

            if (submitModalEl && bootstrap.Modal) submitModal = new bootstrap.Modal(submitModalEl);
            if (rejectModalEl && bootstrap.Modal) rejectModal = new bootstrap.Modal(rejectModalEl);

            // ===== TOOLTIP INIT =====
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });

            // ===== URLS =====
            const urls = {
                submit: "{{ route('admin.finance.reports.submit', ':id') }}",
                approve: "{{ route('admin.finance.reports.approve', ':id') }}",
                reject: "{{ route('admin.finance.reports.reject', ':id') }}"
            };
            const reportId = {{ $report->id }};

            // ===== MODAL FUNCTIONS =====
            window.openSubmitModal = function() {
                document.getElementById('submitTo').value = '';
                if (submitModal) submitModal.show();
            }

            window.openRejectModal = function() {
                document.getElementById('rejectReason').value = '';
                if (rejectModal) rejectModal.show();
            }

            window.closeModal = function() {
                if (submitModal) submitModal.hide();
                if (rejectModal) rejectModal.hide();
            }

            // ===== SUBMIT FORM =====
            const submitForm = document.getElementById('submitForm');
            if (submitForm) {
                submitForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const url = urls.submit.replace(':id', reportId);
                    const btn = this.querySelector('button[type="submit"]');

                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-2"></span> Mengirim...';
                    }

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    ?.content || ''
                            },
                            body: JSON.stringify({
                                submitted_to: document.getElementById('submitTo').value
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
                });
            }

            // ===== REJECT FORM =====
            const rejectForm = document.getElementById('rejectForm');
            if (rejectForm) {
                rejectForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const url = urls.reject.replace(':id', reportId);
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
                                rejection_reason: document.getElementById('rejectReason').value
                            })
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                showToast('success', 'Ditolak', res.message);
                                if (rejectModal) rejectModal.hide();
                                setTimeout(() => location.reload(), 500);
                            } else {
                                showToast('error', 'Gagal', res.message || 'Gagal menolak laporan');
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

            // ===== APPROVE REPORT =====
            window.approveReport = function() {
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
                        if (result.isConfirmed) performApprove();
                    });
                } else {
                    if (confirm('Setujui laporan ini?')) {
                        performApprove();
                    }
                }
            }

            function performApprove() {
                const url = urls.approve.replace(':id', reportId);

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
