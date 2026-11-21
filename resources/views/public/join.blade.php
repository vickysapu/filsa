{{-- resources/views/public/meetings/join.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Meeting - {{ $meeting->nama_meeting }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f1e7;
            min-height: 100vh;
            margin: 0;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        }

        .join-hero {
            min-height: 390px;
            padding: 5rem 1.5rem 7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            text-align: center;
            color: white;
            background:
                linear-gradient(90deg, rgba(16, 59, 43, 0.88), rgba(16, 59, 43, 0.55)),
                url('https://images.unsplash.com/photo-1511988617509-a57c8a288659?w=1800&auto=format&fit=crop&q=82') center 45% / cover;
        }

        .join-hero-content {
            max-width: 860px;
            position: relative;
            z-index: 1;
        }

        .join-hero-kicker {
            display: block;
            margin-bottom: 1rem;
            color: #e4d4ae;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .join-hero h1 {
            margin: 0;
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(2.2rem, 5vw, 4rem);
            font-weight: 500;
            line-height: 1.12;
        }

        .join-photo-note {
            position: absolute;
            right: 1.25rem;
            bottom: 1rem;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.65rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .join-card {
            position: relative;
            margin: -4rem auto 3rem;
            background: white;
            border-radius: 2px;
            padding: 2rem;
            max-width: 660px;
            width: calc(100% - 2rem);
            box-shadow: 0 16px 42px rgba(16, 59, 43, 0.12);
        }

        .meeting-info {
            background: #FAF7EF;
            border: 1px solid #E4DCBF;
            border-radius: 16px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }

        .btn-join {
            background: #174C35;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.05rem;
            font-weight: 700;
            color: white;
            border-radius: 50px;
            width: 100%;
            transition: all 0.25s ease;
        }

        .btn-join:hover {
            background: #103B2B;
            transform: translateY(-2px);
            color: white;
        }

        .platform-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        @media (max-width: 600px) {
            .join-hero {
                min-height: 340px;
                padding: 4.5rem 1.25rem 6rem;
            }

            .join-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <header class="join-hero">
        <div class="join-hero-content">
            <span class="join-hero-kicker">Pertemuan Online</span>
            <h1>{{ $meeting->nama_meeting }}</h1>
        </div>
        <span class="join-photo-note">Foto ilustrasi · bukan dokumentasi pertemuan</span>
    </header>
    <div class="join-card">
        <div class="text-center mb-4">
            @if ($meeting->platform === 'jitsi')
                <span class="platform-badge" style="background: #1d84ff20; color: #1d84ff;">
                    <i class="ri-video-line"></i> Jitsi Meet
                </span>
            @elseif($meeting->platform === 'zoom')
                <span class="platform-badge" style="background: #2d8cff20; color: #2d8cff;">
                    <i class="ri-camera-line"></i> Zoom
                </span>
            @else
                <span class="platform-badge" style="background: #00877920; color: #008779;">
                    <i class="ri-camera-lens-line"></i> Google Meet
                </span>
            @endif
        </div>

        <div class="meeting-info">
            <div class="mb-3">
                <strong><i class="ri-calendar-line"></i> Tanggal & Waktu:</strong>
                <p class="mb-0">{{ $meeting->tanggal_mulai->format('d F Y, H:i') }} WIB</p>
            </div>
            @if ($meeting->deskripsi)
                <div>
                    <strong><i class="ri-information-line"></i> Deskripsi:</strong>
                    <p class="mb-0">{{ $meeting->deskripsi }}</p>
                </div>
            @endif
        </div>

        @if ($meeting->tanggal_mulai <= now() && (!$meeting->tanggal_selesai || $meeting->tanggal_selesai >= now()))
            <!-- Meeting sedang berlangsung -->
            <a href="{{ $meeting->meeting_url }}" target="_blank" class="btn btn-join mb-3">
                <i class="ri-video-add-line"></i> JOIN MEETING SEKARANG
            </a>
            <p class="text-center text-muted small">
                <i class="ri-live-line"></i> Meeting sedang berlangsung
            </p>
        @elseif($meeting->tanggal_mulai > now())
            <!-- Meeting belum mulai -->
            <button class="btn btn-secondary btn-join mb-3" disabled>
                <i class="ri-time-line"></i> Meeting Belum Dimulai
            </button>
            <p class="text-center text-muted small">
                Meeting akan dimulai pada {{ $meeting->tanggal_mulai->format('d M Y, H:i') }} WIB
            </p>
        @else
            <!-- Meeting sudah selesai -->
            <button class="btn btn-secondary btn-join mb-3" disabled>
                <i class="ri-checkbox-circle-line"></i> Meeting Selesai
            </button>
            <p class="text-center text-muted small">
                Meeting ini telah selesai pada {{ $meeting->tanggal_selesai->format('d M Y, H:i') }} WIB
            </p>
        @endif

        <div class="text-center mt-4">
            <a href="{{ url('/') }}" class="text-muted">
                <i class="ri-home-line"></i> Kembali ke Homepage
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
