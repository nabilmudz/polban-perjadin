<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class BKUController extends Controller
{
    private function mapSuratWithPersonel($item)
    {
        $item->loadMissing('detailPelaksanaTugas.personable');

        $personel = $item->detailPelaksanaTugas->map(function ($d) {
            $p = $d->personable;
            if (!$p) return null;
            $isMhs = str_contains($d->personable_type, 'Mahasiswa');

            return [
                'id'       => $p->id,
                'type'     => $isMhs ? 'mahasiswa' : 'pegawai',
                'nama'     => $p->nama,
                'nip'      => $isMhs ? null : ($p->nip ?? null),
                'nim'      => $isMhs ? ($p->nim ?? null) : null,
                'pangkat'  => $p->pangkat ?? null,
                'golongan' => $p->golongan ?? null,
                'jabatan'  => $p->jabatan ?? null,
                'jurusan'  => $p->jurusan ?? null,
                'prodi'    => $p->prodi ?? null,
            ];
        })->filter()->values();

        return [
            'id' => $item->id,
            'nama_kegiatan' => $item->nama_kegiatan ?? $item->perihal_tugas,
            'perihal_tugas' => $item->perihal_tugas,
            'tanggal_pengusulan' => $item->created_at->format('Y-m-d'),
            'created_at' => $item->created_at->format('Y-m-d'),
            'tanggal_berangkat' => $item->tanggal_berangkat->format('Y-m-d'),
            'tanggal_pelaksanaan' => $item->tanggal_berangkat->format('Y-m-d'),
            'no_usulan_surat' => $item->nomor_surat_usulan_jurusan ?? '-', 
            'nomor_surat_usulan_jurusan' => $item->nomor_surat_usulan_jurusan ?? '-', 
            'nomor_surat_resmi' => $item->nomor_surat_resmi ?? '-',
            'nomor_surat_tugas' => $item->nomor_surat_resmi ?? '-',
            'updated_at' => $item->updated_at->format('Y-m-d'),
            'sumber_dana' => $item->sumber_dana,
            'status_surat' => $item->status_surat,
            'laporan' => $item->laporan,
            'diusulkan_kepada' => $item->wadir ? $item->wadir->name : 'Wakil Direktur I',
            'personel' => $personel, 
        ];
    }

    public function dashboard(Request $request)
    {
        $totalPengusulan = SuratTugas::count();
        
        $statusCounts = [
            'completed' => SuratTugas::where('status_surat', 'completed')->count(),
            'published' => SuratTugas::where('status_surat', 'published')->count(), // Belum Selesai (Active/No Report)
            'on_duty'   => SuratTugas::whereIn('status_surat', ['approved', 'published'])
                            ->whereDate('tanggal_berangkat', '<=', now())
                            ->whereDate('tanggal_kembali', '>=', now())
                            ->count(),
            'revision_requested' => SuratTugas::whereIn('status_surat', ['revision_requested', 'returned_for_correction'])->count(),
        ];

        $bkuStatuses = [
            'awaiting_proof_upload', 
            'under_bku_review', 
            'returned_for_correction', 
            'completed'
        ];

        $query = SuratTugas::query()
            ->with(['pengusul', 'laporan', 'detailPelaksanaTugas.personable'])
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
                return $this->mapSuratWithPersonel($item);
            });

        return Inertia::render('BKU/BKUDashboard', [
            'statusCounts'    => $statusCounts,    
            'totalPengusulan' => $totalPengusulan, 
            'latestSurat'     => $latestSurat,
            'filters'         => $request->only(['search']),
        ]);
    }

    public function daftarLaporan(Request $request)
    {
        $query = SuratTugas::with(['pengusul', 'laporan', 'detailPelaksanaTugas.personable'])
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
                return $this->mapSuratWithPersonel($item);
            });

        return Inertia::render('BKU/DaftarLaporanPerjalanan', [ 
            'laporanBukti' => $data,
            'filters' => $request->only(['search']),
        ]);
    }

    public function history(Request $request)
    {
        $query = SuratTugas::with(['pengusul', 'wadir', 'detailPelaksanaTugas.personable'])
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
                return $this->mapSuratWithPersonel($item);
            });

        return Inertia::render('BKU/HistoryPerjalananDinas', [
            'history' => $history,
            'filters' => $request->only(['search']),
        ]);
    }
}