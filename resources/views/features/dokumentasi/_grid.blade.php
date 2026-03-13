@forelse($albums as $album)
    <div class="lux-card" style="padding: 0; overflow: hidden; position: relative;">

        <!-- Featured Badge -->
        @if ($album->is_featured)
            <div style="position: absolute; top: 0.75rem; left: 0.75rem; z-index: 10;">
                <span class="lux-badge" style="background: var(--warning); color: white; font-size: 0.7rem;">
                    <i class="ri-star-line"></i>
                </span>
            </div>
        @endif

        <!-- Cover Image -->
        @if ($album->cover_image)
            <img src="{{ asset('storage/' . $album->cover_image) }}" alt="{{ $album->judul_album }}"
                style="width: 100%; height: 180px; object-fit: cover;">
        @else
            <div style="width: 100%; height: 180px; background: linear-gradient(135deg, var(--primary), var(--accent)); 
                        display: flex; align-items: center; justify-content: center; color: white;">
                <i class="ri-image-line" style="font-size: 3rem; opacity: 0.5;"></i>
            </div>
        @endif

        <!-- Content -->
        <div style="padding: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <span class="lux-badge {{ $album->status === 'published' ? 'lux-badge-success' : 'lux-badge-warning' }}" style="font-size: 0.7rem;">
                    {{ ucfirst($album->status) }}
                </span>
                @if ($album->event)
                    <span class="lux-badge" style="background: #e0e7ff; color: #3730a3; font-size: 0.7rem;">
                        <i class="ri-calendar-line"></i> Event
                    </span>
                @endif
            </div>

            <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1.3;">
                {{ $album->judul_album }}
            </h4>

            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem; font-size: 0.8rem; color: var(--text-muted);">
                <span><i class="ri-image-line"></i> {{ $album->jumlah_foto ?? 0 }} foto</span>
                <span><i class="ri-calendar-line"></i> {{ $album->tanggal_album?->format('d M Y') ?? '-' }}</span>
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('admin.dokumentasi.show', $album) }}" class="lux-btn lux-btn-outline lux-btn-sm" style="flex: 1;">
                    <i class="ri-eye-line"></i> Lihat
                </a>
                @if (auth()->user()->hasPermission('dokumentasi.edit'))
                    <button type="button" class="lux-btn lux-btn-primary lux-btn-sm btn-edit-album" data-id="{{ $album->id }}">
                        <i class="ri-pencil-line"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
@empty
    <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; color: var(--text-muted);">
        <i class="ri-gallery-off-line" style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;"></i>
        <p style="font-size: 1.1rem;">Belum ada album dokumentasi</p>
        @if (auth()->user()->hasPermission('dokumentasi.create'))
            <button type="button" class="lux-btn lux-btn-primary btn-tambah-album" style="margin-top: 1rem;">
                <i class="ri-add-line"></i> Buat Album Pertama
            </button>
        @endif
    </div>
@endforelse