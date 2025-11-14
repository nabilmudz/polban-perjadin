<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SuratTugas;

class SekdirController extends Controller
{
    /**
     * Dashboard Sekretaris Direktur
     */
    public function dashboard(Request $request)
    {
        $query = SuratTugas::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('perihal_tugas', 'like', "%{$request->search}%")
                    ->orWhere('status_surat', 'like', "%{$request->search}%")
                    ->orWhere('sumber_dana', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status_surat', $request->status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('tanggal_pengajuan', [$request->from, $request->to]);
        }

        $query->where('status_surat', 'disetujui_wadir');

        $suratTugas = $query->latest()->paginate(10)
            ->through(function ($item) {
                return [
                    ...$item->toArray(),
                    'tanggal_pengajuan' => $item->tanggal_pengajuan
                        ? \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('Y-m-d')
                        : '-',
                    'tanggal_berangkat' => $item->tanggal_berangkat
                        ? \Carbon\Carbon::parse($item->tanggal_berangkat)->format('Y-m-d')
                        : '-',
                ];
            })
            ->withQueryString();

        $summary = [
            'total_usulan' => SuratTugas::count(),
            'usulan_baru' => SuratTugas::where('status_surat', 'baru')->count(),
            'bertugas' => SuratTugas::where('status_surat', 'bertugas')->count(),
            'selesai' => SuratTugas::where('status_surat', 'selesai')->count(),
        ];

        return Inertia::render('Sekdir/SekdirDashboard', [
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
            'filters' => $request->only(['search', 'status', 'from', 'to', 'page']),
            'summary' => $summary,
        ]);
    }
}