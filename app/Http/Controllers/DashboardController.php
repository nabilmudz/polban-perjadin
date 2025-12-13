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

    public function pengusul()
    {
        $user = auth()->user();

        $suratTugas = SuratTugas::where('user_id', $user->id)
            ->latest()
            ->paginate(10)
            ->through(fn($item) => [
                'id' => $item->id,
                'no_surat' => $item->no_surat,
                'perihal' => $item->perihal,
                'status' => $item->status,
                'tanggal_berangkat' => $item->tanggal_berangkat,
            ]);

        return inertia('Dashboards/PengusulDashboard', [
            'auth' => ['user' => $user],
            'suratTugas' => $suratTugas,
            'filters' => [
                'status' => null,
                'search' => null,
                'from' => null,
                'to' => null,
            ],
        ]);
    }

    public function wadir1()
    {
        return Inertia::render('Wadir/WadirDashboard', [
            'role' => 'wadir1',
        ]);
    }
    // public function wadir1()
    // {
    //     return Inertia::render('Wadir/WadirDashboard', [
    //         'role' => 'wadir1',
    //     ]);
    // }


    // public function direktur()
    // {
    //     $user = auth()->user();


}
