<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SekdirController extends Controller
{
    public function dashboard(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'range' => $request->get('range'),
            'page' => $request->get('page', 1),
        ];

        $query = SuratTugas::query()
            ->with('detailPelaksanaTugas.personable')
            ->where('status_surat', 'pending_sekdir_numbering');

        if ($filters['search']) {
            $query->where('perihal_tugas', 'like', "%{$filters['search']}%");
        }

        if ($filters['status']) {
            $query->where('status_surat', $filters['status']);
        }

        if (!empty($filters['range']) && $filters['range'] !== 'all') {
            $now = now();

            if ($filters['range'] === 'weekly') {
                $from = $now->copy()->subDays(7);
            } elseif ($filters['range'] === 'monthly') {
                $from = $now->copy()->subMonth();
            } elseif ($filters['range'] === 'yearly') {
                $from = $now->copy()->subYear();
            }

            $query->whereBetween('created_at', [
                $from->format('Y-m-d'),
                $now->format('Y-m-d')
            ]);
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->whereBetween('created_at', [$filters['from'], $filters['to']]);
        }

        $surat = $query->latest()->paginate(10)->withQueryString();

        $mapped = [
            'data' => $surat->getCollection()->transform(function ($item) {
                $item->loadMissing('detailPelaksanaTugas.personable');

                $personel = $item->detailPelaksanaTugas->map(function ($d) {
                    $p = $d->personable;
                    $isMhs = str_contains($d->personable_type, 'Mahasiswa');

                    return [
                        'id' => $p->id,
                        'type' => $isMhs ? 'mahasiswa' : 'pegawai',
                        'nama' => $p->nama,
                        'nip' => $isMhs ? null : ($p->nip ?? null),
                        'nim' => $isMhs ? ($p->nim ?? null) : null,
                        'pangkat' => $p->pangkat ?? null,
                        'golongan' => $p->golongan ?? null,
                        'jabatan' => $p->jabatan ?? null,
                        'jurusan' => $p->jurusan ?? null,
                        'prodi' => $p->prodi ?? null,
                    ];
                });

                return [
                    ...$item->toArray(),
                    'personel' => $personel,
                    'created_at' => $item->created_at?->format('Y-m-d'),
                    'tanggal_berangkat' => $item->tanggal_berangkat?->format('Y-m-d'),
                    'nominal_dana' => $item->nominal_dana,
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
            ]
        ];

        $statusCounts = [
            'total' => SuratTugas::count(),
            'completed' => SuratTugas::where('status_surat', 'completed')->count(),
            'published' => SuratTugas::where('status_surat', 'published')->count(),
            'on_duty' => SuratTugas::where('status_surat', 'published')
                ->whereDate('tanggal_berangkat', '<=', now())
                ->whereDate('tanggal_kembali', '>=', now())
                ->count(),
            'revision_requested' => SuratTugas::where('status_surat', 'revision_requested')->count()
        ];

        return inertia('Sekdir/SekdirDashboard', [
            'suratTugas' => $mapped,
            'filters' => $filters,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function history(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'range' => $request->get('range'),
            'page' => $request->get('page', 1),
        ];

        $query = SuratTugas::query()
            ->with('detailPelaksanaTugas.personable')
            ->where('status_surat', 'completed');

        if ($filters['search']) {
            $query->where('perihal_tugas', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['range']) && $filters['range'] !== 'all') {
            $now = now();

            if ($filters['range'] === 'weekly') {
                $from = $now->copy()->subDays(7);
            } elseif ($filters['range'] === 'monthly') {
                $from = $now->copy()->subMonth();
            } elseif ($filters['range'] === 'yearly') {
                $from = $now->copy()->subYear();
            }

            $query->whereBetween('created_at', [
                $from->format('Y-m-d'),
                $now->format('Y-m-d')
            ]);
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->whereBetween('created_at', [$filters['from'], $filters['to']]);
        }

        $surat = $query->latest()->paginate(10)->withQueryString();

        $mapped = [
            'data' => $surat->getCollection()->transform(function ($item) {
                $item->loadMissing('detailPelaksanaTugas.personable');

                $personel = $item->detailPelaksanaTugas->map(function ($d) {
                    $p = $d->personable;
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
                });

                return [
                    ...$item->toArray(),
                    'personel' => $personel,
                    'created_at' => $item->created_at?->format('Y-m-d'),
                    'tanggal_berangkat' => $item->tanggal_berangkat?->format('Y-m-d'),
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
            ]
        ];

        return inertia('Sekdir/HistoryPersetujuan', [
            'surat' => $mapped,
            'filters' => $filters,
        ]);
    }

    public function nomorSurat(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'from'   => $request->get('from'),
            'to'     => $request->get('to'),
            'range'  => $request->get('range'),
            'page'   => $request->get('page', 1),
        ];

        $query = SuratTugas::query()
            ->with('detailPelaksanaTugas.personable')
            ->where('status_surat', 'pending_sekdir_numbering');

        if ($filters['search']) {
            $query->where('perihal_tugas', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['range']) && $filters['range'] !== 'all') {
            $now = now();

            if ($filters['range'] === 'weekly') {
                $from = $now->copy()->subDays(7);
            } elseif ($filters['range'] === 'monthly') {
                $from = $now->copy()->subMonth();
            } elseif ($filters['range'] === 'yearly') {
                $from = $now->copy()->subYear();
            }

            $query->whereBetween('created_at', [
                $from->format('Y-m-d'),
                $now->format('Y-m-d')
            ]);
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->whereBetween('created_at', [$filters['from'], $filters['to']]);
        }

        $surat = $query->latest()->paginate(10)->withQueryString();

        return inertia('Sekdir/NomorSurat', [
            'surat' => [
                'data' => $surat->items(),
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
                ]
            ],
            'filters' => $filters,
        ]);
    }

    public function reviewNomorSurat($id)
    {
        $surat = SuratTugas::with('detailPelaksanaTugas.personable')->findOrFail($id);

        return inertia('Sekdir/ReviewNomorSurat', [
            'surat' => $surat,
            'next_number' => SuratTugas::max('nomor_urutan_surat') + 1,
            'year' => now()->format('Y'),
        ]);
    }
}
