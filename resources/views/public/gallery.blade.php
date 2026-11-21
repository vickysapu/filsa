@extends('layouts.public')

@section('title', 'Galeri Dokumentasi - Gereja Filadelfia')

@push('styles')
<style>
    .gallery-page .gallery-album-list {
        max-width: 1140px;
        margin: 0 auto;
        grid-template-columns: repeat(12, minmax(0, 1fr)) !important;
        gap: 1.5rem !important;
    }

    .gallery-album-entry {
        display: flex;
        flex-direction: column;
        grid-column: span 4;
        padding: 0 !important;
        overflow: hidden;
        border: 0 !important;
        border-radius: 0 !important;
        border-bottom: 1px solid var(--zaitun-border) !important;
        background: transparent !important;
        box-shadow: none !important;
        color: inherit;
        text-decoration: none;
    }

    .gallery-album-entry:nth-child(4n + 1) {
        grid-column: span 7;
    }

    .gallery-album-entry:nth-child(4n + 2) {
        grid-column: span 5;
    }

    .gallery-album-entry:nth-child(4n + 3),
    .gallery-album-entry:nth-child(4n + 4) {
        grid-column: span 6;
    }

    .gallery-page .gallery-album-entry:hover {
        transform: none !important;
        border-color: var(--zaitun-border) !important;
        color: var(--zaitun-deep-dark);
    }

    .gallery-album-image {
        height: auto !important;
        aspect-ratio: 4 / 3;
        overflow: hidden;
        background: var(--zaitun-cream);
    }

    .gallery-album-entry:nth-child(4n + 1) .gallery-album-image {
        aspect-ratio: 16 / 9;
    }

    .gallery-album-image img {
        transition: transform 650ms ease;
    }

    .gallery-album-entry:hover .gallery-album-image img {
        transform: scale(1.025);
    }

    .gallery-album-copy {
        padding: 1.2rem 0 1.5rem !important;
    }

    .gallery-album-copy h2 {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(1.35rem, 2vw, 1.8rem) !important;
        font-weight: 500 !important;
    }

    .gallery-album-entry:focus-visible {
        outline: 2px solid var(--zaitun-primary);
        outline-offset: 4px;
    }

    .gallery-photo-note {
        position: absolute;
        left: 0.75rem;
        bottom: 0.75rem;
        padding: 0.25rem 0.5rem;
        background: rgba(16, 59, 43, 0.88);
        color: var(--zaitun-sand);
        font-size: 0.68rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    @media (max-width: 760px) {
        .gallery-page .gallery-album-list {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 1.25rem !important;
        }

        .gallery-album-entry,
        .gallery-album-entry:nth-child(4n + 1),
        .gallery-album-entry:nth-child(4n + 2),
        .gallery-album-entry:nth-child(4n + 3),
        .gallery-album-entry:nth-child(4n + 4) {
            grid-column: span 1;
        }
    }

    @media (max-width: 520px) {
        .gallery-page .gallery-album-list {
            grid-template-columns: 1fr !important;
        }

        .gallery-album-image,
        .gallery-album-entry:nth-child(4n + 1) .gallery-album-image {
            aspect-ratio: 16 / 10;
        }
    }
</style>
@endpush

@section('content')

    <!-- Subpage Hero Header -->
    <header class="page-hero public-page-hero public-page-hero--gallery">
        <div class="page-hero-container fade-in">
            <nav class="page-breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Beranda</a>
                <i class="ri-arrow-right-s-line"></i>
                <span style="color: var(--zaitun-primary);">Galeri Kegiatan</span>
            </nav>
            <span class="page-kicker">Dokumentasi Momen</span>
            <h1 class="page-hero-title">Galeri & Album Pelayanan</h1>
            <p class="page-hero-desc">
                Kumpulan rekam jejak sukacita persekutuan, ibadah raya, dan karya kasih jemaat Gereja Filadelfia.
            </p>
        </div>
        <span class="page-image-note">Foto ilustrasi · bukan dokumentasi album</span>
    </header>

    <!-- Gallery Grid Section -->
    <section class="content-container gallery-page">
        <div class="gallery-album-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2rem;">
            @php
                $galleryFallbackImages = [
                    'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=900&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=900&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1507692049790-de58290a4334?w=900&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=900&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1485546246426-74dc88dec4d9?w=900&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=900&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=900&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1511988617509-a57c8a288659?w=900&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1528605248644-14dd04022da1?w=900&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=900&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=900&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=900&auto=format&fit=crop&q=80',
                ];
            @endphp
            @forelse($albums as $album)
                @php($fallbackImage = $galleryFallbackImages[$loop->index % count($galleryFallbackImages)])
                <a class="editorial-card gallery-album-entry fade-in" href="{{ route('public.gallery.album', $album->slug) }}">
                    <div class="gallery-album-image" style="position: relative; height: 230px; background: var(--zaitun-deep); overflow: hidden;">
                        @if ($album->cover_image)
                            <img src="{{ asset('storage/' . preg_replace('#^(?:/?storage/)+#', '', $album->cover_image)) }}" alt="{{ $album->judul_album }}" onerror="this.onerror=null; this.src='{{ $fallbackImage }}'; this.alt='Foto ilustrasi gereja, bukan dokumentasi album'; this.parentElement.querySelector('.gallery-photo-note').hidden=false;" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);">
                            <span class="gallery-photo-note" hidden>Foto ilustrasi · bukan dokumentasi album</span>
                        @else
                            <img src="{{ $fallbackImage }}" alt="Foto ilustrasi gereja, bukan dokumentasi album" style="width: 100%; height: 100%; object-fit: cover;">
                            <span class="gallery-photo-note">Foto ilustrasi · bukan dokumentasi album</span>
                        @endif

                        <div style="position: absolute; top: 14px; right: 14px; background: rgba(28, 36, 23, 0.75); backdrop-filter: blur(4px); color: var(--zaitun-gold-light); font-size: 0.76rem; font-weight: 700; padding: 0.35rem 0.8rem; border-radius: var(--radius-pill); display: flex; align-items: center; gap: 0.35rem;">
                            <i class="ri-camera-lens-line"></i>
                            <span>{{ $album->jumlah_foto ?? 0 }} Foto</span>
                        </div>
                    </div>

                    <div class="gallery-album-copy" style="padding: 1.8rem; display: flex; flex-direction: column; flex: 1;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--zaitun-primary); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.4rem;">
                            {{ $album->tanggal_album ? $album->tanggal_album->format('d F Y') : 'Momen' }}
                        </span>

                        <h2 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 800; color: var(--zaitun-deep-dark); margin-bottom: 0.6rem; line-height: 1.3;">
                            {{ $album->judul_album }}
                        </h2>

                        @if ($album->lokasi)
                            <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.86rem; color: var(--zaitun-text-muted); margin-bottom: 1.2rem;">
                                <i class="ri-map-pin-line" style="color: var(--zaitun-gold);"></i>
                                <span>{{ $album->lokasi }}</span>
                            </div>
                        @endif

                        <div style="margin-top: auto; display: inline-flex; align-items: center; gap: 0.4rem; color: var(--zaitun-deep); font-weight: 700; font-size: 0.9rem;">
                            <span>Buka Album</span>
                            <i class="ri-arrow-right-line"></i>
                        </div>
                    </div>
                </a>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem;" class="editorial-card">
                    <i class="ri-image-off-line" style="font-size: 3.5rem; color: var(--zaitun-primary); opacity: 0.4; margin-bottom: 1rem; display: block;"></i>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--zaitun-deep-dark); margin-bottom: 0.5rem;">Belum Ada Album Foto</h3>
                    <p style="color: var(--zaitun-text-secondary); max-width: 500px; margin: 0 auto;">
                        Dokumentasi kegiatan akan segera diperbarui oleh tim multimedia kami.
                    </p>
                </div>
            @endforelse
        </div>

        @if ($albums->hasPages())
            <div style="margin-top: 3.5rem; display: flex; justify-content: center;">
                {{ $albums->links() }}
            </div>
        @endif
    </section>

@endsection
