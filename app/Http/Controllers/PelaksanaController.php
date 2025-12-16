<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\DokumenLampiranLaporan;

class PelaksanaController extends Controller
{
    private function baseQueryForPelaksana()
    {
        $user = auth()->user();
        $pegawaiId = $user->pegawai_id;

        $type = Pegawai::class;
        $typeLegacy = str_replace('\\', '\\\\', $type);

        return SuratTugas::query()
            ->with(['user:id,kode_pengusul,name'])
            ->whereHas('detailPelaksanaTugas', function ($q) use ($pegawaiId, $type, $typeLegacy) {
                $q->whereIn('personable_type', [$type, $typeLegacy])
                ->where('personable_id', $pegawaiId);
            });
    }

    public function uploadLampiranLaporan(Request $request, SuratTugas $suratTugas)
    {
        $surat = $this->baseQueryForPelaksana()
            ->whereKey($suratTugas->getKey())
            ->firstOrFail();

        $validated = $request->validate([
            'doc_type' => 'required|in:laporan,visum,bukti',
            'file'     => 'required|file|mimes:pdf|max:5120',
            'nominal'  => 'nullable|numeric|min:0',
        ]);

        if ($validated['doc_type'] === 'bukti' && !$request->filled('nominal')) {
            return back()->withErrors(['nominal' => 'Nominal wajib untuk Bukti.']);
        }

        DB::transaction(function () use ($surat, $validated, $request) {
        $laporan = $surat->laporan()->firstOrCreate(
            ['surat_tugas_id' => $surat->getKey()],
            [
                'user_id' => auth()->id(),
                'status_laporan' => 'draft',
                'tanggal_pengumpulan_laporan' => now()->toDateString(),
            ]
        );

        logger()->info('Laporan keys', [
            'getKey' => $laporan->getKey(),
            'laporan_id' => $laporan->laporan_id,
            'id' => $laporan->id,
        ]);
        
        $laporan->refresh();

            $folder = match ($validated['doc_type']) {
                'laporan' => 'perjadin/laporan',
                'visum'   => 'perjadin/visum',
                default   => 'perjadin/bukti',
            };

            $file = $request->file('file');
            $path = $file->store($folder, 'public');

            if (in_array($validated['doc_type'], ['laporan','visum'], true)) {
                $old = $laporan->dokumenLampiran()
                    ->where('jenis_dokumen', $validated['doc_type'])
                    ->latest('dokumen_lampiran_id')
                    ->first();

                if ($old) {
                    if ($old->path_file) Storage::disk('public')->delete($old->path_file);
                    $old->update([
                        'nama_file' => $file->getClientOriginalName(),
                        'path_file' => $path,
                        'tanggal_unggah' => now(),
                        'nominal' => null,
                    ]);
                } else {
                    $laporan->dokumenLampiran()->create([
                        'jenis_dokumen' => $validated['doc_type'],
                        'nama_file' => $file->getClientOriginalName(),
                        'path_file' => $path,
                        'tanggal_unggah' => now(),
                    ]);
                }
            } else {
                $laporan->dokumenLampiran()->create([
                    'jenis_dokumen' => 'bukti',
                    'nama_file' => $file->getClientOriginalName(),
                    'path_file' => $path,
                    'tanggal_unggah' => now(),
                    'nominal' => (float) $validated['nominal'],
                ]);
            }

            if ($surat->status_surat === 'published') {
                $surat->update(['status_surat' => 'awaiting_proof_upload']);
            }

        });

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function statusLaporan(Request $request)
    {
        $filters = $request->only(['search','from','to','page','range']);
        $filters['search'] = $filters['search'] ?? '';

        $allowed = [
            'awaiting_proof_upload',
            'published',
            'returned_for_correction',
        ];

        $q = $this->baseQueryForPelaksana()->whereIn('status_surat', $allowed);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $q->where(function ($qq) use ($s) {
                $qq->where('perihal_tugas', 'like', "%{$s}%")
                ->orWhere('nomor_surat_tugas_resmi', 'like', "%{$s}%")
                ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$s}%");
            });
        }

        $paginate = $q->latest()->paginate(10)->withQueryString();

        return Inertia::render('Pelaksana/StatusLaporan', [
            'auth' => ['user' => auth()->user()],
            'suratTugas' => $this->mapPagination($paginate),
            'filters' => $filters,
            'role' => 'pelaksana',
        ]);
    }


    public function lampiranIndex(Request $request, SuratTugas $suratTugas)
    {
        $surat = $this->baseQueryForPelaksana()
            ->whereKey($suratTugas->getKey())
            ->firstOrFail();

        $surat->loadMissing(['laporan.dokumenLampiran' => function ($q) {
            $q->orderByDesc('tanggal_unggah');
        }]);

        $laporan = $surat->laporan;

        $lampiran = $laporan
            ? $laporan->dokumenLampiran->map(fn ($d) => [
                'id' => $d->getKey(),
                'jenis_dokumen' => $d->jenis_dokumen,
                'nama_file' => $d->nama_file,
                'nominal' => $d->nominal,
                'tanggal_unggah' => optional($d->tanggal_unggah)->format('Y-m-d H:i:s'),
            ])->values()
            : collect([]);

        return Inertia::render('Laporan/LampiranIndex', [
            'auth' => ['user' => auth()->user()],
            'surat' => [
                'id' => $surat->getKey(),
                'nomor_surat_tugas_resmi' => $surat->nomor_surat_tugas_resmi,
                'perihal_tugas' => $surat->perihal_tugas,
                'status_surat' => $surat->status_surat,
            ],
            'lampiran' => $lampiran,
        ]);
    }

    public function lampiranShow(DokumenLampiranLaporan $lampiran)
    {
        $lampiran->loadMissing('laporan.suratTugas');
        $surat = $lampiran->laporan?->suratTugas;

        $this->baseQueryForPelaksana()->whereKey($surat?->getKey())->firstOrFail();

        return Inertia::render('Laporan/LampiranShow', [
            'auth' => ['user' => auth()->user()],
            'lampiran' => [
                'id' => $lampiran->getKey(),
                'jenis_dokumen' => $lampiran->jenis_dokumen,
                'nama_file' => $lampiran->nama_file,
                'nominal' => $lampiran->nominal,
                'tanggal_unggah' => optional($lampiran->tanggal_unggah)->format('Y-m-d H:i:s'),
            ],
            'fileUrl' => route('pelaksana.lampiran.file', $lampiran->getKey()),
        ]);
    }

    public function lampiranFile(DokumenLampiranLaporan $lampiran)
    {
        $lampiran->loadMissing('laporan.suratTugas');
        $surat = $lampiran->laporan?->suratTugas;

        $this->baseQueryForPelaksana()->whereKey($surat?->getKey())->firstOrFail();

        $absPath = Storage::disk('public')->path($lampiran->path_file);

        return response()->file($absPath, [
            'Content-Disposition' => 'inline; filename="'.$lampiran->nama_file.'"',
        ]);
    }

    public function submitLampiran(Request $request, SuratTugas $suratTugas)
    {
        $surat = $this->baseQueryForPelaksana()
            ->whereKey($suratTugas->getKey())
            ->firstOrFail();

        if (!in_array($surat->status_surat, ['awaiting_proof_upload', 'returned_for_correction'], true)) {
            return back()->withErrors([
                'submit' => 'Status surat tidak berada pada tahap upload / revisi.',
            ]);
        }

        DB::transaction(function () use ($surat) {
            $surat->loadMissing(['laporan.dokumenLampiran']);

            $laporan = $surat->laporan;

            if (!$laporan) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'submit' => 'Belum ada data laporan. Silakan upload minimal 1 dokumen terlebih dahulu.',
                ]);
            }

            $docs = $laporan->dokumenLampiran;

            $hasLaporan = $docs->contains(fn ($d) => $d->jenis_dokumen === 'laporan');
            $hasVisum   = $docs->contains(fn ($d) => $d->jenis_dokumen === 'visum');
            $hasBukti   = $docs->contains(fn ($d) => $d->jenis_dokumen === 'bukti'); // minimal 1 bukti

            if (!$hasLaporan || !$hasVisum || !$hasBukti) {
                $missing = [];
                if (!$hasLaporan) $missing[] = 'Laporan';
                if (!$hasVisum)   $missing[] = 'Visum';
                if (!$hasBukti)   $missing[] = 'Bukti minimal 1';

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'submit' => 'Dokumen belum lengkap: ' . implode(', ', $missing),
                ]);
            }

            $surat->update(['status_surat' => 'under_bku_review']);

            $laporan->update([
                'status_laporan' => 'submitted',
                'tanggal_pengumpulan_laporan' => now()->toDateString(),
            ]);
        });

        return redirect()
            ->route('pelaksana.status-laporan')
            ->with('success', 'Upload selesai. Berkas berhasil dikirim ke BKU untuk verifikasi.');

    }

    private function mapPagination($paginate)
    {
        return [
            'data' => $paginate->getCollection()->transform(function ($item) {
                $noUsulan = $item->nomor_surat_usulan_jurusan;

                return [
                    ...$item->toArray(),
                    'surat_tugas_id' => $item->surat_tugas_id,

                    'no_usulan_surat' => $noUsulan ?: null,
                    'total_dana' => 'Rp ' . number_format((float) ($item->nominal_dana ?? 0), 0, ',', '.'),

                    'created_at'        => $item->created_at?->format('Y-m-d'),
                    'tanggal_berangkat' => $item->tanggal_berangkat?->format('Y-m-d'),
                    'tanggal_kembali'   => $item->tanggal_kembali?->format('Y-m-d'),
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
        $filters['search'] = $filters['search'] ?? '';

        $q = $this->baseQueryForPelaksana();

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $q->where(function ($qq) use ($s) {
                $qq->where('perihal_tugas', 'like', "%{$s}%")
                   ->orWhere('nomor_surat_tugas_resmi', 'like', "%{$s}%")
                   ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['range']) && $filters['range'] !== 'all') {
            $now = now();
            $from = match ($filters['range']) {
                'weekly', 'week'   => $now->copy()->subDays(7),
                'monthly','month'  => $now->copy()->subMonth(),
                'yearly', 'year'   => $now->copy()->subYear(),
                default            => null,
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

        $paginate = $q->latest()->paginate(5)->withQueryString();

        $base = $this->baseQueryForPelaksana();
        $today = now()->toDateString();

        $statusCounts = [
            'total' => (clone $base)->count(),
            'completed' => (clone $base)->where('status_surat', 'completed')->count(),
            'published' => (clone $base)->where('status_surat', 'published')->count(),
            'on_duty' => (clone $base)
                ->where('status_surat', 'published')
                ->whereDate('tanggal_berangkat', '<=', $today)
                ->whereDate('tanggal_kembali', '>=', $today)
                ->count(),
            'revision_requested' => (clone $base)->whereIn('status_surat', [
                'returned_for_correction',
                'revision_requested',
                'sekdir_revision_requested',
                'direktur_revision_requested',
            ])->count(),
        ];

        return Inertia::render('Pelaksana/PelaksanaDashboard', [
            'auth'         => ['user' => auth()->user()],
            'suratTugas'   => $this->mapPagination($paginate),
            'filters'      => $filters,
            'statusCounts' => $statusCounts,
            'role'         => 'pelaksana',
        ]);
    }

    public function daftarLaporan(Request $request)
    {
        $filters = $request->only(['search', 'from', 'to', 'page', 'range', 'scope']);
        $filters['search'] = $filters['search'] ?? '';
        $filters['range']  = $filters['range'] ?? null;
        $filters['scope']  = $filters['scope'] ?? 'processing';

        $scopes = [
            'processing' => [
                'submitted_wadir_review',
                'revision_requested',
                'rejected',
                'approved_wadir',
                'pending_sekdir_numbering',
                'sekdir_revision_requested',
                'pending_direktur_signature',
                'direktur_revision_requested',
                'published',
            ],

            'published' => ['published'],

            'all' => [
                'submitted_wadir_review',
                'revision_requested',
                'rejected',
                'approved_wadir',
                'pending_sekdir_numbering',
                'sekdir_revision_requested',
                'pending_direktur_signature',
                'direktur_revision_requested',
                'published',
                'awaiting_proof_upload',
                'under_bku_review',
                'returned_for_correction',
                'completed',
            ],
        ];

        $statusSet = $scopes[$filters['scope']] ?? $scopes['processing'];

        $q = $this->baseQueryForPelaksana()
            ->whereIn('status_surat', $statusSet);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $q->where(function ($qq) use ($s) {
                $qq->where('perihal_tugas', 'like', "%{$s}%")
                    ->orWhere('nomor_surat_tugas_resmi', 'like', "%{$s}%")
                    ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['range']) && $filters['range'] !== 'all') {
            $now = now();
            $from = match ($filters['range']) {
                'weekly', 'week'   => $now->copy()->subDays(7),
                'monthly', 'month' => $now->copy()->subMonth(),
                'yearly', 'year'   => $now->copy()->subYear(),
                default            => null,
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

        $base = $this->baseQueryForPelaksana();
        $scopeCounts = [
            'processing' => (clone $base)->whereIn('status_surat', $scopes['processing'])->count(),
            'published'  => (clone $base)->whereIn('status_surat', $scopes['published'])->count(),
            'all'        => (clone $base)->whereIn('status_surat', $scopes['all'])->count(),
        ];

        return Inertia::render('Pelaksana/DaftarLaporan', [
            'auth'        => ['user' => auth()->user()],
            'suratTugas'  => $this->mapPagination($paginate),
            'filters'     => $filters,
            'scopeCounts' => $scopeCounts,
            'role'        => 'pelaksana',
        ]);
    }

    public function historypelaksana(Request $request)
    {
        $filters = $request->only(['search', 'from', 'to', 'page', 'range']);
        $filters['from']  = $filters['from'] ?? null;
        $filters['to']    = $filters['to'] ?? null;
        $filters['range'] = $filters['range'] ?? null;
        $filters['search'] = $filters['search'] ?? '';

        $allowedStatuses = [
            'awaiting_proof_upload',
            'under_bku_review',
            'returned_for_correction',
            'completed',
        ];

        $q = $this->baseQueryForPelaksana()
            ->whereIn('status_surat', $allowedStatuses);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $q->where(function ($qq) use ($s) {
                $qq->where('perihal_tugas', 'like', "%{$s}%")
                ->orWhere('nomor_surat_tugas_resmi', 'like', "%{$s}%")
                ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['range']) && $filters['range'] !== 'all') {
            $now = now();
            $from = match ($filters['range']) {
                'weekly', 'week'   => $now->copy()->subDays(7),
                'monthly','month'  => $now->copy()->subMonth(),
                'yearly', 'year'   => $now->copy()->subYear(),
                default            => null,
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

        return Inertia::render('Pelaksana/HistoryPelaksana', [
            'auth'      => ['user' => auth()->user()],
            'suratTugas'=> $this->mapPagination($paginate),
            'filters'   => $filters,
            'role'      => 'pelaksana',
        ]);
    }

}
