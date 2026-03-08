@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="lux-container">
    
    <!-- Welcome Section -->
    <div class="lux-card mb-5">
        <div class="flex-between">
            <div>
                <h1 class="text-2xl text-bold">Selamat Datang, {{ $user->name }}! 👋</h1>
                <p class="text-muted mt-2">
                    Login terakhir: {{ $user->last_login_at ? $user->last_login_at->format('d M Y, H:i') : 'Pertama kali' }}
                </p>
                <p class="text-muted">
                    Role: 
                    @foreach($user->roles as $role)
                    <span class="lux-badge lux-badge-primary">{{ $role->display_name }}</span>
                    @endforeach
                </p>
            </div>
            <div class="lux-avatar lux-avatar-xl">
                {{ substr($user->name, 0, 1) }}
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="lux-grid lux-grid-4 mb-5">
        <div class="lux-card">
            <p class="text-muted text-sm">Total Users</p>
            <h3 class="text-2xl text-bold text-primary">{{ $stats['total_users'] }}</h3>
        </div>
        <div class="lux-card">
            <p class="text-muted text-sm">Users Aktif</p>
            <h3 class="text-2xl text-bold text-success">{{ $stats['active_users'] }}</h3>
        </div>
        <div class="lux-card">
            <p class="text-muted text-sm">Login (7 Hari)</p>
            <h3 class="text-2xl text-bold text-info">{{ $stats['recent_logins'] }}</h3>
        </div>
        <div class="lux-card">
            <p class="text-muted text-sm">Login Gagal</p>
            <h3 class="text-2xl text-bold text-danger">{{ $stats['failed_logins'] }}</h3>
        </div>
    </div>

    <!-- Recent Login Activity -->
    <div class="lux-card">
        <div class="lux-card-header">
            <h3 class="lux-card-title">Riwayat Login Anda</h3>
        </div>
        <div class="lux-table-container">
            <table class="lux-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>IP Address</th>
                        <th>Device</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogins as $activity)
                    <tr>
                        <td>{{ $activity->attempted_at->diffForHumans() }}</td>
                        <td><code>{{ $activity->ip_address }}</code></td>
                        <td class="text-muted text-sm">
                            @if(str_contains($activity->user_agent, 'Chrome'))
                            <i class="ri-chrome-line"></i> Chrome
                            @elseif(str_contains($activity->user_agent, 'Firefox'))
                            <i class="ri-firefox-line"></i> Firefox
                            @elseif(str_contains($activity->user_agent, 'Safari'))
                            <i class="ri-safari-line"></i> Safari
                            @else
                            <i class="ri-global-line"></i> Other
                            @endif
                        </td>
                        <td>
                            @if($activity->success)
                            <span class="lux-badge lux-badge-success">Berhasil</span>
                            @else
                            <span class="lux-badge lux-badge-danger">Gagal</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Belum ada riwayat login</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection