<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SuratTugas;

class SekdirController extends Controller
{
    private function formatDate($date, $format = 'd M Y')
    {
        return $date ? $date->format($format) : '-';
    }

    public function dashboard(Request $request)
    {
        $query = SuratTugas::query()
            ->where('status_surat', 'submitted_wadir_review')
            ->where('diusulkan_kepada', 'Wadir I');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('perihal_tugas', 'like', "%{$request->search}%")
                  ->orWhere('status_surat', 'like', "%{$request->search}%")
                  ->orWhere('sumber_dana', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status_surat', $request->status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        }

        $suratTugas = $query->latest()->paginate(10)
            ->through(fn($item) => [
                ...$item->toArray(),
                'created_at' => $this->formatDate($item->created_at, 'Y-m-d'),
                'tanggal_berangkat' => $this->formatDate($item->tanggal_berangkat, 'Y-m-d'),
                'nomor_surat' => "$item->nomor_urutan_surat/$item->kode_perihal/$item->tahun_nomor_surat"
            ])
            ->withQueryString();

        $summary = [
            'total_usulan' => SuratTugas::count(),
            'usulan_baru'  => SuratTugas::where('status_surat', 'baru')->count(),
            'bertugas'     => SuratTugas::where('status_surat', 'bertugas')->count(),
            'selesai'      => SuratTugas::where('status_surat', 'selesai')->count(),
        ];

        return Inertia::render('Sekdir/SekdirDashboard', [
            'suratTugas' => [
                'data' => $suratTugas->items(),
                'meta' => [
                    'current_page' => $suratTugas->currentPage(),
                    'last_page'    => $suratTugas->lastPage(),
                    'per_page'     => $suratTugas->perPage(),
                    'from'         => $suratTugas->firstItem(),
                    'to'           => $suratTugas->lastItem(),
                    'total'        => $suratTugas->total(),
                ],
                'links' => [
                    'prev' => $suratTugas->previousPageUrl(),
                    'next' => $suratTugas->nextPageUrl(),
                ],
            ],
            'filters' => $request->only(['search', 'status', 'from', 'to', 'page']),
            'summary' => $summary,
        ]);
    }

    public function nomorSurat(Request $request)
    {
        $query = SuratTugas::query()
            ->where('status_surat', 'submitted_wadir_review')
            ->where('diusulkan_kepada', 'Wadir I');

        if ($request->filled('search')) {
            $query->where('perihal_tugas', 'like', "%{$request->search}%");
        }

        $surat = $query->orderBy('created_at', 'asc')
            ->paginate(10)
            ->through(fn($item) => [
                'id'                     => $item->surat_tugas_id,
                'created_at'             => $this->formatDate($item->created_at),
                'tanggal_berangkat'      => $this->formatDate($item->tanggal_berangkat),
                'nomor_surat_pengusulan' => $item->nomor_surat_usulan_jurusan ?? '-',
                'sumber_dana'            => $item->sumber_dana ?? '-',
            ])
            ->withQueryString();

        return Inertia::render('Sekdir/NomorSurat', [
            'surat' => [
                'data' => $surat->items(),
                'meta' => [
                    'current_page' => $surat->currentPage(),
                    'last_page'    => $surat->lastPage(),
                    'per_page'     => $surat->perPage(),
                    'from'         => $surat->firstItem(),
                    'to'           => $surat->lastItem(),
                    'total'        => $surat->total(),
                ],
                'links' => [
                    'prev' => $surat->previousPageUrl(),
                    'next' => $surat->nextPageUrl(),
                ],
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function history(Request $request)
    {
        $query = SuratTugas::query()
            ->where('diusulkan_kepada', 'Wadir I');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('perihal_tugas', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_tugas_resmi', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status_surat', $request->status);
        }

        $surat = $query->latest()->paginate(10)
            ->through(fn($item) => [
                'id'                     => $item->surat_tugas_id,
                'created_at'             => $this->formatDate($item->created_at),
                'tanggal_berangkat'      => $this->formatDate($item->tanggal_berangkat),
                'nomor_surat_pengantar'  => $item->nomor_surat_usulan_jurusan ?? '-',
                'nomor_surat_tugas'      => $item->nomor_surat_tugas_resmi ?? '-',
                'tanggal_diterbitkan'    => $this->formatDate($item->tanggal_penomoran_sekdir),
                'diusulkan_kepada'       => $item->diusulkan_kepada ?? '-',
                'status_surat'           => $item->status_surat,
                'file_final'             => $item->path_file_surat_tugas_final,
            ])
            ->withQueryString();

        return Inertia::render('Sekdir/HistoryPersetujuan', [
            'surat' => [
                'data' => $surat->items(),
                'meta' => [
                    'current_page' => $surat->currentPage(),
                    'last_page'    => $surat->lastPage(),
                    'per_page'     => $surat->perPage(),
                    'from'         => $surat->firstItem(),
                    'to'           => $surat->lastItem(),
                    'total'        => $surat->total(),
                ],
                'links' => [
                    'prev' => $surat->previousPageUrl(),
                    'next' => $surat->nextPageUrl(),
                ],
            ],
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function review($id)
    {
        $surat = SuratTugas::with('pengusul')->findOrFail($id);

        $lastSurat = SuratTugas::whereYear('created_at', now()->year)
            ->whereNotNull('nomor_urutan_surat')
            ->orderBy('nomor_urutan_surat', 'desc')
            ->first();

        return Inertia::render('Sekdir/ReviewNomorSurat', [
            'surat'       => $surat,
            'next_number' => $lastSurat ? $lastSurat->nomor_urutan_surat + 1 : 1,
            'year'        => now()->year,
        ]);
    }

    public function applyNomor(Request $request, $id)
    {
        $surat = SuratTugas::findOrFail($id);

        $request->validate([
            'nomor_urutan_surat' => 'required|integer',
            'kode_unit'          => 'required|string',
            'kode_perihal'       => 'required|string',
            'tahun'              => 'required|integer',
        ]);

        $nomorFinal = "{$request->nomor_urutan_surat}/{$request->kode_unit}/{$request->kode_perihal}/{$request->tahun}";

        $surat->update([
            'nomor_urutan_surat' => $request->nomor_urutan_surat,
            'kode_unit_kerja'    => $request->kode_unit,
            'kode_perihal'       => $request->kode_perihal,
            'tahun_nomor_surat'  => $request->tahun,
            'nomor_surat'        => $nomorFinal,
            'status_surat'       => 'diterbitkan_sekdir',
        ]);

        return redirect()
            ->route('sekdir.history')
            ->with('success', 'Nomor surat berhasil diterapkan!');
    }
}