@extends('layouts.admin')

@section('title', 'Detail Event')

@section('content')
    <div class="lux-container">

        <div class="mb-5">
            <a href="{{ route('admin.events.index') }}" class="lux-btn lux-btn-outline lux-btn-sm">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
            <h1 class="text-gradient" style="font-size: 2rem; font-weight: 800; margin: 15px 0 5px;">{{ $event->nama_event }}
            </h1>
        </div>

        <div class="lux-grid lux-grid-3" style="gap: 1.5rem;">

            <!-- Main Info -->
            <div class="lux-card" style="grid-column: span 2;">

                @if ($event->gambar_featured)
                    <img src="{{ asset('storage/' . $event->gambar_featured) }}" alt="{{ $event->nama_event }}"
                        style="width: 100%; border-radius: var(--radius); margin-bottom: 1.5rem;">
                @endif

                <div class="lux-card-header">
                    <h3 class="lux-card-title">Informasi Event</h3>
                    <span class="lux-badge {{ $event->status_label['color'] }}">{{ $event->status_label['label'] }}</span>
                </div>

                <div class="lux-grid lux-grid-2" style="gap: 1.5rem; margin-bottom: 2rem;">
                    <div>
                        <p class="lux-label">Tanggal & Waktu</p>
                        <p style="font-weight: 600;">{{ $event->tanggal_mulai->format('d F Y, H:i') }} WIB</p>
                        @if ($event->tanggal_selesai)
                            <p class="text-muted" style="font-size: 0.85rem;">Sampai:
                                {{ $event->tanggal_selesai->format('d F Y, H:i') }} WIB</p>
                        @endif
                    </div>
                    <div>
                        <p class="lux-label">Lokasi</p>
                        <p style="font-weight: 600;">{{ $event->lokasi }}</p>
                        @if ($event->alamat_lengkap)
                            <p class="text-muted" style="font-size: 0.85rem;">{{ $event->alamat_lengkap }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="lux-label">Kategori</p>
                        <span class="lux-badge"
                            style="background: {{ $event->kategori->warna }}20; color: {{ $event->kategori->warna }};">
                            {{ $event->kategori->nama_kategori }}
                        </span>
                    </div>
                    <div>
                        <p class="lux-label">Pembicara</p>
                        <p style="font-weight: 600;">{{ $event->pembicara ?? '-' }}</p>
                    </div>
                </div>

                @if ($event->deskripsi_lengkap)
                    <div style="margin-bottom: 2rem;">
                        <p class="lux-label">Deskripsi Lengkap</p>
                        <div style="line-height: 1.8;">{{ nl2br(e($event->deskripsi_lengkap)) }}</div>
                    </div>
                @endif

                @if ($event->gambar_galeri)
                    <div>
                        <p class="lux-label">Galeri</p>
                        <div class="lux-grid lux-grid-3" style="gap: 0.5rem;">
                            @foreach ($event->gambar_galeri as $image)
                                <img src="{{ asset('storage/' . $image) }}" alt="Gallery"
                                    style="width: 100%; border-radius: 8px; cursor: pointer;"
                                    onclick="window.open(this.src, '_blank')">
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div>
                <div class="lux-card mb-4">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Kontak</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        @if ($event->kontak_person)
                            <div>
                                <p class="lux-label">Kontak Person</p>
                                <p style="font-weight: 600;">{{ $event->kontak_person }}</p>
                            </div>
                        @endif
                        @if ($event->email_kontak)
                            <div>
                                <p class="lux-label">Email</p>
                                <p style="font-weight: 600;">{{ $event->email_kontak }}</p>
                            </div>
                        @endif
                        @if ($event->whatsapp_kontak)
                            <div>
                                <p class="lux-label">WhatsApp</p>
                                <p style="font-weight: 600;">{{ $event->whatsapp_kontak }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="lux-card">
                    <div class="lux-card-header">
                        <h3 class="lux-card-title">Info</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div>
                            <p class="lux-label">Harga Tiket</p>
                            <p style="font-weight: 600;">Rp {{ number_format($event->harga_tiket, 0, ',', '.') }}</p>
                        </div>
                        @if ($event->kuota_peserta)
                            <div>
                                <p class="lux-label">Kuota</p>
                                <p style="font-weight: 600;">{{ $event->jumlah_terdaftar }}/{{ $event->kuota_peserta }}
                                </p>
                            </div>
                        @endif
                        <div>
                            <p class="lux-label">Dibuat Oleh</p>
                            <p style="font-weight: 600;">{{ $event->creator->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
