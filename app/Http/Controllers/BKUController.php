<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class BKUController extends Controller
{
    public function dashboard(Request $request)
    {
        $bkuStatuses = [
            'awaiting_proof_upload', 
            'under_bku_review', 
            'returned_for_correction', 
            'completed'
        ];

        $stats = [
            'total_pengusulan' => SuratTugas::whereIn('status_surat', $bkuStatuses)->count(),
            'surat_tugas_baru' => SuratTugas::where('status_surat', 'under_bku_review')->count(),
            'bertugas' => SuratTugas::where('status_surat', 'approved')
                            ->whereDate('tanggal_berangkat', '<=', now())
                            ->whereDate('tanggal_kembali', '>=', now())
                            ->count(),
            'laporan_belum_selesai' => SuratTugas::where('status_surat', 'completed')
                                            ->doesntHave('laporan') 
                                            ->count(),
        ];

        $query = SuratTugas::query()
            ->with(['pengusul', 'laporan'])
            ->whereIn('status_surat', $bkuStatuses)
            ->latest();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_kegiatan', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$request->search}%") 
                  ->orWhere('nomor_surat_resmi', 'like', "%{$request->search}%");
            });
        }

        $latestSurat = $query->paginate(10)
            ->withQueryString()
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'tanggal_pengusulan' => $item->created_at->format('Y-m-d'),
                    'tanggal_berangkat' => $item->tanggal_berangkat->format('Y-m-d'),
                    'no_usulan_surat' => $item->nomor_surat_usulan_jurusan ?? '-', 
                    'nomor_surat_tugas' => $item->nomor_surat_resmi ?? '-',
                    'sumber_dana' => $item->sumber_dana,
                    'status_surat' => $item->status_surat, // Used for Badge
                ];
            });

        return Inertia::render('BKU/BKUDashboard', [
            'stats' => $stats,
            'latestSurat' => $latestSurat,
            'filters' => $request->only(['search']),
        ]);
    }

    public function daftarLaporan(Request $request)
    {
        $query = SuratTugas::with(['pengusul', 'laporan'])
            ->whereIn('status_surat', [
                'awaiting_proof_upload', 
                'under_bku_review', 
                'returned_for_correction', 
                'completed'
            ]);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_kegiatan', 'like', "%{$request->search}%")
                  ->orWhere('perihal_tugas', 'like', "%{$request->search}%") 
                  ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_resmi', 'like', "%{$request->search}%");
            });
        }

        $data = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_kegiatan' => $item->perihal_tugas, 
                    'created_at' => $item->created_at->format('Y-m-d'), 
                    'tanggal_pelaksanaan' => $item->tanggal_berangkat->format('Y-m-d'),
                    'nomor_surat_resmi' => $item->nomor_surat_resmi ?? '-',
                    'status_surat' => $item->status_surat 
                ];
            });

        return Inertia::render('BKU/DaftarLaporanPerjalanan', [ 
            'laporanBukti' => $data,
            'filters' => $request->only(['search']),
        ]);
    }

    public function history(Request $request)
    {
        $query = SuratTugas::with(['pengusul', 'wadir'])
            ->where('status_surat', 'completed');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_kegiatan', 'like', "%{$request->search}%")
                  ->orWhere('perihal_tugas', 'like', "%{$request->search}%") 
                  ->orWhere('nomor_surat_resmi', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$request->search}%");
            });
        }

        $history = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'created_at' => $item->created_at->format('Y-m-d'),
                    'tanggal_pelaksanaan' => $item->tanggal_berangkat->format('Y-m-d'),
                    'perihal_tugas' => $item->perihal_tugas, 
                    'nomor_surat_usulan_jurusan' => $item->nomor_surat_usulan_jurusan ?? '-',
                    'nomor_surat_resmi' => $item->nomor_surat_resmi ?? '-',
                    'updated_at' => $item->updated_at->format('Y-m-d'),
                    'diusulkan_kepada' => $item->wadir ? $item->wadir->name : 'Wakil Direktur I',
                    'status_surat' => $item->status_surat // Used for Badge
                ];
            });

        return Inertia::render('BKU/HistoryPerjalananDinas', [
            'history' => $history,
            'filters' => $request->only(['search']),
        ]);
    }
}