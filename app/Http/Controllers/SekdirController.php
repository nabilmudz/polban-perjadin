<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SuratTugas;

class SekdirController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = SuratTugas::query();

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
            $query->whereBetween('tanggal_pengajuan', [$request->from, $request->to]);
        }

        $query->where('status_surat', 'disetujui_wadir');

        $suratTugas = $query->latest()->paginate(10)
            ->through(fn($item) => [
                ...$item->toArray(),
                'tanggal_pengajuan'  => $item->tanggal_pengajuan?->format('Y-m-d') ?? '-',
                'tanggal_berangkat'  => $item->tanggal_berangkat?->format('Y-m-d') ?? '-',
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
        $query = SuratTugas::where('status_surat', 'disetujui_wadir');

        if ($request->filled('search')) {
            $query->where('perihal_tugas', 'like', "%{$request->search}%");
        }

        $surat = $query->orderBy('tanggal_pengajuan', 'asc')
            ->paginate(10)
            ->through(fn($item) => [
                'id'                     => $item->id,
                'tanggal_pengajuan'      => $item->tanggal_pengajuan?->format('d M Y') ?? '-',
                'tanggal_berangkat'      => $item->tanggal_berangkat?->format('d M Y') ?? '-',
                'nomor_surat_pengusulan' => $item->nomor_surat_pengusulan ?? '-',
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

    public function review($id)
    {
        $surat = SuratTugas::findOrFail($id);

        $lastSurat = SuratTugas::whereYear('tanggal_pengajuan', now()->year)
            ->whereNotNull('nomor_urutan_surat')
            ->orderBy('nomor_urutan_surat', 'desc')
            ->first();

        $nextNumber = $lastSurat ? $lastSurat->nomor_urutan_surat + 1 : 1;

        return Inertia::render('Sekdir/ReviewNomorSurat', [
            'surat'        => $surat,
            'next_number'  => $nextNumber,
            'year'         => now()->year,
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
            'nomor_urutan_surat'  => $request->nomor_urutan_surat,
            'kode_unit_kerja'     => $request->kode_unit,
            'kode_perihal'        => $request->kode_perihal,
            'tahun_nomor_surat'   => $request->tahun,
            'nomor_surat'         => $nomorFinal,
            'status_surat'        => 'diterbitkan_sekdir',
        ]);

        return redirect()
            ->route('sekdir.nomorsurat')
            ->with('success', 'Nomor surat berhasil diterapkan!');
    }
}