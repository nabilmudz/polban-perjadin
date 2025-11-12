<?php

namespace App\Http\Controllers\BKU;

use App\Http\Controllers\Controller;
use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DaftarLaporanController extends Controller
{
    public function index(Request $request)
    {
        $query = SuratTugas::query()
            ->with('perjalanan'); // if you have perjalanan_dinas relation

        // Search filter
        if ($request->filled('search')) {
            $query->where('nama_kegiatan', 'like', "%{$request->search}%")
                ->orWhere('nomor_surat_tugas', 'like', "%{$request->search}%");
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status_laporan', $request->status);
        }

        //  Date range filter
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('tanggal_pengusulan', [$request->from, $request->to]);
        }

        // Paginate & transform
        $laporan = $query->latest()->paginate(10)
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_kegiatan' => $item->nama_kegiatan,
                    'tanggal_pengusulan' => $item->tanggal_pengusulan,
                    'tanggal_pelaksanaan' => $item->tanggal_pelaksanaan,
                    'nomor_surat_tugas' => $item->nomor_surat_tugas,
                    'status_laporan' => $item->status_laporan,
                ];
            })
            ->withQueryString();

        return Inertia::render('BKU/DaftarLaporanPerjalanan', [
            'laporanBukti' => [
                'data' => $laporan->items(),
                'meta' => [
                    'current_page' => $laporan->currentPage(),
                    'last_page' => $laporan->lastPage(),
                    'per_page' => $laporan->perPage(),
                    'from' => $laporan->firstItem(),
                    'to' => $laporan->lastItem(),
                    'total' => $laporan->total(),
                ],
                'links' => [
                    'prev' => $laporan->previousPageUrl(),
                    'next' => $laporan->nextPageUrl(),
                ],
            ],
            'filters' => $request->only(['search', 'status', 'from', 'to', 'page']),
        ]);
    }
}
