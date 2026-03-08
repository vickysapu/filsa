@extends('layouts.admin')

@section('title', 'Detail Pelayanan')

@section('content')
    <div class="lux-container">

        <!-- Header Actions -->
        <div class="mb-5">
            <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap;">
                <a href="{{ route('admin.jadwal.index') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                    <i class="ri-arrow-left-line"></i> Kembali
                </a>

                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    @if (
                        $pelayanan->status !== 'completed' &&
                            $pelayanan->status !== 'cancelled' &&
                            auth()->user()->hasPermission('pelayanan.edit'))
                        <a href="{{ route('admin.jadwal.edit', $pelayanan) }}" class="lux-btn lux-btn-primary">
                            <i class="ri-pencil-line"></i> Edit
                        </a>
                    @endif

                    <button onclick="window.print()" class="lux-btn lux-btn-outline">
                        <i class="ri-printer-line"></i> Cetak
                    </button>
                </div>
            </div>

            <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Detail Pelayanan</h1>
        </div>

        <div class="lux-grid lux-grid-3" style="gap: 1.5rem;">

            <!-- Main Info Card -->
            <div class="lux-card" style="grid-column: span 2;">

                <!-- Header with Status -->
                <div
                    style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem; 
                        padding-bottom: 1.5rem; border-bottom: 2px solid #f1f5f9;">
                    <div>
                        <span class="lux-badge"
                            style="background: {{ $pelayanan->kategori->warna }}20; color: {{ $pelayanan->kategori->warna }}; margin-bottom: 0.75rem; display: inline-block;">
                            {{ $pelayanan->kategori->nama_kategori }}
                        </span>
                        <h2 style="font-size: 1.8rem; font-weight: 700; color: var(--text-main); margin: 0;">
                            {{ $pelayanan->nama_pelayanan }}
                        </h2>
                        <p class="text-muted" style="margin: 0.5rem 0 0 0; font-family: monospace;">
                            {{ $pelayanan->nomor_jadwal }}
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <span class="lux-badge {{ $pelayanan->status_label['color'] }}"
                            style="font-size: 0.9rem; padding: 6px 16px;">
                            {{ $pelayanan->status_label['label'] }}
                        </span>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="lux-grid lux-grid-2" style="gap: 1.5rem; margin-bottom: 2rem;">
                    <div>
                        <p class="lux-label">Tanggal & Waktu</p>
                        <p style="font-weight: 600; font-size: 1.1rem;">
                            <i class="ri-calendar-line" style="color: var(--primary);"></i>
                            {{ $pelayanan->tanggal->format('d F Y') }}
                            <br>
                            <i class="ri-time-line" style="color: var(--primary);"></i>
                            {{ \Carbon\Carbon::parse($pelayanan->waktu)->format('H:i') }} WIB
                        </p>
                    </div>
                    <div>
                        <p class="lux-label">Lokasi</p>
                        <p style="font-weight: 600; font-size: 1.1rem;">
                            <i class="ri-map-pin-line" style="color: var(--primary);"></i>
                            {{ $pelayanan->lokasi }}
                        </p>
                    </div>
                    <div>
                        <p class="lux-label">Dibuat Oleh</p>
                        <p style="font-weight: 600;">{{ $pelayanan->creator->name ?? '-' }}</p>
                        <p class="text-muted" style="font-size: 0.85rem;">
                            {{ $pelayanan->created_at->format('d M Y, H:i') }}
                        </p>
                    </div>
                    <div>
                        <p class="lux-label">Total Petugas</p>
                        <p style="font-weight: 600;">
                            <span
                                class="lux-badge lux-badge-success">{{ $pelayanan->details->where('status', 'active')->count() }}
                                Aktif</span>
                            <span class="lux-badge lux-badge-danger"
                                style="margin-left: 0.5rem;">{{ $pelayanan->details->where('status', 'off')->count() }}
                                Off</span>
                        </p>
                    </div>
                </div>

                <!-- Keterangan -->
                @if ($pelayanan->keterangan)
                    <div style="margin-bottom: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: var(--radius);">
                        <p class="lux-label" style="margin-bottom: 0.75rem;">Keterangan</p>
                        <p style="line-height: 1.8; color: var(--text-main);">{{ nl2br(e($pelayanan->keterangan)) }}</p>
                    </div>
                @endif

                <!-- Petugas Section -->
                <div class="lux-card-header" style="margin-bottom: 1.5rem;">
                    <h3 class="lux-card-title">Daftar Petugas</h3>
                    <i class="ri-team-line" style="color: var(--accent);"></i>
                </div>

                <!-- Active Petugas -->
                <div style="margin-bottom: 2rem;">
                    <div class="flex-between mb-3">
                        <h4 style="font-size: 1rem; font-weight: 700; color: var(--success);">
                            <i class="ri-checkbox-circle-line"></i> Petugas Aktif
                            ({{ $pelayanan->details->where('status', 'active')->count() }})
                        </h4>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
                        @forelse($pelayanan->details->where('status', 'active') as $detail)
                            <div
                                style="display: flex; align-items: center; gap: 12px; padding: 1rem; background: #f0fdf4; 
                                border: 1px solid #bbf7d0; border-radius: var(--radius);">
                                <div
                                    style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); 
                                    display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                                    {{ substr($detail->jemaat->nama_lengkap, 0, 1) }}
                                </div>
                                <div style="flex: 1;">
                                    <p style="font-weight: 600; color: var(--text-main);">
                                        {{ $detail->jemaat->nama_lengkap }}</p>
                                    @if ($detail->role)
                                        <span class="lux-badge"
                                            style="background: white; border: 1px solid #e2e8f0; font-size: 0.75rem;">
                                            {{ $detail->role }}
                                        </span>
                                    @endif
                                </div>
                                @if (auth()->user()->hasPermission('pelayanan.edit'))
                                    <button
                                        onclick="openOffModal({{ $detail->id }}, '{{ $detail->jemaat->nama_lengkap }}')"
                                        class="lux-btn lux-btn-outline lux-btn-sm"
                                        style="border-color: var(--warning); color: var(--warning);" title="Set OFF">
                                        <i class="ri-pause-circle-line"></i>
                                    </button>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted" style="padding: 1rem; background: #f8fafc; border-radius: var(--radius);">
                                Tidak ada petugas aktif</p>
                        @endforelse
                    </div>
                </div>

                <!-- Off Petugas -->
                @if ($pelayanan->details->where('status', 'off')->count() > 0)
                    <div>
                        <div class="flex-between mb-3">
                            <h4 style="font-size: 1rem; font-weight: 700; color: var(--danger);">
                                <i class="ri-pause-circle-line"></i> Petugas OFF
                                ({{ $pelayanan->details->where('status', 'off')->count() }})
                            </h4>
                        </div>

                        <div
                            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
                            @foreach ($pelayanan->details->where('status', 'off') as $detail)
                                <div
                                    style="display: flex; align-items: center; gap: 12px; padding: 1rem; background: #fef2f2; 
                                border: 1px solid #fecaca; border-radius: var(--radius); opacity: 0.8;">
                                    <div
                                        style="width: 45px; height: 45px; border-radius: 50%; background: #e2e8f0; 
                                    display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-weight: 700;">
                                        {{ substr($detail->jemaat->nama_lengkap, 0, 1) }}
                                    </div>
                                    <div style="flex: 1;">
                                        <p
                                            style="font-weight: 600; color: var(--text-main); text-decoration: line-through;">
                                            {{ $detail->jemaat->nama_lengkap }}</p>
                                        @if ($detail->alasan_off)
                                            <p class="text-muted" style="font-size: 0.8rem; margin-top: 0.25rem;">
                                                <i class="ri-information-line"></i> {{ $detail->alasan_off }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Quick Stats -->
                <div class="lux-card mb-4">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Info Jadwal</h3>
                        <i class="ri-information-line" style="color: var(--accent);"></i>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div>
                            <p class="lux-label" style="margin-bottom: 0.25rem;">ID Jadwal</p>
                            <p style="font-weight: 600; font-family: monospace;">#{{ $pelayanan->id }}</p>
                        </div>
                        <div>
                            <p class="lux-label" style="margin-bottom: 0.25rem;">Status</p>
                            <span
                                class="lux-badge {{ $pelayanan->status_label['color'] }}">{{ $pelayanan->status_label['label'] }}</span>
                        </div>
                        <div>
                            <p class="lux-label" style="margin-bottom: 0.25rem;">Dibuat</p>
                            <p style="font-weight: 600;">{{ $pelayanan->created_at->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="lux-label" style="margin-bottom: 0.25rem;">Terakhir Update</p>
                            <p style="font-weight: 600;">{{ $pelayanan->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Aksi Cepat</h3>
                        <i class="ri-flashlight-line" style="color: var(--primary);"></i>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <button onclick="window.print()" class="lux-btn lux-btn-outline" style="justify-content: start;">
                            <i class="ri-printer-line"></i> Cetak Jadwal
                        </button>
                        <button onclick="navigator.clipboard.writeText('{{ $pelayanan->nomor_jadwal }}')"
                            class="lux-btn lux-btn-outline" style="justify-content: start;">
                            <i class="ri-file-copy-line"></i> Copy No. Jadwal
                        </button>
                        <a href="{{ route('admin.jadwal.index') }}" class="lux-btn lux-btn-outline"
                            style="justify-content: start;">
                            <i class="ri-arrow-left-line"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Set OFF -->
    <div id="offModal" class="lux-modal-overlay" style="display: none;">
        <div class="lux-modal"
            style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
                                  background: white; border-radius: var(--radius); width: 90%; max-width: 500px;
                                  box-shadow: var(--shadow-lg); z-index: 1001;">
            <form id="offForm" method="POST">
                @csrf
                <div class="lux-modal-header"
                    style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; 
                                                display: flex; align-items: center; gap: 10px;">
                    <i class="ri-pause-circle-line" style="color: var(--warning); font-size: 1.5rem;"></i>
                    <h3 class="lux-modal-title" style="font-weight: 700; color: var(--warning);">Set Petugas OFF</h3>
                </div>
                <div class="lux-modal-body" style="padding: 1.5rem;">
                    <p style="margin-bottom: 1rem;">
                        Set <strong id="offJemaatName"></strong> sebagai OFF dari pelayanan ini?
                    </p>
                    <div class="lux-form-group">
                        <label class="lux-label">Alasan OFF *</label>
                        <textarea name="alasan_off" class="lux-textarea" rows="3" required
                            placeholder="Jelaskan alasan petugas ini OFF..."></textarea>
                    </div>
                    <div
                        style="background: #fef9c3; border: 1px solid #fde047; border-radius: var(--radius); 
                            padding: 1rem; color: #854d0e; font-size: 0.9rem;">
                        <i class="ri-alert-line"></i> <strong>Perhatian:</strong> Petugas yang di-set OFF tidak akan
                        muncul di daftar petugas aktif, namun tetap tercatat dalam riwayat.
                    </div>
                </div>
                <div class="lux-modal-footer"
                    style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9; 
                                                display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" class="lux-btn lux-btn-outline"
                        onclick="document.getElementById('offModal').style.display='none'">Batal</button>
                    <button type="submit" class="lux-btn lux-btn-primary" style="background: var(--warning);">
                        <i class="ri-pause-circle-line"></i> Ya, Set OFF
                    </button>
                </div>
            </form>
        </div>
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); 
                z-index: 1000;"
            onclick="document.getElementById('offModal').style.display='none'"></div>
    </div>

@endsection

@push('scripts')
    <script>
        function openOffModal(detailId, jemaatName) {
            document.getElementById('offJemaatName').textContent = jemaatName;
            document.getElementById('offForm').action = '/admin/pelayanan-detail/' + detailId + '/off';
            document.getElementById('offModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        // Close modal with ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('offModal').style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    </script>
@endpush
