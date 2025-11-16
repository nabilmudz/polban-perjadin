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


    // ==== Adding the direktur dashboard method ==== //

    public function direktur()
    {
        return Inertia::render('Dashboards/DirekturDashboard', [
            'role' => 'direktur',
        ]);
    }

    // ==== Adding the bku dashboard method ==== //
    public function bku()
    {
        return Inertia::render('Dashboards/BKUDashboard', [
            'role' => 'bku',
        ]);
    }

    // public function direktur()
    // {
    //     $user = auth()->user();

    //     $suratTugas = SuratTugas::latest()
    //         ->paginate(10)
    //         ->through(fn($item) => [
    //             'id' => $item->id,
    //             'no_surat' => $item->no_surat,
    //             'sumber_dana' => $item->sumber_dana,
    //             'status' => $item->status,
    //             'tanggal_berangkat' => $item->tanggal_berangkat,
    //         ]);

    //     return inertia('Dashboards/DirekturDashboard', [
    //         'suratTugas' => $suratTugas,
    //         'filters' => [
    //             'search' => request('search'),
    //         ],
    //     ]);
    // }

}
