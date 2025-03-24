<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the superadmin dashboard.
     */
    public function index(): View
    {
        // Fetch recent activities
        $activities = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Fetch admin users for admin management
        $admins = User::where('role', 'admin')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Here you would normally fetch additional data specific to superadmin
        
        return view('dashboard.index', [
            'activities' => $activities,
            'admins' => $admins
        ]);
    }
}