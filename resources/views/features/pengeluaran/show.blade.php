@extends('layouts.admin')

@section('title', 'Detail Pengeluaran')

@section('content')
    <div class="lux-container">

        <!-- Header Actions -->
        <div class="mb-5">
            <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap;">
                <a href="{{ route('admin.pengeluaran.index') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                    <i class="ri-arrow-left-line"></i> Kembali
                </a>

                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    @if ($pengeluaran->status === 'pending' && auth()->user()->hasPermission('pengeluaran.edit'))
                        <a href="{{ route('admin.pengeluaran.edit', $pengeluaran) }}" class="lux-btn lux-btn-primary"
                            style="background: var(--danger);">
                            <i class="ri-pencil-line"></i> Edit
                        </a>
                    @endif

                    @if ($pengeluaran->status === 'pending' && auth()->user()->hasPermission('pengeluaran.verify'))
                        <form action="{{ route('admin.pengeluaran.verify', $pengeluaran) }}" method="POST"
                            style="display: inline;">
                            @csrf
                            <button type="submit" class="lux-btn lux-btn-outline"
                                style="border-color: var(--success); color: var(--success);">
                                <i class="ri-checkbox-circle-line"></i> Verifikasi
                            </button>
                        </form>

                        <button onclick="document.getElementById('rejectModal').style.display='block'"
                            class="lux-btn lux-btn-outline" style="border-color: var(--danger); color: var(--danger);">
                            <i class="ri-close-circle-line"></i> Tolak
                        </button>
                    @endif

                    @if ($pengeluaran->status === 'pending' && auth()->user()->hasPermission('pengeluaran.delete'))
                        <form action="{{ route('admin.pengeluaran.destroy', $pengeluaran) }}" method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus pengeluaran ini?')" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="lux-btn lux-btn-outline"
                                style="border-color: var(--danger); color: var(--danger);">
                                <i class="ri-delete-bin-line"></i> Hapus
                            </button>
                        </form>
                    @endif

                    <button onclick="window.print()" class="lux-btn lux-btn-outline">
                        <i class="ri-printer-line"></i> Cetak
                    </button>
                </div>
            </div>

            <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800;">Detail Pengeluaran</h1>
        </div>

        <div class="lux-grid lux-grid-3" style="gap: 1.5rem;">

            <!-- Main Info Card -->
            <div class="lux-card" style="grid-column: span 2;">

                <!-- Transaction Header -->
                <div
                    style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem; 
                        padding-bottom: 1.5rem; border-bottom: 2px solid #f1f5f9;">
                    <div>
                        <p class="lux-label" style="margin-bottom: 0.5rem;">Nomor Transaksi</p>
                        <h2 style="font-size: 1.8rem; font-weight: 700; color: var(--danger); font-family: monospace;">
                            {{ $pengeluaran->nomor_transaksi }}
                        </h2>
                    </div>
                    <div style="text-align: right;">
                        <p class="lux-label" style="margin-bottom: 0.5rem;">Status</p>
                        @if ($pengeluaran->status === 'verified')
                            <span class="lux-badge lux-badge-success" style="font-size: 0.9rem; padding: 6px 16px;">
                                <i class="ri-checkbox-circle-line"></i> Verified
                            </span>
                        @elseif($pengeluaran->status === 'pending')
                            <span class="lux-badge lux-badge-warning" style="font-size: 0.9rem; padding: 6px 16px;">
                                <i class="ri-time-line"></i> Pending
                            </span>
                        @else
                            <span class="lux-badge lux-badge-danger" style="font-size: 0.9rem; padding: 6px 16px;">
                                <i class="ri-close-circle-line"></i> Ditolak
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Amount Display -->
                <div
                    style="background: linear-gradient(135deg, var(--danger), #dc2626); 
                        padding: 2rem; border-radius: var(--radius); margin-bottom: 2rem; 
                        color: white; text-align: center;">
                    <p style="opacity: 0.9; margin-bottom: 0.5rem; font-size: 0.95rem;">Jumlah Pengeluaran</p>
                    <h3 style="font-size: 2.5rem; font-weight: 800; margin: 0;">
                        Rp {{ number_format($pengeluaran->jumlah, 0, ',', '.') }}
                    </h3>
                </div>

                <!-- Transaction Details -->
                <div class="lux-card-header" style="margin-bottom: 1.5rem;">
                    <h3 class="lux-card-title">Detail Transaksi</h3>
                    <i class="ri-receipt-line" style="color: var(--danger);"></i>
                </div>

                <div class="lux-grid lux-grid-2" style="gap: 1.5rem; margin-bottom: 2rem;">
                    <div>
                        <p class="lux-label">Tanggal Transaksi</p>
                        <p style="font-weight: 600; font-size: 0.95rem;">
                            {{ $pengeluaran->tanggal->format('d F Y') }}
                        </p>
                        <p class="text-muted" style="font-size: 0.85rem;">
                            {{ $pengeluaran->tanggal->diffForHumans() }}
                        </p>
                    </div>
                    <div>
                        <p class="lux-label">Kategori</p>
                        <span class="lux-badge" style="background: #fce7f3; color: #9d174d; font-size: 0.9rem;">
                            {{ $pengeluaran->kategori_label }}
                        </span>
                    </div>
                    <div>
                        <p class="lux-label">Keperluan</p>
                        <p style="font-weight: 600; font-size: 0.95rem;">{{ $pengeluaran->keperluan }}</p>
                    </div>
                    <div>
                        <p class="lux-label">Penerima Dana</p>
                        <p style="font-weight: 600; font-size: 0.95rem;">{{ $pengeluaran->penerima }}</p>
                    </div>
                    <div>
                        <p class="lux-label">Dibuat Oleh</p>
                        <p style="font-weight: 600; font-size: 0.95rem;">
                            {{ $pengeluaran->creator->name ?? '-' }}
                        </p>
                        <p class="text-muted" style="font-size: 0.85rem;">
                            {{ $pengeluaran->created_at->format('d M Y, H:i') }}
                        </p>
                    </div>
                </div>

                <!-- Keterangan -->
                @if ($pengeluaran->keterangan)
                    <div style="margin-bottom: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: var(--radius);">
                        <p class="lux-label" style="margin-bottom: 0.75rem;">Keterangan</p>
                        <p style="line-height: 1.8; color: var(--text-main);">{{ nl2br(e($pengeluaran->keterangan)) }}</p>
                    </div>
                @endif

                <!-- Bukti Foto -->
                @if ($pengeluaran->bukti_foto)
                    <div style="margin-bottom: 2rem;">
                        <p class="lux-label" style="margin-bottom: 1rem;">Bukti Transaksi</p>
                        @if (pathinfo($pengeluaran->bukti_foto, PATHINFO_EXTENSION) === 'pdf')
                            <div
                                style="display: flex; align-items: center; gap: 15px; padding: 1.5rem; background: white; 
                            border: 2px solid #e2e8f0; border-radius: var(--radius);">
                                <i class="ri-file-pdf-line" style="font-size: 3rem; color: var(--danger);"></i>
                                <div style="flex: 1;">
                                    <p style="font-weight: 600; font-size: 1rem;">{{ basename($pengeluaran->bukti_foto) }}
                                    </p>
                                    <p class="text-muted" style="font-size: 0.85rem;">PDF Document</p>
                                </div>
                                <a href="{{ asset('storage/' . $pengeluaran->bukti_foto) }}" target="_blank"
                                    class="lux-btn lux-btn-primary" style="background: var(--danger);">
                                    <i class="ri-download-line"></i> Download
                                </a>
                            </div>
                        @else
                            <img src="{{ asset('storage/' . $pengeluaran->bukti_foto) }}" alt="Bukti Transaksi"
                                style="max-width: 100%; border-radius: var(--radius); border: 2px solid #e2e8f0; 
                            box-shadow: var(--shadow-md);">
                            <div style="margin-top: 1rem; text-align: center;">
                                <a href="{{ asset('storage/' . $pengeluaran->bukti_foto) }}" target="_blank"
                                    class="lux-btn lux-btn-outline">
                                    <i class="ri-eye-line"></i> Lihat Full Size
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Verification Info -->
                @if ($pengeluaran->verified_at)
                    <div
                        style="padding: 1.5rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius);">
                        <div style="display: flex; align-items: start; gap: 10px;">
                            <i class="ri-shield-check-line"
                                style="color: var(--success); font-size: 1.5rem; margin-top: 2px;"></i>
                            <div>
                                <p style="font-weight: 700; color: #166534; margin-bottom: 0.5rem;">Terverifikasi</p>
                                <p style="font-size: 0.9rem; color: #166534;">
                                    Diverifikasi oleh <strong>{{ $pengeluaran->verifier->name ?? '-' }}</strong>
                                    pada {{ $pengeluaran->verified_at->format('d F Y, H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar Info -->
            <div>
                <!-- Quick Stats -->
                <div class="lux-card mb-4">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Info Transaksi</h3>
                        <i class="ri-information-line" style="color: var(--accent);"></i>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div>
                            <p class="lux-label" style="margin-bottom: 0.25rem;">ID Transaksi</p>
                            <p style="font-weight: 600; font-family: monospace;">#{{ $pengeluaran->id }}</p>
                        </div>
                        <div>
                            <p class="lux-label" style="margin-bottom: 0.25rem;">Tanggal Dibuat</p>
                            <p style="font-weight: 600;">{{ $pengeluaran->created_at->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="lux-label" style="margin-bottom: 0.25rem;">Terakhir Update</p>
                            <p style="font-weight: 600;">{{ $pengeluaran->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Aksi Cepat</h3>
                        <i class="ri-flashlight-line" style="color: var(--danger);"></i>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <button onclick="window.print()" class="lux-btn lux-btn-outline" style="justify-content: start;">
                            <i class="ri-printer-line"></i> Cetak Transaksi
                        </button>
                        <button onclick="navigator.clipboard.writeText('{{ $pengeluaran->nomor_transaksi }}')"
                            class="lux-btn lux-btn-outline" style="justify-content: start;">
                            <i class="ri-file-copy-line"></i> Copy No. Transaksi
                        </button>
                        <a href="{{ route('admin.pengeluaran.index') }}" class="lux-btn lux-btn-outline"
                            style="justify-content: start;">
                            <i class="ri-arrow-left-line"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="lux-modal-overlay" style="display: none;">
        <div class="lux-modal"
            style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
                                  background: white; border-radius: var(--radius); width: 90%; max-width: 500px;
                                  box-shadow: var(--shadow-lg); z-index: 1001;">
            <form action="{{ route('admin.pengeluaran.reject', $pengeluaran) }}" method="POST">
                @csrf
                <div class="lux-modal-header"
                    style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; 
                                                display: flex; align-items: center; gap: 10px;">
                    <i class="ri-close-circle-line" style="color: var(--danger); font-size: 1.5rem;"></i>
                    <h3 class="lux-modal-title" style="font-weight: 700; color: var(--danger);">Tolak Pengeluaran</h3>
                </div>
                <div class="lux-modal-body" style="padding: 1.5rem;">
                    <p style="margin-bottom: 1rem;">
                        Apakah Anda yakin ingin menolak pengeluaran <strong>{{ $pengeluaran->nomor_transaksi }}</strong>?
                    </p>
                    <div class="lux-form-group">
                        <label class="lux-label">Alasan Penolakan *</label>
                        <textarea name="alasan_reject" class="lux-textarea" rows="3" required
                            placeholder="Jelaskan alasan penolakan ini..."></textarea>
                    </div>
                    <div
                        style="background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius); 
                            padding: 1rem; color: #991b1b; font-size: 0.9rem;">
                        <i class="ri-alert-line"></i> <strong>Perhatian:</strong> Pengeluaran yang ditolak tidak dapat
                        dipulihkan. Pastikan alasan penolakan jelas dan dapat dipertanggungjawabkan.
                    </div>
                </div>
                <div class="lux-modal-footer"
                    style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9; 
                                                display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" class="lux-btn lux-btn-outline"
                        onclick="document.getElementById('rejectModal').style.display='none'">Batal</button>
                    <button type="submit" class="lux-btn lux-btn-primary" style="background: var(--danger);">
                        <i class="ri-close-circle-line"></i> Ya, Tolak
                    </button>
                </div>
            </form>
        </div>
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); 
                z-index: 1000;"
            onclick="document.getElementById('rejectModal').style.display='none'"></div>
    </div>

@endsection

@push('scripts')
    <script>
        // Copy to clipboard notification
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Nomor transaksi berhasil dicopy!');
            });
        }

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('rejectModal').style.display = 'none';
            }
        });

        console.log('💸 Pengeluaran Detail Loaded:', {
            id: {{ $pengeluaran->id }},
            nomor_transaksi: '{{ $pengeluaran->nomor_transaksi }}',
            jumlah: {{ $pengeluaran->jumlah }},
            status: '{{ $pengeluaran->status }}'
        });
    </script>
@endpush
