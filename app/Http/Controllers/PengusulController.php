<?php

namespace App\Http\Controllers;

use App\Services\SuratTugasService;
use App\Services\PegawaiService;
use App\Services\MahasiswaService;
use Illuminate\Http\Request;
use App\Models\SuratTugas;
use Inertia\Inertia;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Mahasiswa;
use App\Models\Pegawai;

class PengusulController extends Controller
{
    protected $suratTugasService;
    protected $pegawaiService;
    protected $mahasiswaService;

    public function __construct(
        SuratTugasService $suratTugasService,
        PegawaiService $pegawaiService,
        MahasiswaService $mahasiswaService
        )
        {
            $this->suratTugasService = $suratTugasService;
            $this->pegawaiService = $pegawaiService;
            $this->mahasiswaService = $mahasiswaService;
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

    public function formPengusulan(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page', 'tab']);
        $tab = $filters['tab'] ?? 'pegawai';

        $data = $tab === 'pegawai'
            ? $this->getPegawai($filters)
            : $this->getMahasiswa($filters);

        return Inertia::render('Pengusul/PengusulanWizard', [
            'personel' => $data,
            'filters'  => $filters,
            'tab'      => $tab,
        ]);
    }
    
    public function personel(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page', 'tab']);
        $tab = $request->get('tab', 'pegawai');

        $data = $tab === 'pegawai'
            ? $this->getPegawai($filters)
            : $this->getMahasiswa($filters);

        return Inertia::render('Pengusul/FormPersonel', [
            'personel' => $data,
            'filters'  => $filters,
            'tab'      => $tab,
        ]);
    }

    private function getMahasiswa(array $filters = [])
    {
        $query = Mahasiswa::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('jurusan', 'like', "%{$search}%")
                    ->orWhere('prodi', 'like', "%{$search}%");
            });
        }

        $paginate = $query->latest()->paginate(10)->withQueryString();

        return $this->formatPagination($paginate);
    }

    private function getPegawai(array $filters = [])
    {
        $query = Pegawai::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('pangkat', 'like', "%{$search}%")
                    ->orWhere('golongan', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->whereBetween('created_at', [$filters['from'], $filters['to']]);
        }

        $paginate = $query->latest()->paginate(10)->withQueryString();

        return $this->formatPagination($paginate);
    }

    private function formatPagination($paginate)
    {
        return [
            'data' => $paginate->items(),
            'meta' => [
                'current_page' => $paginate->currentPage(),
                'last_page' => $paginate->lastPage(),
                'per_page' => $paginate->perPage(),
                'from' => $paginate->firstItem(),
                'to' => $paginate->lastItem(),
                'total' => $paginate->total(),
            ],
            'links' => [
                'prev' => $paginate->previousPageUrl(),
                'next' => $paginate->nextPageUrl(),
            ],
        ];
    }

}
