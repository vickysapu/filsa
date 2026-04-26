@forelse($albums as $album)
    <div class="col-md-6 col-lg-4 col-xl-3">
        <div class="card-custom album-card h-100">
            <!-- Image Area -->
            <div class="album-thumbnail">
                @if ($album->cover_image)
                    <img src="{{ asset('storage/' . $album->cover_image) }}" alt="{{ $album->judul_album }}">
                @else
                    <div class="album-thumbnail-placeholder">
                        <i class="ri-image-line"></i>
                    </div>
                @endif

                <!-- Badges Overlay -->
                <div class="album-badge-container">
                    @if ($album->is_featured)
                        <span class="album-badge featured" title="Featured Album">
                            <i class="ri-star-fill"></i>
                        </span>
                    @endif
                    <span class="album-badge badge-status-{{ $album->status }}">
                        {{ ucfirst($album->status) }}
                    </span>
                </div>
            </div>

            <!-- Content -->
            <div class="album-body d-flex flex-column">
                <div class="mb-2">
                    <h6 class="album-title mb-0">
                        <a href="{{ route('admin.dokumentasi.show', $album) }}" class="text-decoration-none text-reset">
                            {{ $album->judul_album }}
                        </a>
                    </h6>
                </div>

                <div class="album-meta d-flex flex-wrap gap-3 mb-3">
                    <span><i class="ri-image-line me-1"></i> {{ $album->jumlah_foto ?? 0 }} Foto</span>
                    <span><i class="ri-calendar-line me-1"></i>
                        {{ $album->tanggal_album?->format('d M Y') ?? '-' }}</span>
                </div>

                @if ($album->event)
                    <div class="mt-auto mb-3">
                        <span class="badge badge-secondary-custom">
                            <i class="ri-calendar-event-line me-1"></i> {{ $album->event->nama_event }}
                        </span>
                    </div>
                @endif

                <!-- Actions -->
                <div class="album-footer d-flex gap-2 mt-auto pt-3 border-top border-custom">
                    <a href="{{ route('admin.dokumentasi.show', $album) }}"
                        class="btn btn-outline-custom btn-sm flex-fill">
                        <i class="ri-eye-line me-1"></i> Lihat
                    </a>
                    @if (auth()->user()->hasPermission('dokumentasi.edit'))
                        <button type="button" class="btn btn-custom btn-sm btn-edit-album"
                            data-id="{{ $album->id }}" title="Edit">
                            <i class="ri-pencil-line"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="empty-state-card text-center py-5">
            <div class="empty-state-icon mb-3">
                <i class="ri-gallery-line"></i>
            </div>
            <h5 class="fw-semibold mb-2">Belum ada album dokumentasi</h5>
            <p class="text-muted-custom small mb-4">Mulai dokumentasikan kegiatan gereja Anda</p>
            @if (auth()->user()->hasPermission('dokumentasi.create'))
                <button type="button" class="btn btn-custom btn-tambah-album">
                    <i class="ri-add-line me-1"></i> Buat Album Pertama
                </button>
            @endif
        </div>
    </div>
@endforelse

<style>
    /* ===== ALBUM CARD ===== */
    .album-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        overflow: hidden;
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .album-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--accent-blue);
    }

    /* Thumbnail Area */
    .album-thumbnail {
        position: relative;
        aspect-ratio: 16/9;
        overflow: hidden;
        background: var(--bg-body);
    }

    .album-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .album-card:hover .album-thumbnail img {
        transform: scale(1.05);
    }

    .album-thumbnail-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--bg-body) 0%, var(--bg-surface) 100%);
        color: var(--text-secondary);
        font-size: 3rem;
        opacity: 0.5;
    }

    /* Badges */
    .album-badge-container {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        align-items: flex-end;
    }

    .album-badge {
        padding: 0.3em 0.6em;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        backdrop-filter: blur(4px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .album-badge.featured {
        background: #f59e0b;
        color: white;
        border: none;
    }

    /* Body & Content */
    .album-body {
        padding: 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .album-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .album-title a:hover {
        color: var(--accent-blue);
    }

    .album-meta {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .album-footer {
        margin-top: auto;
    }

    /* ===== STATUS BADGES (Consistent with system) ===== */
    .badge-status-draft {
        background: rgba(100, 116, 139, 0.9);
        color: white;
        border: 1px solid rgba(100, 116, 139, 0.5);
    }

    .badge-status-published {
        background: rgba(34, 197, 94, 0.9);
        color: white;
        border: 1px solid rgba(34, 197, 94, 0.5);
    }

    .badge-status-archived {
        background: rgba(139, 92, 246, 0.9);
        color: white;
        border: 1px solid rgba(139, 92, 246, 0.5);
    }

    /* ===== EMPTY STATE ===== */
    .empty-state-card {
        background: var(--bg-surface);
        border: 1px dashed var(--border-color);
        border-radius: var(--radius);
        padding: 3rem 1.5rem;
        text-align: center;
        grid-column: 1 / -1;
        /* Spans full width if grid */
    }

    .empty-state-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--bg-body);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        font-size: 2.5rem;
        opacity: 0.6;
    }

    /* ===== DARK MODE ===== */
    [data-theme="dark"] .album-thumbnail-placeholder {
        background: linear-gradient(135deg, var(--bg-sidebar) 0%, var(--bg-surface) 100%);
    }

    [data-theme="dark"] .empty-state-card {
        background: var(--bg-sidebar);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .empty-state-icon {
        background: var(--bg-sidebar);
    }

    [data-theme="dark"] .album-badge {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
    }
</style>
