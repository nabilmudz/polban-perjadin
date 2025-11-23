<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DaftarPersetujuanController extends Controller
{
    public function index(Request $request)
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
            ->withQueryString();

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
            // Update status to finished/approved
            'status_surat' => 'approved', 
            // 'direktur_id' => auth()->id(),
            // 'tanggal_tanda_tangan' => now(),
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