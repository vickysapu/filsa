{{-- features/pelayanan/_list.blade.php --}}
@foreach ($pelayanans as $jadwal)
    <div class="col-md-6 col-lg-4">
        <div class="jadwal-card">
            <div class="jadwal-card-header">
                <h6 class="jadwal-card-title">{{ $jadwal->nama_pelayanan }}</h6>
                <span class="jadwal-card-status {{ $jadwal->status }}">
                    {{ ucfirst($jadwal->status) }}
                </span>
            </div>
            <div class="jadwal-card-body">
                <div class="jadwal-meta">
                    <div class="jadwal-meta-item">
                        <i class="ri-calendar-line"></i>
                        <span>{{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d M Y') }}</span>
                        <small class="text-muted-custom">• {{ $jadwal->waktu }}</small>
                    </div>
                </div>
                <div class="jadwal-location">
                    <i class="ri-map-pin-line"></i>
                    <span>{{ $jadwal->lokasi }}</span>
                </div>
                @if ($jadwal->keterangan)
                    <p class="jadwal-keterangan">{{ Str::limit($jadwal->keterangan, 80) }}</p>
                @endif
                @if ($jadwal->petugas->isNotEmpty())
                    <div class="jadwal-petugas">
                        @foreach ($jadwal->petugas->take(3) as $petugas)
                            <span class="jadwal-petugas-badge">{{ $petugas->nama_lengkap }}</span>
                        @endforeach
                        @if ($jadwal->petugas->count() > 3)
                            <span class="jadwal-petugas-badge">+{{ $jadwal->petugas->count() - 3 }} lainnya</span>
                        @endif
                    </div>
                @endif
            </div>
            <div class="jadwal-footer">
                <button type="button" class="btn btn-outline-custom btn-sm btn-edit-jadwal"
                    data-id="{{ $jadwal->id }}">
                    <i class="ri-pencil-line"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm btn-delete-jadwal"
                    data-id="{{ $jadwal->id }}" data-nama="{{ $jadwal->nama_pelayanan }}">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </div>
    </div>
@endforeach

@forelse($pelayanans)
    {{-- Jika kosong --}}
    <div class="col-12">
        <div class="text-center py-5">
            <div class="empty-state-icon mb-3">
                <i class="ri-calendar-line"></i>
            </div>
            <h6 class="fw-semibold mb-2">Belum ada jadwal pelayanan</h6>
            <p class="text-muted-custom small mb-3">Buat jadwal pertama Anda untuk mulai mengelola pelayanan</p>
            <button type="button" class="btn btn-custom btn-tambah-jadwal">
                <i class="ri-calendar-add-line me-1"></i> Buat Jadwal Pertama
            </button>
        </div>
    </div>
@endforelse
