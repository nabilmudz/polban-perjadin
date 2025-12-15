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
    private function wadirTargets(): array
    {
        $role = auth()->user()->role;
        $label = match ($role) {
            'wadir1' => 'Wadir I',
            'wadir2' => 'Wadir II',
            'wadir3' => 'Wadir III',
            'wadir4' => 'Wadir IV',
            default  => null,
        };

        return array_values(array_filter(array_unique([$role, $label])));
    }
    private function mapPagination($paginate)
    {
        return [
            'data' => $paginate->getCollection()->transform(function ($item) {
                $noUsulan = $item->nomor_surat_usulan_jurusan;

                return [
                    ...$item->toArray(),

                    'kode_pengusul' => $item->user?->kode_pengusul ?? '-',
                    'nama_pengusul' => $item->user?->name ?? '-',

                    'no_usulan_surat' => $noUsulan ?: null,

                    'sumber_dana' => $item->sumber_dana,
                    'total_dana'  => 'Rp ' . number_format((float) ($item->nominal_dana ?? 0), 0, ',', '.'),

                    'created_at'        => $item->created_at?->format('Y-m-d'),
                    'tanggal_berangkat' => $item->tanggal_berangkat?->format('Y-m-d'),
                    'tanggal_kembali'   => $item->tanggal_kembali?->format('Y-m-d'),
                    'tanggal_penomoran_sekdir' => $item->tanggal_penomoran_sekdir?->format('Y-m-d'),

                    'personel' => $this->mapPersonel($item),
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
        $targets = $this->wadirTargets();

        return SuratTugas::query()
            ->with([
                'user:id,kode_pengusul,name',
                'detailPelaksanaTugas.personable',
            ])
            ->whereIn('diusulkan_kepada', $targets)
            ->when($filters['search'] ?? null, function ($q, $s) {
                $q->where(fn($xx) => $xx->where('perihal_tugas', 'like', "%$s%"));
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
        $filters['from'] = $filters['from'] ?? null;
        $filters['to']   = $filters['to'] ?? null;

        $filters['status'] = 'submitted_wadir_review';

        $paginate = $this->querySurat($filters)
            ->latest()
            ->paginate(5)
            ->withQueryString();

        $statusCounts = SuratTugas::whereIn('diusulkan_kepada', $this->wadirTargets())
            ->selectRaw('status_surat, COUNT(*) as count')
            ->groupBy('status_surat')
            ->pluck('count', 'status_surat')
            ->toArray();

        $today = now()->toDateString();
        $onDuty = SuratTugas::whereIn('diusulkan_kepada', $this->wadirTargets())
            ->where('status_surat', 'published')
            ->whereDate('tanggal_berangkat', '<=', $today)
            ->whereDate('tanggal_kembali', '>=', $today)
            ->count();

        return Inertia::render('Wadir/WadirDashboard', [
            'suratTugas'   => $this->mapPagination($paginate),
            'filters'      => $filters,
            'statusCounts' => array_merge($statusCounts, ['on_duty' => $onDuty]),
        ]);
    }

    public function history(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page', 'range']);
        $validStatuses = [
            'draft',
            'submitted_wadir_review',
            'revision_requested',
            'rejected',
            'approved_wadir',
            'pending_sekdir_numbering',
            'pending_direktur_signature',
            'published',
            'awaiting_proof_upload',
            'under_bku_review',
            'returned_for_correction',
            'completed',
        ];

        if (!empty($filters['status']) && !in_array($filters['status'], $validStatuses, true)) {
            unset($filters['status']);
        }

        $q = $this->querySurat($filters);

        if (empty($filters['status'])) {
            $q->whereNotIn('status_surat', ['draft', 'submitted_wadir_review']);
        }

        $paginate = $q->latest()->paginate(10)->withQueryString();

        return Inertia::render('Wadir/HistoryWadir', [
            'suratTugas' => $this->mapPagination($paginate),
            'filters'    => $filters,
        ]);
    }

    private function mapPersonel(SuratTugas $item)
    {
        $item->loadMissing('detailPelaksanaTugas.personable');

        return $item->detailPelaksanaTugas->map(function ($d) {
            $p     = $d->personable;
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
        })->values();
    }

    public function persetujuan(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page']);
        $filters['status'] = 'submitted_wadir_review';

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
        $data = SuratTugas::with([
            'user:id,kode_pengusul,name',
            'detailPelaksanaTugas.personable',
        ])->findOrFail($id);

        $data->created_at_formatted = $data->created_at?->format('Y-m-d');
        $data->setAttribute('personel', $this->mapPersonel($data));

        return Inertia::render('Wadir/ReviewWadir', [
            'data' => $data,
        ]);
    }

}
