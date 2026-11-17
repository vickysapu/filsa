<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Jemaat;
use App\Models\LoginActivities;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = [
            'total_jemaat' => Jemaat::count(),
            'saldo_kas' => 150000000, 
            'total_event' => Event::count(),
            'event_bulan_ini' => Event::whereMonth('tanggal_mulai', now()->month)->count(),
            'total_doa' => 12, 
            'doa_urgent' => 3,
        ];

        $upcoming_events = Event::with('kategori')->where('tanggal_mulai', '>=', now())->orderBy('tanggal_mulai', 'asc')->take(6)->get();

        $birthdays = Jemaat::whereMonth('tanggal_lahir', now()->month)
            ->take(5)
            ->get()
            ->map(function ($jemaat) {
                $jemaat->umur = now()->year - $jemaat->tanggal_lahir->year;
                return $jemaat;
            });

        // $prayer_requests = \App\Models\PrayerRequest::latest()->take(4)->get();
        $prayer_requests = []; 

        $recentLogins = LoginActivities::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('user', 'stats', 'upcoming_events', 'birthdays', 'prayer_requests', 'recentLogins'));
    }
}
