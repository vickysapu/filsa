@extends('layouts.admin')
@section('title', 'Detail Laporan Keuangan')

@push('styles')
    <style>
        /* ── Status Pills ── */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: .82rem;
            font-weight: 600;
        }

        .status-pill.draft {
            background: #fef3c7;
            color: #92400e;
        }

        .status-pill.submitted {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-pill.reviewed {
            background: #ede9fe;
            color: #5b21b6;
        }

        .status-pill.approved {
            background: #d1fae5;
            color: #065f46;
        }

        .status-pill.rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ── Summary Cards ── */
        .sum-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        @media(max-width:768px) {
            .sum-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .sum-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            text-align: center;
        }

        .sum-card .lbl {
            font-size: .75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .sum-card .val {
            font-size: 1.4rem;
            font-weight: 800;
            margin-top: 8px;
            line-height: 1.2;
        }

        /* ── Timeline ── */
        .timeline {
            position: relative;
            padding-left: 32px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 11px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border);
        }

        .tl-item {
            position: relative;
            margin-bottom: 20px;
        }

        .tl-dot {
            position: absolute;
            left: -32px;
            top: 3px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .65rem;
            z-index: 1;
        }

        .tl-dot.submit {
            background: #dbeafe;
            border: 2px solid #93c5fd;
            color: #1d4ed8;
        }

        .tl-dot.forward {
            background: #ede9fe;
            border: 2px solid #a78bfa;
            color: #6d28d9;
        }

        .tl-dot.approve {
            background: #d1fae5;
            border: 2px solid #6ee7b7;
            color: #065f46;
        }

        .tl-dot.reject {
            background: #fee2e2;
            border: 2px solid #fca5a5;
            color: #991b1b;
        }

        .tl-body {
            background: var(--bg-hover);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 12px 16px;
        }

        .tl-meta {
            font-size: .78rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .tl-route {
            font-weight: 600;
            font-size: .88rem;
        }

        /* ── Detail Table ── */
        .section-title {
            font-weight: 700;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--text-muted);
            padding: 10px 0 6px;
            border-bottom: 2px solid var(--primary);
            margin-bottom: 0;
        }
    </style>
@endpush

@section('content')
    <div class="lux-container lux-py-5">

        {{-- ── Header ── --}}
        <div class="lux-flex lux-flex-between lux-mb-5">
            <div>
                <h1 class="lux-text-2xl lux-text-bold">
                    {{ $report->report_type === 'monthly' ? 'Laporan Bulanan' : 'Laporan Tahunan' }}
                </h1>
                <div class="lux-text-muted lux-mt-1">
                    @if ($report->report_type === 'monthly')
                        {{ \Carbon\Carbon::create()->month($report->report_month)->year($report->report_year)->format('F Y') }}
                    @else
                        Tahun {{ $report->report_year }}
                    @endif
                    &bull; <strong>{{ $report->branch->name }}</strong>
                    @if ($report->period_start && $report->period_end)
                        &bull; {{ $report->period_start->format('d M Y') }} – {{ $report->period_end->format('d M Y') }}
                    @endif
                </div>
            </div>
            <div class="lux-text-end">
                <span class="status-pill {{ $report->status }}">
                    <i
                        class="ri-{{ $report->status === 'approved' ? 'checkbox-circle' : ($report->status === 'rejected' ? 'close-circle' : ($report->status === 'reviewed' ? 'eye' : ($report->status === 'submitted' ? 'send-plane' : 'draft'))) }}-line"></i>
                    {{ $report->status_label }}
                </span>
                @if ($report->submitted_at)
                    <div class="lux-text-sm lux-text-muted lux-mt-1">
                        Dikirim {{ $report->submitted_at->format('d M Y H:i') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Alert Rejected ── --}}
        @if ($report->status === 'rejected' && $report->rejection_reason)
            <div class="lux-alert lux-alert-danger lux-mb-4" style="border-left:4px solid var(--danger)">
                <strong><i class="ri-close-circle-line"></i> Laporan Ditolak</strong>
                <div class="lux-mt-1">{{ $report->rejection_reason }}</div>
                @if ($report->approver)
                    <small class="lux-text-muted">oleh {{ $report->approver->name }} pada
                        {{ $report->approved_at?->format('d M Y H:i') }}</small>
                @endif
            </div>
        @endif

        @if ($report->status === 'approved')
            <div class="lux-alert lux-alert-success lux-mb-4" style="border-left:4px solid var(--success)">
                <strong><i class="ri-checkbox-circle-line"></i> Laporan Disetujui</strong>
                @if ($report->approver)
                    <small class="lux-text-muted lux-mt-1 lux-block">
                        oleh {{ $report->approver->name }} pada {{ $report->approved_at?->format('d M Y H:i') }}
                    </small>
                @endif
            </div>
        @endif

        {{-- ── Summary Cards ── --}}
        <div class="lux-card lux-mb-4">
            <div class="sum-grid">
                <div class="sum-card">
                    <div class="lbl">Saldo Awal</div>
                    <div class="val">Rp {{ number_format($report->opening_balance, 0, ',', '.') }}</div>
                </div>
                <div class="sum-card">
                    <div class="lbl">Total Pemasukan</div>
                    <div class="val lux-text-success">+ Rp {{ number_format($report->total_income, 0, ',', '.') }}</div>
                </div>
                <div class="sum-card">
                    <div class="lbl">Total Pengeluaran</div>
                    <div class="val lux-text-danger">− Rp {{ number_format($report->total_expense, 0, ',', '.') }}</div>
                </div>
                <div class="sum-card" style="border-top:3px solid var(--primary)">
                    <div class="lbl">Saldo Akhir</div>
                    <div class="val" style="color:var(--primary)">Rp
                        {{ number_format($report->closing_balance, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        {{-- ── Rincian Pemasukan ── --}}
        @php $incomeCats = ['PP','IURAN_PEJABAT','DANA_MISI','PENERIMAAN_LAIN','BUNGA_BANK']; @endphp
        <div class="lux-card lux-mb-4">
            <div class="section-title">PENERIMAAN DANA</div>
            <table class="lux-table lux-mt-3">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th class="lux-text-end">Jumlah (Rp)</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hasIncome = false; @endphp
                    @foreach ($incomeCats as $code)
                        @php $detail = $report->details->where('category_code',$code)->first(); @endphp
                        @if ($detail && $detail->amount > 0)
                            @php $hasIncome = true; @endphp
                            <tr>
                                <td>{{ $categories[$code]['name'] ?? $code }}</td>
                                <td class="lux-text-end lux-text-success">{{ number_format($detail->amount, 0, ',', '.') }}
                                </td>
                                <td class="lux-text-muted">{{ $detail->notes ?: '—' }}</td>
                            </tr>
                        @endif
                    @endforeach
                    @if (!$hasIncome)
                        <tr>
                            <td colspan="3" class="lux-text-center lux-text-muted lux-py-3">Tidak ada data pemasukan</td>
                        </tr>
                    @endif
                    <tr style="background:var(--bg-hover); font-weight:700">
                        <td>TOTAL PENERIMAAN</td>
                        <td class="lux-text-end lux-text-success">+ Rp {{ number_format($report->total_income, 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- ── Rincian Pengeluaran per Bidang ── --}}
        @php
            $expenseGroups = [
                'BIAYA MAJELIS DAERAH' => [
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
                'BIAYA BIDANG PENATALAYANAN' => ['BIAYA_BUKU_PENATALAYANAN', 'BIAYA_PENATALAYANAN_LAIN'],
                'BIAYA BIDANG PENGGEMBALAAN' => [
                    'BIAYA_SEKOLAH_MINGGU',
                    'BIAYA_PEMUDA',
                    'BIAYA_WANITA',
                    'BIAYA_CAMP_SEMINAR',
                ],
                'BIAYA BIDANG PENDIDIKAN DAN MISI' => [
                    'SETORAN_DANA_MISI_MP',
                    'BIAYA_MISI_SEMINAR',
                    'BIAYA_PENDIDIKAN_MD',
                ],
                'BIAYA BIDANG PELAYANAN MASYARAKAT' => [
                    'BANTUAN_SAKIT',
                    'BANTUAN_DUKACITA',
                    'BANTUAN_PEMBANGUNAN',
                    'BANTUAN_BENCANA',
                    'BIAYA_MULTIMEDIA',
                ],
            ];
        @endphp

        @foreach ($expenseGroups as $groupTitle => $codes)
            @php
                $groupDetails = $report->details->whereIn('category_code', $codes)->where('amount', '>', 0);
            @endphp
            @if ($groupDetails->isNotEmpty())
                <div class="lux-card lux-mb-4">
                    <div class="section-title">{{ $groupTitle }}</div>
                    <table class="lux-table lux-mt-3">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th class="lux-text-end">Jumlah (Rp)</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($codes as $code)
                                @php $detail = $report->details->where('category_code',$code)->first(); @endphp
                                @if ($detail && $detail->amount > 0)
                                    <tr>
                                        <td>{{ $categories[$code]['name'] ?? $code }}</td>
                                        <td class="lux-text-end lux-text-danger">
                                            {{ number_format($detail->amount, 0, ',', '.') }}</td>
                                        <td class="lux-text-muted">{{ $detail->notes ?: '—' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            <tr style="background:var(--bg-hover); font-weight:600">
                                <td>Subtotal</td>
                                <td class="lux-text-end lux-text-danger">
                                    Rp {{ number_format($groupDetails->sum('amount'), 0, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        @endforeach

        {{-- ── Timeline Riwayat Pengiriman ── --}}
        @if ($report->forwards->isNotEmpty() || $report->submitted_at)
            <div class="lux-card lux-mb-4">
                <div class="section-title lux-mb-4">RIWAYAT PENGIRIMAN & REVIEW</div>

                <div class="timeline lux-mt-4">
                    {{-- Entry pertama: dibuat --}}
                    <div class="tl-item">
                        <div class="tl-dot submit"><i class="ri-file-add-line"></i></div>
                        <div class="tl-body">
                            <div class="tl-route">📄 Laporan Dibuat</div>
                            <div class="lux-text-sm">oleh <strong>{{ $report->branch->name }}</strong></div>
                            <div class="tl-meta">{{ $report->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>

                    @foreach ($report->forwards as $fwd)
                        <div class="tl-item">
                            <div class="tl-dot {{ $fwd->action }}">
                                @if ($fwd->action === 'submit')
                                    <i class="ri-send-plane-line"></i>
                                @elseif($fwd->action === 'forward')
                                    <i class="ri-share-forward-line"></i>
                                @elseif($fwd->action === 'approve')
                                    <i class="ri-checkbox-circle-line"></i>
                                @else
                                    <i class="ri-close-circle-line"></i>
                                @endif
                            </div>
                            <div class="tl-body"
                                style="
                    @if ($fwd->action === 'approve') border-color:#6ee7b7; background:#f0fdf4;
                    @elseif($fwd->action === 'reject') border-color:#fca5a5; background:#fff5f5;
                    @elseif($fwd->action === 'forward') border-color:#a78bfa; background:#faf5ff; @endif
                ">
                                <div class="tl-route">
                                    @if ($fwd->action === 'submit')
                                        📤 Dikirim
                                    @elseif($fwd->action === 'forward')
                                        🔄 Diteruskan
                                    @elseif($fwd->action === 'approve')
                                        ✅ Disetujui
                                    @else
                                        ❌ Ditolak
                                    @endif
                                    :
                                    <span style="font-weight:400">{{ $fwd->fromBranch->name }}</span>
                                    →
                                    <strong>{{ $fwd->toBranch->name }}</strong>
                                </div>
                                @if ($fwd->notes)
                                    <div class="lux-text-sm lux-mt-1" style="color:var(--text-muted)">
                                        <i class="ri-chat-3-line"></i> {{ $fwd->notes }}
                                    </div>
                                @endif
                                <div class="tl-meta">
                                    oleh {{ $fwd->actor?->name ?? '—' }}
                                    &bull; {{ $fwd->forwarded_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Info Tambahan ── --}}
        <div class="lux-card lux-mb-5">
            <div class="section-title lux-mb-3">INFORMASI LAPORAN</div>
            <div class="lux-grid lux-grid-3 lux-mt-3">
                <div>
                    <small class="lux-text-muted">Cabang</small>
                    <div class="lux-text-bold">{{ $report->branch->name }}</div>
                </div>
                <div>
                    <small class="lux-text-muted">Periode</small>
                    <div>{{ $report->period_start?->format('d M Y') }} – {{ $report->period_end?->format('d M Y') }}</div>
                </div>
                <div>
                    <small class="lux-text-muted">Tipe Laporan</small>
                    <div>{{ $report->report_type === 'monthly' ? 'Bulanan' : 'Tahunan' }}</div>
                </div>
                @if ($report->submittedToBranch)
                    <div>
                        <small class="lux-text-muted">Pemegang Saat Ini</small>
                        <div class="lux-text-bold">{{ $report->submittedToBranch->name }}</div>
                    </div>
                @endif
                @if ($report->submitted_at)
                    <div>
                        <small class="lux-text-muted">Tanggal Kirim</small>
                        <div>{{ $report->submitted_at->format('d M Y, H:i') }}</div>
                    </div>
                @endif
                @if ($report->approved_at)
                    <div>
                        <small class="lux-text-muted">Tanggal
                            {{ $report->status === 'approved' ? 'Disetujui' : 'Ditolak' }}</small>
                        <div>{{ $report->approved_at->format('d M Y, H:i') }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Action Buttons ── --}}
        <div class="lux-flex lux-gap-2 lux-mt-4" style="flex-wrap:wrap">
            <a href="{{ route('admin.finance.reports.index') }}" class="lux-btn lux-btn-ghost">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>

            @if ($report->status === 'draft')
                @if (auth()->user()->hasPermission('finance.report.edit') &&
                        $report->branch_id === auth()->user()->roles->first()?->branch_id)
                    <a href="{{ route('admin.finance.reports.edit', $report) }}" class="lux-btn lux-btn-outline">
                        <i class="ri-edit-line"></i> Edit
                    </a>
                @endif
                @if (auth()->user()->hasPermission('finance.report.submit') &&
                        $report->branch_id === auth()->user()->roles->first()?->branch_id)
                    <button class="lux-btn lux-btn-primary" onclick="openModal('submitModal')">
                        <i class="ri-send-plane-line"></i> Kirim Laporan
                    </button>
                @endif
            @endif

            @if (in_array($report->status, ['submitted', 'reviewed']))
                @if (auth()->user()->hasPermission('finance.report.approve') &&
                        $report->submitted_to === auth()->user()->roles->first()?->branch_id)
                    <button class="lux-btn lux-btn-success" onclick="approveReport()">
                        <i class="ri-checkbox-circle-line"></i> Setujui
                    </button>
                    <button class="lux-btn lux-btn-danger" onclick="openModal('rejectModal')">
                        <i class="ri-close-circle-line"></i> Tolak
                    </button>
                @endif
                @if (auth()->user()->hasPermission('finance.report.forward') &&
                        $report->submitted_to === auth()->user()->roles->first()?->branch_id)
                    <button class="lux-btn lux-btn-primary" onclick="openModal('forwardModal')">
                        <i class="ri-share-forward-line"></i> Teruskan ke Atasan
                    </button>
                @endif
            @endif

            @can('finance.report.export')
                <a href="{{ route('admin.finance.export.annual-report', $report) }}"
                    class="lux-btn lux-btn-outline lux-text-success">
                    <i class="ri-file-excel-2-line"></i> Export Excel
                </a>
            @endcan
        </div>
    </div>

    {{-- ── Modal Submit ── --}}
    <div class="lux-modal-overlay" id="submitModal">
        <div class="lux-modal lux-modal-sm">
            <div class="lux-modal-header">
                <div class="lux-modal-title"><i class="ri-send-plane-line"></i> Kirim Laporan</div>
                <button type="button" class="lux-modal-close" onclick="closeModal('submitModal')">&times;</button>
            </div>
            <div class="lux-modal-body">
                <div class="lux-alert lux-alert-info lux-mb-3" style="font-size:.85rem">
                    <i class="ri-information-line"></i> Setelah dikirim, laporan tidak dapat diedit lagi. Pastikan semua
                    data sudah benar.
                </div>
                <div class="lux-form-group">
                    <label class="lux-label">Kirim ke Cabang / Pusat *</label>
                    <select id="submitTo" class="lux-select" required>
                        <option value="">— Pilih Tujuan —</option>
                        @foreach ($availableBranches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lux-form-group">
                    <label class="lux-label">Catatan (opsional)</label>
                    <textarea id="submitNotes" class="lux-textarea" rows="3" placeholder="Tambahkan catatan untuk penerima..."></textarea>
                </div>
            </div>
            <div class="lux-modal-footer">
                <button type="button" class="lux-btn lux-btn-ghost" onclick="closeModal('submitModal')">Batal</button>
                <button type="button" class="lux-btn lux-btn-primary" onclick="doSubmit()">
                    <i class="ri-send-plane-line"></i> Kirim
                </button>
            </div>
        </div>
    </div>

    {{-- ── Modal Reject ── --}}
    <div class="lux-modal-overlay" id="rejectModal">
        <div class="lux-modal lux-modal-sm">
            <div class="lux-modal-header">
                <div class="lux-modal-title"><i class="ri-close-circle-line lux-text-danger"></i> Tolak Laporan</div>
                <button type="button" class="lux-modal-close" onclick="closeModal('rejectModal')">&times;</button>
            </div>
            <div class="lux-modal-body">
                <div class="lux-form-group">
                    <label class="lux-label">Alasan Penolakan *</label>
                    <textarea id="rejectReason" class="lux-textarea" rows="4" required minlength="10"
                        placeholder="Jelaskan alasan penolakan (minimal 10 karakter)..."></textarea>
                </div>
            </div>
            <div class="lux-modal-footer">
                <button type="button" class="lux-btn lux-btn-ghost" onclick="closeModal('rejectModal')">Batal</button>
                <button type="button" class="lux-btn lux-btn-danger" onclick="doReject()">
                    <i class="ri-close-circle-line"></i> Tolak Laporan
                </button>
            </div>
        </div>
    </div>

    {{-- ── Modal Forward ── --}}
    <div class="lux-modal-overlay" id="forwardModal">
        <div class="lux-modal lux-modal-sm">
            <div class="lux-modal-header">
                <div class="lux-modal-title"><i class="ri-share-forward-line lux-text-primary"></i> Teruskan ke Atasan
                </div>
                <button type="button" class="lux-modal-close" onclick="closeModal('forwardModal')">&times;</button>
            </div>
            <div class="lux-modal-body">
                <div class="lux-alert lux-alert-info lux-mb-3" style="font-size:.85rem">
                    <i class="ri-information-line"></i> Laporan akan diteruskan dan dapat disetujui atau ditolak oleh
                    penerima berikutnya.
                </div>
                <div class="lux-form-group">
                    <label class="lux-label">Tujuan Berikutnya *</label>
                    <select id="forwardTo" class="lux-select" required>
                        <option value="">— Pilih Cabang / Pusat —</option>
                        @foreach ($availableBranches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lux-form-group">
                    <label class="lux-label">Catatan (opsional)</label>
                    <textarea id="forwardNotes" class="lux-textarea" rows="3"
                        placeholder="Tambahkan catatan atau instruksi khusus..."></textarea>
                </div>
            </div>
            <div class="lux-modal-footer">
                <button type="button" class="lux-btn lux-btn-ghost" onclick="closeModal('forwardModal')">Batal</button>
                <button type="button" class="lux-btn lux-btn-primary" onclick="doForward()">
                    <i class="ri-share-forward-line"></i> Teruskan
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const reportId = {{ $report->id }};
        const urls = {
            submit: "{{ route('admin.finance.reports.submit', ':id') }}",
            approve: "{{ route('admin.finance.reports.approve', ':id') }}",
            reject: "{{ route('admin.finance.reports.reject', ':id') }}",
            forward: "{{ route('admin.finance.reports.forward', ':id') }}",
        };

        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function doSubmit() {
            const to = document.getElementById('submitTo').value;
            const notes = document.getElementById('submitNotes').value;
            if (!to) {
                alert('Pilih tujuan terlebih dahulu');
                return;
            }
            fetch(urls.submit.replace(':id', reportId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    submitted_to: to,
                    notes
                }),
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    alert(res.message);
                    location.reload();
                } else alert(res.message || 'Gagal');
            });
        }

        function approveReport() {
            if (!confirm('Setujui laporan ini?')) return;
            fetch(urls.approve.replace(':id', reportId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    alert(res.message);
                    location.reload();
                } else alert(res.message);
            });
        }

        function doReject() {
            const reason = document.getElementById('rejectReason').value.trim();
            if (reason.length < 10) {
                alert('Alasan penolakan minimal 10 karakter');
                return;
            }
            fetch(urls.reject.replace(':id', reportId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    rejection_reason: reason
                }),
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    alert(res.message);
                    location.reload();
                } else alert(res.message);
            });
        }

        function doForward() {
            const to = document.getElementById('forwardTo').value;
            const notes = document.getElementById('forwardNotes').value;
            if (!to) {
                alert('Pilih tujuan terlebih dahulu');
                return;
            }
            fetch(urls.forward.replace(':id', reportId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    to_branch_id: to,
                    notes
                }),
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    alert(res.message);
                    location.reload();
                } else alert(res.message);
            });
        }
    </script>
@endpush
