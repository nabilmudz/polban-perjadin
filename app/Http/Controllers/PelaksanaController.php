<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PelaksanaController extends Controller
{
    // DASHBOARD PELAKSANA
    public function dashboard()
    {
        $user = auth()->user();

        $list = SuratTugas::latest()->paginate(10);

        return Inertia::render('Dashboards/PelaksanaDashboard', [
            'auth' => ['user' => $user],

            'suratTugas' => [
                'data'  => $list->items(),
                'meta'  => [
                    'current_page' => $list->currentPage(),
                    'last_page'    => $list->lastPage(),
                    'from'         => $list->firstItem(),
                    'to'           => $list->lastItem(),
                    'total'        => $list->total(),
                    'per_page'     => $list->perPage(),
                ],
                'links' => [
                    'prev' => $list->previousPageUrl(),
                    'next' => $list->nextPageUrl(),
                ],
            ],

            'filters' => [],
            'role' => 'pelaksana',
        ]);
    }


    // DAFTAR LAPORAN (SEMUA DATA)
    public function daftarLaporan()
    {
        $user = auth()->user();

        $list = SuratTugas::latest()->paginate(10);

        return Inertia::render('Pelaksana/DaftarLaporan', [
            'auth' => ['user' => $user],

            'suratTugas' => [
                'data'  => $list->items(),
                'meta'  => [
                    'current_page' => $list->currentPage(),
                    'last_page'    => $list->lastPage(),
                    'from'         => $list->firstItem(),
                    'to'           => $list->lastItem(),
                    'total'        => $list->total(),
                    'per_page'     => $list->perPage(),
                ],
                'links' => [
                    'prev' => $list->previousPageUrl(),
                    'next' => $list->nextPageUrl(),
                ],
            ],

            'filters' => [],
            'role' => 'pelaksana',
        ]);
    }


    // HISTORY PELAKSANA (SEMUA DATA)
    public function historypelaksana()
    {
        $user = auth()->user();

        $list = SuratTugas::latest()->paginate(10);

        return Inertia::render('Pelaksana/HistoryPelaksana', [
            'auth' => ['user' => $user],

            'suratTugas' => [
                'data'  => $list->items(),
                'meta'  => [
                    'current_page' => $list->currentPage(),
                    'last_page'    => $list->lastPage(),
                    'from'         => $list->firstItem(),
                    'to'           => $list->lastItem(),
                    'total'        => $list->total(),
                    'per_page'     => $list->perPage(),
                ],
                'links' => [
                    'prev' => $list->previousPageUrl(),
                    'next' => $list->nextPageUrl(),
                ],
            ],

            'filters' => [],
            'role' => 'pelaksana',
        ]);
    }
}
