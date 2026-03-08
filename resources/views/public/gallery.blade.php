@extends('layouts.public')

@section('title', 'Galeri - Gereja Filadelfia')

@section('content')

    <!-- Hero Section -->
    <section class="hero-section"
        style="min-height: 60vh; background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(236, 72, 153, 0.1));">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="ri-gallery-line"></i> Galeri
            </div>
            <h1 class="hero-title">Dokumentasi Kegiatan</h1>
            <p class="hero-subtitle">
                Lihat momen-momen indah dari berbagai kegiatan, ibadah,
                dan pelayanan di Gereja Filadelfia.
            </p>
        </div>
    </section>

    <!-- Gallery Albums -->
    <section class="section">
        <div class="section-header fade-in">
            <h2 class="section-title">Semua Album</h2>
        </div>

        <div class="gallery-grid">
            @forelse($albums as $album)
                <div class="gallery-item fade-in"
                    onclick="window.location.href='{{ route('public.gallery.album', $album->slug) }}'">
                    @if ($album->cover_image)
                        <img src="{{ asset('storage/' . $album->cover_image) }}" alt="{{ $album->judul_album }}"
                            class="gallery-image">
                    @else
                        <div
                            style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary)); display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="ri-image-line" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                    @endif
                    <div class="gallery-overlay">
                        <div>
                            <div class="gallery-title">{{ $album->judul_album }}</div>
                            <div class="gallery-count">{{ $album->jumlah_foto }} Foto</div>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem;">
                    <i class="ri-gallery-off-line"
                        style="font-size: 4rem; color: var(--brand-primary); opacity: 0.3; margin-bottom: 1rem;"></i>
                    <p style="font-size: 1.2rem; color: var(--brand-dark); opacity: 0.7;">Belum ada album galeri</p>
                </div>
            @endforelse
        </div>
    </section>

@endsection
