<?php

namespace App\Http\Controllers\Wadir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SuratTugas;

class WadirController extends Controller
{
    // Dashboard Wadir
    public function index(Request $request)
    {
        $role = auth()->user()->role; // wadir1, wadir2, wadir3, wadir4

        // List data surat tugas
        $list = SuratTugas::with('user')
            ->latest()
            ->paginate(10);

        // Statistik
        $stats = [
            'total'    => SuratTugas::count(),
            'approved' => SuratTugas::where('status_surat', 'approved_wadir')->count(),
            'pending'  => SuratTugas::where('status_surat', 'pending')->count(),
            'rejected' => SuratTugas::where('status_surat', 'rejected')->count(),
        ];

        return Inertia::render('Wadir/WadirDashboard', [
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

            'stats' => $stats,

            'filters' => [
                'search' => $request->search ?? null,
            ],
        ]);
    }


    // Persetujuan Wadir
    public function persetujuan()
    {
        $list = SuratTugas::with('user')
            ->latest()
            ->paginate(10);

        return Inertia::render('Wadir/Persetujuan', [
            'list' => [
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
            ]
        ]);
    }

    // History Wadir
    public function history()
    {
        $role = auth()->user()->role;

        $surat = SuratTugas::with('user')
            ->whereIn('status_surat', [
                'approved_wadir',
                'rejected',
                'revision_requested'
            ])
            ->latest()
            ->paginate(10);

        return Inertia::render('Wadir/HistoryWadir', [
            'suratTugas' => [
                'data'  => $surat->items(),
                'meta'  => [
                    'total' => $surat->total(),
                    'per_page' => $surat->perPage(),
                    'current_page' => $surat->currentPage(),
                    'last_page' => $surat->lastPage(),
                ],
                'links' => [
                    'prev' => $surat->previousPageUrl(),
                    'next' => $surat->nextPageUrl(),
                ],
            ],
            'filters' => [
                'search' => null,
            ],
        ]);
    }

    // Review Wadir
    public function show($id)
    {
        $data = SuratTugas::with('user')->findOrFail($id);

        return Inertia::render('Wadir/ReviewWadir', [
            'data' => $data
        ]);
    }
}
