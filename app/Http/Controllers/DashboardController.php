<?php

namespace App\Http\Controllers;

use App\Models\LoginActivities;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->last_login_at === null || $user->last_login_at->diffInMinutes(now()) > 5) {
            $user->update(['last_login_at' => now()]);
        }
        
        $recentLogins = LoginActivities::where('user_id', $user->id)
            ->orderBy('attempted_at', 'desc')
            ->limit(5)
            ->get();
        
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'recent_logins' => LoginActivities::where('success', true)
                ->where('attempted_at', '>=', now()->subDays(7))
                ->count(),
            'failed_logins' => LoginActivities::where('success', false)
                ->where('attempted_at', '>=', now()->subDays(7))
                ->count(),
        ];
        
        return view('dashboard', compact('user', 'recentLogins', 'stats'));
    }
}