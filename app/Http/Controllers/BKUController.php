<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BKUController extends Controller
{

    public function dashboard(Request $request)
    {
        $stats = [
            'total_pengusulan' => SuratTugas::count(),
            'surat_tugas_baru' => SuratTugas::where('status_surat', 'approved')
                                    ->whereDate('created_at', '>=', now()->subDays(7))
                                    ->count(),
            'bertugas' => SuratTugas::where('status_surat', 'approved')
                            ->whereDate('tanggal_berangkat', '<=', now())
                            ->whereDate('tanggal_kembali', '>=', now())
                            ->count(),
            'laporan_belum_selesai' => SuratTugas::where('status_surat', 'approved')
                                        ->whereDate('tanggal_kembali', '<', now())
                                        ->doesntHave('laporan') 
                                        ->count(),
        ];

        $query = SuratTugas::query()
            ->with(['pengusul', 'laporan']) 
            ->latest();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_kegiatan', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_resmi', 'like', "%{$request->search}%");
            });
        }

        $latestSurat = $query->paginate(5)->withQueryString();

        $latestSurat->getCollection()->transform(function ($item) {
            $statusLaporan = 'Belum Upload';
            $badgeColor = 'yellow'; 

            if ($item->laporan) {
                $statusLaporan = 'Selesai'; 
                $badgeColor = 'green';
            } elseif ($item->tanggal_berangkat <= now() && $item->tanggal_kembali >= now()) {
                $statusLaporan = 'Sedang Bertugas';
                $badgeColor = 'blue';
            } elseif ($item->status_surat !== 'approved') {
                 $statusLaporan = '-'; 
                 $badgeColor = 'gray';
            }

            $item->display_status_laporan = $statusLaporan;
            $item->badge_color = $badgeColor;
            $item->tanggungan_biaya = '-'; 

            return $item;
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
            ->where('status_surat', 'approved');

        if ($request->search) {
            $query->where('nama_kegiatan', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_resmi', 'like', "%{$request->search}%");
        }

        $data = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('BKU/DaftarLaporanPerjalanan', [ 
            'laporanBukti' => $data,
            'filters' => $request->only(['search']),
        ]);
    }

    public function history(Request $request)
    {
        $query = SuratTugas::with(['pengusul', 'wadir']);

        if ($request->search) {
            $query->where('nama_kegiatan', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_resmi', 'like', "%{$request->search}%");
        }

        $history = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('BKU/HistoryPerjalananDinas', [
            'history' => $history,
            'filters' => $request->only(['search']),
        ]);
    }
}