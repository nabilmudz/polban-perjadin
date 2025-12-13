<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SuratTugas;
use Illuminate\Support\Facades\Redirect;

class DirekturController extends Controller
{
    private function mapPagination($paginate)
    {
        return [
            'data' => $paginate->getCollection()->transform(function ($item) {
                $item->loadMissing('detailPelaksanaTugas.personable');

                $personel = $item->detailPelaksanaTugas->map(function ($d) {
                    $p = $d->personable;
                    if (!$p) return null;

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
                })->filter();

                return [
                    ...$item->toArray(),
                    'nama_kegiatan' => $item->nama_kegiatan ?? $item->perihal_tugas,
                    'created_at' => $item->created_at->format('Y-m-d'),
                    'tanggal_pelaksanaan' => $item->tanggal_berangkat ? $item->tanggal_berangkat->format('Y-m-d') : '-',
                    'no_usulan_surat' => $item->nomor_surat_usulan_jurusan ?? '-', 
                    // ----------------------------------------------------------
                    'personel' => $personel,
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
            'links' => $paginate->toArray()['links'] ?? [],
        ];
    }

    public function direktur(Request $request)
    {
        $user = auth()->user();

        $query = SuratTugas::query()
            ->with(['detailPelaksanaTugas.personable']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('perihal_tugas', 'like', "%{$request->search}%")
                    ->orWhere('status_surat', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status_surat', $request->status);
        }

        if ($request->filled('range') && $request->range !== 'all') {
            $now = now();
            $from = null;

            if ($request->range === 'weekly') {
                $from = $now->copy()->subDays(7);
            } elseif ($request->range === 'monthly') {
                $from = $now->copy()->subMonth();
            } elseif ($request->range === 'yearly') {
                $from = $now->copy()->subYear();
            }

            if ($from) {
                $query->whereBetween('created_at', [
                    $from->format('Y-m-d'),
                    $now->format('Y-m-d')
                ]);
            }
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        }

        $paginate = $query->latest()->paginate(5)->withQueryString();

        $statusCounts = [
            'completed' => SuratTugas::where('status_surat', 'completed')->count(),
            'published' => SuratTugas::where('status_surat', 'published')->count(),
            'on_duty' => SuratTugas::where('status_surat', 'approved')
                            ->whereDate('tanggal_berangkat', '<=', now())
                            ->whereDate('tanggal_kembali', '>=', now())
                            ->count(),
            'revision_requested' => SuratTugas::where('status_surat', 'revision_requested')->count(),
        ];

        $totalPengusulan = SuratTugas::count();

        return inertia('Direktur/DirekturDashboard', [
            'suratTugas' => $this->mapPagination($paginate),
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
                'from' => $request->from,
                'to' => $request->to,
                'range' => $request->range,
            ],
            'totalPengusulan' => $totalPengusulan,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function persetujuan(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'range']);

        $query = SuratTugas::with(['pengusul', 'wadir', 'detailPelaksanaTugas.personable'])
            ->where('status_surat', 'pending_direktur_signature');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_kegiatan', 'like', "%{$request->search}%")
                  ->orWhere('perihal_tugas', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_resmi', 'like', "%{$request->search}%");
            });
        }

        if (!empty($filters['range']) && $filters['range'] !== 'all') {
            $now = now();
            $from = null;
            if ($filters['range'] === 'weekly') $from = $now->copy()->subDays(7);
            elseif ($filters['range'] === 'monthly') $from = $now->copy()->subMonth();
            elseif ($filters['range'] === 'yearly') $from = $now->copy()->subYear();

            if ($from) {
                $query->whereBetween('created_at', [$from->format('Y-m-d'), $now->format('Y-m-d')]);
            }
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
             $query->whereBetween('created_at', [$filters['from'], $filters['to']]);
        }

        $paginate = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Direktur/DaftarPersetujuan', [
            'suratTugas' => $this->mapPagination($paginate),
            'filters' => $filters,
        ]);
    }

    public function history(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'range']);

        $statuses = [
            'published',
            'awaiting_proof_upload',
            'under_bku_review',
            'returned_for_correction',
            'completed',
            'rejected' 
        ];

        $query = SuratTugas::with(['detailPelaksanaTugas.personable'])
            ->whereIn('status_surat', $statuses);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_kegiatan', 'like', "%{$request->search}%")
                  ->orWhere('perihal_tugas', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_resmi', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all' && in_array($request->status, $statuses)) {
            $query->where('status_surat', $request->status);
        }

        if (!empty($filters['range']) && $filters['range'] !== 'all') {
            $now = now();
            $from = null;
            if ($filters['range'] === 'weekly') $from = $now->copy()->subDays(7);
            elseif ($filters['range'] === 'monthly') $from = $now->copy()->subMonth();
            elseif ($filters['range'] === 'yearly') $from = $now->copy()->subYear();

            if ($from) {
                $query->whereBetween('created_at', [$from->format('Y-m-d'), $now->format('Y-m-d')]);
            }
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
             $query->whereBetween('created_at', [$filters['from'], $filters['to']]);
        }

        $paginate = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Direktur/DirekturHistory', [
            'history' => $this->mapPagination($paginate),
            'filters' => $filters,
        ]);
    }

    public function show($id)
    {
        $surat = SuratTugas::with(['detailPelaksanaTugas.personable', 'user'])->findOrFail($id);
        
        $surat->loadMissing('detailPelaksanaTugas.personable');
        $personel = $surat->detailPelaksanaTugas->map(function ($d) {
            $p = $d->personable;
            if (!$p) return null;
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
        })->filter();

        $suratData = [
            ...$surat->toArray(),
            'personel' => $personel,
            'no_usulan_surat' => $surat->nomor_surat_usulan_jurusan ?? '-',
            // --------------------------------------------------------- 
            'created_at_formatted' => $surat->created_at->format('Y-m-d'),
        ];

        return Inertia::render('Direktur/ReviewDirektur', [
            'data' => $suratData,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $surat = SuratTugas::findOrFail($id);
        
        if ($surat->status_surat !== 'pending_direktur_signature') {
             return Redirect::back()->with('error', 'Status surat tidak valid untuk aksi ini.');
        }

        $surat->update([
            'status_surat' => 'approved', 
        ]);

        return Redirect::route('direktur.daftarpersetujuan')
            ->with('success', 'Surat tugas berhasil disetujui dan diterbitkan.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|max:1000',
        ]);

        $surat = SuratTugas::findOrFail($id);

        $surat->update([
            'status_surat' => 'rejected',
            'catatan' => $request->catatan, 
        ]);

        return Redirect::route('direktur.daftarpersetujuan')
            ->with('success', 'Surat tugas ditolak.');
    }

    public function revise(Request $request, $id)
    {
         $request->validate([
            'catatan' => 'required|string|max:1000',
        ]);
        
        $surat = SuratTugas::findOrFail($id);

        $surat->update([
            'status_surat' => 'revision_requested', 
            'catatan' => $request->catatan,
        ]);

        return Redirect::route('direktur.daftarpersetujuan')
            ->with('success', 'Surat tugas dikembalikan untuk revisi.');
    }
}