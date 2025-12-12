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
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'range' => $request->get('range'), // FILTER RANGE BARU
            'page' => $request->get('page', 1),
        ];

        $query = SuratTugas::query()
            ->with('detailPelaksanaTugas.personable')
            ->where('status_surat', 'pending_sekdir_numbering');

        if (!empty($filters['search'])) {
            $query->where('perihal_tugas', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status_surat', $filters['status']);
        }

        if (!empty($filters['range'])) {

            // Jika user pilih SEMUA RENTANG
            if ($filters['range'] === "") {
                // Jangan terapkan filter apa pun
            } else {
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
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->whereBetween('created_at', [
                $filters['from'],
                $filters['to']
            ]);
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
            'revision_requested' => SuratTugas::where('status_surat', 'revision_requested')->count(),
        ];

        return inertia('Sekdir/SekdirDashboard', [
            'suratTugas' => $mapped,
            'filters' => $filters,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function nomorSurat(Request $request)
    {
        $query = SuratTugas::query()
            ->where('status_surat', 'pending_sekdir_numbering')
            ->where('diusulkan_kepada', 'Wadir I');

        if (!empty($filters['search'])) {
            $query->where('perihal_tugas', 'like', "%{$filters['search']}%");
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
            'page' => $request->get('page', 1),
        ];

        $query = SuratTugas::where('status_surat', 'pending_sekdir_numbering');

        if (!empty($filters['search'])) {
            $query->where('perihal_tugas', 'like', "%{$filters['search']}%");
        }

        $surat = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Sekdir/HistoryPersetujuan', [
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
                ],
            ],
            'filters' => $request->only(['search', 'status', 'page']),
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
            'surat' => $surat,
            'next_number' => $lastSurat ? $lastSurat->nomor_urutan_surat + 1 : 1,
            'year' => now()->year,
        ]);
    }

    public function applyNomor(Request $request, $id)
    {
        $surat = SuratTugas::findOrFail($id);

        $request->validate([
            'nomor_urutan_surat' => 'required|integer',
            'kode_unit' => 'required|string',
            'kode_perihal' => 'required|string',
            'tahun' => 'required|integer',
        ]);

        $nomorFinal = "{$request->nomor_urutan_surat}/{$request->kode_unit}/{$request->kode_perihal}/{$request->tahun}";

        $surat->update([
            'nomor_urutan_surat' => $request->nomor_urutan_surat,
            'kode_unit_kerja' => $request->kode_unit,
            'kode_perihal' => $request->kode_perihal,
            'tahun_nomor_surat' => $request->tahun,
            'nomor_surat' => $nomorFinal,
            'status_surat' => 'diterbitkan_sekdir',
        ]);

        return redirect()
            ->route('sekdir.history')
            ->with('success', 'Nomor surat berhasil diterapkan!');
    }
}
