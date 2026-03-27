<!-- resources/views/admin/jadwal/_list.blade.php -->

@if ($pelayanans->isEmpty())
    <div style="grid-column: 1 / -1; text-align: center; padding: 5rem 1rem; color: var(--text-muted);">
        <i class="ri-calendar-off-line" style="font-size: 5rem; opacity: 0.4; margin-bottom: 1.5rem;"></i>
        <h4 style="font-weight: 600; margin-bottom: 1rem;">Belum ada jadwal pelayanan</h4>
        <p class="text-muted mb-4">Mulai dengan membuat jadwal baru untuk kegiatan pelayanan gereja</p>
        <button type="button" class="lux-btn lux-btn-primary btn-tambah-jadwal">
            <i class="ri-calendar-add-line"></i> Buat Jadwal Pertama
        </button>
    </div>
@else
    @foreach ($pelayanans as $pelayanan)
        <div class="lux-card" style="border-top: 5px solid #6b7280; position: relative; overflow: hidden;">

            <!-- Badge Status (pojok kanan atas) -->
            <div style="position: absolute; top: 1rem; right: 1rem; z-index: 2;">
                @php
                    $statusConfig = [
                        'draft'     => ['class' => 'lux-badge-warning',   'label' => 'Draft'],
                        'active'    => ['class' => 'lux-badge-success',   'label' => 'Aktif'],
                        'completed' => ['class' => 'lux-badge-info',      'label' => 'Selesai'],
                        'cancelled' => ['class' => 'lux-badge-danger',    'label' => 'Dibatalkan'],
                    ];
                    $cfg = $statusConfig[$pelayanan->status] ?? ['class' => 'lux-badge-secondary', 'label' => ucfirst($pelayanan->status)];
                @endphp
                <span class="lux-badge {{ $cfg['class'] }}">
                    {{ $cfg['label'] }}
                </span>
            </div>

            <!-- Judul Pelayanan -->
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; line-height: 1.4; color: var(--text-main);">
                {{ $pelayanan->nama_pelayanan }}
            </h3>

            <!-- Informasi Utama -->
            <div style="display: grid; gap: 0.9rem; margin-bottom: 1.5rem; font-size: 0.95rem; color: var(--text-muted);">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <i class="ri-calendar-line" style="color: var(--primary); font-size: 1.1rem;"></i>
                    <span>{{ $pelayanan->tanggal->format('d F Y') }}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <i class="ri-time-line" style="color: var(--primary); font-size: 1.1rem;"></i>
                    <span>{{ \Carbon\Carbon::parse($pelayanan->waktu)->format('H:i') }} WIB</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <i class="ri-map-pin-line" style="color: var(--primary); font-size: 1.1rem;"></i>
                    <span>{{ $pelayanan->lokasi }}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <i class="ri-team-line" style="color: var(--primary); font-size: 1.1rem;"></i>
                    <span>{{ $pelayanan->details->where('status', 'active')->count() }} Petugas Aktif</span>
                </div>
            </div>

            <!-- Preview Petugas (max 4, + more jika banyak) -->
            @if ($pelayanan->details->where('status', 'active')->isNotEmpty())
                <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: var(--radius);">
                    <p class="lux-label" style="margin-bottom: 0.6rem; font-size: 0.9rem;">Petugas:</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        @foreach ($pelayanan->details->where('status', 'active')->take(4) as $detail)
                            <span class="lux-badge" style="background: white; border: 1px solid #e2e8f0; font-size: 0.8rem; padding: 0.4rem 0.8rem;">
                                {{ explode(' ', $detail->jemaat->nama_lengkap)[0] ?? 'Jemaat' }}
                                @if ($detail->role)
                                    <small style="opacity: 0.7;">({{ $detail->role }})</small>
                                @endif
                            </span>
                        @endforeach

                        @if ($pelayanan->details->where('status', 'active')->count() > 4)
                            <span class="lux-badge" style="background: #e2e8f0; font-size: 0.8rem;">
                                +{{ $pelayanan->details->where('status', 'active')->count() - 4 }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Tombol Aksi -->
            <div style="display: flex; gap: 0.6rem; margin-top: auto;">
                <a href="{{ route('admin.jadwal.show', $pelayanan) }}" class="lux-btn lux-btn-outline lux-btn-sm" style="flex: 1;">
                    <i class="ri-eye-line"></i> Detail
                </a>

                @if (in_array($pelayanan->status, ['draft', 'active']) && auth()->user()->hasPermission('pelayanan.edit'))
                    <button type="button" class="lux-btn lux-btn-primary lux-btn-sm btn-edit-jadwal"
                            data-id="{{ $pelayanan->id }}"
                            data-nama="{{ $pelayanan->nama_pelayanan }}">
                        <i class="ri-pencil-line"></i>
                    </button>
                @endif

                @if (auth()->user()->hasPermission('pelayanan.delete'))
                    <button type="button" class="lux-btn lux-btn-outline lux-btn-sm btn-delete-jadwal"
                            style="border-color: var(--danger); color: var(--danger);"
                            data-id="{{ $pelayanan->id }}"
                            data-nama="{{ $pelayanan->nama_pelayanan }}">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                @endif
            </div>
        </div>
    @endforeach
@endif