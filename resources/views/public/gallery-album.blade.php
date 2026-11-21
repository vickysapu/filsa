@extends('layouts.public')

@section('title', $album->judul_album . ' - Gereja Filadelfia')

@push('styles')
<style>
    .page-hero.album-hero {
        min-height: 420px;
        display: flex;
        align-items: flex-end;
        padding-top: 9rem;
        padding-bottom: 4rem;
        background: var(--zaitun-deep-dark);
    }

    .album-hero .page-hero-container {
        width: 100%;
        text-align: center;
    }

    .album-hero .page-breadcrumbs {
        justify-content: center;
    }

    .album-hero .page-hero-title {
        max-width: 900px;
        font-size: clamp(2.4rem, 5vw, 4.2rem);
    }

    .album-hero > div:first-child::after {
        position: absolute;
        inset: 0;
        content: '';
        background: linear-gradient(90deg, rgba(16, 59, 43, 0.78), rgba(16, 59, 43, 0.12)), linear-gradient(0deg, rgba(16, 59, 43, 0.52), transparent 78%);
    }

    .album-illustration-label {
        position: absolute;
        right: 2rem;
        bottom: 1.5rem;
        z-index: 1;
        color: var(--zaitun-sand);
        font-size: 0.68rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .album-meta-strip {
        padding-top: 1.75rem !important;
        padding-bottom: 1.75rem !important;
        border-bottom: 1px solid var(--zaitun-border);
    }

    .album-metadata {
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .album-metadata > div {
        justify-content: flex-start !important;
        gap: clamp(1.5rem, 4vw, 4rem) !important;
    }

    .album-metadata > div > div > div:first-child {
        display: none !important;
    }

    .album-metadata strong {
        font-size: 0.9rem !important;
    }

    .album-photo-grid {
        grid-template-columns: repeat(12, minmax(0, 1fr)) !important;
        gap: 1rem !important;
    }

    .album-photo {
        grid-column: span 4;
        position: relative;
        overflow: hidden;
        display: block;
        width: 100%;
        padding: 0;
        border: 0;
        border-radius: 0 !important;
        aspect-ratio: 4 / 3 !important;
        background: var(--zaitun-cream);
        box-shadow: none !important;
        cursor: zoom-in;
    }

    .album-photo:nth-child(5n + 1) {
        grid-column: span 8;
        aspect-ratio: 16 / 9 !important;
    }

    .album-photo:focus-visible {
        outline: 2px solid var(--zaitun-primary);
        outline-offset: 3px;
    }

    .album-photo-caption {
        background: linear-gradient(to top, rgba(16, 59, 43, 0.78), transparent 95%) !important;
        opacity: 1 !important;
        padding: 1rem !important;
        pointer-events: none;
    }

    .album-photo-note {
        left: 0.65rem;
        bottom: 0.65rem;
        z-index: 1;
    }

    .album-lightbox {
        padding: 1rem !important;
    }

    @media (max-width: 760px) {
        .album-photo-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        .album-photo,
        .album-photo:nth-child(5n + 1) {
            grid-column: span 1;
            aspect-ratio: 4 / 3 !important;
        }
    }

    @media (max-width: 520px) {
        .album-hero {
            min-height: 350px;
        }

        .album-metadata > div {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 1rem !important;
        }

        .album-photo-grid {
            grid-template-columns: 1fr !important;
        }

        .album-photo,
        .album-photo:nth-child(5n + 1) {
            grid-column: auto;
            aspect-ratio: 16 / 10 !important;
        }
    }
</style>
@endpush

@section('content')

    <!-- Subpage Hero Header with Backdrop -->
    <header class="page-hero album-hero" style="position: relative; overflow: hidden;">
        <div style="position: absolute; inset: 0; z-index: 0;">
            @if ($album->cover_image)
                <img src="{{ asset('storage/' . preg_replace('#^(?:/?storage/)+#', '', $album->cover_image)) }}" alt="" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=1800&amp;auto=format&amp;fit=crop&amp;q=82'; this.parentElement.querySelector('.album-illustration-label').hidden=false;" style="width: 100%; height: 100%; object-fit: cover; filter: brightness(0.28);">
                <span class="album-illustration-label" hidden>Foto ilustrasi · bukan dokumentasi album</span>
            @else
                <img src="https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=1800&auto=format&fit=crop&q=82" alt="" style="width: 100%; height: 100%; object-fit: cover; filter: brightness(0.28);">
                <span class="album-illustration-label">Foto ilustrasi · bukan dokumentasi album</span>
            @endif
        </div>

        <div class="page-hero-container fade-in" style="position: relative; z-index: 1; color: #FFFFFF;">
            <nav class="page-breadcrumbs" aria-label="Breadcrumb" style="color: rgba(255,255,255,0.7);">
                <a href="{{ route('public.home') }}">Beranda</a>
                <i class="ri-arrow-right-s-line"></i>
                <a href="{{ route('public.gallery') }}">Galeri</a>
                <i class="ri-arrow-right-s-line"></i>
                <span style="color: var(--zaitun-gold-light);">{{ Str::limit($album->judul_album, 32) }}</span>
            </nav>

            <span class="page-kicker" style="color: var(--zaitun-gold-light);">
                {{ $album->kategori->nama_kategori ?? 'Dokumentasi Acara' }}
            </span>

            <h1 class="page-hero-title" style="color: #FFFFFF;">
                {{ $album->judul_album }}
            </h1>

            @if ($album->deskripsi)
                <p class="page-hero-desc" style="color: rgba(255,255,255,0.9);">
                    {{ $album->deskripsi }}
                </p>
            @endif
        </div>
    </header>

    <!-- Metadata Strip -->
    <section class="content-container album-meta-strip" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="editorial-card album-metadata fade-in" style="padding: 1.5rem 2rem;">
            <div style="display: flex; gap: 2.5rem; flex-wrap: wrap; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.8rem;">
                    <div style="width: 42px; height: 42px; background: rgba(82, 110, 59, 0.1); color: var(--zaitun-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="ri-calendar-line"></i>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.76rem; font-weight: 800; text-transform: uppercase; color: var(--zaitun-text-muted); letter-spacing: 0.08em;">Tanggal Album</span>
                        <strong style="color: var(--zaitun-deep-dark); font-size: 0.95rem;">{{ $album->tanggal_album ? $album->tanggal_album->format('d F Y') : '-' }}</strong>
                    </div>
                </div>

                @if ($album->lokasi)
                    <div style="display: flex; align-items: center; gap: 0.8rem;">
                        <div style="width: 42px; height: 42px; background: rgba(192, 138, 62, 0.12); color: var(--zaitun-gold); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="ri-map-pin-line"></i>
                        </div>
                        <div>
                            <span style="display: block; font-size: 0.76rem; font-weight: 800; text-transform: uppercase; color: var(--zaitun-text-muted); letter-spacing: 0.08em;">Lokasi Kegiatan</span>
                            <strong style="color: var(--zaitun-deep-dark); font-size: 0.95rem;">{{ $album->lokasi }}</strong>
                        </div>
                    </div>
                @endif

                @if ($album->fotografer)
                    <div style="display: flex; align-items: center; gap: 0.8rem;">
                        <div style="width: 42px; height: 42px; background: rgba(52, 67, 44, 0.1); color: var(--zaitun-deep); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="ri-camera-lens-line"></i>
                        </div>
                        <div>
                            <span style="display: block; font-size: 0.76rem; font-weight: 800; text-transform: uppercase; color: var(--zaitun-text-muted); letter-spacing: 0.08em;">Fotografer</span>
                            <strong style="color: var(--zaitun-deep-dark); font-size: 0.95rem;">{{ $album->fotografer }}</strong>
                        </div>
                    </div>
                @endif

                <div style="display: flex; align-items: center; gap: 0.8rem;">
                    <div style="width: 42px; height: 42px; background: rgba(200, 90, 50, 0.1); color: var(--zaitun-terracotta); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="ri-image-line"></i>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.76rem; font-weight: 800; text-transform: uppercase; color: var(--zaitun-text-muted); letter-spacing: 0.08em;">Total Foto</span>
                        <strong style="color: var(--zaitun-deep-dark); font-size: 0.95rem;">{{ $album->jumlah_foto ?? count($album->photos) }} Momen</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Photos Grid Section -->
    <section class="content-container" style="padding-top: 0;">
        <div class="album-photo-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.25rem;">
            @forelse($album->photos as $photo)
                <button type="button" class="album-photo fade-in" aria-label="Perbesar foto{{ $photo->caption ? ': ' . $photo->caption : '' }}" onclick="openLightbox('{{ asset('storage/' . $photo->file_path) }}', '{{ addslashes($photo->caption ?? '') }}')">
                    <img src="{{ asset('storage/' . preg_replace('#^(?:/?storage/)+#', '', $photo->file_path)) }}" alt="{{ $photo->caption ?? 'Foto' }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=900&amp;auto=format&amp;fit=crop&amp;q=80'; this.alt='Foto ilustrasi, bukan dokumentasi album'; this.parentElement.querySelector('.album-photo-note').hidden=false;" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;">
                    <span class="gallery-photo-note album-photo-note" hidden>Foto ilustrasi · bukan dokumentasi album</span>
                    @if ($photo->caption)
                        <div class="album-photo-caption" style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(28,36,23,0.85) 0%, transparent 60%); display: flex; align-items: flex-end; padding: 1.2rem; opacity: 0; transition: opacity 0.3s ease;">
                            <span style="color: #FFFFFF; font-size: 0.88rem; font-weight: 600;">{{ $photo->caption }}</span>
                        </div>
                    @endif
                </button>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem;" class="editorial-card">
                    <i class="ri-image-off-line" style="font-size: 3.5rem; color: var(--zaitun-primary); opacity: 0.4; margin-bottom: 1rem; display: block;"></i>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.4rem; color: var(--zaitun-deep-dark); margin-bottom: 0.5rem;">Belum Ada Foto dalam Album Ini</h3>
                    <p style="color: var(--zaitun-text-secondary); max-width: 450px; margin: 0 auto 1.5rem;">
                        Foto-foto sedang dalam proses pemilihan dan unggah oleh tim dokumentasi.
                    </p>
                    <a href="{{ route('public.gallery') }}" class="btn-primary-olive">
                        <i class="ri-arrow-left-line"></i>
                        <span>Kembali ke Galeri Album</span>
                    </a>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Professional Lightbox Modal -->
    <div id="lightboxModal" class="album-lightbox" role="dialog" aria-modal="true" aria-label="Tampilan foto" style="display: none; position: fixed; inset: 0; background: rgba(18, 24, 15, 0.95); z-index: 9999; align-items: center; justify-content: center; padding: 2rem;" onclick="closeLightbox()">
        <button onclick="closeLightbox()" style="position: absolute; top: 1.8rem; right: 2rem; background: transparent; border: none; color: #FFFFFF; font-size: 2.2rem; cursor: pointer;" aria-label="Tutup Foto">
            <i class="ri-close-line"></i>
        </button>
        <div style="max-width: 90vw; max-height: 85vh; text-align: center;" onclick="event.stopPropagation()">
            <img id="lightboxImg" src="" alt="Perbesar Foto" style="max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.5);">
            <p id="lightboxCaption" style="color: var(--zaitun-gold-light); margin-top: 1rem; font-size: 1rem; font-weight: 600;"></p>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function openLightbox(url, caption) {
        document.getElementById('lightboxImg').src = url;
        document.getElementById('lightboxCaption').innerText = caption;
        const modal = document.getElementById('lightboxModal');
        modal.style.display = 'flex';
    }

    function closeLightbox() {
        document.getElementById('lightboxModal').style.display = 'none';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLightbox();
    });
</script>
@endpush
