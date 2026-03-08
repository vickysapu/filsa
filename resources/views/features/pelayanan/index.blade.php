@extends('layouts.admin')

@section('title', 'Jadwal Pelayanan')

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Jadwal Pelayanan</h1>
                <p class="text-muted">Kelola jadwal dan petugas pelayanan gereja</p>
            </div>
            @if (auth()->user()->hasPermission('pelayanan.create'))
                <a href="{{ route('admin.jadwal.create') }}" class="lux-btn lux-btn-primary">
                    <i class="ri-calendar-add-line"></i> Buat Jadwal Baru
                </a>
            @endif
        </div>

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="lux-card mb-4" style="border-left: 4px solid var(--success); background: #f0fdf4;">
                <span style="color: #166534; font-weight: 600;">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="lux-card mb-4" style="border-left: 4px solid var(--danger); background: #fef2f2;">
                <span style="color: #991b1b; font-weight: 600;">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="lux-card mb-4">
            <form action="{{ route('admin.jadwal.index') }}" method="GET"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">

                <div>
                    <label class="lux-label">Dari Tanggal</label>
                    <input type="date" name="tanggal_start" class="lux-input" value="{{ request('tanggal_start') }}">
                </div>

                <div>
                    <label class="lux-label">Sampai Tanggal</label>
                    <input type="date" name="tanggal_end" class="lux-input" value="{{ request('tanggal_end') }}">
                </div>

                <div>
                    <label class="lux-label">Kategori</label>
                    <select name="kategori_id" class="lux-select">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="lux-label">Status</label>
                    <select name="status" class="lux-select">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan
                        </option>
                    </select>
                </div>

                <div>
                    <label class="lux-label">Cari</label>
                    <input type="text" name="search" class="lux-input" value="{{ request('search') }}"
                        placeholder="Nama pelayanan, lokasi...">
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="lux-btn lux-btn-primary" style="flex: 1;">
                        <i class="ri-filter-line"></i> Filter
                    </button>
                    <a href="{{ route('admin.jadwal.index') }}" class="lux-btn lux-btn-outline" title="Reset">
                        <i class="ri-refresh-line"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Jadwal Cards Grid -->
        <div class="lux-grid lux-grid-3">
            @forelse($pelayanans as $pelayanan)
                <div class="lux-card" style="border-top: 4px solid {{ $pelayanan->kategori->warna }}; position: relative;">

                    <!-- Status Badge -->
                    <div style="position: absolute; top: 1rem; right: 1rem;">
                        @php
                            $statusColors = [
                                'draft' => 'lux-badge-warning',
                                'active' => 'lux-badge-success',
                                'completed' => 'lux-badge-info',
                                'cancelled' => 'lux-badge-danger',
                            ];
                            $statusLabels = [
                                'draft' => 'Draft',
                                'active' => 'Aktif',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ];
                        @endphp
                        <span class="lux-badge {{ $statusColors[$pelayanan->status] }}">
                            {{ $statusLabels[$pelayanan->status] }}
                        </span>
                    </div>

                    <!-- Kategori Badge -->
                    <div style="margin-bottom: 0.75rem;">
                        <span class="lux-badge"
                            style="background: {{ $pelayanan->kategori->warna }}20; color: {{ $pelayanan->kategori->warna }};">
                            {{ $pelayanan->kategori->nama_kategori }}
                        </span>
                    </div>

                    <!-- Nama Pelayanan -->
                    <h3
                        style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-main); line-height: 1.4;">
                        {{ $pelayanan->nama_pelayanan }}
                    </h3>

                    <!-- Info Grid -->
                    <div style="display: grid; gap: 0.75rem; margin-bottom: 1.5rem;">
                        <div
                            style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem;">
                            <i class="ri-calendar-line" style="color: var(--primary);"></i>
                            <span>{{ $pelayanan->tanggal->format('d F Y') }}</span>
                        </div>
                        <div
                            style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem;">
                            <i class="ri-time-line" style="color: var(--primary);"></i>
                            <span>{{ \Carbon\Carbon::parse($pelayanan->waktu)->format('H:i') }} WIB</span>
                        </div>
                        <div
                            style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem;">
                            <i class="ri-map-pin-line" style="color: var(--primary);"></i>
                            <span>{{ $pelayanan->lokasi }}</span>
                        </div>
                        <div
                            style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.9rem;">
                            <i class="ri-team-line" style="color: var(--primary);"></i>
                            <span>{{ $pelayanan->details->where('status', 'active')->count() }} Petugas</span>
                        </div>
                    </div>

                    <!-- Preview Petugas -->
                    <div
                        style="margin-bottom: 1.5rem; padding: 0.75rem; background: #f8fafc; border-radius: var(--radius);">
                        <p class="lux-label" style="margin-bottom: 0.5rem; font-size: 0.85rem;">Petugas Aktif:</p>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            @foreach ($pelayanan->details->where('status', 'active')->take(3) as $detail)
                                <span class="lux-badge"
                                    style="background: white; border: 1px solid #e2e8f0; font-size: 0.75rem;">
                                    {{ explode(' ', $detail->jemaat->nama_lengkap)[0] }}
                                    @if ($detail->role)
                                        <span style="opacity: 0.6;">({{ $detail->role }})</span>
                                    @endif
                                </span>
                            @endforeach
                            @if ($pelayanan->details->where('status', 'active')->count() > 3)
                                <span class="lux-badge" style="background: #e2e8f0;">
                                    +{{ $pelayanan->details->where('status', 'active')->count() - 3 }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="{{ route('admin.jadwal.show', $pelayanan) }}"
                            class="lux-btn lux-btn-outline lux-btn-sm" style="flex: 1;">
                            <i class="ri-eye-line"></i> Detail
                        </a>
                        @if (
                            $pelayanan->status !== 'completed' &&
                                $pelayanan->status !== 'cancelled' &&
                                auth()->user()->hasPermission('pelayanan.edit'))
                            <a href="{{ route('admin.jadwal.edit', $pelayanan) }}"
                                class="lux-btn lux-btn-primary lux-btn-sm">
                                <i class="ri-pencil-line"></i>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('pelayanan.delete'))
                            <form action="{{ route('admin.jadwal.destroy', $pelayanan) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="lux-btn lux-btn-outline lux-btn-sm"
                                    style="border-color: var(--danger); color: var(--danger);">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; color: var(--text-muted);">
                    <i class="ri-calendar-off-line" style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                    <p style="font-size: 1.1rem;">Belum ada jadwal pelayanan</p>
                    @if (auth()->user()->hasPermission('pelayanan.create'))
                        <a href="{{ route('admin.jadwal.create') }}" class="lux-btn lux-btn-primary"
                            style="margin-top: 1rem;">
                            <i class="ri-add-line"></i> Buat Jadwal Pertama
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($pelayanans->hasPages())
            <div style="margin-top: 2rem; display: flex; justify-content: center;">
                {{ $pelayanans->links('pagination::simple-bootstrap-4') }}
            </div>
        @endif
    </div>
@endsection
