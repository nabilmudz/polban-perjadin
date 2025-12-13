<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use App\Models\BuktiLaporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;

class PelaksanaController extends Controller
{
    private const STATUS_LAPORAN = [
        'awaiting_proof_upload',
        'under_bku_review',
        'returned_for_correction',
    ];

    private function baseQuery()
    {
        return SuratTugas::query()
            ->select('surat_tugas.*')
            ->with('user')
            ->orderByDesc('created_at');
    }

    private function applyFilters(Request $request, $query)
    {
        return $query
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('perihal_tugas', 'like', "%{$request->search}%")
                        ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$request->search}%");
                });
            })

            ->when($request->filled('status_surat'), fn ($q) =>
                $q->where('status_surat', $request->status_surat)
            )

            ->when($request->filled('range'), function ($q) use ($request) {
                match ($request->range) {
                    'week' => $q->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ]),
                    'month' => $q->whereBetween('created_at', [
                        now()->startOfMonth(),
                        now()->endOfMonth(),
                    ]),
                    'year' => $q->whereBetween('created_at', [
                        now()->startOfYear(),
                        now()->endOfYear(),
                    ]),
                    default => null,
                };
            })

            ->when(
                $request->filled('from') && $request->filled('to'),
                fn ($q) =>
                    $q->whereBetween('created_at', [
                        Carbon::parse($request->from)->startOfDay(),
                        Carbon::parse($request->to)->endOfDay(),
                    ])
            );
    }

    private function mapPagination($paginate)
    {
        return [
            'data' => $paginate->getCollection()->map(fn ($item) => [
                'id'                         => $item->getKey(), // FIX UTAMA
                'user_name'                  => $item->user?->name ?? '-',
                'perihal_tugas'              => $item->perihal_tugas,
                'nomor_surat_tugas_resmi'    => $item->nomor_surat_tugas_resmi,
                'nomor_surat_usulan_jurusan' => $item->nomor_surat_usulan_jurusan,
                'tanggal_berangkat'          => optional($item->tanggal_berangkat)->format('Y-m-d'),
                'tanggal_kembali'            => optional($item->tanggal_kembali)->format('Y-m-d'),
                'sumber_dana'                => $item->sumber_dana,
                'status_surat'               => $item->status_surat,
                'created_at'                 => optional($item->created_at)->format('Y-m-d'),
                'diusulkan_kepada'           => $item->diusulkan_kepada,
            ]),
            'meta' => [
                'current_page' => $paginate->currentPage(),
                'last_page'    => $paginate->lastPage(),
                'from'         => $paginate->firstItem(),
                'to'           => $paginate->lastItem(),
                'total'        => $paginate->total(),
                'per_page'     => $paginate->perPage(),
            ],
            'links' => [
                'prev' => $paginate->previousPageUrl(),
                'next' => $paginate->nextPageUrl(),
            ],
        ];
    }

    public function dashboard(Request $request)
    {
        $baseQuery = $this->applyFilters($request, $this->baseQuery());

        $list = (clone $baseQuery)->paginate(5)->withQueryString();

        $stats = [
            'selesai'            => (clone $baseQuery)->where('status_surat', 'selesai')->count(),
            'published'          => (clone $baseQuery)->where('status_surat', 'published')->count(),
            'on_duty'            => (clone $baseQuery)->where('status_surat', 'on_duty')->count(),
            'revision_requested' => (clone $baseQuery)->where('status_surat', 'revision_requested')->count(),
        ];

        return Inertia::render('Dashboards/PelaksanaDashboard', [
            'auth'       => ['user' => auth()->user()],
            'suratTugas' => $this->mapPagination($list),
            'stats'      => $stats,
            'filters'    => $request->only([
                'search',
                'status_surat',
                'range',
                'from',
                'to',
                'page',
            ]),
            'role' => 'pelaksana',
        ]);
    }

    public function daftarLaporan(Request $request)
    {
        $list = $this->applyFilters(
            $request,
            $this->baseQuery()
        )->paginate(10)->withQueryString();

        return Inertia::render('Pelaksana/DaftarLaporan', [
            'auth'       => ['user' => auth()->user()],
            'suratTugas' => $this->mapPagination($list),
            'filters'    => $request->only([
                'search',
                'status_surat',
                'range',
                'from',
                'to',
                'page',
            ]),
            'role' => 'pelaksana',
        ]);
    }

    public function historypelaksana(Request $request)
    {
        $list = $this->applyFilters(
            $request,
            $this->baseQuery()
        )->paginate(10)->withQueryString();

        return Inertia::render('Pelaksana/HistoryPelaksana', [
            'auth'       => ['user' => auth()->user()],
            'suratTugas' => $this->mapPagination($list),
            'filters'    => $request->only([
                'search',
                'status_surat',
                'range',
                'from',
                'to',
                'page',
            ]),
            'role' => 'pelaksana',
        ]);
    }

    public function statusLaporan(Request $request)
    {
        $query = $this->baseQuery()
            ->whereIn('status_surat', self::STATUS_LAPORAN);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('perihal_tugas', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$request->search}%");
            });
        }

        if (
            $request->filled('status_surat') &&
            in_array($request->status_surat, self::STATUS_LAPORAN)
        ) {
            $query->where('status_surat', $request->status_surat);
        }

        $list = $query->paginate(10)->withQueryString();

        return Inertia::render('Pelaksana/StatusLaporan', [
            'auth'       => ['user' => auth()->user()],
            'suratTugas' => $this->mapPagination($list),
            'filters'    => $request->only([
                'search',
                'status_surat',
                'page',
            ]),
            'role' => 'pelaksana',
        ]);
    }

    public function uploadBuktiPage(SuratTugas $laporan)
    {
        return Inertia::render('Pelaksana/UploadBukti', [
            'auth'    => ['user' => auth()->user()],
            'laporan' => $laporan,
            'bukti'   => BuktiLaporan::where(
                    'surat_tugas_id',
                    $laporan->surat_tugas_id // FIX
                )
                ->latest()
                ->get()
                ->map(fn ($f) => [
                    'id'         => $f->id,
                    'kategori'   => $f->kategori,
                    'url'        => Storage::url($f->file_path),
                    'nominal'    => $f->nominal,
                    'keterangan' => $f->keterangan,
                ]),
        ]);
    }

    public function storeBukti(Request $request, SuratTugas $laporan)
    {
        $data = $request->validate([
            'file'       => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'kategori'   => 'required|string',
            'keterangan' => 'nullable|string|max:255',
            'nominal'    => 'nullable|numeric|min:0',
        ]);

        $path = $request->file('file')->store('bukti', 'public');

        BuktiLaporan::create([
            'surat_tugas_id' => $laporan->surat_tugas_id,
            'kategori'       => $data['kategori'],
            'file_path'      => $path,
            'keterangan'     => $data['keterangan'] ?? null,
            'nominal'        => $data['nominal'] ?? null,
        ]);

        return back()->with('success', 'Bukti berhasil diupload');
    }
}
