@extends('layouts.public')

@section('title', 'Events - Gereja Filadelfia')

@section('content')

    <!-- Hero Section -->
    <section class="hero-section"
        style="min-height: 60vh; background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.1));">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="ri-calendar-event-line"></i> Events
            </div>
            <h1 class="hero-title">Events Gereja</h1>
            <p class="hero-subtitle">
                Bergabunglah dalam berbagai events dan kegiatan rohani
                untuk memperkuat iman dan persekutuan.
            </p>
        </div>
    </section>

    <!-- Events List -->
    <section class="section">
        <div class="section-header fade-in">
            <h2 class="section-title">Semua Events</h2>
        </div>

        <div class="card-grid">
            @forelse($events as $event)
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
                            {{ $event->tanggal_mulai->format('d M Y') }} • {{ $event->tanggal_mulai->format('H:i') }} WIB
                        </div>
                        <h3 class="event-title">{{ $event->nama_event }}</h3>
                        <p
                            style="color: var(--brand-dark); opacity: 0.7; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $event->deskripsi }}
                        </p>
                        <div class="event-location">
                            <i class="ri-map-pin-line"></i>
                            {{ $event->lokasi }}
                        </div>
                        @if ($event->pembicara)
                            <div
                                style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; color: var(--brand-dark); opacity: 0.7; font-size: 0.9rem;">
                                <i class="ri-mic-line"></i>
                                {{ $event->pembicara }}
                            </div>
                        @endif
                        <a href="{{ route('public.event.detail', $event->slug) }}" class="event-btn">
                            Detail Event <i class="ri-arrow-right-line"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem;">
                    <i class="ri-calendar-off-line"
                        style="font-size: 4rem; color: var(--brand-primary); opacity: 0.3; margin-bottom: 1rem;"></i>
                    <p style="font-size: 1.2rem; color: var(--brand-dark); opacity: 0.7;">Belum ada events</p>
                </div>
            @endforelse
        </div>
    </section>

@endsection
