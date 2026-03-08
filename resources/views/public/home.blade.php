@extends('layouts.public')

@section('title', 'Gereja Filadelfia - Welcome Home')

@section('content')

    <!-- Hero Section -->
    <section class="hero-section">
        <!-- Floating Shapes -->
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>

        <div class="hero-content">
            <div class="hero-badge">
                <i class="ri-heart-fill"></i> Welcome Home
            </div>
            <h1 class="hero-title">
                Gereja Filadelfia
            </h1>
            <p class="hero-subtitle">
                Tempat dimana kasih Kristus nyata, iman bertumbuh, dan komunitas yang hangat
                menanti Anda. Mari bersama-sama mengalami kehadiran Tuhan dalam setiap ibadah.
            </p>
            <div class="hero-buttons">
                <a href="{{ route('public.events') }}" class="hero-btn hero-btn-primary">
                    <i class="ri-calendar-event-line"></i>
                    Lihat Events
                </a>
                <a href="{{ route('public.about') }}" class="hero-btn hero-btn-secondary">
                    <i class="ri-information-line"></i>
                    Tentang Kami
                </a>
                <a href="https://youtube.com" target="_blank" class="hero-btn hero-btn-secondary">
                    <i class="ri-youtube-line"></i>
                    Live Streaming
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section">
        <div class="section-header fade-in">
            <span class="section-badge">
                <i class="ri-star-smile-line"></i> Mengapa Kami
            </span>
            <h2 class="section-title">Bergabung Bersama Kami</h2>
            <p class="section-subtitle">
                Gereja Filadelfia berkomitmen untuk menjadi rumah rohani dimana setiap orang
                dapat tumbuh dalam iman dan melayani dengan kasih.
            </p>
        </div>

        <div class="card-grid">
            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="ri-heart-3-line"></i>
                </div>
                <h3 class="feature-title">Kasih Kristus</h3>
                <p class="feature-description">
                    Kami percaya bahwa kasih Kristus adalah dasar dari segala pelayanan.
                    Setiap orang diterima dengan kasih tanpa syarat.
                </p>
            </div>

            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="ri-book-open-line"></i>
                </div>
                <h3 class="feature-title">Pengajaran Alkitab</h3>
                <p class="feature-description">
                    Pengajaran yang berpusat pada Alkitab untuk membangun iman
                    dan karakter Kristus dalam setiap aspek kehidupan.
                </p>
            </div>

            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="ri-team-line"></i>
                </div>
                <h3 class="feature-title">Komunitas Hangat</h3>
                <p class="feature-description">
                    Bergabunglah dengan komunitas yang peduli, saling mendukung,
                    dan bertumbuh bersama dalam perjalanan iman.
                </p>
            </div>

            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="ri-music-line"></i>
                </div>
                <h3 class="feature-title">Ibadah Yang Hidup</h3>
                <p class="feature-description">
                    Ibadah yang penuh dengan pujian, penyembahan, dan kehadiran
                    Tuhan yang nyata dalam setiap pertemuan.
                </p>
            </div>

            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="ri-hand-heart-line"></i>
                </div>
                <h3 class="feature-title">Pelayanan Sosial</h3>
                <p class="feature-description">
                    Terlibat dalam pelayanan sosial untuk menjadi berkat bagi
                    masyarakat sekitar dan mereka yang membutuhkan.
                </p>
            </div>

            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="ri-children-line"></i>
                </div>
                <h3 class="feature-title">Pelayanan Anak</h3>
                <p class="feature-description">
                    Program khusus untuk anak-anak dan remaja untuk mengenal
                    Tuhan sejak dini dalam suasana yang menyenangkan.
                </p>
            </div>
        </div>
    </section>

    <!-- Upcoming Events Section -->
    <section class="section"
        style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.05), rgba(6, 182, 212, 0.05)); border-radius: 40px;">
        <div class="section-header fade-in">
            <span class="section-badge">
                <i class="ri-calendar-line"></i> Events
            </span>
            <h2 class="section-title">Events Mendatang</h2>
            <p class="section-subtitle">
                Bergabunglah dalam events kami dan alami persekutuan yang hangat
                bersama jemaat Gereja Filadelfia.
            </p>
        </div>

        <div class="card-grid">
            @forelse($upcomingEvents as $event)
                <div class="event-card fade-in">
                    @if ($event->gambar_featured)
                        <img src="{{ asset('storage/' . $event->gambar_featured) }}" alt="{{ $event->nama_event }}"
                            class="event-image">
                    @else
                        <div
                            style="width: 100%; height: 220px; background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary)); display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="ri-calendar-event-line" style="font-size: 4rem; opacity: 0.5;"></i>
                        </div>
                    @endif
                    <div class="event-content">
                        <div class="event-date">
                            <i class="ri-calendar-line"></i>
                            {{ $event->tanggal_mulai->format('d M Y') }}
                        </div>
                        <h3 class="event-title">{{ $event->nama_event }}</h3>
                        <div class="event-location">
                            <i class="ri-map-pin-line"></i>
                            {{ $event->lokasi }}
                        </div>
                        <a href="{{ route('public.event.detail', $event->slug) }}" class="event-btn">
                            Detail Event <i class="ri-arrow-right-line"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem;">
                    <i class="ri-calendar-off-line"
                        style="font-size: 4rem; color: var(--brand-primary); opacity: 0.3; margin-bottom: 1rem;"></i>
                    <p style="font-size: 1.2rem; color: var(--brand-dark); opacity: 0.7;">Belum ada events mendatang</p>
                </div>
            @endforelse
        </div>

        <div style="text-align: center; margin-top: 3rem;">
            <a href="{{ route('public.events') }}" class="hero-btn hero-btn-primary">
                Lihat Semua Events <i class="ri-arrow-right-line"></i>
            </a>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="section">
        <div class="section-header fade-in">
            <span class="section-badge">
                <i class="ri-gallery-line"></i> Galeri
            </span>
            <h2 class="section-title">Dokumentasi Kegiatan</h2>
            <p class="section-subtitle">
                Lihat momen-momen indah dari berbagai kegiatan dan ibadah
                di Gereja Filadelfia.
            </p>
        </div>

        <div class="gallery-grid">
            @forelse($featuredAlbums as $album)
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
                    <p style="font-size: 1.2rem; color: var(--brand-dark); opacity: 0.7;">Belum ada galeri</p>
                </div>
            @endforelse
        </div>

        <div style="text-align: center; margin-top: 3rem;">
            <a href="{{ route('public.gallery') }}" class="hero-btn hero-btn-secondary">
                Lihat Semua Galeri <i class="ri-arrow-right-line"></i>
            </a>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section" style="text-align: center;">
        <div
            style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary)); border-radius: 40px; padding: 5rem 2rem; color: white; position: relative; overflow: hidden;">
            <div
                style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);">
            </div>
            <div style="position: relative; z-index: 10;">
                <h2 style="font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; margin-bottom: 1rem;">
                    Siap Untuk Bergabung?
                </h2>
                <p
                    style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                    Kami menanti kedatangan Anda. Mari bersama-sama mengalami kasih
                    dan persekutuan yang hangat di Gereja Filadelfia.
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="{{ route('public.contact') }}" class="hero-btn"
                        style="background: white; color: var(--brand-primary);">
                        <i class="ri-map-pin-line"></i> Kunjungi Kami
                    </a>
                    <a href="https://wa.me/6281234567890" class="hero-btn"
                        style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">
                        <i class="ri-whatsapp-line"></i> Hubungi Kami
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('scripts')
    <script>
        // Additional animations for homepage
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🏠 Homepage loaded');
        });
    </script>
@endpush
