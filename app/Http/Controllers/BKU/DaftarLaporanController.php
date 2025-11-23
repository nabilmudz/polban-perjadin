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
}