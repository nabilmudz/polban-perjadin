<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class DirekturController extends Controller
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
                    'id' => $item->getKey(),
                    ...$item->toArray(),
                    'kode_pengusul' => $item->user?->kode_pengusul ?? '-',
                    'nama_pengusul' => $item->user?->name ?? '-',

                    'no_usulan_surat' => $noUsulan ?: null,

                    'nomor_surat_tugas_resmi' => $item->nomor_surat_tugas_resmi ?? null,
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
            ->where('status_surat', 'pending_direktur_signature');

        if (!empty($filters['search'])) {
            $s = $filters['search'];

            $q->where(function ($qq) use ($s) {
                $qq->where('perihal_tugas', 'like', "%{$s}%")
                   ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$s}%")
                   ->orWhere('nomor_surat_tugas_resmi', 'like', "%{$s}%");
            });
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
            'returned_for_correction' => SuratTugas::where('status_surat', 'returned_for_correction')->count(),
        ];

        return inertia('Direktur/DirekturDashboard', [
            'suratTugas'   => $this->mapPagination($paginate),
            'filters'      => $filters,
            'statusCounts' => $statusCounts,
        ]);
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

    public function daftarPersetujuan(Request $request)
    {
        $filters = $request->only(['search', 'from', 'to', 'page', 'range']);
        $filters['from']  = $filters['from'] ?? null;
        $filters['to']    = $filters['to'] ?? null;
        $filters['range'] = $filters['range'] ?? null;

        $q = $this->baseQuery()
            ->where('status_surat', 'pending_direktur_signature');

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $q->where(function ($qq) use ($s) {
                $qq->where('perihal_tugas', 'like', "%{$s}%")
                   ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$s}%")
                   ->orWhere('nomor_surat_tugas_resmi', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['range']) && $filters['range'] !== 'all') {
            $now = now();
            $from = match ($filters['range']) {
                'weekly'  => $now->copy()->subDays(7),
                'monthly' => $now->copy()->subMonth(),
                'yearly'  => $now->copy()->subYear(),
                default   => null,
            };
            if ($from) $q->whereBetween('created_at', [$from->startOfDay(), $now->endOfDay()]);
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $q->whereBetween('created_at', [
                now()->parse($filters['from'])->startOfDay(),
                now()->parse($filters['to'])->endOfDay(),
            ]);
        }

        $paginate = $q->latest()->paginate(10)->withQueryString();

        return inertia('Direktur/DaftarPersetujuan', [
            'suratTugas' => $this->mapPagination($paginate),
            'filters' => $filters,
        ]);
    }

    public function review($id)
    {
        $surat = $this->baseQuery()->findOrFail($id);

        return inertia('Direktur/ReviewPersetujuan', [
            'surat' => [
                'id' => $surat->getKey(),
                ...$surat->toArray(),
                'barcode_url' => $surat->barcode_path
                    ? "/storage/{$surat->barcode_path}"
                    : null,
            ],
        ]);
    }

    public function approve(Request $request, $id)
    {
        DB::transaction(function () use ($id) {
            $surat = SuratTugas::query()
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($surat->status_surat !== 'pending_direktur_signature') {
                abort(409, 'Surat sudah tidak berada pada tahap tanda tangan Direktur.');
            }

            $token = Str::random(40);
            $verifyUrl = route('verifikasi.surat-tugas', ['token' => $token]);

            $barcodePath = $this->generateQrToStorage($verifyUrl, $surat->getKey());

            $surat->update([
                'status_surat'                 => 'published',
                'tanggal_tte_direktur'         => now(),
                'tanggal_persetujuan_direktur' => now(),
                'direktur_approver_id'         => auth()->id(),

                'barcode_token'                => $token,
                'barcode_payload'              => $verifyUrl,
                'barcode_path'                 => $barcodePath,
                'path_file_surat_tugas_final' => null,
            ]);
        });

        return redirect()
            ->route('direktur.daftarpersetujuan')
            ->with('success', 'Surat tugas berhasil dipublish + QR verifikasi dibuat.');
    }

    public function reject(Request $request, $id)
    {
        $data = $request->validate([
            'catatan' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($id, $data) {
            $surat = SuratTugas::query()->whereKey($id)->lockForUpdate()->firstOrFail();

            if ($surat->status_surat !== 'pending_direktur_signature') {
                abort(409, 'Status surat tidak valid untuk aksi ini.');
            }

            $surat->update([
                'status_surat'   => 'rejected',
                'catatan_revisi' => $data['catatan'],
                'direktur_approver_id' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('direktur.daftarpersetujuan')
            ->with('success', 'Surat tugas ditolak.');
    }

    public function revise(Request $request, $id)
    {
        $data = $request->validate([
            'catatan' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($id, $data) {
            $surat = SuratTugas::query()->whereKey($id)->lockForUpdate()->firstOrFail();

            if ($surat->status_surat !== 'pending_direktur_signature') {
                abort(409, 'Status surat tidak valid untuk aksi ini.');
            }

            $surat->update([
                'status_surat'    => 'direktur_revision_requested',
                'catatan_revisi'  => $data['catatan'],
                'direktur_approver_id' => auth()->id(),
                'tanggal_persetujuan_direktur' => now(),
            ]);
        });

        return redirect()->route('direktur.daftarpersetujuan')
            ->with('success', 'Surat tugas dikembalikan untuk revisi.');
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

        return inertia('Direktur/DaftarPersetujuan', [
            'suratTugas' => $this->mapPagination($paginate),
            'filters' => $filters,
        ]);
    }

    public function history(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page', 'range']);
        $filters['from']  = $filters['from'] ?? null;
        $filters['to']    = $filters['to'] ?? null;
        $filters['range'] = $filters['range'] ?? null;
        $filters['status'] = $filters['status'] ?? null;

        $allowedStatuses = [
            'published',
            'awaiting_proof_upload',
            'under_bku_review',
            'returned_for_correction',
            'completed',
        ];

        if (!empty($filters['status']) && !in_array($filters['status'], $allowedStatuses, true)) {
            $filters['status'] = null;
        }

        $q = $this->baseQuery()
            ->whereIn('status_surat', $allowedStatuses);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $q->where(function ($qq) use ($s) {
                $qq->where('perihal_tugas', 'like', "%{$s}%")
                ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$s}%")
                ->orWhere('nomor_surat_tugas_resmi', 'like', "%{$s}%");
            });
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

        return inertia('Direktur/DirekturHistory', [
            'suratTugas' => $this->mapPagination($paginate),
            'filters'    => $filters,
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
            'created_at_formatted' => $surat->created_at->format('Y-m-d'),
        ];

        return inertia('Direktur/ReviewDirektur', [
            'data' => $suratData,
        ]);
    }

    private function generateQrToStorage(string $payload, $suratId): string
    {
        $svg = QrCode::format('svg')->size(220)->margin(1)->generate($payload);

        $path = "barcode-tte/surat-tugas-{$suratId}.svg";

        $ok = Storage::disk('public')->put($path, $svg);
        if (!$ok) {
            throw new \RuntimeException("Failed to write QR to storage path: {$path}");
        }

        return $path;
    }

}