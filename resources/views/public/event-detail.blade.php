@extends('layouts.public')

@section('title', $event->nama_event . ' - Gereja Filadelfia')

@section('content')

    <!-- Hero Section -->
    <section class="hero-section" style="min-height: 50vh;">
        @if ($event->gambar_featured)
            <img src="{{ asset('storage/' . $event->gambar_featured) }}" alt="{{ $event->nama_event }}"
                style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: -1;">
            <div
                style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.7)); z-index: -1;">
            </div>
        @endif
        <div class="hero-content" style="color: white;">
            <div class="hero-badge"
                style="background: rgba(255,255,255,0.2); border-color: rgba(255,255,255,0.3); color: white;">
                <i class="ri-calendar-event-line"></i> {{ $event->kategori->nama_kategori }}
            </div>
            <h1 class="hero-title" style="color: white; -webkit-text-fill-color: white;">{{ $event->nama_event }}</h1>
            <p class="hero-subtitle" style="color: rgba(255,255,255,0.9);">{{ $event->deskripsi }}</p>
        </div>
    </section>

    <!-- Event Details -->
    <section class="section">
        <div class="lux-grid lux-grid-3" style="gap: 2rem;">
            <div class="lux-card" style="grid-column: span 2;">
                <h2 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--brand-dark);">Detail
                    Event</h2>

                @if ($event->deskripsi_lengkap)
                    <div style="line-height: 1.8; color: var(--brand-dark); opacity: 0.8; margin-bottom: 2rem;">
                        {!! nl2br(e($event->deskripsi_lengkap)) !!}
                    </div>
                @endif

                @if ($event->gambar_galeri)
                    <h3 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 1rem;">Galeri</h3>
                    <div class="lux-grid lux-grid-3" style="gap: 0.5rem; margin-bottom: 2rem;">
                        @foreach ($event->gambar_galeri as $image)
                            <img src="{{ asset('storage/' . $image) }}" alt="Gallery"
                                style="width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 12px; cursor: pointer;"
                                onclick="window.open(this.src, '_blank')">
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <div class="lux-card mb-4">
                    <h3 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 1.5rem;">Informasi</h3>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                <i class="ri-calendar-line" style="color: var(--brand-primary); font-size: 1.2rem;"></i>
                                <strong>Tanggal</strong>
                            </div>
                            <p style="margin-left: 2rem; color: var(--brand-dark); opacity: 0.8;">
                                {{ $event->tanggal_mulai->format('d F Y') }}
                            </p>
                            <p style="margin-left: 2rem; color: var(--brand-dark); opacity: 0.7; font-size: 0.9rem;">
                                {{ $event->tanggal_mulai->format('H:i') }} WIB
                            </p>
                        </div>

                        <div>
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                <i class="ri-map-pin-line" style="color: var(--brand-primary); font-size: 1.2rem;"></i>
                                <strong>Lokasi</strong>
                            </div>
                            <p style="margin-left: 2rem; color: var(--brand-dark); opacity: 0.8;">{{ $event->lokasi }}</p>
                            @if ($event->alamat_lengkap)
                                <p style="margin-left: 2rem; color: var(--brand-dark); opacity: 0.7; font-size: 0.9rem;">
                                    {{ $event->alamat_lengkap }}
                                </p>
                            @endif
                        </div>

                        @if ($event->pembicara)
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                    <i class="ri-mic-line" style="color: var(--brand-primary); font-size: 1.2rem;"></i>
                                    <strong>Pembicara</strong>
                                </div>
                                <p style="margin-left: 2rem; color: var(--brand-dark); opacity: 0.8;">
                                    {{ $event->pembicara }}</p>
                            </div>
                        @endif

                        @if ($event->harga_tiket > 0)
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                    <i class="ri-ticket-line" style="color: var(--brand-primary); font-size: 1.2rem;"></i>
                                    <strong>Harga Tiket</strong>
                                </div>
                                <p
                                    style="margin-left: 2rem; color: var(--brand-dark); opacity: 0.8; font-size: 1.2rem; font-weight: 700;">
                                    Rp {{ number_format($event->harga_tiket, 0, ',', '.') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="lux-card">
                    <h3 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 1.5rem;">Kontak</h3>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        @if ($event->kontak_person)
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="ri-user-line" style="color: var(--brand-primary);"></i>
                                <span>{{ $event->kontak_person }}</span>
                            </div>
                        @endif
                        @if ($event->whatsapp_kontak)
                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', $event->whatsapp_kontak) }}"
                                class="lux-btn lux-btn-primary" style="justify-content: center;">
                                <i class="ri-whatsapp-line"></i> Hubungi via WhatsApp
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
