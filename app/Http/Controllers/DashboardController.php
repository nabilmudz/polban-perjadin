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
    // public function wadir1()
    // {
    //     return Inertia::render('Dashboards/WadirDashboard', [
    //         'role' => 'wadir1',
    //     ]);
    // }



    // ==== Adding the BKU dashboard method ==== //
    public function bku(Request $request)
    {
        $stats = [
            'total_pengusulan' => SuratTugas::count(),
            'surat_tugas_baru' => SuratTugas::where('status_surat', 'approved')->count(),
            'bertugas' => SuratTugas::where('status_surat', 'approved')
                            ->whereDate('tanggal_berangkat', '<=', now())
                            ->whereDate('tanggal_kembali', '>=', now())
                            ->count(),
            'laporan_belum_selesai' => SuratTugas::where('status_surat', 'approved')
                                        ->doesntHave('laporan') 
                                        ->count(),
        ];

        $query = SuratTugas::query()
            ->with(['pengusul', 'laporan'])
            ->whereIn('status_surat', [
                'awaiting_proof_upload', 
                'under_bku_review', 
                'returned_for_correction', 
                'completed'
            ])
            ->latest();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_kegiatan', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_resmi', 'like', "%{$request->search}%");
            });
        }

        $latestSurat = $query->paginate(5)
            ->withQueryString()
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'tanggal_pengusulan' => $item->created_at->format('Y-m-d'),
                    'tanggal_berangkat' => $item->tanggal_berangkat->format('Y-m-d'),
                    'no_usulan_surat' => $item->nomor_surat_usulan_jurusan ?? '-', 
                    'nomor_surat_tugas' => $item->nomor_surat_resmi ?? '-',
                    'sumber_dana' => $item->sumber_dana,
                    'status_surat' => $item->status_surat, 
                    'tanggungan_biaya' => '-',
                ];
            });

        return Inertia::render('Dashboards/BKUDashboard', [
            'stats' => $stats,
            'latestSurat' => $latestSurat,
            'filters' => $request->only(['search']),
        ]);
    }


    // public function direktur()
    // {
    //     $user = auth()->user();

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
