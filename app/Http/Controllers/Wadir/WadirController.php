<?php

namespace App\Http\Controllers\Wadir;

use App\Http\Controllers\Controller;
use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class WadirController extends Controller
{
   private function wadirLabel()
    {
        return match (auth()->user()->role) {
            'wadir1' => 'Wadir I',
            'wadir2' => 'Wadir II',
            'wadir3' => 'Wadir III',
            'wadir4' => 'Wadir IV',
            default  => null,
        };
    }

    private function baseQuery(array $filters)
    {
        $wadir = $this->wadirLabel();

        return SuratTugas::query()
            ->where('diusulkan_kepada', $wadir)

            ->when($filters['search'] ?? null, function ($q, $s) {
                $q->where('perihal_tugas', 'like', "%{$s}%");
            })

            ->when($filters['status'] ?? null, function ($q, $s) {
                $q->where('status_surat', $s);
            })

            ->when($filters['tanggal'] ?? null, function ($q, $tgl) {
                $q->whereDate('created_at', $tgl);
            })

            ->when($filters['range'] ?? null, function ($q, $range) {
                match ($range) {
                    'week'  => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                    'month' => $q->whereMonth('created_at', now()->month),
                    'year'  => $q->whereYear('created_at', now()->year),
                    default => null,
                };
            })

            ->when(($filters['from'] ?? null) && ($filters['to'] ?? null), function ($q) use ($filters) {
                $q->whereBetween('created_at', [
                    Carbon::parse($filters['from'])->startOfDay(),
                    Carbon::parse($filters['to'])->endOfDay(),
                ]);
            });
    }

    private function formatPagination($paginate)
    {
        return [
            'data' => $paginate->getCollection()->transform(
                fn (SuratTugas $item) => $this->suratTransformer($item)
            ),
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

    private function suratTransformer(SuratTugas $item)
    {
        return [
            'id' => $item->surat_tugas_id,

            'perihal_tugas' => $item->perihal_tugas,
            'status_surat' => $item->status_surat,
            'sumber_dana' => $item->sumber_dana,
            'total_dana' => $item->total_dana,
            'path_file_surat_usulan' => $item->path_file_surat_usulan,

            'nomor_surat_usulan_jurusan' => $item->nomor_surat_usulan_jurusan,
            'nomor_surat_tugas_resmi' => $item->nomor_surat_tugas_resmi,

            'created_at' => $item->created_at?->format('Y-m-d'),
            'tanggal_berangkat' => $item->tanggal_berangkat?->format('Y-m-d'),
            'tanggal_kembali' => $item->tanggal_kembali?->format('Y-m-d'),
            'tanggal_penomoran_sekdir' => $item->tanggal_penomoran_sekdir?->format('Y-m-d'),

            'catatan_revisi' => $item->catatan_revisi,
        ];
    }

    public function dashboard(Request $request)
    {
        $filters = $request->only([
            'search',
            'status',
            'tanggal',
            'range',
            'from',
            'to',
            'page',
        ]);

        $wadir = $this->wadirLabel();

        $query = $this->baseQuery($filters)
            ->where('status_surat', 'submitted_wadir_review')
            ->latest();

        $paginate = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => SuratTugas::where('diusulkan_kepada', $wadir)->count(),
            'baru'  => SuratTugas::where('diusulkan_kepada', $wadir)
                ->where('status_surat', 'submitted_wadir_review')
                ->count(),
            'proses_direktur' => SuratTugas::where('diusulkan_kepada', $wadir)
                ->where('status_surat', 'pending_direktur_signature')
                ->count(),
            'bertugas' => SuratTugas::where('diusulkan_kepada', $wadir)
                ->whereIn('status_surat', ['published', 'awaiting_proof_upload'])
                ->count(),
            'rejected' => SuratTugas::where('diusulkan_kepada', $wadir)
                ->where('status_surat', 'rejected')
                ->count(),
        ];

        return Inertia::render('Wadir/WadirDashboard', [
            'suratTugas' => $this->formatPagination($paginate),
            'filters'    => $filters,
            'stats'      => $stats,
        ]);
    }

    public function history(Request $request)
    {
        $filters = $request->only([
            'search',
            'status',
            'tanggal',
            'range',
            'from',
            'to',
            'page',
        ]);

        $query = $this->baseQuery($filters)->latest();
        $paginate = $query->paginate(10)->withQueryString();

        return Inertia::render('Wadir/HistoryWadir', [
            'suratTugas' => $this->formatPagination($paginate),
            'filters'    => $filters,
        ]);
    }

    public function persetujuan(Request $request)
    {
        $filters = $request->only([
            'search',
            'range',
            'from',
            'to',
            'page',
        ]);

        $filters['status'] = 'submitted_wadir_review';

        $query = $this->baseQuery($filters)->latest();
        $paginate = $query->paginate(10)->withQueryString();

        return Inertia::render('Wadir/Persetujuan', [
            'suratTugas' => $this->formatPagination($paginate),
            'filters'    => $filters,
        ]);
    }

    public function show($id)
    {
        $surat = SuratTugas::findOrFail($id);

        return Inertia::render('Wadir/ReviewWadir', [
            'data' => $this->suratTransformer($surat),
        ]);
    }
}
