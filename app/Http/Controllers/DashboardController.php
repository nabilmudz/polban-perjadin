<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SuratTugas;

class DashboardController extends Controller
{
    public function admin()
    {
        return Inertia::render('Dashboards/AdminDashboard');
    }

    public function wadir1()
    {
        return Inertia::render('Dashboards/WadirDashboard', [
            'role' => 'wadir1',
        ]);
    }
}
