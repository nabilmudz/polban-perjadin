<?php

namespace App\Http\Controllers\BKU;

use App\Http\Controllers\Controller;
use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HistoryPerjalananDinasController extends Controller
{
    public function index(Request $request)
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