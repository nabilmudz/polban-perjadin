<?php

namespace App\Http\Controllers;

use App\Services\SuratTugasService;
use Illuminate\Http\Request;
use App\Models\SuratTugas;
use Inertia\Inertia;
use App\Models\User;
use Carbon\Carbon;

class PengusulController extends Controller
{
    protected $suratTugasService;

    public function __construct(SuratTugasService $suratTugasService)
    {
        $this->suratTugasService = $suratTugasService;
    }
    
    public function dashboardPengusulan(Request $request)
    {
        $user = $request->user();

        $filters = $request->only(['search', 'status', 'from', 'to', 'page']);
        $filters['from'] = $filters['from'] ?? null;
        $filters['to'] = $filters['to'] ?? null;

        $surat = $this->suratTugasService->getAll($filters, $user);

        $mapped = [
            'data' => $surat->getCollection()->transform(function ($item) {
                return [
                    ...$item->toArray(),
                    'created_at' => $item->created_at->format('Y-m-d'),
                ];
            }),
            'meta' => [
                'current_page' => $surat->currentPage(),
                'last_page' => $surat->lastPage(),
                'per_page' => $surat->perPage(),
                'from' => $surat->firstItem(),
                'to' => $surat->lastItem(),
                'total' => $surat->total(),
            ],
            'links' => [
                'prev' => $surat->previousPageUrl(),
                'next' => $surat->nextPageUrl(),
            ],
        ];

        $statusCounts = SuratTugas::where('user_id', $user->id)
            ->selectRaw('status_surat, COUNT(*) as count')
            ->groupBy('status_surat')
            ->pluck('count', 'status_surat')
            ->toArray();

        $today = now()->toDateString();

        $onDuty = SuratTugas::where('user_id', $user->id)
            ->where('status_surat', 'published')
            ->whereDate('tanggal_berangkat', '<=', $today)
            ->whereDate('tanggal_kembali', '>=', $today)
            ->count();

        return inertia('Pengusul/PengusulDashboard', [
            'suratTugas' => $mapped,
            'filters' => $filters,
            'statusCounts' => array_merge($statusCounts, ['on_duty' => $onDuty]),
        ]);
    }

    public function daftarPengusulan(Request $request)
    {
        $user = $request->user();

        $filters = $request->only(['search', 'status', 'from', 'to', 'page']);
        $filters['from'] = $filters['from'] ?? null;
        $filters['to'] = $filters['to'] ?? null;

        $surat = $this->suratTugasService->getAll($filters, $user);

        $mapped = [
            'data' => $surat->getCollection()->transform(function ($item) {
                return [
                    ...$item->toArray(),
                    'tanggal_berangkat' => $item->created_at->format('Y-m-d'),
                    'created_at' => $item->created_at->format('Y-m-d'),
                    'no_usulan_surat' => "$item->nomor_urutan_surat/$item->kode_perihal/$item->tahun_nomor_surat",
                ];
            }),
            'meta' => [
                'current_page' => $surat->currentPage(),
                'last_page' => $surat->lastPage(),
                'per_page' => $surat->perPage(),
                'from' => $surat->firstItem(),
                'to' => $surat->lastItem(),
                'total' => $surat->total(),
            ],
            'links' => [
                'prev' => $surat->previousPageUrl(),
                'next' => $surat->nextPageUrl(),
            ],
        ];

        return inertia('Pengusul/DaftarPengusulan', [
            'suratTugas' => $mapped,
            'filters' => $filters
        ]);
    }

    public function draftPengusulan(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['search', 'from', 'to', 'page']);
        $filters['status'] = 'draft';

        $surat = $this->suratTugasService->getAll($filters, $user);

        $mapped = [
            'data' => $surat->getCollection()->transform(function ($item) {
                return [
                    ...$item->toArray(),
                    'created_at' => $item->created_at->format('Y-m-d'),
                    'tanggal_berangkat' => $item->created_at->format('Y-m-d'),
                    'no_usulan_surat' => "$item->nomor_urutan_surat/$item->kode_perihal/$item->tahun_nomor_surat",
                ];
            }),
            'meta' => [
                'current_page' => $surat->currentPage(),
                'last_page' => $surat->lastPage(),
                'per_page' => $surat->perPage(),
                'from' => $surat->firstItem(),
                'to' => $surat->lastItem(),
                'total' => $surat->total(),
            ],
            'links' => [
                'prev' => $surat->previousPageUrl(),
                'next' => $surat->nextPageUrl(),
            ],
        ];

        return inertia('Pengusul/DraftPengusulan', [
            'suratTugas' => $mapped,
            'filters' => $filters,
        ]);
    }

    public function formPengusulan(Request $request){
        return inertia('Pengusul/FormPengusulan');
    }
}
