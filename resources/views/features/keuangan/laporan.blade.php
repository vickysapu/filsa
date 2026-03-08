@extends('layouts.admin')

@section('title', 'Laporan Keuangan')

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Laporan Keuangan</h1>
                <p class="text-muted">Rekap lengkap pemasukan dan pengeluaran</p>
            </div>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="{{ route('admin.keuangan.dashboard') }}" class="lux-btn lux-btn-outline">
                    <i class="ri-dashboard-line"></i> Dashboard
                </a>
                <button onclick="window.print()" class="lux-btn lux-btn-outline">
                    <i class="ri-printer-line"></i> Cetak
                </button>
                <a href="{{ route('admin.keuangan.laporan.export', ['start' => $start, 'end' => $end]) }}"
                    class="lux-btn lux-btn-primary">
                    <i class="ri-file-excel-line"></i> Export Excel
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="lux-card mb-4">
            <form action="{{ route('admin.keuangan.laporan') }}" method="GET"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div>
                    <label class="lux-label">Dari Tanggal</label>
                    <input type="date" name="start" class="lux-input" value="{{ $start }}">
                </div>
                <div>
                    <label class="lux-label">Sampai Tanggal</label>
                    <input type="date" name="end" class="lux-input" value="{{ $end }}">
                </div>
                <div>
                    <label class="lux-label">Kategori</label>
                    <select name="kategori" class="lux-select">
                        <option value="">Semua</option>
                        <option value="persembahan_minggu" {{ $kategori == 'persembahan_minggu' ? 'selected' : '' }}>
                            Persembahan Minggu</option>
                        <option value="donasi" {{ $kategori == 'donasi' ? 'selected' : '' }}>Donasi</option>
                        <option value="operasional" {{ $kategori == 'operasional' ? 'selected' : '' }}>Operasional</option>
                        <option value="kegiatan" {{ $kategori == 'kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                        <option value="lainnya" {{ $kategori == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <button type="submit" class="lux-btn lux-btn-primary">
                    <i class="ri-filter-line"></i> Filter
                </button>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="lux-grid lux-grid-3 mb-4">
            <div class="lux-card" style="border-left: 4px solid var(--success); text-align: center;">
                <p class="text-muted" style="font-size: 0.85rem;">Total Pemasukan</p>
                <h3 style="font-size: 1.8rem; font-weight: 700; color: var(--success);">
                    Rp {{ number_format($totalPemasukan, 0, ',', '.') }}
                </h3>
            </div>
            <div class="lux-card" style="border-left: 4px solid var(--danger); text-align: center;">
                <p class="text-muted" style="font-size: 0.85rem;">Total Pengeluaran</p>
                <h3 style="font-size: 1.8rem; font-weight: 700; color: var(--danger);">
                    Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                </h3>
            </div>
            <div class="lux-card" style="border-left: 4px solid var(--primary); text-align: center;">
                <p class="text-muted" style="font-size: 0.85rem;">Saldo Akhir</p>
                <h3
                    style="font-size: 1.8rem; font-weight: 700; color: {{ $saldoAkhir >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                    Rp {{ number_format(abs($saldoAkhir), 0, ',', '.') }}
                </h3>
                <p style="font-size: 0.75rem; color: {{ $saldoAkhir >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                    {{ $saldoAkhir >= 0 ? 'Surplus' : 'Defisit' }}
                </p>
            </div>
        </div>

        <!-- Transaction Report Table -->
        <div class="lux-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.5rem; border-bottom: 2px solid #e2e8f0; background: #f8fafc;">
                <h3 class="lux-card-title" style="margin: 0;">Rincian Transaksi</h3>
                <p class="text-muted" style="font-size: 0.85rem; margin: 0.5rem 0 0 0;">
                    Periode: {{ \Carbon\Carbon::parse($start)->format('d F Y') }} -
                    {{ \Carbon\Carbon::parse($end)->format('d F Y') }}
                </p>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
                            <th
                                style="padding: 12px; text-align: left; color: var(--text-muted); font-size: 0.8rem; font-weight: 700;">
                                Tanggal</th>
                            <th
                                style="padding: 12px; text-align: left; color: var(--text-muted); font-size: 0.8rem; font-weight: 700;">
                                No. Transaksi</th>
                            <th
                                style="padding: 12px; text-align: left; color: var(--text-muted); font-size: 0.8rem; font-weight: 700;">
                                Jenis</th>
                            <th
                                style="padding: 12px; text-align: left; color: var(--text-muted); font-size: 0.8rem; font-weight: 700;">
                                Kategori</th>
                            <th
                                style="padding: 12px; text-align: left; color: var(--text-muted); font-size: 0.8rem; font-weight: 700;">
                                Keterangan</th>
                            <th
                                style="padding: 12px; text-align: right; color: var(--text-muted); font-size: 0.8rem; font-weight: 700;">
                                Pemasukan</th>
                            <th
                                style="padding: 12px; text-align: right; color: var(--text-muted); font-size: 0.8rem; font-weight: 700;">
                                Pengeluaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaksiPerHari as $tanggal => $data)
                            @foreach ($data['transactions'] as $transaksi)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px; font-weight: 600;">
                                        {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}</td>
                                    <td style="padding: 12px; font-family: monospace; font-size: 0.85rem;">
                                        {{ $transaksi['data']->nomor_transaksi }}</td>
                                    <td style="padding: 12px;">
                                        @if ($transaksi['type'] === 'pemasukan')
                                            <span class="lux-badge lux-badge-success">Pemasukan</span>
                                        @else
                                            <span class="lux-badge lux-badge-danger">Pengeluaran</span>
                                        @endif
                                    </td>
                                    <td style="padding: 12px;">{{ $transaksi['data']->kategori_label }}</td>
                                    <td style="padding: 12px; max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $transaksi['type'] === 'pemasukan' ? $transaksi['data']->sumber_dana : $transaksi['data']->keperluan }}
                                    </td>
                                    <td style="padding: 12px; text-align: right; font-weight: 600; color: var(--success);">
                                        @if ($transaksi['type'] === 'pemasukan')
                                            Rp {{ number_format($transaksi['data']->jumlah, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="padding: 12px; text-align: right; font-weight: 600; color: var(--danger);">
                                        @if ($transaksi['type'] === 'pengeluaran')
                                            Rp {{ number_format($transaksi['data']->jumlah, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                                    <i class="ri-inbox-line" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p style="margin-top: 1rem;">Tidak ada transaksi pada periode ini</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8fafc; border-top: 2px solid #e2e8f0; font-weight: 700;">
                            <td colspan="5" style="padding: 15px; text-align: right;">TOTAL</td>
                            <td style="padding: 15px; text-align: right; color: var(--success);">
                                Rp {{ number_format($totalPemasukan, 0, ',', '.') }}
                            </td>
                            <td style="padding: 15px; text-align: right; color: var(--danger);">
                                Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr style="background: linear-gradient(135deg, var(--primary), var(--accent)); color: white;">
                            <td colspan="5" style="padding: 15px; text-align: right;">SALDO AKHIR</td>
                            <td colspan="2"
                                style="padding: 15px; text-align: center; font-size: 1.2rem; font-weight: 800;">
                                Rp {{ number_format(abs($saldoAkhir), 0, ',', '.') }}
                                ({{ $saldoAkhir >= 0 ? 'Surplus' : 'Defisit' }})
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Print styling
        window.addEventListener('beforeprint', function() {
            document.querySelectorAll('.lux-btn').forEach(btn => btn.style.display = 'none');
        });
        window.addEventListener('afterprint', function() {
            document.querySelectorAll('.lux-btn').forEach(btn => btn.style.display = '');
        });
    </script>
@endpush
