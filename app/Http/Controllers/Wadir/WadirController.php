<?php

namespace App\Http\Controllers\Wadir;

use App\Http\Controllers\Controller;
use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WadirController extends Controller
{
    private function wadirLabel()
    {
        return match(auth()->user()->role) {
            'wadir1' => 'Wadir I',
            'wadir2' => 'Wadir II',
            'wadir3' => 'Wadir III',
            'wadir4' => 'Wadir IV',
            default  => null,
        };
    }

    private function mapPagination($paginate)
    {
        return [
            'data' => $paginate->getCollection()->transform(function ($item) {
                return [
                    ...$item->toArray(),
                    'created_at'        => $item->created_at?->format('Y-m-d'),
                    'tanggal_berangkat' => $item->tanggal_berangkat?->format('Y-m-d'),
                    'tanggal_kembali'   => $item->tanggal_kembali?->format('Y-m-d'),
                    'tanggal_penomoran_sekdir' => $item->tanggal_penomoran_sekdir?->format('Y-m-d'),
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

            'links' => [
                'prev' => $paginate->previousPageUrl(),
                'next' => $paginate->nextPageUrl(),
            ],
        ];
    }

    private function querySurat($filters)
    {
        $wadir = $this->wadirLabel();

        return SuratTugas::where('diusulkan_kepada', $wadir)
            ->when($filters['search'] ?? null, function ($q, $s) {
                $q->where(function ($xx) use ($s) {
                    $xx->where('perihal_tugas', 'like', "%$s%")
                       ->orWhere('nomor_surat_tugas', 'like', "%$s%")
                       ->orWhere('nama_kegiatan', 'like', "%$s%")
                       ->orWhere('lokasi_tugas', 'like', "%$s%");
                });
            })
            ->when($filters['status'] ?? null, function ($q, $s) {
                $q->where('status_surat', $s);
            })
            ->when(($filters['from'] ?? null) && ($filters['to'] ?? null), function ($q) use ($filters) {
                $q->whereBetween('created_at', [$filters['from'], $filters['to']]);
            });
    }

    public function dashboard(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page']);
        $wadir = $this->wadirLabel();

        $paginate = $this->querySurat($filters)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Wadir/WadirDashboard', [
            'suratTugas' => $this->mapPagination($paginate),
            'filters'    => $filters,

            'stats'      => [
                // 2.1 Total Pengusulan
                "total" => SuratTugas::where('diusulkan_kepada', $wadir)->count(),

                // 2.2 Usulan Baru
                "baru" => SuratTugas::where('diusulkan_kepada', $wadir)
                    ->where('status_surat', 'submitted_wadir_review')
                    ->count(),

                // 2.3 Dalam Proses Direktur
                "proses_direktur" => SuratTugas::where('diusulkan_kepada', $wadir)
                    ->where('status_surat', 'pending_direktur_signature')
                    ->count(),

                // 2.4 Bertugas
                "bertugas" => SuratTugas::where('diusulkan_kepada', $wadir)
                    ->whereIn('status_surat', ['published', 'awaiting_proof_upload'])
                    ->count(),

                // 2.5 Ditolak
                "rejected" => SuratTugas::where('diusulkan_kepada', $wadir)
                    ->where('status_surat', 'rejected')
                    ->count(),
            ],
        ]);
    }

    public function history(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page']);

        $paginate = $this->querySurat($filters)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Wadir/HistoryWadir', [
            'suratTugas' => $this->mapPagination($paginate),
            'filters'    => $filters,
        ]);
    }

    public function persetujuan(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page']);

        $paginate = $this->querySurat($filters)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Wadir/Persetujuan', [
            'suratTugas' => $this->mapPagination($paginate),
            'filters'    => $filters,
        ]);
    }

    public function show($id)
    {
        $data = SuratTugas::with('user')->findOrFail($id);
        $data->created_at_formatted = $data->created_at->format('Y-m-d');

        return Inertia::render('Wadir/ReviewWadir', [
            'data' => $data,
        ]);
    }
}
