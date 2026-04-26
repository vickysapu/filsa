@extends('layouts.admin')

@section('title', 'Buat Laporan Keuangan')

@section('content')
    <div class="page-wrapper">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.finance.reports.index') }}"
                                class="text-muted-custom text-decoration-none">
                                <i class="ri-arrow-left-line me-1"></i> Laporan Keuangan
                            </a>
                        </li>
                    </ol>
                </nav>
                <h1 class="h4 fw-bold mb-1">
                    {{ isset($report) ? 'Edit Laporan' : 'Buat Laporan Baru' }}
                </h1>
                <p class="text-muted-custom small mb-0">
                    <i class="ri-building-line me-1"></i> {{ auth()->user()->roles->first()?->branch->name ?? '' }}
                </p>
            </div>
            <a href="{{ route('admin.finance.reports.index') }}" class="btn btn-outline-custom">
                <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
        </div>

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="alert-custom error mb-4" data-auto-close>
                <i class="ri-error-warning-fill"></i>
                <div>
                    <strong>Terjadi kesalahan validasi</strong>
                    <ul class="mb-0 mt-1 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button class="alert-close">&times;</button>
            </div>
        @endif

        <form id="formReport" action="{{ route('admin.finance.reports.storeReport') }}" method="POST">
            @csrf

            <!-- Informasi Laporan -->
            <div class="card-custom mb-4">
                <div class="card-header-custom border-bottom border-custom py-3 px-4">
                    <h6 class="mb-0 fw-semibold">
                        <i class="ri-file-list-3-line me-2 text-primary"></i>Informasi Laporan
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted-custom">Tipe Laporan <span
                                    class="text-danger">*</span></label>
                            <select id="reportType" name="report_type" class="form-select form-select-custom" required
                                onchange="updatePeriod()">
                                <option value="monthly"
                                    {{ old('report_type', $report->report_type ?? 'monthly') === 'monthly' ? 'selected' : '' }}>
                                    Bulanan</option>
                                <option value="annual"
                                    {{ old('report_type', $report->report_type ?? '') === 'annual' ? 'selected' : '' }}>
                                    Tahunan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted-custom">Tahun <span
                                    class="text-danger">*</span></label>
                            <input type="number" id="reportYear" name="report_year"
                                class="form-control form-control-custom"
                                value="{{ old('report_year', $report->report_year ?? $year) }}" min="2020"
                                max="2030" required>
                        </div>
                        <div class="col-md-4" id="monthGroup">
                            <label class="form-label small fw-semibold text-muted-custom">Bulan <span
                                    class="text-danger">*</span></label>
                            <select id="reportMonth" name="report_month" class="form-select form-select-custom">
                                <option value="">Pilih Bulan</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}"
                                        {{ old('report_month', $report->report_month ?? '') == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted-custom">Periode Mulai <span
                                    class="text-danger">*</span></label>
                            <input type="date" id="periodStart" name="period_start"
                                class="form-control form-control-custom"
                                value="{{ old('period_start', $report->period_start ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted-custom">Periode Akhir <span
                                    class="text-danger">*</span></label>
                            <input type="date" id="periodEnd" name="period_end" class="form-control form-control-custom"
                                value="{{ old('period_end', $report->period_end ?? '') }}" required>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="button" class="btn btn-outline-custom" onclick="generateFromTransactions()">
                            <i class="ri-magic-line me-1"></i> Generate dari Transaksi
                        </button>
                    </div>
                </div>
            </div>

            <!-- Saldo Awal -->
            <div class="card-custom mb-4">
                <div class="card-header-custom border-bottom border-custom py-3 px-4">
                    <h6 class="mb-0 fw-semibold">
                        <i class="ri-wallet-line me-2 text-primary"></i>Saldo Awal
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label small fw-semibold text-muted-custom">Saldo Awal Periode <span
                                    class="text-danger">*</span></label>
                            <div class="input-group-custom">
                                <span class="input-group-text-custom">Rp</span>
                                <input type="text" id="openingBalance" name="opening_balance"
                                    class="form-control form-control-custom currency-input text-end"
                                    value="{{ old('opening_balance', isset($report) ? number_format($report->opening_balance, 0, '', '.') : '') }}"
                                    placeholder="0" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rincian Pemasukan & Pengeluaran -->
            <div class="card-custom mb-4">
                <div class="card-header-custom border-bottom border-custom py-3 px-4">
                    <h6 class="mb-0 fw-semibold">
                        <i class="ri-list-check me-2 text-primary"></i>Rincian Pemasukan & Pengeluaran
                    </h6>
                </div>
                <div class="card-body p-0">
                    @foreach ($groups as $gKey => $g)
                        <div class="accordion accordion-flush" id="accordion{{ $loop->index }}">
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header">
                                    <button class="accordion-button-custom {{ $loop->first ? '' : 'collapsed' }}"
                                        type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse{{ $loop->index }}">
                                        <i class="ri-folder-2-line me-2"></i> {{ $g['title'] }}
                                    </button>
                                </h2>
                                <div id="collapse{{ $loop->index }}"
                                    class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                    data-bs-parent="#accordion{{ $loop->index }}">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0 align-middle">
                                                <thead class="bg-body-tertiary">
                                                    <tr>
                                                        <th
                                                            class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">
                                                            Kategori</th>
                                                        <th
                                                            class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-end">
                                                            Jumlah</th>
                                                        <th
                                                            class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                                            Tipe</th>
                                                        <th
                                                            class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold">
                                                            Catatan</th>
                                                        <th
                                                            class="border-0 py-3 px-4 small text-uppercase text-muted-custom fw-semibold text-center">
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($g['cats'] as $catCode)
                                                        @php
                                                            $catName = $categories[$catCode]['name'] ?? $catCode;
                                                            $catType =
                                                                $categories[$catCode]['group'] === 'balance'
                                                                    ? 'balance'
                                                                    : ($categories[$catCode]['group'] === 'income'
                                                                        ? 'income'
                                                                        : 'expense');
                                                            $ex = isset($report)
                                                                ? $report->details
                                                                    ->where('category_code', $catCode)
                                                                    ->first()
                                                                : null;
                                                            $amt = old("details.{$catCode}.amount", $ex?->amount ?? '');
                                                            $notes = old("details.{$catCode}.notes", $ex?->notes ?? '');
                                                        @endphp
                                                        <tr class="border-bottom border-custom"
                                                            data-cat="{{ $catCode }}">
                                                            <input type="hidden"
                                                                name="details[{{ $catCode }}][category_code]"
                                                                value="{{ $catCode }}">
                                                            <input type="hidden"
                                                                name="details[{{ $catCode }}][category_name]"
                                                                value="{{ $catName }}">
                                                            <input type="hidden"
                                                                name="details[{{ $catCode }}][category_group]"
                                                                value="{{ $categories[$catCode]['group'] }}">
                                                            @if ($catType !== 'balance')
                                                                <input type="hidden"
                                                                    name="details[{{ $catCode }}][type]"
                                                                    value="{{ $catType }}">
                                                            @endif
                                                            <input type="hidden" class="raw-amount"
                                                                name="details[{{ $catCode }}][amount]"
                                                                value="{{ is_numeric($amt) ? $amt : '' }}">

                                                            <td class="py-3 px-4 fw-medium">{{ $catName }}</td>
                                                            <td class="py-3 px-4">
                                                                <input type="text"
                                                                    class="form-control form-control-custom amount-input currency-input text-end"
                                                                    value="{{ is_numeric($amt) ? number_format($amt, 0, '', '.') : '' }}"
                                                                    placeholder="0" data-raw="{{ $amt }}">
                                                            </td>
                                                            <td class="py-3 px-4 text-center">
                                                                <span class="badge badge-type-{{ $catType }}">
                                                                    {{ $catType === 'income' ? 'Pemasukan' : ($catType === 'expense' ? 'Pengeluaran' : 'Saldo') }}
                                                                </span>
                                                            </td>
                                                            <td class="py-3 px-4">
                                                                <input type="text"
                                                                    name="details[{{ $catCode }}][notes]"
                                                                    class="form-control form-control-custom"
                                                                    value="{{ $notes }}" placeholder="Opsional">
                                                            </td>
                                                            <td class="py-3 px-4 text-center">
                                                                <button type="button"
                                                                    class="btn-action btn-action-danger"
                                                                    onclick="clearRow(this)" title="Hapus baris">
                                                                    <i class="ri-close-line"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Ringkasan Keuangan -->
            <div class="card-custom mb-4">
                <div class="card-header-custom border-bottom border-custom py-3 px-4">
                    <h6 class="mb-0 fw-semibold">
                        <i class="ri-pie-chart-line me-2 text-primary"></i>Ringkasan Keuangan
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="summary-card border rounded p-4">
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-custom">
                            <span class="text-muted-custom">Saldo Awal</span>
                            <span id="sumOpening" class="fw-bold fs-5">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-custom">
                            <span class="text-muted-custom">Total Pemasukan</span>
                            <span id="sumIncome" class="fw-bold fs-5 text-success">+ Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-custom">
                            <span class="text-muted-custom">Total Pengeluaran</span>
                            <span id="sumExpense" class="fw-bold fs-5 text-danger">- Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-3 mt-3 border-top border-2"
                            style="border-color: var(--accent-blue) !important;">
                            <span class="fw-bold fs-5">Saldo Akhir</span>
                            <span id="sumClosing" class="fw-bold fs-5 text-primary">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-3 mt-4 pt-4 border-top border-custom">
                <a href="{{ route('admin.finance.reports.index') }}" class="btn btn-outline-custom">
                    <i class="ri-close-line me-1"></i> Batal
                </a>
                <button id="btnSubmitReport" type="submit" class="btn btn-custom">
                    <i class="ri-save-line me-1"></i> Simpan Laporan
                </button>
            </div>
        </form>
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

        .form-control-custom.text-end {
            text-align: right !important;
        }

        .input-group-custom {
            display: flex;
            align-items: stretch;
        }

        .input-group-text-custom {
            display: flex;
            align-items: center;
            padding: 0.625rem 0.875rem;
            background: var(--bg-body);
            border: 1px solid var(--border-color);
            border-right: none;
            border-radius: var(--radius-sm) 0 0 var(--radius-sm);
            color: var(--text-secondary);
            font-weight: 500;
        }

        .input-group-custom .form-control-custom {
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
            border-left: none;
        }

        .input-group-custom .form-control-custom:focus {
            border-left-color: var(--accent-blue);
        }

        /* ===== ACCORDION CUSTOM ===== */
        .accordion-button-custom {
            background: var(--bg-surface);
            color: var(--text-primary);
            padding: 1rem 1.25rem;
            font-weight: 600;
            font-size: 0.95rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm) !important;
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }

        .accordion-button-custom:not(.collapsed) {
            background: rgba(37, 99, 235, 0.08);
            color: var(--accent-blue);
            border-color: var(--accent-blue);
            box-shadow: none;
        }

        .accordion-button-custom:hover {
            background: var(--bg-body);
        }

        .accordion-button-custom:focus {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .accordion-item {
            background: transparent;
            border: none;
        }

        .accordion-collapse {
            border: 1px solid var(--border-color);
            border-radius: 0 0 var(--radius-sm) var(--radius-sm);
            margin-top: -0.5rem;
        }

        /* ===== TABLE CUSTOM ===== */
        .table> :not(caption)>*>* {
            background: var(--bg-surface);
            border-color: var(--border-color);
        }

        .table-hover>tbody>tr:hover>* {
            background: var(--bg-body);
        }

        .bg-body-tertiary {
            background: var(--bg-body) !important;
        }

        /* ===== BADGES ===== */
        .badge-type-income {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-type-expense {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-type-balance {
            background: var(--bg-body);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            padding: 0.35em 0.65em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        /* ===== SUMMARY CARD ===== */
        .summary-card {
            background: var(--bg-body);
            border: 1px solid var(--border-color);
        }

        /* ===== ACTION BUTTONS ===== */
        .btn-action {
            width: 28px;
            height: 28px;
            border-radius: var(--radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-primary);
            transition: var(--transition);
            font-size: 0.9rem;
            padding: 0;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .btn-action-danger {
            border-color: #ef4444;
            color: #ef4444;
        }

        .btn-action-danger:hover {
            background: #ef4444;
            color: white;
        }

        /* ===== DARK MODE ===== */
        [data-theme="dark"] .bg-body-tertiary {
            background: var(--bg-sidebar) !important;
        }

        [data-theme="dark"] .summary-card {
            background: var(--bg-sidebar);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .accordion-button-custom {
            background: var(--bg-surface);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .accordion-button-custom:not(.collapsed) {
            background: rgba(37, 99, 235, 0.15);
            border-color: var(--accent-blue);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .table-responsive table {
                min-width: 700px;
            }

            .input-group-custom {
                flex-wrap: wrap;
            }

            .input-group-text-custom {
                border-radius: var(--radius-sm) var(--radius-sm) 0 0;
                border-right: 1px solid var(--border-color);
                width: 100%;
                justify-content: center;
            }

            .input-group-custom .form-control-custom {
                border-radius: 0 0 var(--radius-sm) var(--radius-sm);
                border-left: 1px solid var(--border-color);
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== CURRENCY FORMATTING =====
            function formatRupiah(num) {
                if (!num && num !== 0) return '';
                const clean = num.toString().replace(/\D/g, '');
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(clean);
            }

            function getCleanNumber(val) {
                if (!val) return '';
                return val.toString().replace(/\D/g, '');
            }

            function initCurrencyInputs() {
                document.querySelectorAll('.currency-input').forEach(input => {
                    input.addEventListener('focus', function() {
                        const raw = this.closest('tr')?.querySelector('.raw-amount')?.value || this
                            .value;
                        this.value = getCleanNumber(raw);
                    });

                    input.addEventListener('input', function() {
                        this.value = getCleanNumber(this.value);
                        if (this.classList.contains('amount-input')) {
                            const row = this.closest('tr');
                            const hidden = row?.querySelector('.raw-amount');
                            if (hidden) hidden.value = this.value;
                        }
                        calc();
                    });

                    input.addEventListener('blur', function() {
                        const clean = getCleanNumber(this.value);
                        if (this.classList.contains('amount-input')) {
                            const hidden = this.closest('tr')?.querySelector('.raw-amount');
                            if (hidden) hidden.value = clean;
                        }
                        if (clean) {
                            this.value = formatRupiah(clean);
                        }
                        calc();
                    });
                });
            }

            // ===== CALCULATE SUMMARY =====
            function calc() {
                const oInput = document.getElementById('openingBalance');
                const o = parseInt(getCleanNumber(oInput?.value)) || 0;
                let inc = 0,
                    exp = 0;

                document.querySelectorAll('.raw-amount').forEach(inp => {
                    const v = parseInt(inp.value) || 0;
                    const row = inp.closest('tr');
                    const typeInput = row?.querySelector('input[name$="[type]"]');
                    if (typeInput?.value === 'income') inc += v;
                    else if (typeInput?.value === 'expense') exp += v;
                });

                document.getElementById('sumOpening').textContent = formatRupiah(o);
                document.getElementById('sumIncome').textContent = '+ ' + formatRupiah(inc);
                document.getElementById('sumExpense').textContent = '- ' + formatRupiah(exp);
                document.getElementById('sumClosing').textContent = formatRupiah(o + inc - exp);
            }

            // ===== UPDATE PERIOD =====
            function updatePeriod() {
                const t = document.getElementById('reportType').value;
                const monthGroup = document.getElementById('monthGroup');
                if (monthGroup) {
                    monthGroup.style.display = t === 'annual' ? 'none' : 'block';
                    const monthSelect = document.getElementById('reportMonth');
                    if (monthSelect) {
                        monthSelect.required = t !== 'annual';
                        if (t === 'annual') monthSelect.value = '';
                    }
                }
            }

            // ===== CLEAR ROW =====
            function clearRow(btn) {
                const row = btn.closest('tr');
                if (row) {
                    const amountInput = row.querySelector('.amount-input');
                    const rawInput = row.querySelector('.raw-amount');
                    const notesInput = row.querySelector('input[name$="[notes]"]');
                    if (amountInput) amountInput.value = '';
                    if (rawInput) rawInput.value = '';
                    if (notesInput) notesInput.value = '';
                    calc();
                }
            }

            // ===== GENERATE FROM TRANSACTIONS =====
            async function generateFromTransactions() {
                const year = document.getElementById('reportYear')?.value;
                const month = document.getElementById('reportMonth')?.value;
                const type = document.getElementById('reportType')?.value;

                if (!year) {
                    showToast('error', 'Peringatan', 'Tahun harus diisi terlebih dahulu');
                    return;
                }

                const btn = document.querySelector('.btn-outline-custom');
                const originalBtnText = btn?.innerHTML || '';
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Loading...';
                }

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

                    const res = await fetch('/admin/finance/reports/generate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            year: year,
                            month: type !== 'annual' ? month : null,
                            type: type
                        })
                    });

                    const contentType = res.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        const text = await res.text();
                        console.error('Bukan JSON response:', text.substring(0, 200));
                        throw new Error('Server mengembalikan halaman HTML, bukan JSON.');
                    }

                    const json = await res.json();

                    if (res.ok && json.success) {
                        document.getElementById('periodStart').value = json.data.period_start;
                        document.getElementById('periodEnd').value = json.data.period_end;
                        document.getElementById('openingBalance').value = formatRupiah(json.data
                            .opening_balance);

                        json.data.details.forEach(d => {
                            const row = document.querySelector(`tr[data-cat="${d.category_code}"]`);
                            if (row) {
                                const amountInput = row.querySelector('.amount-input');
                                const rawInput = row.querySelector('.raw-amount');
                                if (amountInput) amountInput.value = formatRupiah(d.amount);
                                if (rawInput) rawInput.value = d.amount;
                            }
                        });
                        calc();
                        showToast('success', 'Berhasil', 'Data berhasil diisi dari transaksi');
                    } else {
                        showToast('error', 'Gagal', json.message || 'Gagal mengambil data');
                    }
                } catch (e) {
                    console.error('Error:', e);
                    showToast('error', 'Error', e.message);
                } finally {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalBtnText;
                    }
                }
            }

            // ===== SHOW TOAST =====
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
            const form = document.getElementById('formReport');
            const btnSubmit = document.getElementById('btnSubmitReport');

            if (form && btnSubmit) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';

                    const formData = new FormData(this);

                    fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    ?.content || '',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                showToast('success', 'Berhasil', data.message);
                                window.location.href = data.redirect;
                            } else {
                                showToast('error', 'Gagal', data.message || 'Validasi gagal');
                            }
                        })
                        .catch(xhr => {
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON?.errors || {};
                                let msg = Object.values(errors).flat().join('\n');
                                showToast('error', 'Validasi Gagal', msg);
                            } else if (xhr.status === 401) {
                                showToast('error', 'Session Habis', 'Silakan login kembali');
                                location.reload();
                            } else {
                                showToast('error', 'Error Server', 'Status: ' + xhr.status);
                            }
                        })
                        .finally(() => {
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = '<i class="ri-save-line me-1"></i> Simpan Laporan';
                        });
                });
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

            // ===== INIT =====
            updatePeriod();
            initCurrencyInputs();
            calc();

            // Make functions globally available
            window.updatePeriod = updatePeriod;
            window.clearRow = clearRow;
            window.generateFromTransactions = generateFromTransactions;
        });
    </script>
@endsection
