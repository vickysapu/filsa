@if ($pelayanans->isEmpty())
    <div class="col-12 text-center py-5">
        <i class="ri-calendar-off-line display-1 opacity-25 mb-4" style="color: var(--zaitun-young, #A9C77D);"></i>
        <h4 class="fw-semibold mb-3">Belum ada jadwal pelayanan</h4>
        <p class="text-muted-custom mb-4">Mulai dengan membuat jadwal baru untuk kegiatan pelayanan gereja</p>
        <button type="button" class="btn btn-custom btn-tambah-jadwal">
            <i class="ri-calendar-add-line me-1"></i> Buat Jadwal Pertama
        </button>
    </div>
@else
    @foreach ($pelayanans as $pelayanan)
        <div class="col-md-6 col-lg-4">
            <div class="card card-custom h-100 pelayanan-card" style="border-top: 4px solid var(--zaitun-primary, #718F50);">
                <div class="position-absolute top-0 end-0 p-3 z-1">
                    @php
                        $statusConfig = [
                            'draft'     => ['bg' => 'rgba(229, 216, 155, 0.35)', 'color' => '#7D651E', 'border' => 'rgba(229, 216, 155, 0.6)', 'label' => 'Draft'],
                            'active'    => ['bg' => 'rgba(169, 199, 125, 0.25)', 'color' => '#3F5035', 'border' => 'rgba(113, 143, 80, 0.4)',  'label' => 'Aktif'],
                            'completed' => ['bg' => 'rgba(63, 80, 53, 0.15)',    'color' => '#3F5035', 'border' => 'rgba(63, 80, 53, 0.3)',    'label' => 'Selesai'],
                            'cancelled' => ['bg' => 'rgba(125, 72, 86, 0.15)',   'color' => '#7D4856', 'border' => 'rgba(125, 72, 86, 0.3)',   'label' => 'Dibatalkan'],
                        ];
                        $cfg = $statusConfig[$pelayanan->status] ?? [
                            'bg'     => 'rgba(169, 199, 125, 0.18)',
                            'color'  => '#3F5035',
                            'border' => 'rgba(169, 199, 125, 0.35)',
                            'label'  => ucfirst($pelayanan->status),
                        ];
                    @endphp
                    <span class="badge" style="background: {{ $cfg['bg'] }}; color: {{ $cfg['color'] }}; border: 1px solid {{ $cfg['border'] }}; font-weight: 600; font-size: 0.75rem; padding: 0.4em 0.8em; border-radius: 6px;">
                        {{ $cfg['label'] }}
                    </span>
                </div>
                <div class="card-body pt-4">
                    <h5 class="card-title fw-semibold mb-3" style="font-family: 'Plus Jakarta Sans', sans-serif; color: var(--text-primary);">{{ $pelayanan->nama_pelayanan }}</h5>
                    <div class="d-flex flex-column gap-2 mb-3 small text-muted-custom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-calendar-line" style="color: var(--zaitun-primary, #718F50);"></i>
                            <span>{{ $pelayanan->tanggal->format('d F Y') }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-time-line" style="color: var(--zaitun-primary, #718F50);"></i>
                            <span>{{ \Carbon\Carbon::parse($pelayanan->waktu)->format('H:i') }} WIB</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-map-pin-line" style="color: #8F7226;"></i>
                            <span>{{ $pelayanan->lokasi }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-team-line" style="color: var(--zaitun-primary, #718F50);"></i>
                            <span>{{ $pelayanan->details->where('status', 'active')->count() }} Petugas Aktif</span>
                        </div>
                    </div>
                    @if ($pelayanan->details->where('status', 'active')->isNotEmpty())
                        <div class="mb-3 p-3" style="background: var(--bg-body); border-radius: var(--radius); border: 1px solid var(--border-color-subtle, var(--border-color));">
                            <p class="small text-muted-custom mb-2" style="font-weight: 600; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em;">Petugas:</p>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($pelayanan->details->where('status', 'active')->take(4) as $detail)
                                    <span class="badge" style="background: rgba(169, 199, 125, 0.18); color: var(--zaitun-deep, #3F5035); border: 1px solid rgba(169, 199, 125, 0.35); font-size: 0.78rem; font-weight: 500; padding: 0.35em 0.7em; border-radius: 6px;">
                                        {{ explode(' ', $detail->jemaat->nama_lengkap)[0] ?? 'Jemaat' }}
                                        @if ($detail->role)
                                            <small style="opacity: 0.75;">({{ $detail->role }})</small>
                                        @endif
                                    </span>
                                @endforeach
                                @if ($pelayanan->details->where('status', 'active')->count() > 4)
                                    <span class="badge" style="background: rgba(229, 216, 155, 0.3); color: #7D651E; border: 1px solid rgba(229, 216, 155, 0.5); font-size: 0.78rem; border-radius: 6px;">
                                        +{{ $pelayanan->details->where('status', 'active')->count() - 4 }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                    <div class="d-flex gap-2 mt-auto">
                        <a href="{{ route('admin.jadwal.show', $pelayanan) }}"
                            class="btn btn-sm flex-grow-1"
                            style="background: var(--bg-body); border: 1px solid var(--border-color); color: var(--text-primary); font-weight: 500; border-radius: var(--radius-sm);">
                            <i class="ri-eye-line me-1"></i> Detail
                        </a>
                        @if (in_array($pelayanan->status, ['draft', 'active']) && auth()->user()->hasPermission('pelayanan.edit'))
                            <button type="button" class="btn btn-custom btn-sm btn-edit-jadwal"
                                data-id="{{ $pelayanan->id }}" data-nama="{{ $pelayanan->nama_pelayanan }}"
                                style="border-radius: var(--radius-sm);">
                                <i class="ri-pencil-line"></i>
                            </button>
                        @endif
                        @if (auth()->user()->hasPermission('pelayanan.delete'))
                            <button type="button" class="btn btn-sm btn-delete-jadwal"
                                data-id="{{ $pelayanan->id }}" data-nama="{{ $pelayanan->nama_pelayanan }}"
                                style="background: rgba(125, 72, 86, 0.12); border: 1px solid rgba(125, 72, 86, 0.3); color: var(--zaitun-ripe, #7D4856); border-radius: var(--radius-sm);">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
