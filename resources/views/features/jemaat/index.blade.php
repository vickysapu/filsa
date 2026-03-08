@extends('layouts.admin')

@section('title', 'Data Jemaat')

@section('content')
    <div class="lux-container">

        <!-- Header -->
        <div class="flex-between mb-5">
            <div>
                <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Data Jemaat</h1>
                <p class="text-muted">Kelola data anggota jemaat Gereja Filadelfia</p>
            </div>
            <a href="{{ route('admin.jemaat.create') }}" class="lux-btn lux-btn-primary">
                <i class="ri-user-add-line"></i> Tambah Jemaat
            </a>
        </div>

        <!-- Alert Success -->
        @if (session('success'))
            <div class="lux-card mb-4"
                style="border-left: 4px solid var(--success); background: #f0fdf4; display: flex; align-items: center; gap: 10px;">
                <i class="ri-checkbox-circle-line" style="color: var(--success); font-size: 1.5rem;"></i>
                <span style="color: #166534; font-weight: 600;">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter & Search Card -->
        <div class="lux-card mb-4">
            <form action="{{ route('admin.jemaat.index') }}" method="GET"
                style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">

                <div>
                    <label class="lux-label">Cari Jemaat</label>
                    <div style="position: relative;">
                        <i class="ri-search-line"
                            style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="text" name="search" class="lux-input" value="{{ request('search') }}"
                            placeholder="Nama, NIK, atau Email..." style="padding-left: 36px;">
                    </div>
                </div>

                <div>
                    <label class="lux-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="lux-select">
                        <option value="">Semua</option>
                        <option value="1" {{ request('jenis_kelamin') == '1' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="0" {{ request('jenis_kelamin') == '0' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="lux-label">Status Pernikahan</label>
                    <select name="status_pernikahan" class="lux-select">
                        <option value="">Semua</option>
                        <option value="belum menikah"
                            {{ request('status_pernikahan') == 'belum menikah' ? 'selected' : '' }}>Belum Menikah</option>
                        <option value="menikah" {{ request('status_pernikahan') == 'menikah' ? 'selected' : '' }}>Menikah
                        </option>
                        <option value="janda" {{ request('status_pernikahan') == 'janda' ? 'selected' : '' }}>Janda
                        </option>
                        <option value="duda" {{ request('status_pernikahan') == 'duda' ? 'selected' : '' }}>Duda</option>
                    </select>
                </div>

                <div>
                    <label class="lux-label">Status Jemaat</label>
                    <select name="status_jemaat" class="lux-select">
                        <option value="">Semua</option>
                        <option value="1" {{ request('status_jemaat') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('status_jemaat') === '0' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>

                <button type="submit" class="lux-btn lux-btn-primary">
                    <i class="ri-filter-line"></i> Filter
                </button>
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
                                Jemaat</th>
                            <th
                                style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Kontak</th>
                            <th
                                style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Status</th>
                            <th
                                style="padding: 15px; text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Bergabung</th>
                            <th
                                style="padding: 15px; text-align: center; color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jemaats as $jemaat)
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: 0.2s;"
                                onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                <td style="padding: 15px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        @if ($jemaat->foto)
                                            <img src="{{ asset('storage/' . $jemaat->foto) }}"
                                                alt="{{ $jemaat->nama_lengkap }}"
                                                style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid white; box-shadow: var(--shadow-sm);">
                                        @else
                                            <div
                                                style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); 
                                            display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.2rem;">
                                                {{ substr($jemaat->nama_lengkap, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-main);">
                                                {{ $jemaat->nama_lengkap }}</div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                NIK: {{ number_format($jemaat->NIK, 0, ',', '.') }} •
                                                {{ $jemaat->jenis_kelamin == 1 ? 'L' : 'P' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 15px;">
                                    <div style="font-size: 0.9rem; font-weight: 500;">{{ $jemaat->no_hp }}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $jemaat->email ?? '-' }}
                                    </div>
                                </td>
                                <td style="padding: 15px;">
                                    <div style="margin-bottom: 5px;">
                                        @if ($jemaat->status_jemaat)
                                            <span class="lux-badge lux-badge-success">Aktif</span>
                                        @else
                                            <span class="lux-badge lux-badge-danger">Non-Aktif</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="lux-badge"
                                            style="background: #f1f5f9; color: var(--text-muted); font-size: 0.7rem;">
                                            {{ ucfirst($jemaat->status_pernikahan) }}
                                        </span>
                                    </div>
                                </td>
                                <td style="padding: 15px;">
                                    <div style="font-size: 0.9rem;">
                                        {{ \Carbon\Carbon::parse($jemaat->tanggal_bergabung)->format('d M Y') }}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                                        {{ \Carbon\Carbon::parse($jemaat->tanggal_bergabung)->diffForHumans() }}
                                    </div>
                                </td>
                                <td style="padding: 15px;">
                                    <div style="display: flex; gap: 8px; justify-content: center;">
                                        <a href="{{ route('admin.jemaat.show', $jemaat) }}"
                                            class="lux-btn lux-btn-outline lux-btn-sm" title="Detail">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        <a href="{{ route('admin.jemaat.edit', $jemaat) }}"
                                            class="lux-btn lux-btn-primary lux-btn-sm" title="Edit">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        <form action="{{ route('admin.jemaat.destroy', $jemaat) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus data jemaat ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="lux-btn lux-btn-outline lux-btn-sm"
                                                style="border-color: var(--danger); color: var(--danger);" title="Hapus">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                                    <i class="ri-inbox-line" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p style="margin-top: 1rem;">Belum ada data jemaat</p>
                                    <a href="{{ route('admin.jemaat.create') }}"
                                        class="lux-btn lux-btn-primary lux-btn-sm" style="margin-top: 1rem;">
                                        <i class="ri-user-add-line"></i> Tambah Jemaat Pertama
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($jemaats->hasPages())
                <div
                    style="padding: 15px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                    <span class="text-muted" style="font-size: 0.85rem;">Menampilkan {{ $jemaats->firstItem() }} -
                        {{ $jemaats->lastItem() }} dari {{ $jemaats->total() }} data</span>
                    <div>
                        {{ $jemaats->links('pagination::simple-bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
