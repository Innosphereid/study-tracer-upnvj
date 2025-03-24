<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        // Fetch recent activities
        $activities = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Here you would normally fetch real data from your database
        // For now, we'll just return the view with the activities
        
        return view('dashboard.index', [
            'activities' => $activities
        ]);
    }
}