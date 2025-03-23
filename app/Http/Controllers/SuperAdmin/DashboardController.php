<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the superadmin dashboard.
     */
    public function index(): View
    {
        return view('superadmin.dashboard');
    }
}