@forelse($events as $event)
    <div class="lux-card" style="padding: 0; overflow: hidden; position: relative;">

        <!-- Featured Badge -->
        @if ($event->is_featured)
            <div style="position: absolute; top: 1rem; left: 1rem; z-index: 10;">
                <span class="lux-badge" style="background: var(--warning); color: white;">
                    <i class="ri-star-line"></i> Featured
                </span>
            </div>
        @endif

        <!-- Status Badge -->
        <div style="position: absolute; top: 1rem; right: 1rem; z-index: 10;">
            <span class="lux-badge {{ $event->status_label['color'] ?? 'bg-secondary' }}">
                {{ $event->status_label['label'] ?? ucfirst($event->status) }}
            </span>
        </div>

        <!-- Featured Image -->
        @if ($event->gambar_featured)
            <img src="{{ asset('storage/' . $event->gambar_featured) }}" alt="{{ $event->nama_event }}"
                style="width: 100%; height: 200px; object-fit: cover;">
        @else
            <div style="width: 100%; height: 200px; background: linear-gradient(135deg, var(--primary), var(--accent)); 
                        display: flex; align-items: center; justify-content: center; color: white;">
                <i class="ri-calendar-event-line" style="font-size: 4rem; opacity: 0.5;"></i>
            </div>
        @endif

        <!-- Content -->
        <div style="padding: 1.5rem;">
            <!-- Kategori -->
            <span class="lux-badge"
                style="background: {{ $event->kategori->warna ?? '#e2e8f0' }}20; 
                       color: {{ $event->kategori->warna ?? '#64748b' }}; 
                       margin-bottom: 0.75rem; display: inline-block;">
                {{ $event->kategori->nama_kategori ?? 'Uncategorized' }}
            </span>

            <!-- Nama Event -->
            <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.75rem; line-height: 1.4;">
                {{ $event->nama_event }}
            </h3>

            <!-- Deskripsi Singkat -->
            <p class="text-muted"
                style="font-size: 0.9rem; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                {{ Str::limit($event->deskripsi ?? '', 120) }}
            </p>

            <!-- Info Grid -->
            <div style="display: grid; gap: 0.5rem; margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">
                    <i class="ri-calendar-line" style="color: var(--primary);"></i>
                    <span>{{ $event->tanggal_mulai?->format('d M Y') ?? '-' }}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">
                    <i class="ri-time-line" style="color: var(--primary);"></i>
                    <span>{{ $event->tanggal_mulai?->format('H:i') ?? '-' }} WIB</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">
                    <i class="ri-map-pin-line" style="color: var(--primary);"></i>
                    <span>{{ Str::limit($event->lokasi ?? '-', 30) }}</span>
                </div>
                @if ($event->kuota_peserta)
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">
                        <i class="ri-team-line" style="color: var(--primary);"></i>
                        <span>{{ $event->jumlah_terdaftar ?? 0 }} / {{ $event->kuota_peserta }} Terdaftar</span>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('admin.events.show', $event) }}" class="lux-btn lux-btn-outline lux-btn-sm" style="flex: 1;">
                    <i class="ri-eye-line"></i> Detail
                </a>

                @if (auth()->user()->hasPermission('event.edit'))
                    <button type="button" class="lux-btn lux-btn-primary lux-btn-sm btn-edit-event" data-id="{{ $event->id }}">
                        <i class="ri-pencil-line"></i>
                    </button>
                @endif

                @if (auth()->user()->hasPermission('event.delete'))
                    <button type="button" class="lux-btn lux-btn-danger lux-btn-sm btn-delete-event" data-id="{{ $event->id }}" 
                            data-nama="{{ $event->nama_event }}">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
@empty
    <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; color: var(--text-muted);">
        <i class="ri-calendar-off-line" style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;"></i>
        <p style="font-size: 1.1rem;">Belum ada event</p>
        @if (auth()->user()->hasPermission('event.create'))
            <button type="button" class="lux-btn lux-btn-primary btn-tambah-event" style="margin-top: 1rem;">
                <i class="ri-add-line"></i> Buat Event Pertama
            </button>
        @endif
    </div>
@endforelse