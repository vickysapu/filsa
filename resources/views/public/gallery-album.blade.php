@extends('layouts.public')

@section('title', $album->judul_album . ' - Gereja Filadelfia')

@section('content')

    <!-- Hero Section -->
    <section class="hero-section" style="min-height: 50vh;">
        @if ($album->cover_image)
            <img src="{{ asset('storage/' . $album->cover_image) }}" alt="{{ $album->judul_album }}"
                style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: -1;">
            <div
                style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.7)); z-index: -1;">
            </div>
        @endif
        <div class="hero-content" style="color: white;">
            <div class="hero-badge"
                style="background: rgba(255,255,255,0.2); border-color: rgba(255,255,255,0.3); color: white;">
                <i class="ri-gallery-line"></i> {{ $album->kategori->nama_kategori ?? 'Galeri' }}
            </div>
            <h1 class="hero-title" style="color: white; -webkit-text-fill-color: white;">{{ $album->judul_album }}</h1>
            @if ($album->deskripsi)
                <p class="hero-subtitle" style="color: rgba(255,255,255,0.9);">{{ $album->deskripsi }}</p>
            @endif
        </div>
    </section>

    <!-- Album Info -->
    <section class="section" style="padding-top: 0;">
        <div class="lux-card fade-in" style="margin-top: -5rem; position: relative; z-index: 10;">
            <div class="lux-grid lux-grid-4" style="gap: 2rem; align-items: center;">
                <div>
                    <p class="lux-label" style="margin-bottom: 0.5rem;">Tanggal</p>
                    <p style="font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ri-calendar-line" style="color: var(--brand-primary);"></i>
                        {{ $album->tanggal_album->format('d F Y') }}
                    </p>
                </div>
                @if ($album->lokasi)
                    <div>
                        <p class="lux-label" style="margin-bottom: 0.5rem;">Lokasi</p>
                        <p style="font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ri-map-pin-line" style="color: var(--brand-primary);"></i>
                            {{ $album->lokasi }}
                        </p>
                    </div>
                @endif
                @if ($album->fotografer)
                    <div>
                        <p class="lux-label" style="margin-bottom: 0.5rem;">Fotografer</p>
                        <p style="font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ri-camera-line" style="color: var(--brand-primary);"></i>
                            {{ $album->fotografer }}
                        </p>
                    </div>
                @endif
                <div>
                    <p class="lux-label" style="margin-bottom: 0.5rem;">Total Foto</p>
                    <p style="font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ri-image-line" style="color: var(--brand-primary);"></i>
                        {{ $album->jumlah_foto }} Foto
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Photos Grid -->
    <section class="section">
        <div class="lux-grid lux-grid-4" style="gap: 1rem;">
            @forelse($album->photos as $photo)
                <div class="gallery-item fade-in" style="aspect-ratio: 1;"
                    onclick="openLightbox('{{ asset('storage/' . $photo->file_path) }}', '{{ $photo->caption ?? '' }}')">
                    <img src="{{ asset('storage/' . $photo->file_path) }}" alt="{{ $photo->caption ?? 'Foto' }}"
                        class="gallery-image">
                    @if ($photo->caption)
                        <div class="gallery-overlay">
                            <div>
                                <div class="gallery-title">{{ $photo->caption }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem;">
                    <i class="ri-image-off-line"
                        style="font-size: 4rem; color: var(--brand-primary); opacity: 0.3; margin-bottom: 1rem;"></i>
                    <p style="font-size: 1.2rem; color: var(--brand-dark); opacity: 0.7;">Belum ada foto dalam album ini</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Lightbox -->
    <div id="lightbox"
        style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.95); z-index: 9999; align-items: center; justify-content: center;"
        onclick="closeLightbox()">
        <button onclick="closeLightbox()"
            style="position: absolute; top: 2rem; right: 2rem; background: none; border: none; color: white; font-size: 2rem; cursor: pointer;">
            <i class="ri-close-line"></i>
        </button>
        <img id="lightbox-image" src="" alt="Full size"
            style="max-width: 90%; max-height: 90vh; object-fit: contain;">
        <p id="lightbox-caption"
            style="position: absolute; bottom: 2rem; color: white; font-size: 1.1rem; text-align: center; max-width: 80%;">
        </p>
    </div>

    <!-- Back Button -->
    <section class="section" style="padding-top: 0; text-align: center;">
        <a href="{{ route('public.gallery') }}" class="hero-btn hero-btn-secondary">
            <i class="ri-arrow-left-line"></i> Kembali ke Galeri
        </a>
    </section>

@endsection

@push('scripts')
    <script>
        function openLightbox(src, caption) {
            document.getElementById('lightbox-image').src = src;
            document.getElementById('lightbox-caption').textContent = caption;
            document.getElementById('lightbox').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').style.display = 'none';
            document.body.style.overflow = '';
        }

        // Close with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
            }
        });
    </script>
@endpush
