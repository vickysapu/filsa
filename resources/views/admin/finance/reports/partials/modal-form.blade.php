<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="p-4">
    <form id="formReport"
        action="{{ isset($report) ? route('admin.finance.reports.update', $report) : route('admin.finance.reports.storeReport') }}"
        method="POST">
        @csrf
        @if (isset($report))
            @method('PUT')
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-12">
                <label class="form-label fw-semibold">Gereja <span class="text-danger">*</span></label>
                <select name="church_id" id="churchSelect" class="form-select" required>
                    <option value="">Pilih Gereja</option>
                    @foreach ($churches as $church)
                        <option value="{{ $church->id }}"
                            {{ old('church_id', isset($report) && $report->details->first() ? $report->details->first()->church_id : '') == $church->id ? 'selected' : '' }}>
                            {{ $church->name }} {{ $church->pic_name ? '- ' . $church->pic_name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tipe Laporan <span class="text-danger">*</span></label>
                <select id="reportType" name="report_type" class="form-select" required onchange="updatePeriod()">
                    <option value="monthly"
                        {{ old('report_type', $report->report_type ?? 'monthly') === 'monthly' ? 'selected' : '' }}>
                        Bulanan</option>
                    <option value="annual"
                        {{ old('report_type', $report->report_type ?? '') === 'annual' ? 'selected' : '' }}>Tahunan
                    </option>
                </select>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Tahun <span class="text-danger">*</span></label>
                <input type="number" id="reportYear" name="report_year" class="form-control"
                    value="{{ old('report_year', $report->report_year ?? ($year ?? date('Y'))) }}" min="2020"
                    max="2030" required>
            </div>
            <div class="col-md-4" id="monthGroup">
                <label class="form-label fw-semibold">Bulan <span class="text-danger">*</span></label>
                <select id="reportMonth" name="report_month" class="form-select">
                    <option value="">Pilih Bulan</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}"
                            {{ old('report_month', $report->report_month ?? '') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Periode Mulai <span class="text-danger">*</span></label>
                <input type="date" id="periodStart" name="period_start" class="form-control"
                    value="{{ old('period_start', $report->period_start ?? '') }}" required>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Periode Akhir <span class="text-danger">*</span></label>
                <input type="date" id="periodEnd" name="period_end" class="form-control"
                    value="{{ old('period_end', $report->period_end ?? '') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Saldo Awal <span
                        class="badge bg-info text-white fs-6">Otomatis</span></label>
                <input type="text" id="openingBalanceDisplay" class="form-control bg-light"
                    value="{{ isset($report) && $report->opening_balance ? 'Rp ' . number_format($report->opening_balance, 0, ',', '.') : 'Akan dihitung otomatis saat disimpan...' }}"
                    readonly>
                <input type="hidden" id="openingBalance" name="opening_balance"
                    value="{{ $report->opening_balance ?? 0 }}">
                <small class="text-muted fst-italic">Saldo awal dihitung otomatis berdasarkan saldo akhir periode
                    sebelumnya.</small>
            </div>
        </div>

        <hr class="my-4">

        <h6 class="fw-bold mb-3">Rincian Pemasukan & Pengeluaran</h6>
        <p class="text-muted small mb-3"><i class="ri-information-line me-1"></i> Kosongkan kolom jumlah jika tidak ada
            nominal. Sistem akan otomatis mengisi 0.</p>

        @foreach ($groups as $gKey => $g)
            <div class="accordion accordion-flush mb-2" id="accordion{{ $loop->index }}">
                <div class="accordion-item border rounded">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapse{{ $loop->index }}">
                            <i class="ri-folder-2-line me-2"></i> {{ $g['title'] }}
                        </button>
                    </h2>
                    <div id="collapse{{ $loop->index }}"
                        class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                        data-bs-parent="#accordion{{ $loop->index }}">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="py-2 px-3 small text-uppercase text-muted fw-semibold">Kategori
                                            </th>
                                            <th class="py-2 px-3 small text-uppercase text-muted fw-semibold text-end">
                                                Jumlah</th>
                                            <th
                                                class="py-2 px-3 small text-uppercase text-muted fw-semibold text-center">
                                                Tipe</th>
                                            <th class="py-2 px-3 small text-uppercase text-muted fw-semibold">Catatan
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($g['cats'] as $catCode)
                                            @php
                                                $catName = $categories[$catCode]['name'] ?? $catCode;
                                                $catGroup = $categories[$catCode]['group'] ?? 'income';
                                                $catType =
                                                    $catGroup === 'balance'
                                                        ? 'balance'
                                                        : ($catGroup === 'income'
                                                            ? 'income'
                                                            : 'expense');
                                                $ex = isset($report)
                                                    ? $report->details->where('category_code', $catCode)->first()
                                                    : null;
                                                $amt = old("details.{$catCode}.amount", $ex?->amount ?? '');
                                                $notes = old("details.{$catCode}.notes", $ex?->notes ?? '');
                                            @endphp
                                            <tr class="border-bottom" data-cat="{{ $catCode }}">
                                                <input type="hidden"
                                                    name="details[{{ $catCode }}][category_code]"
                                                    value="{{ $catCode }}">
                                                <input type="hidden"
                                                    name="details[{{ $catCode }}][category_name]"
                                                    value="{{ $catName }}">
                                                <input type="hidden"
                                                    name="details[{{ $catCode }}][category_group]"
                                                    value="{{ $catGroup }}">
                                                @if ($catType !== 'balance')
                                                    <input type="hidden" name="details[{{ $catCode }}][type]"
                                                        value="{{ $catType }}">
                                                @endif
                                                <input type="hidden" class="raw-amount"
                                                    name="details[{{ $catCode }}][amount]"
                                                    value="{{ is_numeric($amt) ? $amt : '0' }}">

                                                <td class="py-2 px-3 fw-medium">{{ $catName }}</td>
                                                <td class="py-2 px-3">
                                                    @php $raw = is_numeric($amt) ? $amt : 0; @endphp
                                                    <input type="text"
                                                        class="form-control form-control-sm amount-input currency-input text-end"
                                                        value="{{ $raw > 0 ? number_format($raw, 0, '', '.') : '' }}"
                                                        placeholder="0">
                                                </td>
                                                <td class="py-2 px-3 text-center">
                                                    <span
                                                        class="badge {{ $catType === 'income' ? 'bg-success' : ($catType === 'expense' ? 'bg-danger' : 'bg-secondary') }}">
                                                        {{ $catType === 'income' ? 'Debit' : ($catType === 'expense' ? 'Kredit' : 'Saldo') }}
                                                    </span>
                                                </td>
                                                <td class="py-2 px-3">
                                                    <input type="text" name="details[{{ $catCode }}][notes]"
                                                        class="form-control form-control-sm"
                                                        value="{{ $notes }}" placeholder="Opsional">
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

        <div class="card bg-light border-0 mt-4">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-3">Ringkasan Keuangan</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded border">
                            <span class="text-muted">Saldo Awal</span>
                            <span id="sumOpening" class="fw-bold">Rp 0</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded border">
                            <span class="text-muted">Total Debit (Pemasukan)</span>
                            <span id="sumIncome" class="fw-bold text-success">+ Rp 0</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded border">
                            <span class="text-muted">Total Kredit (Pengeluaran)</span>
                            <span id="sumExpense" class="fw-bold text-danger">- Rp 0</span>
                        </div>
                    </div>
                </div>
                <div class="mt-3 p-3 bg-primary bg-opacity-10 rounded border border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-5">Saldo Akhir</span>
                        <span id="sumClosing" class="fw-bold fs-5 text-primary">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" id="btnSubmitReport" class="btn btn-primary">
                <i class="ri-save-line me-1"></i> Simpan Laporan
            </button>
        </div>
    </form>
</div>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        padding-left: 0;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
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
                const raw = this.closest('tr')?.querySelector('.raw-amount')?.value || this.value;
                this.value = getCleanNumber(raw);
            });

            input.addEventListener('input', function() {
                this.value = getCleanNumber(this.value);

                if (this.classList.contains('amount-input')) {
                    const row = this.closest('tr');
                    const hidden = row?.querySelector('.raw-amount');

                    if (hidden) {
                        hidden.value = this.value || '0';
                    }
                }

                calc();
            });

            input.addEventListener('blur', function() {
                const clean = getCleanNumber(this.value);

                if (this.classList.contains('amount-input')) {
                    const hidden = this.closest('tr')?.querySelector('.raw-amount');

                    if (hidden) {
                        hidden.value = clean || '0';
                    }
                }

                this.value = clean ? formatRupiah(clean) : '';

                calc();
            });
        });
    }

    function calc() {
        const oInput = document.getElementById('openingBalance');
        const o = parseInt(getCleanNumber(oInput?.value)) || 0;

        let inc = 0;
        let exp = 0;

        document.querySelectorAll('.raw-amount').forEach(inp => {
            const v = parseInt(inp.value) || 0;
            const row = inp.closest('tr');
            const typeInput = row?.querySelector('input[name$="[type]"]');

            if (typeInput?.value === 'income') {
                inc += v;
            } else if (typeInput?.value === 'expense') {
                exp += v;
            }
        });

        document.getElementById('sumOpening').textContent = formatRupiah(o);
        document.getElementById('sumIncome').textContent = '+ ' + formatRupiah(inc);
        document.getElementById('sumExpense').textContent = '- ' + formatRupiah(exp);
        document.getElementById('sumClosing').textContent = formatRupiah(o + inc - exp);
    }

    window.updatePeriod = function() {
        const t = document.getElementById('reportType').value;
        const monthGroup = document.getElementById('monthGroup');

        if (monthGroup) {
            monthGroup.style.display = t === 'annual' ? 'none' : 'block';

            const monthSelect = document.getElementById('reportMonth');

            if (monthSelect) {
                monthSelect.required = t !== 'annual';

                if (t === 'annual') {
                    monthSelect.value = '';
                }
            }
        }

        const year = document.getElementById('reportYear').value;
        const month = document.getElementById('reportMonth').value;

        if (!year) {
            return;
        }

        if (t === 'annual') {
            document.getElementById('periodStart').value = `${year}-01-01`;
            document.getElementById('periodEnd').value = `${year}-12-31`;
        } else {
            if (!month) {
                return;
            }

            const lastDay = new Date(year, month, 0).getDate();
            const mStr = month.toString().padStart(2, '0');

            document.getElementById('periodStart').value = `${year}-${mStr}-01`;
            document.getElementById('periodEnd').value = `${year}-${mStr}-${lastDay}`;
        }
    };

    document.getElementById('reportYear')?.addEventListener('change', updatePeriod);
    document.getElementById('reportMonth')?.addEventListener('change', updatePeriod);

    updatePeriod();
    initCurrencyInputs();
    calc();
</script>
