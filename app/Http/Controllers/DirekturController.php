<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SuratTugas;
use Illuminate\Support\Facades\Redirect;

class DirekturController extends Controller
{
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

        return inertia('Direktur/DirekturDashboard', [
            'suratTugas' => $suratTugas, 
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
                'from' => $request->from,
                'to' => $request->to ?: now()->toDateString(),
            ],
        ]);
    }
    
    public function persetujuan(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to']);

        $query = SuratTugas::with(['pengusul', 'wadir'])
            ->where('status_surat', 'pending_direktur_signature');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_kegiatan', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_resmi', 'like', "%{$request->search}%");
            });
        }

        $suratTugas = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function($item) {
                return [
                    ...$item->toArray(),
                    'tanggal_pelaksanaan' => $item->tanggal_berangkat ? $item->tanggal_berangkat->format('Y-m-d') : '-',
                ];
            });

        return Inertia::render('Direktur/DaftarPersetujuan', [
            'suratTugas' => $suratTugas,
            'filters' => $filters,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $surat = SuratTugas::findOrFail($id);
        
        if ($surat->status_surat !== 'pending_direktur_signature') {
             return Redirect::back()->with('error', 'Status surat tidak valid untuk aksi ini.');
        }

        $surat->update([
            'status_surat' => 'approved', 
        ]);

        return Redirect::route('direktur.daftarpersetujuan')
            ->with('success', 'Surat tugas berhasil disetujui dan diterbitkan.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|max:1000',
        ]);

        $surat = SuratTugas::findOrFail($id);

        $surat->update([
            'status_surat' => 'rejected',
            'catatan' => $request->catatan, 
        ]);

        return Redirect::route('direktur.daftarpersetujuan')
            ->with('success', 'Surat tugas ditolak.');
    }

    public function revise(Request $request, $id)
    {
         $request->validate([
            'catatan' => 'required|string|max:1000',
        ]);
        
        $surat = SuratTugas::findOrFail($id);

        $surat->update([
            'status_surat' => 'revision_requested', 
            'catatan' => $request->catatan,
        ]);

        return Redirect::route('direktur.daftarpersetujuan')
            ->with('success', 'Surat tugas dikembalikan untuk revisi.');
    }
}