@if ($pelayanans->isEmpty())
    <div class="col-12 text-center py-5">
        <i class="ri-calendar-off-line display-1 text-muted-custom opacity-25 mb-4"></i>
        <h4 class="fw-semibold mb-3">Belum ada jadwal pelayanan</h4>
        <p class="text-muted-custom mb-4">Mulai dengan membuat jadwal baru untuk kegiatan pelayanan gereja</p>
        <button type="button" class="btn btn-custom btn-tambah-jadwal">
            <i class="ri-calendar-add-line me-1"></i> Buat Jadwal Pertama
        </button>
    </div>
@else
    @foreach ($pelayanans as $pelayanan)
        <div class="col-md-6 col-lg-4">
            <div class="card card-custom h-100" style="border-top: 5px solid var(--border-color);">
                <div class="position-absolute top-0 end-0 p-3 z-1">
                    @php
                        $statusConfig = [
                            'draft' => ['class' => 'bg-warning text-dark', 'label' => 'Draft'],
                            'active' => ['class' => 'bg-success', 'label' => 'Aktif'],
                            'completed' => ['class' => 'bg-primary', 'label' => 'Selesai'],
                            'cancelled' => ['class' => 'bg-danger', 'label' => 'Dibatalkan'],
                        ];
                        $cfg = $statusConfig[$pelayanan->status] ?? [
                            'class' => 'bg-secondary',
                            'label' => ucfirst($pelayanan->status),
                        ];
                    @endphp
                    <span class="badge {{ $cfg['class'] }}">{{ $cfg['label'] }}</span>
                </div>
                <div class="card-body pt-4">
                    <h5 class="card-title fw-semibold mb-3">{{ $pelayanan->nama_pelayanan }}</h5>
                    <div class="d-flex flex-column gap-2 mb-3 small text-muted-custom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-calendar-line text-primary"></i>
                            <span>{{ $pelayanan->tanggal->format('d F Y') }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-time-line text-primary"></i>
                            <span>{{ \Carbon\Carbon::parse($pelayanan->waktu)->format('H:i') }} WIB</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-map-pin-line text-primary"></i>
                            <span>{{ $pelayanan->lokasi }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-team-line text-primary"></i>
                            <span>{{ $pelayanan->details->where('status', 'active')->count() }} Petugas Aktif</span>
                        </div>
                    </div>
                    @if ($pelayanan->details->where('status', 'active')->isNotEmpty())
                        <div class="mb-3 p-3" style="background: var(--bg-body); border-radius: var(--radius);">
                            <p class="small text-muted-custom mb-2">Petugas:</p>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($pelayanan->details->where('status', 'active')->take(4) as $detail)
                                    <span class="badge bg-light text-dark border" style="font-size: 0.8rem;">
                                        {{ explode(' ', $detail->jemaat->nama_lengkap)[0] ?? 'Jemaat' }}
                                        @if ($detail->role)
                                            <small class="text-muted-custom">({{ $detail->role }})</small>
                                        @endif
                                    </span>
                                @endforeach
                                @if ($pelayanan->details->where('status', 'active')->count() > 4)
                                    <span class="badge bg-secondary" style="font-size: 0.8rem;">
                                        +{{ $pelayanan->details->where('status', 'active')->count() - 4 }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                    <div class="d-flex gap-2 mt-auto">
                        <a href="{{ route('admin.jadwal.show', $pelayanan) }}"
                            class="btn btn-outline-secondary btn-sm flex-grow-1">
                            <i class="ri-eye-line me-1"></i> Detail
                        </a>
                        @if (in_array($pelayanan->status, ['draft', 'active']) && auth()->user()->hasPermission('pelayanan.edit'))
                            <button type="button" class="btn btn-custom btn-sm btn-edit-jadwal"
                                data-id="{{ $pelayanan->id }}" data-nama="{{ $pelayanan->nama_pelayanan }}">
                                <i class="ri-pencil-line"></i>
                            </button>
                        @endif
                        @if (auth()->user()->hasPermission('pelayanan.delete'))
                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete-jadwal"
                                data-id="{{ $pelayanan->id }}" data-nama="{{ $pelayanan->nama_pelayanan }}">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
