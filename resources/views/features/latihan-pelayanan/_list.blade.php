@if ($latihans->isEmpty())
    <div class="col-12 text-center py-5">
        <i class="ri-calendar-line" style="font-size: 4rem; opacity: 0.3;"></i>
        <p class="mt-3 text-muted">Belum ada jadwal latihan pelayanan</p>
    </div>
@else
    @foreach ($latihans as $latihan)
        <div class="lux-card h-100" style="border-left: 6px solid {{ $latihan->kategori->warna ?? '#4f46e5' }};">
            
            <!-- Header Card -->
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="lux-badge" style="background: {{ $latihan->kategori->warna ?? '#4f46e5' }}; color: white;">
                        {{ $latihan->kategori->nama_kategori ?? 'Umum' }}
                    </span>
                </div>
                <span class="badge {{ $latihan->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                    {{ $latihan->status === 'active' ? 'Aktif' : 'Dibatalkan' }}
                </span>
            </div>

            <!-- Nama Latihan -->
            <h5 class="fw-bold mb-2">{{ $latihan->nama_latihan }}</h5>

            <!-- Tanggal & Waktu -->
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="ri-calendar-line"></i>
                <span>{{ $latihan->tanggal->format('d F Y') }}</span>
                <span class="mx-2">•</span>
                <i class="ri-time-line"></i>
                <span>{{ $latihan->waktu }}</span>
            </div>

            <!-- Lokasi -->
            @if ($latihan->lokasi)
                <p class="text-muted small mb-3">
                    <i class="ri-map-pin-line"></i> {{ $latihan->lokasi }}
                </p>
            @endif

            <!-- Jumlah Peserta -->
            <div class="d-flex align-items-center gap-2 mb-4">
                <i class="ri-team-line text-primary"></i>
                <strong>{{ $latihan->details->count() }}</strong>
                <span class="text-muted">Peserta</span>
            </div>

            <!-- Keterangan -->
            @if ($latihan->keterangan)
                <p class="text-muted small mb-4" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    {{ $latihan->keterangan }}
                </p>
            @endif

            <!-- Action Buttons -->
            <div class="d-flex gap-2 mt-auto">
                <button type="button" 
                        class="lux-btn lux-btn-outline lux-btn-sm flex-fill btn-edit-latihan"
                        data-id="{{ $latihan->id }}">
                    <i class="ri-pencil-line"></i> Edit
                </button>

                <button type="button" 
                        class="lux-btn lux-btn-outline lux-btn-sm flex-fill btn-delete-latihan"
                        data-id="{{ $latihan->id }}"
                        data-nama="{{ $latihan->nama_latihan }}">
                    <i class="ri-delete-bin-line"></i> Hapus
                </button>
            </div>
        </div>
    @endforeach
@endif