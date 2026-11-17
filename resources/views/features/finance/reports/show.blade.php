@extends('layouts.admin')

@section('title', 'Detail Laporan')

@section('content')
    <div class="page-wrapper">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.finance.reports.index') }}"
                                class="text-decoration-none text-muted-custom">
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
                    &bull; {{ $report->branch?->name ?? '-' }}
                </p>
            </div>
            <div class="text-md-end">
                <span class="badge badge-status-{{ $report->status }}">
                    {{ ucfirst($report->status) }}
                </span>
                @if ($report->submitted_to && $report->submittedToBranch)
                    <div class="text-muted-custom small mt-1">
                        <i class="ri-arrow-right-line me-1"></i> Dikirim ke: {{ $report->submittedToBranch?->name ?? '-' }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="card-custom mb-4">
            <div class="card-body p-4">
                <div class="row g-4 text-center">
                    <div class="col-6 col-lg-3">
                        <div class="summary-item">
                            <div class="summary-label">Saldo Awal</div>
                            <div class="summary-value">Rp {{ number_format($report->opening_balance, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="summary-item">
                            <div class="summary-label text-success-custom">Total Pemasukan</div>
                            <div class="summary-value text-success-custom">+ Rp
                                {{ number_format($report->total_income, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="summary-item">
                            <div class="summary-label text-warning-custom">Total Pengeluaran</div>
                            <div class="summary-value text-warning-custom">- Rp
                                {{ number_format($report->total_expense, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="summary-item">
                            <div class="summary-label">Saldo Akhir</div>
                            <div class="summary-value text-primary fw-bold">Rp
                                {{ number_format($report->closing_balance, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejection Alert -->
        @if ($report->status === 'rejected' && $report->rejection_reason)
            <div class="alert-custom alert-rejected mb-4">
                <i class="ri-error-warning-fill"></i>
                <div><strong>Alasan Penolakan:</strong> {{ $report->rejection_reason }}</div>
            </div>
        @endif

        <!-- Income Table -->
        <div class="card-custom mb-4">
            <div class="card-header-custom border-bottom border-custom py-3 px-4">
                <h6 class="mb-0 fw-semibold"><i class="ri-arrow-up-circle-line me-2 text-success-custom"></i>Penerimaan Dana
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
                                    <td class="py-3 px-4 text-end text-success-custom fw-medium">Rp
                                        {{ number_format($detail->amount, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-muted-custom small">{{ $detail->notes ?: '-' }}</td>
                                </tr>
                            @endif
                        @endforeach
                        <tr class="border-bottom border-custom bg-light-subtle">
                            <td class="py-3 px-4 fw-bold">TOTAL PENERIMAAN</td>
                            <td class="py-3 px-4 text-end text-success-custom fw-bold">+ Rp
                                {{ number_format($report->total_income, 0, ',', '.') }}</td>
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
                    <h6 class="mb-0 fw-semibold"><i
                            class="{{ $group['icon'] }} me-2 text-warning-custom"></i>{{ $group['title'] }}</h6>
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
                                        <td class="py-3 px-4 text-end text-warning-custom fw-medium">Rp
                                            {{ number_format($detail->amount, 0, ',', '.') }}</td>
                                        <td class="py-3 px-4 text-muted-custom small">{{ $detail->notes ?: '-' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            @if ($report->details->whereIn('category_code', $group['cats'])->sum('amount') > 0)
                                <tr class="border-bottom border-custom bg-light-subtle">
                                    <td class="py-3 px-4 fw-bold">Total {{ $group['title'] }}</td>
                                    <td class="py-3 px-4 text-end text-warning-custom fw-bold">Rp
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
                <h6 class="mb-0 fw-semibold"><i class="ri-information-line me-2 text-primary"></i>Informasi Tambahan</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="info-item">
                            <i class="ri-calendar-line text-primary"></i>
                            <div>
                                <p class="text-muted-custom small mb-1">Dibuat Pada</p>
                                <p class="fw-semibold mb-0">{{ $report->created_at?->format('d F Y H:i') ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <i class="ri-building-line text-primary"></i>
                            <div>
                                <p class="text-muted-custom small mb-1">Dibuat Oleh</p>
                                <p class="fw-semibold mb-0">{{ $report->branch?->name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    @if ($report->submitted_at)
                        <div class="col-md-6">
                            <div class="info-item">
                                <i class="ri-send-plane-line text-primary"></i>
                                <div>
                                    <p class="text-muted-custom small mb-1">Dikirim Pada</p>
                                    <p class="fw-semibold mb-0">{{ $report->submitted_at?->format('d F Y H:i') ?? '-' }}</p>
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
                                    <p class="fw-semibold mb-0">{{ $report->approver?->name ?: '-' }}</p><small
                                        class="text-muted-custom">{{ $report->approved_at?->format('d F Y H:i') ?? '-' }}</small>
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
                        <button type="button" class="btn btn-custom" id="btnSubmitReport">
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
                    <button type="button" class="btn btn-success-custom" id="btnApproveReport">
                        <i class="ri-check-line me-1"></i> Setujui
                    </button>
                    <button type="button" class="btn btn-warning-custom" id="btnRejectReport">
                        <i class="ri-close-line me-1"></i> Tolak
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="submitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="submitForm">
                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold"><i class="ri-send-plane-line me-2 text-primary"></i>Kirim
                            Laporan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert-info-custom mb-3">
                            <i class="ri-information-line me-2"></i>
                            <span class="small">Laporan akan dikirim ke cabang/pusat tujuan untuk direview.</span>
                        </div>
                        <div class="mb-3">
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
                        <button type="submit" class="btn btn-custom" id="btnConfirmSubmit">
                            <i class="ri-send-plane-line me-1"></i> Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form id="rejectForm">
                    <div class="modal-header border-bottom border-custom">
                        <h5 class="modal-title fw-semibold"><i
                                class="ri-close-circle-line me-2 text-warning-custom"></i>Tolak Laporan</h5>
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
                        <button type="submit" class="btn btn-warning-custom" id="btnConfirmReject">
                            <i class="ri-close-line me-1"></i> Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

    <style>
        .text-success-custom {
            color: #10b981 !important;
        }

        .text-warning-custom {
            color: #f59e0b !important;
        }

        .btn-success-custom {
            background-color: #10b981;
            border-color: #10b981;
            color: white;
        }

        .btn-success-custom:hover {
            background-color: #059669;
            border-color: #059669;
            color: white;
        }

        .btn-warning-custom {
            background-color: #f59e0b;
            border-color: #f59e0b;
            color: white;
        }

        .btn-warning-custom:hover {
            background-color: #d97706;
            border-color: #d97706;
            color: white;
        }

        .alert-rejected {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            color: #ef4444;
            display: flex;
            gap: 0.75rem;
            padding: 1rem;
            border-radius: var(--radius-sm);
        }

        .toast-custom {
            background: white;
            border-left: 4px solid;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 1rem;
            margin-top: 0.5rem;
            border-radius: 4px;
            display: flex;
            align-items: center;
            min-width: 300px;
            animation: slideIn 0.3s ease-out;
            color: #333;
        }

        .toast-success {
            border-left-color: #10b981;
        }

        .toast-error {
            border-left-color: #ef4444;
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
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Cek Bootstrap
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap JS tidak ditemukan!');
                return;
            }

            // 2. Inisialisasi Modal
            const submitModalEl = document.getElementById('submitModal');
            const rejectModalEl = document.getElementById('rejectModal');
            let submitModal = submitModalEl ? new bootstrap.Modal(submitModalEl) : null;
            let rejectModal = rejectModalEl ? new bootstrap.Modal(rejectModalEl) : null;

            // 3. Helper Functions
            function getCsrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.content || '';
            }

            function showToast(type, title, message) {
                const container = document.getElementById('toast-container');
                if (!container) return;
                const toast = document.createElement('div');
                toast.className = `toast-custom toast-${type}`;
                const icon = type === 'success' ?
                    '<i class="ri-checkbox-circle-line me-2" style="color:#10b981; font-size:1.2rem;"></i>' :
                    '<i class="ri-error-warning-line me-2" style="color:#ef4444; font-size:1.2rem;"></i>';

                toast.innerHTML = `${icon}<div><strong>${title}</strong><br><small>${message}</small></div>`;
                container.appendChild(toast);
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }

            function setLoading(btn, isLoading, originalHtml) {
                if (!btn) return;
                if (isLoading) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            }

            // --- LOGIC SUBMIT (PERBAIKAN UTAMA DI SINI) ---
            const btnSubmit = document.getElementById('btnSubmitReport');
            if (btnSubmit) {
                btnSubmit.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Mencegah event bubbling

                    const select = document.getElementById('submitTo');
                    if (select) select.value = '';

                    if (submitModal) submitModal.show();
                });
            }

            const submitForm = document.getElementById('submitForm');
            if (submitForm) {
                submitForm.addEventListener('submit', function(e) {
                    e.preventDefault(); // WAJIB: Mencegah form submit secara default (GET)
                    e.stopPropagation();

                    const btn = document.getElementById('btnConfirmSubmit');
                    const originalHtml = btn ? btn.innerHTML : 'Kirim';
                    const targetId = document.getElementById('submitTo').value;

                    if (!targetId) {
                        showToast('error', 'Error', 'Pilih cabang tujuan terlebih dahulu');
                        return;
                    }

                    if (btn) setLoading(btn, true, originalHtml);

                    // Gunakan URL eksplisit dari route helper Laravel
                    const url = "{{ route('admin.finance.reports.submit', ['report' => $report->id]) }}";

                    fetch(url, {
                            method: 'POST', // Force POST
                            headers: {
                                'Content-Type': 'application/json', // Penting agar Laravel membaca JSON
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                submitted_to: targetId
                            })
                        })
                        .then(r => {
                            if (!r.ok) throw new Error('Network response was not ok');
                            return r.json();
                        })
                        .then(res => {
                            if (res.success) {
                                showToast('success', 'Berhasil', res.message);
                                if (submitModal) submitModal.hide();
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                showToast('error', 'Gagal', res.message || 'Gagal mengirim laporan');
                            }
                        })
                        .catch(err => {
                            console.error('Fetch Error:', err);
                            showToast('error', 'Error', 'Terjadi kesalahan koneksi: ' + err.message);
                        })
                        .finally(() => {
                            if (btn) setLoading(btn, false, originalHtml);
                        });
                });
            }

            // --- LOGIC REJECT ---
            const btnReject = document.getElementById('btnRejectReport');
            if (btnReject) {
                btnReject.addEventListener('click', function(e) {
                    e.preventDefault();
                    const textarea = document.getElementById('rejectReason');
                    if (textarea) textarea.value = '';
                    if (rejectModal) rejectModal.show();
                });
            }

            const rejectForm = document.getElementById('rejectForm');
            if (rejectForm) {
                rejectForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const btn = document.getElementById('btnConfirmReject');
                    const originalHtml = btn ? btn.innerHTML : 'Tolak';
                    const reason = document.getElementById('rejectReason').value;

                    if (reason.length < 10) {
                        showToast('error', 'Error', 'Alasan penolakan minimal 10 karakter');
                        return;
                    }

                    if (btn) setLoading(btn, true, originalHtml);

                    const url = "{{ route('admin.finance.reports.reject', ['report' => $report->id]) }}";

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                rejection_reason: reason
                            })
                        })
                        .then(r => {
                            if (!r.ok) throw new Error('Network response was not ok');
                            return r.json();
                        })
                        .then(res => {
                            if (res.success) {
                                showToast('success', 'Ditolak', res.message);
                                if (rejectModal) rejectModal.hide();
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                showToast('error', 'Gagal', res.message || 'Gagal menolak laporan');
                            }
                        })
                        .catch(err => {
                            console.error('Fetch Error:', err);
                            showToast('error', 'Error', 'Terjadi kesalahan koneksi');
                        })
                        .finally(() => {
                            if (btn) setLoading(btn, false, originalHtml);
                        });
                });
            }

            // --- LOGIC APPROVE ---
            const btnApprove = document.getElementById('btnApproveReport');
            if (btnApprove) {
                btnApprove.addEventListener('click', function() {
                    if (confirm('Apakah Anda yakin ingin menyetujui laporan ini?')) {
                        const btn = this;
                        const originalHtml = btn.innerHTML;
                        setLoading(btn, true, originalHtml);

                        const url =
                            "{{ route('admin.finance.reports.approve', ['report' => $report->id]) }}";

                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': getCsrfToken(),
                                    'Accept': 'application/json'
                                }
                            })
                            .then(r => {
                                if (!r.ok) throw new Error('Network response was not ok');
                                return r.json();
                            })
                            .then(res => {
                                if (res.success) {
                                    showToast('success', 'Disetujui', res.message);
                                    setTimeout(() => location.reload(), 1000);
                                } else {
                                    showToast('error', 'Gagal', res.message);
                                    setLoading(btn, false, originalHtml);
                                }
                            })
                            .catch(() => {
                                showToast('error', 'Error', 'Terjadi kesalahan koneksi');
                                setLoading(btn, false, originalHtml);
                            });
                    }
                });
            }
        });
    </script>
@endsection
