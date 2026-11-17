<div class="p-4">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h5 class="fw-bold mb-1">{{ $report->report_type === 'monthly' ? 'Laporan Bulanan' : 'Laporan Tahunan' }}
            </h5>
            <p class="text-muted small mb-1">
                <i class="ri-building-line me-1"></i> {{ $report->branch?->name ?? '-' }}
                @if ($report->church)
                    • <i class="ri-user-line me-1"></i> {{ $report->church->name }}
                @endif
            </p>
            <small class="text-muted">
                <i class="ri-calendar-line me-1"></i>
                {{ \Carbon\Carbon::create($report->report_year, $report->report_month ?? 1, 1)->format('F Y') }}
            </small>
        </div>
        <span
            class="badge bg-{{ $report->status === 'approved' ? 'success' : ($report->status === 'rejected' ? 'danger' : ($report->status === 'submitted' ? 'primary' : ($report->status === 'reviewed' ? 'info' : 'warning'))) }} fs-6">
            {{ ucfirst($report->status) }}
        </span>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="p-2 bg-success bg-opacity-10 rounded text-center border border-success border-opacity-25">
                <small class="text-success d-block">Total Debit</small>
                <div class="fw-bold text-success">Rp {{ number_format($report->total_income, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2 bg-danger bg-opacity-10 rounded text-center border border-danger border-opacity-25">
                <small class="text-danger d-block">Total Kredit</small>
                <div class="fw-bold text-danger">Rp {{ number_format($report->total_expense, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2 bg-primary bg-opacity-10 rounded text-center border border-primary border-opacity-25">
                <small class="text-primary d-block">Saldo Akhir</small>
                <div class="fw-bold text-primary">Rp {{ number_format($report->closing_balance, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    @if ($report->status === 'rejected' && $report->rejection_reason)
        <div class="alert alert-danger small mb-3 d-flex align-items-center">
            <i class="ri-error-warning-fill me-2 fs-5"></i>
            <div><strong>Alasan Penolakan:</strong> {{ $report->rejection_reason }}</div>
        </div>
    @endif

    @php
        $incomeCats = ['PP', 'IURAN_PEJABAT', 'DANA_MISI', 'BUNGA_BANK', 'PENERIMAAN_LAIN'];
        $expenseCats = ['BPJS', 'IURAN_MDF', 'TAHAKA', 'PAJAK_BUNGA', 'ADMIN_BANK', 'PENGELUARAN_LAIN'];
    @endphp

    <h6 class="fw-bold text-success mb-2"><i class="ri-arrow-up-circle-line me-1"></i> Penerimaan Dana (Debit)</h6>
    <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Uraian</th>
                    <th class="text-end" style="width:120px;">Jumlah</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($incomeCats as $code)
                    @php $d = $report->details->where('category_code', $code)->first(); @endphp
                    @if ($d && $d->amount > 0)
                        <tr>
                            <td>{{ $d->category_name }}</td>
                            <td class="text-end text-success">Rp {{ number_format($d->amount, 0, ',', '.') }}</td>
                            <td>{{ $d->notes ?: '-' }}</td>
                        </tr>
                    @endif
                @endforeach
                <tr class="table-secondary fw-bold">
                    <td>TOTAL PENERIMAAN (DEBIT)</td>
                    <td class="text-end text-success">Rp {{ number_format($report->total_income, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <h6 class="fw-bold text-danger mb-2"><i class="ri-arrow-down-circle-line me-1"></i> Pengeluaran Dana (Kredit)</h6>
    <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Uraian</th>
                    <th class="text-end" style="width:120px;">Jumlah</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($expenseCats as $code)
                    @php $d = $report->details->where('category_code', $code)->first(); @endphp
                    @if ($d && $d->amount > 0)
                        <tr>
                            <td>{{ $d->category_name }}</td>
                            <td class="text-end text-danger">Rp {{ number_format($d->amount, 0, ',', '.') }}</td>
                            <td>{{ $d->notes ?: '-' }}</td>
                        </tr>
                    @endif
                @endforeach
                <tr class="table-secondary fw-bold">
                    <td>TOTAL PENGELUARAN (KREDIT)</td>
                    <td class="text-end text-danger">Rp {{ number_format($report->total_expense, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="card bg-light border-0 mb-3">
        <div class="card-body p-3 small">
            <div class="row g-2">
                <div class="col-6"><strong>Dibuat:</strong> {{ $report->created_at?->format('d M Y H:i') ?? '-' }}
                </div>
                @if ($report->submitted_at)
                    <div class="col-6"><strong>Dikirim:</strong>
                        {{ $report->submitted_at?->format('d M Y H:i') ?? '-' }}</div>
                @endif
                @if ($report->approved_at)
                    <div class="col-6"><strong>Disetujui:</strong> {{ $report->approver?->name ?? '-' }}
                        ({{ $report->approved_at?->format('d M Y H:i') }})</div>
                @endif
            </div>
            @if ($report->notes)
                <div class="mt-2"><strong>Catatan:</strong> {{ $report->notes }}</div>
            @endif
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
        @if ($report->status === 'draft')
            <button class="btn btn-sm btn-primary" onclick="editReport({{ $report->id }})"><i
                    class="ri-pencil-line me-1"></i> Edit</button>
            <button class="btn btn-sm btn-warning" onclick="submitReport({{ $report->id }})"><i
                    class="ri-send-plane-line me-1"></i> Kirim</button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteReport({{ $report->id }})"><i
                    class="ri-delete-bin-line me-1"></i> Hapus</button>
        @endif
        @if ($report->status === 'submitted' && auth()->user()->hasPermission('finance.report.approve'))
            <button class="btn btn-sm btn-success" onclick="approveReport({{ $report->id }})"><i
                    class="ri-check-line me-1"></i> Setujui</button>
            <button class="btn btn-sm btn-danger" onclick="rejectReport({{ $report->id }})"><i
                    class="ri-close-line me-1"></i> Tolak</button>
        @endif
        @if (auth()->user()->hasPermission('finance.report.export'))
            <a href="{{ route('admin.finance.export.transactions', ['branch_id' => $report->branch_id, 'year' => $report->report_year, 'month' => $report->report_month]) }}"
                class="btn btn-sm btn-outline-success ms-auto"><i class="ri-file-excel-2-line me-1"></i> Export</a>
        @endif
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    window.editReport = function(id) {
        $('#modalGeneric').modal('hide');
        setTimeout(() => $('.btn-edit-report[data-id="' + id + '"]').click(), 300);
    };

    window.submitReport = function(id) {
        $('#modalGeneric').modal('hide');
        setTimeout(() => $('.btn-submit-report[data-id="' + id + '"]').click(), 300);
    };

    window.deleteReport = function(id) {
        if (!confirm('Yakin ingin menghapus laporan ini?')) return;
        $.ajax({
            url: '/admin/finance/reports/' + id,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.success) {
                    $('#modalGeneric').modal('hide');
                    alert(res.message);
                    setTimeout(() => location.reload(), 500);
                }
            }
        });
    };

    window.approveReport = function(id) {
        if (!confirm('Apakah Anda yakin ingin menyetujui laporan ini?')) return;
        $.ajax({
            url: '/admin/finance/reports/' + id + '/approve',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.success) {
                    $('#modalGeneric').modal('hide');
                    alert(res.message);
                    setTimeout(() => location.reload(), 500);
                } else {
                    alert(res.message || 'Gagal menyetujui.');
                }
            }
        });
    };

    window.rejectReport = function(id) {
        const reason = prompt('Masukkan alasan penolakan (min. 10 karakter):');
        if (reason && reason.length >= 10) {
            $.ajax({
                url: '/admin/finance/reports/' + id + '/reject',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    rejection_reason: reason
                },
                success: function(res) {
                    if (res.success) {
                        $('#modalGeneric').modal('hide');
                        alert(res.message);
                        setTimeout(() => location.reload(), 500);
                    } else {
                        alert(res.message || 'Gagal menolak laporan.');
                    }
                }
            });
        } else if (reason !== null) {
            alert('Alasan penolakan minimal 10 karakter.');
        }
    };
</script>
