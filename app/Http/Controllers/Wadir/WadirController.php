<?php

namespace App\Http\Controllers\Wadir;

use App\Http\Controllers\Controller;
use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WadirController extends Controller
{
    private function mapSurat($surat)
    {
        return [
            'data' => $surat->getCollection()->transform(function ($item) {
                return [
                    'id'                => $item->id,
                    'perihal_tugas'     => $item->perihal_tugas,
                    'created_at'        => $item->created_at->format('Y-m-d'),
                    'status_surat'      => $item->status_surat,
                    'tanggal_berangkat' => $item->tanggal_berangkat?->format('Y-m-d'),
                    'tanggal_kembali'   => $item->tanggal_kembali?->format('Y-m-d'),
                    'tanggal_penomoran_sekdir' => $item->tanggal_penomoran_sekdir?->format('Y-m-d'),
                ];
            }),
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
        ];
    }

    public function dashboard(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page']);

        $surat = SuratTugas::when($filters['search'] ?? null, fn($q, $s) =>
                $q->where('perihal_tugas', 'like', "%$s%")
            )
            ->when($filters['status'] ?? null, fn($q, $s) =>
                $q->where('status_surat', $s)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Wadir/WadirDashboard', [
            'suratTugas' => $this->mapSurat($surat),
            'filters'    => $filters,
            'stats'      => [
                'total'    => SuratTugas::count(),
                'approved' => SuratTugas::where('status_surat', 'approved')->count(),
                'pending'  => SuratTugas::where('status_surat', 'pending')->count(),
                'rejected' => SuratTugas::where('status_surat', 'rejected')->count(),
            ]
        ]);
    }

    public function history(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page']);

        $surat = SuratTugas::latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Wadir/HistoryWadir', [
            'suratTugas' => $this->mapSurat($surat),
            'filters' => $filters,
        ]);
    }

    public function persetujuan(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page']);

        $surat = SuratTugas::where('status_surat', 'pending')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Wadir/Persetujuan', [
            'suratTugas' => $this->mapSurat($surat),
            'filters'    => $filters,
        ]);
    }

    public function show($id)
    {
        $data = SuratTugas::with('user')->findOrFail($id);
        $data->created_at_formatted = $data->created_at->format('d M Y H:i');

        return Inertia::render('Wadir/ReviewWadir', [
            'data' => $data,
        ]);
    }
}
