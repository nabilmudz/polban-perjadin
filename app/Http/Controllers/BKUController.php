<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\DokumenLampiranLaporan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BKUController extends Controller
{
    private function mapSuratWithPersonel($item)
    {
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
        })->filter()->values();

        return array_merge($item->toArray(), [ 
            'id' => $item->id,
            'nama_kegiatan' => $item->nama_kegiatan ?? $item->perihal_tugas,
            'perihal_tugas' => $item->perihal_tugas,
            'tanggal_pengusulan' => $item->created_at->format('Y-m-d'),
            'tanggal_berangkat' => $item->tanggal_berangkat ? $item->tanggal_berangkat->format('Y-m-d') : '-',
            'tanggal_pelaksanaan' => $item->tanggal_berangkat ? $item->tanggal_berangkat->format('Y-m-d') : '-',
            'no_usulan_surat' => $item->nomor_surat_usulan_jurusan ?? '-', 
            'nomor_surat_resmi' => $item->nomor_surat_resmi ?? '-',
            'status_surat' => $item->status_surat,
            'diusulkan_kepada' => $item->wadir ? $item->wadir->name : 'Wakil Direktur I',
            'personel' => $personel, 
            'nominal_biaya' => $item->nominal_biaya ?? 0,
        ]);
    }


    private function baseQueryForBku()
    {
        return SuratTugas::query()
            ->with(['user:id,kode_pengusul,name'])
            ->with(['laporan.dokumenLampiran' => function ($q) {
                $q->orderByDesc('tanggal_unggah');
            }]);
    }

    public function lampiranIndex(Request $request, SuratTugas $suratTugas)
    {
        $surat = $this->baseQueryForBku()
            ->whereKey($suratTugas->getKey())
            ->firstOrFail();

        // BKU hanya perlu akses jika sudah masuk proses verifikasi
        if (!in_array($surat->status_surat, ['under_bku_review', 'completed', 'returned_for_correction'], true)) {
            abort(403, 'Surat belum masuk tahap verifikasi BKU.');
        }

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

        $totalBukti = $lampiran
            ->where('jenis_dokumen', 'bukti')
            ->sum(fn ($d) => (float) ($d['nominal'] ?? 0));

        return Inertia::render('Laporan/LampiranIndex', [
            'auth' => ['user' => auth()->user()],
            'role' => 'bku',
            'surat' => [
                'id' => $surat->getKey(),
                'nomor_surat_tugas_resmi' => $surat->nomor_surat_tugas_resmi,
                'perihal_tugas' => $surat->perihal_tugas,
                'status_surat' => $surat->status_surat,
                'nominal_dana' => $surat->nominal_dana,
            ],
            'laporanMeta' => $laporan ? [
                'status_laporan' => $laporan->status_laporan,
                'catatan_verifikasi_bku' => $laporan->catatan_verifikasi_bku,
                'tanggal_verifikasi_bku' => $laporan->tanggal_verifikasi_bku,
                'verifikator_bku_user_id' => $laporan->verifikator_bku_user_id,
            ] : null,
            'lampiran' => $lampiran,
            'totalBukti' => $totalBukti,
        ]);
    }

    public function lampiranShow(DokumenLampiranLaporan $lampiran)
    {
        $lampiran->loadMissing('laporan.suratTugas');
        $surat = $lampiran->laporan?->suratTugas;
        if (!$surat) abort(404);

        if (!in_array($surat->status_surat, ['under_bku_review', 'completed', 'returned_for_correction'], true)) {
            abort(403);
        }

        return Inertia::render('Laporan/LampiranShow', [
            'auth' => ['user' => auth()->user()],
            'role' => 'bku',
            'lampiran' => [
                'id' => $lampiran->getKey(),
                'jenis_dokumen' => $lampiran->jenis_dokumen,
                'nama_file' => $lampiran->nama_file,
                'nominal' => $lampiran->nominal,
                'tanggal_unggah' => optional($lampiran->tanggal_unggah)->format('Y-m-d H:i:s'),
            ],
            'fileUrl' => route('bku.lampiran.file', $lampiran->getKey()),
        ]);
    }

    public function lampiranFile(DokumenLampiranLaporan $lampiran)
    {
        $lampiran->loadMissing('laporan.suratTugas');
        $surat = $lampiran->laporan?->suratTugas;
        if (!$surat) abort(404);

        if (!in_array($surat->status_surat, ['under_bku_review', 'completed', 'returned_for_correction'], true)) {
            abort(403);
        }

        $absPath = Storage::disk('public')->path($lampiran->path_file);

        return response()->file($absPath, [
            'Content-Disposition' => 'inline; filename="'.$lampiran->nama_file.'"',
        ]);
    }

    public function approveLampiran(Request $request, SuratTugas $suratTugas)
    {
        $surat = $this->baseQueryForBku()
            ->whereKey($suratTugas->getKey())
            ->firstOrFail();

        if ($surat->status_surat !== 'under_bku_review') {
            return back()->withErrors(['verify' => 'Status surat tidak berada pada tahap verifikasi BKU.']);
        }

        $validated = $request->validate([
            'catatan' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($surat, $validated) {
            $laporan = $surat->laporan;
            if (!$laporan) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'verify' => 'Data laporan tidak ditemukan.',
                ]);
            }

            $docs = $laporan->dokumenLampiran ?? collect();
            $hasLaporan = $docs->contains(fn ($d) => $d->jenis_dokumen === 'laporan');
            $hasVisum   = $docs->contains(fn ($d) => $d->jenis_dokumen === 'visum');
            $hasBukti   = $docs->contains(fn ($d) => $d->jenis_dokumen === 'bukti');

            if (!$hasLaporan || !$hasVisum || !$hasBukti) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'verify' => 'Dokumen belum lengkap untuk diverifikasi.',
                ]);
            }

            $surat->update(['status_surat' => 'completed']);

            $laporan->update([
                'status_laporan' => 'approved',
                'catatan_verifikasi_bku' => $validated['catatan'] ?? null,
                'tanggal_verifikasi_bku' => now()->toDateString(),
                'verifikator_bku_user_id' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('bku.daftarlaporanperjalanan')
            ->with('success', 'Verifikasi berhasil. Laporan disetujui (Completed).');
    }

    public function returnLampiran(Request $request, SuratTugas $suratTugas)
    {
        $surat = $this->baseQueryForBku()
            ->whereKey($suratTugas->getKey())
            ->firstOrFail();

        if ($surat->status_surat !== 'under_bku_review') {
            return back()->withErrors(['verify' => 'Status surat tidak berada pada tahap verifikasi BKU.']);
        }

        $validated = $request->validate([
            'catatan' => 'required|string|max:2000',
        ]);

        DB::transaction(function () use ($surat, $validated) {
            $laporan = $surat->laporan;
            if (!$laporan) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'verify' => 'Data laporan tidak ditemukan.',
                ]);
            }

            $surat->update(['status_surat' => 'returned_for_correction']);

            $laporan->update([
                'status_laporan' => 'draft',
                'catatan_verifikasi_bku' => $validated['catatan'],
                'tanggal_verifikasi_bku' => now()->toDateString(),
                'verifikator_bku_user_id' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('bku.daftarlaporanperjalanan')
            ->with('success', 'Laporan dikembalikan untuk koreksi.');
    }
    public function dashboard(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'range']);

        $totalPengusulan = SuratTugas::count();
        
        $statusCounts = [
            'completed' => SuratTugas::where('status_surat', 'completed')->count(),
            'published' => SuratTugas::where('status_surat', 'published')->count(),
            'on_duty'   => SuratTugas::whereIn('status_surat', ['approved', 'published'])
                            ->whereDate('tanggal_berangkat', '<=', now())
                            ->whereDate('tanggal_kembali', '>=', now())
                            ->count(),
            'revision_requested' => SuratTugas::whereIn('status_surat', ['revision_requested', 'returned_for_correction'])->count(),
        ];

        $bkuStatuses = [
            'awaiting_proof_upload', 
            'under_bku_review', 
            'returned_for_correction', 
            'completed'
        ];

        $query = SuratTugas::query()
            ->with(['pengusul', 'laporan', 'detailPelaksanaTugas.personable'])
            ->whereIn('status_surat', $bkuStatuses)
            ->latest();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_kegiatan', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$request->search}%") 
                  ->orWhere('nomor_surat_resmi', 'like', "%{$request->search}%");
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
                $query->whereBetween('created_at', [$from->format('Y-m-d'), $now->format('Y-m-d')]);
            }
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        }

        $latestSurat = $query->paginate(10)
            ->withQueryString()
            ->through(function ($item) {
                return $this->mapSuratWithPersonel($item);
            });

        return Inertia::render('BKU/BKUDashboard', [
            'statusCounts'    => $statusCounts,    
            'totalPengusulan' => $totalPengusulan, 
            'latestSurat'     => $latestSurat,
            'filters'         => $filters,
        ]);
    }

    public function daftarLaporan(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'range']);

        $query = SuratTugas::with(['pengusul', 'laporan', 'detailPelaksanaTugas.personable'])
            ->whereIn('status_surat', [
                'awaiting_proof_upload', 
                'under_bku_review', 
                'returned_for_correction'
            ]);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_kegiatan', 'like', "%{$request->search}%")
                  ->orWhere('perihal_tugas', 'like', "%{$request->search}%") 
                  ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_resmi', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status_surat', $request->status);
        }

        if ($request->filled('range') && $request->range !== 'all') {
            $now = now();
            $from = null;
            if ($request->range === 'weekly') $from = $now->copy()->subDays(7);
            elseif ($request->range === 'monthly') $from = $now->copy()->subMonth();
            elseif ($request->range === 'yearly') $from = $now->copy()->subYear();

            if ($from) {
                $query->whereBetween('created_at', [$from->format('Y-m-d'), $now->format('Y-m-d')]);
            }
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        }

        $data = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($item) {
                return $this->mapSuratWithPersonel($item);
            });

        return Inertia::render('BKU/DaftarLaporanPerjalanan', [ 
            'laporanBukti' => $data,
            'filters' => $filters,
        ]);
    }

    public function exportHistoryExcel(Request $request)
    {
        $filters = $request->only(['search', 'from', 'to', 'range']);

        $query = SuratTugas::with(['pengusul', 'wadir', 'detailPelaksanaTugas.personable'])
            ->where('status_surat', 'completed');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('nama_kegiatan', 'like', "%{$s}%")
                ->orWhere('perihal_tugas', 'like', "%{$s}%")
                ->orWhere('nomor_surat_resmi', 'like', "%{$s}%")
                ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$s}%");
            });
        }

        if ($request->filled('range') && $request->range !== 'all') {
            $now = now();
            $from = null;

            if ($request->range === 'weekly')  $from = $now->copy()->subDays(7);
            if ($request->range === 'monthly') $from = $now->copy()->subMonth();
            if ($request->range === 'yearly')  $from = $now->copy()->subYear();

            if ($from) {
                $query->whereBetween('created_at', [$from->startOfDay(), $now->endOfDay()]);
            }
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [
                now()->parse($request->from)->startOfDay(),
                now()->parse($request->to)->endOfDay(),
            ]);
        }

        $rows = $query->orderBy('created_at', 'desc')->get()->map(function ($item, $idx) {
            return [
                $idx + 1,
                $item->perihal_tugas ?? $item->nama_kegiatan ?? '-',
                optional($item->created_at)->format('Y-m-d'),
                optional($item->tanggal_berangkat)->format('Y-m-d'),
                $item->nomor_surat_usulan_jurusan ?? '-',
                optional($item->wadir)->name ?? 'Wakil Direktur I',
                $item->sumber_dana ?? '-',
                (float) ($item->nominal_biaya ?? $item->nominal_dana ?? 0),
                $item->status_surat ?? '-',
            ];
        });

        $headers = [
            'No',
            'Nama Kegiatan',
            'Tanggal Pengusulan',
            'Tanggal Berangkat',
            'Nomor Surat Usulan',
            'Diusulkan Kepada',
            'Sumber Dana',
            'Total Dana',
            'Status',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray($headers, null, 'A1');
        if ($rows->count()) $sheet->fromArray($rows->toArray(), null, 'A2');

        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('H2:H' . ($rows->count() + 1))
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        $filename = 'History-Perjalanan-Dinas_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
    public function exportLaporanBuktiExcel(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'range']);

        $query = SuratTugas::query()
            ->with([
                'pengusul',
                'wadir',
                'laporan.dokumenLampiran',
            ])
            ->whereIn('status_surat', [
                'awaiting_proof_upload',
                'under_bku_review',
                'returned_for_correction',
                'completed',
            ]);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_kegiatan', 'like', "%{$s}%")
                ->orWhere('perihal_tugas', 'like', "%{$s}%")
                ->orWhere('nomor_surat_resmi', 'like', "%{$s}%")
                ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$s}%")
                ->orWhere('nomor_surat_tugas_resmi', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status_surat', $request->status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [
                now()->parse($request->from)->startOfDay(),
                now()->parse($request->to)->endOfDay(),
            ]);
        } elseif ($request->filled('range') && $request->range !== 'all') {
            $now = now();
            $from = null;

            if ($request->range === 'weekly')  $from = $now->copy()->subDays(7);
            if ($request->range === 'monthly') $from = $now->copy()->subMonth();
            if ($request->range === 'yearly')  $from = $now->copy()->subYear();

            if ($from) {
                $query->whereBetween('created_at', [$from->startOfDay(), $now->endOfDay()]);
            }
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        $rows = $data->values()->map(function ($item, $idx) {
            $laporan = $item->laporan;

            $totalBukti = 0;
            if ($laporan && $laporan->relationLoaded('dokumenLampiran')) {
                $totalBukti = (float) $laporan->dokumenLampiran
                    ->where('jenis_dokumen', 'bukti')
                    ->sum(fn ($d) => (float) ($d->nominal ?? 0));
            }

            $statusUpload = $laporan ? 'Sudah Upload' : 'Belum Upload';

            return [
                $idx + 1,
                $item->perihal_tugas ?? $item->nama_kegiatan ?? '-',
                optional($item->created_at)->format('Y-m-d'),
                optional($item->tanggal_berangkat)->format('Y-m-d'),
                $item->nomor_surat_usulan_jurusan ?? '-',
                $item->sumber_dana ?? '-',
                (float) ($item->nominal_biaya ?? $item->nominal_dana ?? 0),
                $statusUpload,
                $totalBukti,
                $item->status_surat ?? '-',
            ];
        });

        $headers = [
            'No',
            'Nama Kegiatan',
            'Tanggal Pengusulan',
            'Tanggal Berangkat',
            'Nomor Surat Usulan',
            'Sumber Dana',
            'Total Dana',
            'Status Upload',
            'Total Bukti',
            'Status Surat',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray($headers, null, 'A1');
        if ($rows->count()) $sheet->fromArray($rows->toArray(), null, 'A2');

        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:J1');

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $lastRow = $rows->count() + 1;
        if ($lastRow >= 2) {
            $sheet->getStyle('G2:G' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('I2:I' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
        }

        $filename = 'Laporan-Bukti_Perjalanan-Dinas_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function history(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'range']);

        $query = SuratTugas::with(['pengusul', 'wadir', 'detailPelaksanaTugas.personable'])
            ->where('status_surat', 'completed');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_kegiatan', 'like', "%{$request->search}%")
                  ->orWhere('perihal_tugas', 'like', "%{$request->search}%") 
                  ->orWhere('nomor_surat_resmi', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('range') && $request->range !== 'all') {
            $now = now();
            $from = null;
            if ($request->range === 'weekly') $from = $now->copy()->subDays(7);
            elseif ($request->range === 'monthly') $from = $now->copy()->subMonth();
            elseif ($request->range === 'yearly') $from = $now->copy()->subYear();

            if ($from) {
                $query->whereBetween('created_at', [$from->format('Y-m-d'), $now->format('Y-m-d')]);
            }
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        }

        $history = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($item) {
                return $this->mapSuratWithPersonel($item);
            });

        return Inertia::render('BKU/HistoryPerjalananDinas', [
            'history' => $history,
            'filters' => $filters,
        ]);
    }
}