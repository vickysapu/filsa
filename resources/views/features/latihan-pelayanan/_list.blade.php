@if ($latihans->isEmpty())
    <div class="col-12 text-center py-5">
        <i class="ri-calendar-off-line display-1 text-muted-custom opacity-25 mb-4"></i>
        <h4 class="fw-semibold mb-3">Belum ada jadwal latihan</h4>
        <p class="text-muted-custom mb-4">Mulai dengan membuat jadwal latihan untuk pelayanan yang sudah terdaftar</p>
        <button type="button" class="btn btn-custom btn-tambah-latihan">
            <i class="ri-calendar-add-line me-1"></i> Buat Jadwal Latihan Pertama
        </button>
    </div>
@else
    @foreach ($latihans as $latihan)
        <div class="col-md-6 col-lg-4">
            <div class="card card-custom h-100" style="border-top: 5px solid var(--border-color);">
                <div class="position-absolute top-0 end-0 p-3 z-1">
                    @php
                        $statusConfig = [
                            'active' => ['class' => 'bg-success', 'label' => 'Aktif'],
                            'cancelled' => ['class' => 'bg-danger', 'label' => 'Dibatalkan'],
                        ];
                        $cfg = $statusConfig[$latihan->status] ?? [
                            'class' => 'bg-secondary',
                            'label' => ucfirst($latihan->status),
                        ];
                    @endphp
                    <span class="badge {{ $cfg['class'] }}">{{ $cfg['label'] }}</span>
                </div>
                <div class="card-body pt-4">
                    <h5 class="card-title fw-semibold mb-3">{{ $latihan->nama_latihan }}</h5>
                    <div class="d-flex flex-column gap-2 mb-3 small text-muted-custom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-calendar-line text-primary"></i>
                            <span>{{ $latihan->tanggal->format('d F Y') }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-time-line text-primary"></i>
                            <span>{{ \Carbon\Carbon::parse($latihan->waktu)->format('H:i') }} WIB</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-map-pin-line text-primary"></i>
                            <span>{{ $latihan->lokasi }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-building-line text-primary"></i>
                            <span>{{ $latihan->pelayanan->nama_pelayanan ?? '-' }}</span>
                        </div>
                    </div>
                    @if ($latihan->keterangan)
                        <div class="mb-3 p-3" style="background: var(--bg-body); border-radius: var(--radius);">
                            <p class="small text-muted-custom mb-2">Catatan:</p>
                            <p class="small mb-0">{{ Str::limit($latihan->keterangan, 100) }}</p>
                        </div>
                    @endif
                    <div class="d-flex gap-2 mt-auto">
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-edit-latihan flex-grow-1"
                            data-id="{{ $latihan->id }}">
                            <i class="ri-pencil-line me-1"></i> Edit
                        </button>
                        @if (auth()->user()->hasPermission('pelayanan.delete'))
                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete-latihan"
                                data-id="{{ $latihan->id }}" data-nama="{{ $latihan->nama_latihan }}">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
