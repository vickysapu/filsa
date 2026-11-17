@extends('layouts.admin')

@section('title', 'Dashboard Gereja')

@section('content')
    <style>
        .dashboard-wrapper {
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            color: white;
            border: none;
            border-radius: 20px;
            overflow: hidden;
        }

        [data-theme="dark"] .welcome-card {
            background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
        }

        .text-dashboard-welcome {
            color: white !important;
        }

        .dashboard-quote {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .dashboard-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(5px);
            padding: 0.6rem 1rem;
            border-radius: 50px;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dashboard-bg-icon {
            font-size: 10rem;
            color: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .user-avatar-large {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 2rem;
            font-weight: 700;
            backdrop-filter: blur(5px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        /* Coming Soon Card */
        .coming-soon-card {
            border: none;
            border-radius: 20px;
            background: var(--bg-surface, #fff);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            min-height: 350px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .coming-soon-card:hover {
            transform: translateY(-5px);
        }

        .coming-soon-icon {
            font-size: 5rem;
            color: #2563eb;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .coming-soon-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary, #1f2937);
            margin-bottom: 0.75rem;
        }

        .coming-soon-text {
            color: var(--text-secondary, #6b7280);
            max-width: 500px;
            margin: auto;
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        @media (max-width: 768px) {
            .user-avatar-large {
                width: 65px;
                height: 65px;
                font-size: 1.5rem;
            }

            .coming-soon-title {
                font-size: 1.5rem;
            }

            .coming-soon-icon {
                font-size: 4rem;
            }
        }
    </style>
    <div class="dashboard-wrapper">

        <!-- Welcome Section -->
        <div class="card-custom mb-4 welcome-card position-relative overflow-hidden">
            <div class="card-body p-4 position-relative z-1">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <h1 class="h3 mb-2 fw-bold text-dashboard-welcome">
                            Shalom, {{ $user->name }}! 👋
                        </h1>

                        <p class="mb-4 dashboard-quote">
                            "Segala perkara dapat kutanggung di dalam Dia yang memberi kekuatan kepadaku."
                            - Filipi 4:13
                        </p>

                        <div class="d-flex flex-wrap gap-3">
                            <div class="dashboard-badge">
                                <i class="ri-calendar-check-line"></i>
                                <span class="small">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</span>
                            </div>

                            <div class="dashboard-badge">
                                <i class="ri-time-line"></i>
                                <span class="small" id="clock">00:00:00</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 text-lg-end d-none d-lg-block">
                        <div class="user-avatar-large d-inline-flex align-items-center justify-content-center">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="position-absolute top-0 end-0 mt-n5 me-n5 dashboard-bg-icon">
                <i class="ri-church-line"></i>
            </div>
        </div>

        <!-- Coming Soon Content -->
        <div class="card-custom">
            <div class="card-body py-5 text-center">
                <i class="ri-dashboard-3-line display-1 text-primary-custom mb-3"></i>

                <h3 class="fw-bold mb-2">
                    Dashboard Akan Segera Tampil
                </h3>

                <p class="text-muted-custom mb-0">
                    Fitur dashboard sedang dalam tahap pengembangan dan akan segera tersedia.
                </p>
            </div>
        </div>

    </div>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID');
        }

        setInterval(updateClock, 1000);
        updateClock();
    </script>
@endsection
