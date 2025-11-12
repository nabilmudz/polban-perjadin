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
        return Inertia::render('Dashboards/WadirDashboard', [
            'role' => 'wadir1',
        ]);
    }


    // ==== Adding the direktur dashboard method ==== //

    // public function direktur()
    // {
    //     return Inertia::render('Dashboards/DirekturDashboard', [
    //         'role' => 'direktur',
    //     ]);
    // }

    // ==== Adding the direktur dashboard method ==== //
    public function bku()
    {
        return Inertia::render('Dashboards/BKUDashboard', [
            'role' => 'bku',
        ]);
    }



    public function direktur(Request $request)
    {
        $user = auth()->user();

        $query = SuratTugas::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('perihal_tugas', 'like', "%{$request->search}%")
                    ->orWhere('status_surat', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status_surat', $request->status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        }

        $suratTugas = $query->latest()->paginate(10)
        ->through(function($item) {
            return [
                ...$item->toArray(),
                'created_at' => $item->created_at->format('Y-m-d'),
            ];
        })
        ->withQueryString();

        return inertia('Dashboards/DirekturDashboard', [
            'suratTugas' => [
                'data' => $suratTugas->items(),
                'meta' => [
                    'current_page' => $suratTugas->currentPage(),
                    'last_page' => $suratTugas->lastPage(),
                    'per_page' => $suratTugas->perPage(),
                    'from' => $suratTugas->firstItem(),
                    'to' => $suratTugas->lastItem(),
                    'total' => $suratTugas->total(),
                ],
                'links' => [
                    'prev' => $suratTugas->previousPageUrl(),
                    'next' => $suratTugas->nextPageUrl(),
                ],
            ],
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
                'from' => $request->from,
                'to' => $request->to ?: now()->toDateString(),
            ],
        ]);
    }

}
