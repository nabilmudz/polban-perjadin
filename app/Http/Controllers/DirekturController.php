<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SuratTugas;
use Illuminate\Support\Facades\Redirect;

class DirekturController extends Controller
{
    private function mapPagination($paginate)
    {
        return [
            'data' => $paginate->getCollection()->transform(function ($item) {
                return [
                    ...$item->toArray(),
                    'nama_kegiatan' => $item->nama_kegiatan ?? $item->perihal_tugas, 
                    'created_at' => $item->created_at->format('Y-m-d'),
                    'tanggal_pelaksanaan' => $item->tanggal_berangkat ? $item->tanggal_berangkat->format('Y-m-d') : '-',
                ];
            }),
            'meta' => [
                'current_page' => $paginate->currentPage(),
                'last_page'    => $paginate->lastPage(),
                'per_page'     => $paginate->perPage(),
                'from'         => $paginate->firstItem(),
                'to'           => $paginate->lastItem(),
                'total'        => $paginate->total(),
            ],
            'links' => $paginate->toArray()['links'] ?? [], 
        ];
    }

    public function direktur(Request $request)
    {
        $user = auth()->user();

        $query = SuratTugas::query()
            ->where('status_surat', 'pending_direktur_signature');

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

        $paginate = $query->latest()->paginate(5)->withQueryString();

        $stats = [
            'total_ulasan' => SuratTugas::count(),
            'bertugas' => SuratTugas::where('status_surat', 'approved')
                            ->whereDate('tanggal_berangkat', '<=', now())
                            ->whereDate('tanggal_kembali', '>=', now())
                            ->count(),
        ];

        return inertia('Direktur/DirekturDashboard', [
            'suratTugas' => $this->mapPagination($paginate), 
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
                'from' => $request->from,
                'to' => $request->to ?: now()->toDateString(),
            ],
            'stats' => $stats, 
        ]);
    }
    
    public function persetujuan(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to']);

        $query = SuratTugas::with(['pengusul', 'wadir'])
            ->where('status_surat', 'pending_direktur_signature');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('perihal_tugas', 'like', "%{$request->search}%");
            });
        }

        $paginate = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Direktur/DaftarPersetujuan', [
            'suratTugas' => $this->mapPagination($paginate),
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