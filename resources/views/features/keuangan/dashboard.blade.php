@extends('layouts.admin')

@section('title', 'Dashboard Keuangan')

@section('content')
    <div class="lux-container">

        <!-- Header with Date Filter -->
        <div class="flex-between mb-5" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Dashboard Keuangan</h1>
                <p class="text-muted">Ringkasan keuangan Gereja Filadelfia</p>
            </div>

            <form action="{{ route('admin.keuangan.dashboard') }}" method="GET"
                style="display: flex; gap: 0.75rem; align-items: end; flex-wrap: wrap;">
                <div>
                    <label class="lux-label">Dari Tanggal</label>
                    <input type="date" name="start" class="lux-input" value="{{ $start }}"
                        style="width: 160px;">
                </div>
                <div>
                    <label class="lux-label">Sampai Tanggal</label>
                    <input type="date" name="end" class="lux-input" value="{{ $end }}"
                        style="width: 160px;">
                </div>
                <button type="submit" class="lux-btn lux-btn-primary">
                    <i class="ri-filter-line"></i> Filter
                </button>
                <a href="{{ route('admin.keuangan.laporan') }}" class="lux-btn lux-btn-outline">
                    <i class="ri-file-text-line"></i> Lihat Laporan
                </a>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="lux-grid lux-grid-4 mb-4">
            <!-- Total Pemasukan -->
            <div class="lux-card"
                style="border-left: 4px solid var(--success); background: linear-gradient(135deg, #f0fdf4 0%, white 100%);">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem;">Total Pemasukan</p>
                        <h3 style="font-size: 1.8rem; font-weight: 700; color: var(--success);">
                            Rp {{ number_format($totalPemasukan, 0, ',', '.') }}
                        </h3>
                    </div>
                    <div
                        style="width: 50px; height: 50px; background: var(--success); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="ri-arrow-down-circle-line" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                </div>
                <p class="text-muted" style="font-size: 0.8rem; margin-top: 1rem;">
                    <i class="ri-time-line"></i>
                    {{ \Carbon\Carbon::parse($start)->diffForHumans(\Carbon\Carbon::parse($end), true) }}
                </p>
            </div>

            <!-- Total Pengeluaran -->
            <div class="lux-card"
                style="border-left: 4px solid var(--danger); background: linear-gradient(135deg, #fef2f2 0%, white 100%);">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem;">Total Pengeluaran</p>
                        <h3 style="font-size: 1.8rem; font-weight: 700; color: var(--danger);">
                            Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                        </h3>
                    </div>
                    <div
                        style="width: 50px; height: 50px; background: var(--danger); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="ri-arrow-up-circle-line" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                </div>
                <p class="text-muted" style="font-size: 0.8rem; margin-top: 1rem;">
                    <i class="ri-time-line"></i>
                    {{ \Carbon\Carbon::parse($start)->diffForHumans(\Carbon\Carbon::parse($end), true) }}
                </p>
            </div>

            <!-- Saldo -->
            <div class="lux-card"
                style="border-left: 4px solid var(--primary); background: linear-gradient(135deg, #e0e7ff 0%, white 100%);">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem;">Saldo</p>
                        <h3
                            style="font-size: 1.8rem; font-weight: 700; color: {{ $saldo >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                            Rp {{ number_format(abs($saldo), 0, ',', '.') }}
                        </h3>
                        <p style="font-size: 0.75rem; color: {{ $saldo >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                            {{ $saldo >= 0 ? 'Surplus' : 'Defisit' }}
                        </p>
                    </div>
                    <div
                        style="width: 50px; height: 50px; background: {{ $saldo >= 0 ? 'var(--success)' : 'var(--danger)' }}; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="ri-wallet-line" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                </div>
                <p class="text-muted" style="font-size: 0.8rem; margin-top: 1rem;">
                    <i class="ri-percent-line"></i> Growth: {{ number_format($growth, 1) }}%
                </p>
            </div>

            <!-- Quick Actions -->
            <div class="lux-card" style="border-left: 4px solid var(--accent);">
                <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 1rem;">Aksi Cepat</p>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="{{ route('admin.pemasukan.create') }}" class="lux-btn lux-btn-outline lux-btn-sm"
                        style="justify-content: start;">
                        <i class="ri-add-circle-line" style="color: var(--success);"></i> Tambah Pemasukan
                    </a>
                    <a href="{{ route('admin.pengeluaran.create') }}" class="lux-btn lux-btn-outline lux-btn-sm"
                        style="justify-content: start;">
                        <i class="ri-subtract-circle-line" style="color: var(--danger);"></i> Tambah Pengeluaran
                    </a>
                    <a href="{{ route('admin.keuangan.laporan.export', ['start' => $start, 'end' => $end]) }}"
                        class="lux-btn lux-btn-outline lux-btn-sm" style="justify-content: start;">
                        <i class="ri-file-excel-line"></i> Export Laporan
                    </a>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="lux-grid lux-grid-2 mb-4">
            <!-- Trend Chart -->
            <div class="lux-card">
                <div class="lux-card-header">
                    <h3 class="lux-card-title">Trend Keuangan</h3>
                    <i class="ri-line-chart-line" style="color: var(--primary);"></i>
                </div>
                <canvas id="trendChart" style="max-height: 300px;"></canvas>
            </div>

            <!-- Category Distribution -->
            <div class="lux-card">
                <div class="lux-card-header">
                    <h3 class="lux-card-title">Distribusi per Kategori</h3>
                    <i class="ri-pie-chart-line" style="color: var(--accent);"></i>
                </div>
                <canvas id="categoryChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <!-- Pending & Recent Transactions -->
        <div class="lux-grid lux-grid-2 mb-4">
            <!-- Pending Verification -->
            <div class="lux-card">
                <div class="lux-card-header">
                    <h3 class="lux-card-title">Menunggu Verifikasi</h3>
                    <span class="lux-badge lux-badge-warning">{{ $pendingPemasukan + $pendingPengeluaran }}</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div style="padding: 1.5rem; background: #fef9c3; border-radius: var(--radius); text-align: center;">
                        <i class="ri-arrow-down-circle-line"
                            style="font-size: 2rem; color: var(--warning); margin-bottom: 0.5rem;"></i>
                        <h4 style="font-size: 2rem; font-weight: 700; color: var(--warning);">{{ $pendingPemasukan }}</h4>
                        <p class="text-muted" style="font-size: 0.85rem;">Pemasukan Pending</p>
                        <a href="{{ route('admin.pemasukan.index', ['status' => 'pending']) }}"
                            class="lux-btn lux-btn-outline lux-btn-sm" style="margin-top: 0.75rem;">
                            Lihat Semua
                        </a>
                    </div>

                    <div style="padding: 1.5rem; background: #fee2e2; border-radius: var(--radius); text-align: center;">
                        <i class="ri-arrow-up-circle-line"
                            style="font-size: 2rem; color: var(--danger); margin-bottom: 0.5rem;"></i>
                        <h4 style="font-size: 2rem; font-weight: 700; color: var(--danger);">{{ $pendingPengeluaran }}
                        </h4>
                        <p class="text-muted" style="font-size: 0.85rem;">Pengeluaran Pending</p>
                        <a href="{{ route('admin.pengeluaran.index', ['status' => 'pending']) }}"
                            class="lux-btn lux-btn-outline lux-btn-sm" style="margin-top: 0.75rem;">
                            Lihat Semua
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="lux-card">
                <div class="lux-card-header">
                    <h3 class="lux-card-title">Transaksi Terbaru</h3>
                    <a href="{{ route('admin.keuangan.laporan') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                        Lihat Semua
                    </a>
                </div>

                <div style="max-height: 300px; overflow-y: auto;">
                    @forelse($transaksiTerbaru as $transaksi)
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; border-bottom: 1px solid #f1f5f9;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div
                                    style="width: 40px; height: 40px; border-radius: 50%; background: {{ $transaksi->type === 'pemasukan' ? '#dcfce7' : '#fee2e2' }}; display: flex; align-items: center; justify-content: center;">
                                    <i class="ri-{{ $transaksi->type === 'pemasukan' ? 'arrow-down' : 'arrow-up' }}-line"
                                        style="color: {{ $transaksi->type === 'pemasukan' ? 'var(--success)' : 'var(--danger)' }};"></i>
                                </div>
                                <div>
                                    <p style="font-weight: 600; font-size: 0.9rem;">{{ $transaksi->nomor_transaksi }}</p>
                                    <p class="text-muted" style="font-size: 0.8rem;">{{ $transaksi->kategori }}</p>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <p
                                    style="font-weight: 700; color: {{ $transaksi->type === 'pemasukan' ? 'var(--success)' : 'var(--danger)' }};">
                                    {{ $transaksi->type === 'pemasukan' ? '+' : '-' }} Rp
                                    {{ number_format($transaksi->jumlah, 0, ',', '.') }}
                                </p>
                                <p class="text-muted" style="font-size: 0.75rem;">
                                    {{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d M') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted" style="text-align: center; padding: 2rem;">Belum ada transaksi</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Trend Chart Data
            const trendLabels = {!! json_encode(
                $trendPemasukan->merge($trendPengeluaran)->unique('date')->sortBy('date')->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->values(),
            ) !!};

            const pemasukanData = {!! json_encode($trendPemasukan->keyBy('date')->map(fn($d) => $d->total)) !!};
            const pengeluaranData = {!! json_encode($trendPengeluaran->keyBy('date')->map(fn($d) => $d->total)) !!};

            // Fill missing dates with 0
            const filledPemasukan = trendLabels.map(label => {
                const date = {!! json_encode(
                    $trendPemasukan->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->values(),
                ) !!}.find(d => d === label);
                return date ? pemasukanData[date] : 0;
            });

            const filledPengeluaran = trendLabels.map(label => {
                const date = {!! json_encode(
                    $trendPengeluaran->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->values(),
                ) !!}.find(d => d === label);
                return date ? pengeluaranData[date] : 0;
            });

            // Trend Chart
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                            label: 'Pemasukan',
                            data: filledPemasukan,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Pengeluaran',
                            data: filledPengeluaran,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + context.parsed.y
                                        .toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + 'Jt';
                                }
                            }
                        }
                    }
                }
            });

            // Category Chart
            const kategoriLabels = {!! json_encode($pemasukanPerKategori->pluck('kategori')->map(fn($k) => ucfirst(str_replace('_', ' ', $k)))) !!};
            const kategoriData = {!! json_encode($pemasukanPerKategori->pluck('total')) !!};

            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: kategoriLabels,
                    datasets: [{
                        data: kategoriData,
                        backgroundColor: [
                            '#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                            '#ec4899'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': Rp ' + context.parsed.toLocaleString(
                                        'id-ID');
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
