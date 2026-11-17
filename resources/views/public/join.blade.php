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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .join-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .meeting-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }

        .btn-join {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            border-radius: 10px;
            width: 100%;
            transition: transform 0.2s;
        }

        .btn-join:hover {
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
    </style>
</head>

<body>
    <div class="join-card">
        <div class="text-center mb-4">
            <i class="ri-video-line" style="font-size: 3rem; color: #667eea;"></i>
            <h2 class="mt-3 mb-1">{{ $meeting->nama_meeting }}</h2>
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
