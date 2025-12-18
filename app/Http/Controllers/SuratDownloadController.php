<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;

class SuratDownloadController extends Controller
{
    public function print(Request $request, SuratTugas $suratTugas)
    {
        $surat = $this->baseQueryByRole($request)->whereKey($suratTugas->getKey())->firstOrFail();
        $vm = $this->buildPrintVm($surat);
        return view('print.surat-tugas', $vm);
    }

    private function baseQueryByRole(Request $request)
    {
        $user = $request->user();

        $q = SuratTugas::query()
            ->with([
                'user:id,kode_pengusul,name',
                'wadir:id,name',
                'laporan.dokumenLampiran',
                'detailPelaksanaTugas.personable',
            ]);

        switch ($user->role) {
            case 'pelaksana': {
                $pegawaiId = $user->pegawai_id;

                $type = Pegawai::class;
                $typeLegacy = str_replace('\\', '\\\\', $type);

                $q->whereHas('detailPelaksanaTugas', function ($qq) use ($pegawaiId, $type, $typeLegacy) {
                    $qq->whereIn('personable_type', [$type, $typeLegacy])
                       ->where('personable_id', $pegawaiId);
                });
                break;
            }

            case 'bku': {
                $q->whereIn('status_surat', [
                    'awaiting_proof_upload',
                    'under_bku_review',
                    'returned_for_correction',
                    'completed',
                ]);
                break;
            }

            case 'pengusul': {
                $q->where('user_id', $user->id);
                break;
            }

            default:
                break;
        }

        return $q;
    }
    public function pdf(Request $request, SuratTugas $suratTugas)
    {
        $surat = $this->baseQueryByRole($request)
            ->whereKey($suratTugas->getKey())
            ->firstOrFail();

        $vm = $this->buildPrintVm($surat);

        $pdf = Pdf::loadView('print.surat-tugas', $vm)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        $noRaw = (string) ($surat->nomor_surat_tugas_resmi ?? $surat->getKey());
        $noSafe = $this->safeFilename($noRaw);

        return $pdf->stream("Surat-Tugas-{$noSafe}.pdf");
    }

    public function preview(Request $request, SuratTugas $suratTugas)
    {
        $surat = $this->baseQueryByRole($request)->whereKey($suratTugas->getKey())->firstOrFail();
        $vm = $this->buildPrintVm($surat);
        return view('print.surat-tugas', $vm);
    }
    private function safeFilename(string $name): string
    {
        $name = str_replace(['/', '\\'], '-', $name);
        $name = preg_replace('/[^A-Za-z0-9._-]+/', '-', $name);
        $name = trim($name, '-');

        return $name ?: 'surat';
    }

    public function download(Request $request, SuratTugas $suratTugas)
    {
        $surat = $this->baseQueryByRole($request)->whereKey($suratTugas->getKey())->firstOrFail();

        $force = $request->boolean('force');

        if ($force && $surat->path_file_surat_tugas_final) {
            Storage::disk('public')->delete($surat->path_file_surat_tugas_final);
            $surat->update(['path_file_surat_tugas_final' => null]);
        }

        if (!$force && $surat->path_file_surat_tugas_final) {
            $full = storage_path('app/public/' . ltrim($surat->path_file_surat_tugas_final, '/'));
            if (is_file($full)) {
                $noSafe = $this->safeFilename((string)($surat->nomor_surat_tugas_resmi ?? $surat->getKey()));
                return response()->download($full, "Surat-Tugas-{$noSafe}.pdf");
            }
        }

        if ($surat->path_file_surat_tugas_final) {
            $full = storage_path('app/public/' . ltrim($surat->path_file_surat_tugas_final, '/'));
            if (is_file($full)) {
                $noSafe = $this->safeFilename((string)($surat->nomor_surat_tugas_resmi ?? $surat->getKey()));
                return response()->download($full, "Surat-Tugas-{$noSafe}.pdf");
            }
        }

        set_time_limit(180);
        ini_set('max_execution_time', '180');

        $vm   = $this->buildPrintVm($surat);
        $html = view('print.surat-tugas', $vm)->render();

        $pdfBinary = Browsershot::html($html)
            ->timeout(180)
            ->setOption('waitUntil', 'load')
            ->setDelay(300)
            ->showBackground()
            ->format('A4')
            ->margins(25, 25, 25, 25)
            ->pdf();


        $noSafe = $this->safeFilename((string)($surat->nomor_surat_tugas_resmi ?? $surat->getKey()));
        $path   = "uploads/surat-final/Surat-Tugas-{$noSafe}.pdf";

        \Storage::disk('public')->put($path, $pdfBinary);
        $surat->update(['path_file_surat_tugas_final' => $path]);

        return response()->download(storage_path('app/public/' . $path), "Surat-Tugas-{$noSafe}.pdf");
    }
    private function buildPrintVm($surat): array
    {
        $personel = collect($surat->personel ?? [])
            ->when(empty($surat->personel) && !empty($surat->detailPelaksanaTugas), function ($c) use ($surat) {
                return collect($surat->detailPelaksanaTugas)->map(function ($d) {
                    $p = $d->personable ?? null;
                    if (!$p) return null;
                    $isMhs = str_contains($d->personable_type ?? '', 'Mahasiswa');

                    return [
                        'type' => $isMhs ? 'mahasiswa' : 'pegawai',
                        'nama' => $p->nama ?? '',
                        'nip'  => $isMhs ? null : ($p->nip ?? null),
                        'nim'  => $isMhs ? ($p->nim ?? null) : null,
                        'pangkat' => $p->pangkat ?? null,
                        'golongan'=> $p->golongan ?? null,
                        'jabatan' => $p->jabatan ?? null,
                        'jurusan' => $p->jurusan ?? null,
                        'prodi'   => $p->prodi ?? null,
                    ];
                })->filter();
            });

        $pegawaiList   = $personel->where('type','pegawai')->values();
        $mahasiswaList = $personel->where('type','mahasiswa')->values();
                
        $barcodeData = null;
        if (!empty($surat->barcode_path)) {
            $rel = ltrim($surat->barcode_path, '/');
            if (Storage::disk('public')->exists($rel)) {
                $raw = Storage::disk('public')->get($rel);
                $barcodeData = 'data:image/svg+xml;base64,' . base64_encode($raw);
            }
        }


        $lokasiList = collect($surat->lokasi_kegiatan ?? []);

        $isLampiran = $personel->count() > 3 || ($personel->count() > 2 && $lokasiList->count() > 1);

        $itemsPerPage = 9;
        $needChunking = $personel->count() > $itemsPerPage;
        $personnelChunks = $needChunking ? collect(array_chunk($personel->values()->all(), $itemsPerPage)) : collect([]);

        $logoPath = public_path('images/polban.png');
        $logoData = is_file($logoPath) ? ('data:image/png;base64,'.base64_encode(file_get_contents($logoPath))) : null;

        return [
            'logoData' => $logoData,
            'barcodeData' => $barcodeData,
            'surat' => $surat,
            'personel' => $personel,
            'pegawaiList' => $pegawaiList,
            'mahasiswaList' => $mahasiswaList,
            'lokasiList' => $lokasiList,
            'tembusanList' => collect($surat->template_tembusan ?? [])->filter()->values(),
            'isLampiran' => $isLampiran,
            'needChunking' => $needChunking,
            'personnelChunks' => $personnelChunks,
        ];
    }

}
