<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BKUController extends Controller
{

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
}