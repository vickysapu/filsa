@extends('layouts.admin')

@section('title', 'Data Pengeluaran')

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Data Pengeluaran</h1>
                <p class="text-muted">Kelola semua pengeluaran keuangan gereja</p>
            </div>
            @if (auth()->user()->hasPermission('pengeluaran.create'))
                <a href="{{ route('admin.pengeluaran.create') }}" class="lux-btn lux-btn-primary">
                    <i class="ri-add-line"></i> Tambah Pengeluaran
                </a>
            @endif
        </div>

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="lux-card mb-4"
                style="border-left: 4px solid var(--success); background: #f0fdf4; display: flex; align-items: center; gap: 10px;">
                <i class="ri-checkbox-circle-line" style="color: var(--success); font-size: 1.5rem;"></i>
                <span style="color: #166534; font-weight: 600;">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="lux-card mb-4"
                style="border-left: 4px solid var(--danger); background: #fef2f2; display: flex; align-items: center; gap: 10px;">
                <i class="ri-error-warning-line" style="color: var(--danger); font-size: 1.5rem;"></i>
                <span style="color: #991b1b; font-weight: 600;">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="lux-grid lux-grid-4 mb-4">
            <div class="lux-card" style="border-left: 4px solid var(--danger);">
                <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem;">Total Pengeluaran</p>
                <h3 style="font-size: 1.8rem; font-weight: 700; color: var(--danger);">
                    Rp {{ number_format($stats['total'], 0, ',', '.') }}
                </h3>
                <p class="text-muted" style="font-size: 0.8rem;">{{ $stats['count'] }} transaksi</p>
            </div>

            <div class="lux-card" style="border-left: 4px solid var(--success);">
                <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem;">Terverifikasi</p>
                <h3 style="font-size: 1.8rem; font-weight: 700; color: var(--success);">
                    Rp {{ number_format($stats['verified'], 0, ',', '.') }}
                </h3>
                <p class="text-muted" style="font-size: 0.8rem;">Sudah diverifikasi</p>
            </div>

            <div class="lux-card" style="border-left: 4px solid var(--warning);">
                <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem;">Pending</p>
                <h3 style="font-size: 1.8rem; font-weight: 700; color: var(--warning);">
                    {{ $stats['pending'] }}
                </h3>
                <p class="text-muted" style="font-size: 0.8rem;">Menunggu verifikasi</p>
            </div>

            <div class="lux-card" style="border-left: 4px solid var(--accent);">
                <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem;">Export</p>
                <button onclick="window.location.href='{{ route('admin.pengeluaran.export', request()->query()) }}'"
                    class="lux-btn lux-btn-outline lux-btn-sm" style="width: 100%; margin-top: 1rem;">
                    <i class="ri-file-excel-line"></i> Download Excel
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="lux-card mb-4">
            <form action="{{ route('admin.pengeluaran.index') }}" method="GET"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">

                <div>
                    <label class="lux-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_start" class="lux-input" value="{{ request('tanggal_start') }}">
                </div>

                <div>
                    <label class="lux-label">Tanggal Akhir</label>
                    <input type="date" name="tanggal_end" class="lux-input" value="{{ request('tanggal_end') }}">
                </div>

                <div>
                    <label class="lux-label">Kategori</label>
                    <select name="kategori" class="lux-select">
                        <option value="">Semua</option>
                        <option value="operasional" {{ request('kategori') == 'operasional' ? 'selected' : '' }}>
                            Operasional</option>
                        <option value="listrik_air" {{ request('kategori') == 'listrik_air' ? 'selected' : '' }}>Listrik &
                            Air</option>
                        <option value="gaji_karyawan" {{ request('kategori') == 'gaji_karyawan' ? 'selected' : '' }}>Gaji
                            Karyawan</option>
                        <option value="kegiatan" {{ request('kategori') == 'kegiatan' ? 'selected' : '' }}>Kegiatan
                        </option>
                        <option value="pemeliharaan" {{ request('kategori') == 'pemeliharaan' ? 'selected' : '' }}>
                            Pemeliharaan</option>
                        <option value="amal_sosial" {{ request('kategori') == 'amal_sosial' ? 'selected' : '' }}>Amal &
                            Sosial</option>
                        <option value="konsumsi" {{ request('kategori') == 'konsumsi' ? 'selected' : '' }}>Konsumsi
                        </option>
                        <option value="lainnya" {{ request('kategori') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="lux-label">Status</label>
                    <select name="status" class="lux-select">
                        <option value="">Semua</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <div>
                    <label class="lux-label">Cari</label>
                    <input type="text" name="search" class="lux-input" value="{{ request('search') }}"
                        placeholder="No. Transaksi, Keperluan...">
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="lux-btn lux-btn-primary" style="flex: 1;">
                        <i class="ri-filter-line"></i> Filter
                    </button>
                    <a href="{{ route('admin.pengeluaran.index') }}" class="lux-btn lux-btn-outline" title="Reset Filter">
                        <i class="ri-refresh-line"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="lux-card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th
                                style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Tanggal</th>
                            <th
                                style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                No. Transaksi</th>
                            <th
                                style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Kategori</th>
                            <th
                                style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Keperluan</th>
                            <th
                                style="padding: 15px; text-align: right; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Jumlah</th>
                            <th
                                style="padding: 15px; text-align: center; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Status</th>
                            <th
                                style="padding: 15px; text-align: center; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengeluarans as $pengeluaran)
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: 0.2s;"
                                onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                <td style="padding: 15px;">
                                    <div style="font-weight: 600;">{{ $pengeluaran->tanggal->format('d M Y') }}</div>
                                    <div class="text-muted" style="font-size: 0.8rem;">
                                        {{ $pengeluaran->tanggal->diffForHumans() }}</div>
                                </td>
                                <td style="padding: 15px;">
                                    <span style="font-family: monospace; font-weight: 600; color: var(--danger);">
                                        {{ $pengeluaran->nomor_transaksi }}
                                    </span>
                                </td>
                                <td style="padding: 15px;">
                                    <span class="lux-badge" style="background: #fce7f3; color: #9d174d;">
                                        {{ $pengeluaran->kategori_label }}
                                    </span>
                                </td>
                                <td style="padding: 15px;">
                                    <div style="font-weight: 500;">{{ $pengeluaran->keperluan }}</div>
                                    <div class="text-muted" style="font-size: 0.8rem;">
                                        {{ $pengeluaran->penerima }}
                                    </div>
                                </td>
                                <td style="padding: 15px; text-align: right;">
                                    <div style="font-weight: 700; color: var(--danger);">
                                        Rp {{ number_format($pengeluaran->jumlah, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    @if ($pengeluaran->status === 'verified')
                                        <span class="lux-badge lux-badge-success">Verified</span>
                                    @elseif($pengeluaran->status === 'pending')
                                        <span class="lux-badge lux-badge-warning">Pending</span>
                                    @else
                                        <span class="lux-badge lux-badge-danger">Ditolak</span>
                                    @endif
                                </td>
                                <td style="padding: 15px;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <a href="{{ route('admin.pengeluaran.show', $pengeluaran) }}"
                                            class="lux-btn lux-btn-outline lux-btn-sm" title="Detail">
                                            <i class="ri-eye-line"></i>
                                        </a>

                                        @if ($pengeluaran->status === 'pending' && auth()->user()->hasPermission('pengeluaran.edit'))
                                            <a href="{{ route('admin.pengeluaran.edit', $pengeluaran) }}"
                                                class="lux-btn lux-btn-primary lux-btn-sm" title="Edit">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                        @endif

                                        @if ($pengeluaran->status === 'pending' && auth()->user()->hasPermission('pengeluaran.verify'))
                                            <form action="{{ route('admin.pengeluaran.verify', $pengeluaran) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="lux-btn lux-btn-outline lux-btn-sm"
                                                    style="border-color: var(--success); color: var(--success);"
                                                    title="Verifikasi">
                                                    <i class="ri-checkbox-circle-line"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($pengeluaran->status === 'pending' && auth()->user()->hasPermission('pengeluaran.delete'))
                                            <form action="{{ route('admin.pengeluaran.destroy', $pengeluaran) }}"
                                                method="POST" onsubmit="return confirm('Yakin ingin menghapus?')"
                                                style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="lux-btn lux-btn-outline lux-btn-sm"
                                                    style="border-color: var(--danger); color: var(--danger);"
                                                    title="Hapus">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                                    <i class="ri-inbox-line" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p style="margin-top: 1rem;">Belum ada data pengeluaran</p>
                                    @if (auth()->user()->hasPermission('pengeluaran.create'))
                                        <a href="{{ route('admin.pengeluaran.create') }}"
                                            class="lux-btn lux-btn-primary lux-btn-sm" style="margin-top: 1rem;">
                                            <i class="ri-add-line"></i> Tambah Pengeluaran Pertama
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($pengeluarans->hasPages())
                <div
                    style="padding: 15px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                    <span class="text-muted" style="font-size: 0.85rem;">
                        Menampilkan {{ $pengeluarans->firstItem() }} - {{ $pengeluarans->lastItem() }} dari
                        {{ $pengeluarans->total() }} data
                    </span>
                    <div>
                        {{ $pengeluarans->links('pagination::simple-bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
