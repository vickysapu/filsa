@extends('layouts.public')

@section('title', 'Events & Agenda - Gereja Filadelfia')

@push('styles')
<style>
    .agenda-list {
        max-width: 1080px;
        margin: 0 auto;
        grid-template-columns: minmax(0, 1fr) !important;
        gap: 0 !important;
        border-top: 1px solid var(--zaitun-border);
    }

    .agenda-entry {
        display: grid !important;
        grid-template-columns: minmax(220px, 30%) minmax(0, 1fr);
        min-height: 250px;
        border: 0 !important;
        border-bottom: 1px solid var(--zaitun-border) !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .agenda-page .agenda-entry:hover {
        transform: none !important;
        border-color: var(--zaitun-border) !important;
    }

    .agenda-photo {
        height: auto !important;
        min-height: 220px;
        align-self: stretch;
        background: var(--zaitun-cream);
    }

    .agenda-copy {
        padding: 1.6rem 2rem !important;
    }

    .agenda-entry:first-child .agenda-photo {
        min-height: 280px;
    }

    .agenda-entry:first-child .agenda-copy {
        padding: 2rem 2.5rem !important;
    }

    .agenda-date {
        top: auto !important;
        bottom: 1rem;
        left: 1rem !important;
        border-radius: 2px !important;
        box-shadow: none !important;
        min-width: 66px;
        padding: 0.6rem 0.75rem !important;
    }

    .agenda-date div:first-child {
        font-size: 1.75rem !important;
    }

    .agenda-date div:last-child {
        letter-spacing: 0.08em;
    }

    .agenda-image-note {
        position: absolute;
        right: 0.65rem;
        bottom: 0.65rem;
        padding: 0.25rem 0.45rem;
        background: rgba(16, 59, 43, 0.88);
        color: var(--zaitun-sand);
        font-size: 0.65rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .agenda-copy > div:first-child {
        letter-spacing: 0.04em;
    }

    @media (max-width: 700px) {
        .agenda-entry {
            grid-template-columns: 1fr;
        }

        .agenda-photo,
        .agenda-entry:first-child .agenda-photo {
            min-height: 220px;
        }

        .agenda-copy,
        .agenda-entry:first-child .agenda-copy {
            padding: 1.3rem 0 !important;
        }
    }
</style>
@endpush

@section('content')

    <!-- Subpage Hero Header -->
    <header class="page-hero public-page-hero public-page-hero--events">
        <div class="page-hero-container fade-in">
            <nav class="page-breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Beranda</a>
                <i class="ri-arrow-right-s-line"></i>
                <span style="color: var(--zaitun-primary);">Events & Agenda</span>
            </nav>
            <span class="page-kicker">Kalender Kegiatan</span>
            <h1 class="page-hero-title">Agenda & Acara Gereja</h1>
            <p class="page-hero-desc">
                Bergabunglah dalam berbagai kegiatan rohani, seminar keluarga, dan persekutuan untuk memperkuat iman serta kebersamaan jemaat.
            </p>
        </div>
        <span class="page-image-note">Foto ilustrasi · bukan dokumentasi acara</span>
    </header>

    <!-- Events List Section -->
    <section class="content-container agenda-page">
        <div class="agenda-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 2.2rem;">
            @forelse($events as $event)
                <article class="editorial-card agenda-entry fade-in" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                    <div class="agenda-photo" style="position: relative; height: 210px; background: var(--zaitun-deep); overflow: hidden;">
                        @if ($event->gambar_featured)
                            <img src="{{ asset('storage/' . preg_replace('#^(?:/?storage/)+#', '', $event->gambar_featured)) }}" alt="{{ $event->nama_event }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">
                        @else
                            <img src="https://images.unsplash.com/photo-1507692049790-de58290a4334?w=900&auto=format&fit=crop&q=80" alt="Foto ilustrasi suasana ibadah, bukan dokumentasi acara" style="width: 100%; height: 100%; object-fit: cover;">
                            <span class="agenda-image-note">Foto ilustrasi</span>
                        @endif

                        <!-- Date Badge Pill -->
                        <div class="agenda-date" style="position: absolute; top: 14px; left: 14px; background: #FFFFFF; border-radius: 12px; padding: 0.4rem 0.8rem; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                            <div style="font-family: 'Playfair Display', serif; font-size: 1.3rem; font-weight: 800; color: var(--zaitun-deep); line-height: 1;">
                                {{ $event->tanggal_mulai ? $event->tanggal_mulai->format('d') : '01' }}
                            </div>
                            <div style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--zaitun-terracotta);">
                                {{ $event->tanggal_mulai ? $event->tanggal_mulai->format('M') : 'BLN' }}
                            </div>
                        </div>

                        @if ($event->kategori)
                            <div style="position: absolute; top: 14px; right: 14px; background: rgba(28, 36, 23, 0.75); backdrop-filter: blur(4px); color: var(--zaitun-gold-light); font-size: 0.75rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: var(--radius-pill);">
                                {{ $event->kategori->nama_kategori }}
                            </div>
                        @endif
                    </div>

                    <div class="agenda-copy" style="padding: 1.8rem; display: flex; flex-direction: column; flex: 1;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--zaitun-primary); font-size: 0.85rem; font-weight: 700; margin-bottom: 0.6rem;">
                            <i class="ri-time-line"></i>
                            <span>{{ $event->tanggal_mulai ? $event->tanggal_mulai->format('d M Y • H:i') : '' }} WIB</span>
                        </div>

                        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 800; color: var(--zaitun-deep-dark); margin-bottom: 0.6rem; line-height: 1.3;">
                            {{ $event->nama_event }}
                        </h3>

                        <p style="color: var(--zaitun-text-secondary); font-size: 0.92rem; line-height: 1.6; margin-bottom: 1.2rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $event->deskripsi }}
                        </p>

                        <div style="display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.88rem; color: var(--zaitun-text-muted); margin-bottom: 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.45rem;">
                                <i class="ri-map-pin-line" style="color: var(--zaitun-gold);"></i>
                                <span>{{ $event->lokasi ?? 'Gereja Filadelfia' }}</span>
                            </div>
                            @if ($event->pembicara)
                                <div style="display: flex; align-items: center; gap: 0.45rem;">
                                    <i class="ri-user-voice-line" style="color: var(--zaitun-gold);"></i>
                                    <span>{{ $event->pembicara }}</span>
                                </div>
                            @endif
                        </div>

                        <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--zaitun-border-subtle); display: flex; justify-content: space-between; align-items: center;">
                            <a href="{{ route('public.event.detail', $event->slug) }}" class="btn-primary-olive" style="padding: 0.6rem 1.3rem; font-size: 0.88rem;">
                                <span>Detail Acara</span>
                                <i class="ri-arrow-right-line"></i>
                            </a>
                            @if ($event->harga_tiket > 0)
                                <span style="font-weight: 700; color: var(--zaitun-terracotta); font-size: 0.95rem;">
                                    Rp {{ number_format($event->harga_tiket, 0, ',', '.') }}
                                </span>
                            @else
                                <span style="font-size: 0.82rem; font-weight: 700; color: var(--zaitun-primary); text-transform: uppercase;">
                                    Gratis / Terbuka
                                </span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem;" class="editorial-card">
                    <i class="ri-calendar-line" style="font-size: 3.5rem; color: var(--zaitun-primary); opacity: 0.4; margin-bottom: 1rem; display: block;"></i>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--zaitun-deep-dark); margin-bottom: 0.5rem;">Belum Ada Agenda Mendatang</h3>
                    <p style="color: var(--zaitun-text-secondary); max-width: 500px; margin: 0 auto 1.5rem;">
                        Saat ini belum ada event khusus yang dijadwalkan. Silakan ikuti ibadah raya rutin kami setiap hari Minggu.
                    </p>
                    <a href="{{ route('public.home') }}#jadwal-ibadah" class="btn-primary-olive">
                        <i class="ri-time-line"></i>
                        <span>Lihat Jadwal Ibadah Rutin</span>
                    </a>
                </div>
            @endforelse
        </div>

        @if ($events->hasPages())
            <div style="margin-top: 3.5rem; display: flex; justify-content: center;">
                {{ $events->links() }}
            </div>
        @endif
    </section>

@endsection
