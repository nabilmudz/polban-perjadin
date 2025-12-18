<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SekdirController extends Controller
{
    private function baseQuery()
    {
        return SuratTugas::query()
            ->with([
                'user:id,kode_pengusul,name',
                'detailPelaksanaTugas.personable',
            ]);
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

                    // keep consistent with Wadir (Rp formatting)
                    'total_dana' => 'Rp ' . number_format((float) ($item->nominal_dana ?? 0), 0, ',', '.'),
                    'created_at' => $item->created_at?->format('Y-m-d'),
                    'tanggal_berangkat' => $item->tanggal_berangkat?->format('Y-m-d'),
                    'tanggal_kembali' => $item->tanggal_kembali?->format('Y-m-d'),

                    'personel' => $item->detailPelaksanaTugas->map(function ($d) {
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
                    })->values(),
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

    public function dashboard(Request $request)
    {
        $filters = $request->only(['search', 'from', 'to', 'page', 'range']);
        $filters['from']  = $filters['from'] ?? null;
        $filters['to']    = $filters['to'] ?? null;
        $filters['range'] = $filters['range'] ?? null;

        $q = $this->baseQuery()
            ->where('status_surat', 'pending_sekdir_numbering');

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $q->where('perihal_tugas', 'like', "%{$s}%");
        }

        if (!empty($filters['range']) && $filters['range'] !== 'all') {
            $now = now();
            $from = match ($filters['range']) {
                'weekly'  => $now->copy()->subDays(7),
                'monthly' => $now->copy()->subMonth(),
                'yearly'  => $now->copy()->subYear(),
                default   => null,
            };

            if ($from) {
                $q->whereBetween('created_at', [$from->startOfDay(), $now->endOfDay()]);
            }
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $q->whereBetween('created_at', [
                now()->parse($filters['from'])->startOfDay(),
                now()->parse($filters['to'])->endOfDay(),
            ]);
        }

        $paginate = $q->latest()->paginate(10)->withQueryString();

        $today = now()->toDateString();
        $statusCounts = [
            'total' => SuratTugas::count(),
            'completed' => SuratTugas::where('status_surat', 'completed')->count(),
            'published' => SuratTugas::where('status_surat', 'published')->count(),
            'on_duty' => SuratTugas::where('status_surat', 'published')
                ->whereDate('tanggal_berangkat', '<=', $today)
                ->whereDate('tanggal_kembali', '>=', $today)
                ->count(),
            'revision_requested' => SuratTugas::where('status_surat', 'revision_requested')->count(),
        ];

        return inertia('Sekdir/SekdirDashboard', [
            'suratTugas'    => $this->mapPagination($paginate),
            'filters'       => $filters,
            'statusCounts'  => $statusCounts,
        ]);
    }

    public function history(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page', 'range']);
        $filters['from']  = $filters['from'] ?? null;
        $filters['to']    = $filters['to'] ?? null;
        $filters['range'] = $filters['range'] ?? null;
        $filters['status'] = $filters['status'] ?? null;

        $allowedStatuses = $this->historyAllowedStatuses();

        if (!empty($filters['status']) && !in_array($filters['status'], $allowedStatuses, true)) {
            $filters['status'] = null;
        }

        $q = $this->baseQuery()
            ->whereIn('status_surat', $allowedStatuses);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $q->where('perihal_tugas', 'like', "%{$s}%");
        }

        if (!empty($filters['status'])) {
            $q->where('status_surat', $filters['status']);
        }

        if (!empty($filters['range']) && $filters['range'] !== 'all') {
            $now = now();
            $from = match ($filters['range']) {
                'weekly'  => $now->copy()->subDays(7),
                'monthly' => $now->copy()->subMonth(),
                'yearly'  => $now->copy()->subYear(),
                default   => null,
            };

            if ($from) {
                $q->whereBetween('created_at', [$from->startOfDay(), $now->endOfDay()]);
            }
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $q->whereBetween('created_at', [
                now()->parse($filters['from'])->startOfDay(),
                now()->parse($filters['to'])->endOfDay(),
            ]);
        }

        $paginate = $q->latest()->paginate(10)->withQueryString();

        return inertia('Sekdir/HistoryPersetujuan', [
            'surat'   => $this->mapPagination($paginate),
            'filters' => $filters,
        ]);
    }

    public function nomorSurat(Request $request)
    {
        $filters = $request->only(['search', 'from', 'to', 'page', 'range']);
        $filters['from']  = $filters['from'] ?? null;
        $filters['to']    = $filters['to'] ?? null;
        $filters['range'] = $filters['range'] ?? null;

        $q = $this->baseQuery()
            ->where('status_surat', 'pending_sekdir_numbering');

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $q->where('perihal_tugas', 'like', "%{$s}%");
        }

        if (!empty($filters['range']) && $filters['range'] !== 'all') {
            $now = now();
            $from = match ($filters['range']) {
                'weekly'  => $now->copy()->subDays(7),
                'monthly' => $now->copy()->subMonth(),
                'yearly'  => $now->copy()->subYear(),
                default   => null,
            };

            if ($from) {
                $q->whereBetween('created_at', [$from->startOfDay(), $now->endOfDay()]);
            }
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $q->whereBetween('created_at', [
                now()->parse($filters['from'])->startOfDay(),
                now()->parse($filters['to'])->endOfDay(),
            ]);
        }

        $paginate = $q->latest()->paginate(10)->withQueryString();

        return inertia('Sekdir/NomorSurat', [
            'surat'    => $this->mapPagination($paginate),
            'filters'  => $filters,
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
    private function historyAllowedStatuses(): array
    {
        return [
            'pending_direktur_signature',
            'published',
            'awaiting_proof_upload',
            'under_bku_review',
            'returned_for_correction',
            'completed',
        ];
    }

    public function applyNomor(Request $request, $id)
    {
        $data = $request->validate([
            'nomor_urutan_surat' => ['required', 'integer', 'min:1'],
            'kode_unit'          => ['required', 'string', 'max:20'],
            'kode_perihal'       => ['required', 'string', 'max:50'],
            'tahun'              => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        DB::transaction(function () use ($id, $data) {
            $surat = SuratTugas::query()
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($surat->status_surat !== 'pending_sekdir_numbering') {
                abort(409, 'Surat sudah tidak berada pada tahap penomoran Sekdir.');
            }

            $exists = SuratTugas::query()
                ->where('tahun_nomor_surat', $data['tahun'])
                ->where('kode_unit_kerja', $data['kode_unit'])
                ->where('kode_perihal', $data['kode_perihal'])
                ->where('nomor_urutan_surat', $data['nomor_urutan_surat'])
                ->whereKeyNot($surat->getKey())
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'nomor_urutan_surat' =>
                        'Nomor surat sudah digunakan untuk kombinasi tahun/unit/perihal tersebut.'
                ]);
            }

            $nomorResmi = sprintf(
                '%03d/%s/%s/%d',
                $data['nomor_urutan_surat'],
                $data['kode_unit'],
                $data['kode_perihal'],
                $data['tahun']
            );

            $surat->update([
                'nomor_urutan_surat'       => $data['nomor_urutan_surat'],
                'kode_unit_kerja'          => $data['kode_unit'],
                'kode_perihal'             => $data['kode_perihal'],
                'tahun_nomor_surat'        => $data['tahun'],
                'tanggal_penomoran_sekdir' => now(),
                'status_surat'             => 'pending_direktur_signature',
                'nomor_surat_tugas_resmi'  => $nomorResmi,
            ]);
        });

        return redirect()
            ->route('sekdir.nomorsurat')
            ->with('success', 'Nomor surat berhasil diterapkan dan dikirim ke tahap Direktur.');
    }
}
