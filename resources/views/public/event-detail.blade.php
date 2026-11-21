@extends('layouts.public')

@section('title', $event->nama_event . ' - Gereja Filadelfia')

@push('styles')
<style>
    .page-hero.event-detail-hero {
        min-height: 390px;
        display: flex;
        align-items: flex-end;
        padding-top: 9rem;
        padding-bottom: 4rem;
        background: var(--zaitun-cream);
    }

    .event-detail-hero.has-cover {
        color: var(--zaitun-sand);
        background: var(--zaitun-deep-dark);
    }

    .event-detail-hero.has-cover > div:first-child::after {
        position: absolute;
        inset: 0;
        content: '';
        background: linear-gradient(90deg, rgba(16, 59, 43, 0.8), rgba(16, 59, 43, 0.1)), linear-gradient(0deg, rgba(16, 59, 43, 0.48), transparent 80%);
    }

    .event-illustration-label {
        position: absolute;
        right: 2rem;
        bottom: 1.5rem;
        z-index: 1;
        color: var(--zaitun-sand);
        font-size: 0.68rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .event-detail-hero .page-hero-container {
        width: 100%;
        text-align: center;
    }

    .event-detail-hero .page-breadcrumbs {
        justify-content: center;
    }

    .event-detail-hero .page-hero-title {
        max-width: 900px;
        font-size: clamp(2.4rem, 5vw, 4.2rem);
    }

    .event-detail-hero .page-hero-desc {
        max-width: 700px;
    }

    .event-detail-page .event-detail-layout {
        grid-template-columns: minmax(0, 1.6fr) minmax(280px, 0.8fr) !important;
        align-items: start;
        gap: clamp(2.5rem, 6vw, 5.5rem) !important;
    }

    .event-story {
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .event-story h2,
    .event-story h3 {
        font-weight: 500 !important;
    }

    .event-story > div {
        max-width: 700px;
    }

    .event-story > div:last-child > div {
        border-radius: 2px !important;
        box-shadow: none !important;
    }

    .event-facts {
        position: sticky;
        top: 96px;
        padding: 1.5rem 0 0 !important;
        border: 0 !important;
        border-top: 2px solid var(--zaitun-deep) !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .event-facts h2 {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        font-size: 0.78rem !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 0.12em;
    }

    .event-facts > div:nth-child(2) > div {
        gap: 0.75rem !important;
    }

    .event-facts > div:nth-child(2) > div > div:first-child {
        display: none !important;
    }

    @media (max-width: 800px) {
        .event-detail-page .event-detail-layout {
            grid-template-columns: 1fr !important;
        }

        .event-facts {
            position: static;
        }
    }
</style>
@endpush

@section('content')

    <!-- Subpage Hero Header -->
    <header class="page-hero event-detail-hero has-cover" style="position: relative; overflow: hidden;">
        <div style="position: absolute; inset: 0; z-index: 0;">
            @if ($event->gambar_featured)
                <img src="{{ asset('storage/' . preg_replace('#^(?:/?storage/)+#', '', $event->gambar_featured)) }}" alt="" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1507692049790-de58290a4334?w=1800&amp;auto=format&amp;fit=crop&amp;q=82'; this.parentElement.querySelector('.event-illustration-label').hidden=false;" style="width: 100%; height: 100%; object-fit: cover; filter: brightness(0.28);">
                <span class="event-illustration-label" hidden>Foto ilustrasi · bukan dokumentasi acara</span>
            @else
                <img src="https://images.unsplash.com/photo-1507692049790-de58290a4334?w=1800&auto=format&fit=crop&q=82" alt="" style="width: 100%; height: 100%; object-fit: cover; filter: brightness(0.28);">
                <span class="event-illustration-label">Foto ilustrasi · bukan dokumentasi acara</span>
            @endif
        </div>

        <div class="page-hero-container fade-in" style="position: relative; z-index: 1; {{ $event->gambar_featured ? 'color: #FFFFFF;' : '' }}">
            <nav class="page-breadcrumbs" aria-label="Breadcrumb" style="color: rgba(255,255,255,0.7);">
                <a href="{{ route('public.home') }}">Beranda</a>
                <i class="ri-arrow-right-s-line"></i>
                <a href="{{ route('public.events') }}">Events</a>
                <i class="ri-arrow-right-s-line"></i>
                <span style="color: var(--zaitun-gold-light);">{{ Str::limit($event->nama_event, 30) }}</span>
            </nav>

            <span class="page-kicker" style="color: var(--zaitun-gold-light);">
                {{ $event->kategori->nama_kategori ?? 'Agenda Gereja' }}
            </span>

            <h1 class="page-hero-title" style="color: #FFFFFF;">
                {{ $event->nama_event }}
            </h1>

            <p class="page-hero-desc" style="color: rgba(255,255,255,0.9);">
                {{ $event->deskripsi }}
            </p>
        </div>
    </header>

    <!-- Event Detail Body -->
    <section class="content-container event-detail-page">
        <div class="event-detail-layout lux-grid-2" style="display: grid; grid-template-columns: 2fr 1fr; gap: 3.5rem;">
            <!-- Left Main Column -->
            <div class="fade-in">
                <div class="editorial-card event-story" style="margin-bottom: 2.5rem;">
                    <h2 style="font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 800; color: var(--zaitun-deep-dark); margin-bottom: 1.5rem;">
                        Tentang Acara Ini
                    </h2>

                    <div style="font-size: 1.05rem; line-height: 1.85; color: var(--zaitun-text-secondary); margin-bottom: 2rem;">
                        @if ($event->deskripsi_lengkap)
                            {!! nl2br(e($event->deskripsi_lengkap)) !!}
                        @else
                            {{ $event->deskripsi }}
                        @endif
                    </div>

                    @if ($event->gambar_galeri && count($event->gambar_galeri) > 0)
                        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 800; margin-bottom: 1rem; color: var(--zaitun-deep-dark);">
                            Dokumentasi & Poster Kegiatan
                        </h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; margin-top: 1rem;">
                            @foreach ($event->gambar_galeri as $image)
                                <div style="border-radius: 12px; overflow: hidden; aspect-ratio: 1; cursor: pointer; box-shadow: var(--shadow-subtle);" onclick="window.open('{{ asset('storage/' . $image) }}', '_blank')">
                                    <img src="{{ asset('storage/' . $image) }}" alt="Dokumentasi Event" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Sidebar Column -->
            <div class="fade-in">
                <div class="editorial-card event-facts" style="position: sticky; top: 90px;">
                    <h2 style="font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--zaitun-deep-dark); padding-bottom: 0.8rem; border-bottom: 1px solid var(--zaitun-border-subtle);">
                        Informasi Jadwal
                    </h2>

                    <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 2rem;">
                        <!-- Tanggal -->
                        <div style="display: flex; gap: 1rem;">
                            <div style="width: 44px; height: 44px; background: rgba(82, 110, 59, 0.1); color: var(--zaitun-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                <i class="ri-calendar-event-line"></i>
                            </div>
                            <div>
                                <span style="display: block; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; color: var(--zaitun-text-muted); letter-spacing: 0.08em;">Tanggal Pelaksanaan</span>
                                <strong style="color: var(--zaitun-deep-dark); font-size: 1rem;">{{ $event->tanggal_mulai ? $event->tanggal_mulai->format('d F Y') : '-' }}</strong>
                                <span style="display: block; font-size: 0.88rem; color: var(--zaitun-text-secondary);">Pukul {{ $event->tanggal_mulai ? $event->tanggal_mulai->format('H:i') : '' }} WIB</span>
                            </div>
                        </div>

                        <!-- Lokasi -->
                        <div style="display: flex; gap: 1rem;">
                            <div style="width: 44px; height: 44px; background: rgba(192, 138, 62, 0.12); color: var(--zaitun-gold); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                <i class="ri-map-pin-line"></i>
                            </div>
                            <div>
                                <span style="display: block; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; color: var(--zaitun-text-muted); letter-spacing: 0.08em;">Tempat & Ruangan</span>
                                <strong style="color: var(--zaitun-deep-dark); font-size: 1rem;">{{ $event->lokasi ?? 'Gereja Filadelfia' }}</strong>
                                @if ($event->alamat_lengkap)
                                    <span style="display: block; font-size: 0.85rem; color: var(--zaitun-text-secondary);">{{ $event->alamat_lengkap }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Pembicara -->
                        @if ($event->pembicara)
                            <div style="display: flex; gap: 1rem;">
                                <div style="width: 44px; height: 44px; background: rgba(52, 67, 44, 0.1); color: var(--zaitun-deep); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                    <i class="ri-user-voice-line"></i>
                                </div>
                                <div>
                                    <span style="display: block; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; color: var(--zaitun-text-muted); letter-spacing: 0.08em;">Pembicara / Pelayan</span>
                                    <strong style="color: var(--zaitun-deep-dark); font-size: 1rem;">{{ $event->pembicara }}</strong>
                                </div>
                            </div>
                        @endif

                        <!-- Biaya Tiket -->
                        <div style="display: flex; gap: 1rem;">
                            <div style="width: 44px; height: 44px; background: rgba(200, 90, 50, 0.1); color: var(--zaitun-terracotta); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                <i class="ri-ticket-line"></i>
                            </div>
                            <div>
                                <span style="display: block; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; color: var(--zaitun-text-muted); letter-spacing: 0.08em;">Biaya Partisipasi</span>
                                @if ($event->harga_tiket > 0)
                                    <strong style="color: var(--zaitun-terracotta); font-size: 1.2rem;">Rp {{ number_format($event->harga_tiket, 0, ',', '.') }}</strong>
                                @else
                                    <strong style="color: var(--zaitun-primary); font-size: 1.05rem;">Gratis / Terbuka untuk Umum</strong>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                        <a href="https://wa.me/6281234567890?text=Halo%20Admin,%20saya%20ingin%20mendaftar%20atau%20bertanya%20seputar%20event:%20{{ urlencode($event->nama_event) }}" target="_blank" class="btn-terracotta-cta" style="justify-content: center; width: 100%;">
                            <i class="ri-whatsapp-line"></i>
                            <span>Konfirmasi / Hubungi Panitia</span>
                        </a>

                        <a href="{{ route('public.events') }}" class="btn-outline-sand" style="justify-content: center; width: 100%;">
                            <i class="ri-arrow-left-line"></i>
                            <span>Kembali ke Kalender Events</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
