<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SuratDownloadController extends Controller
{
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

    public function preview(Request $request, SuratTugas $suratTugas)
    {
        $surat = $this->baseQueryByRole($request)->whereKey($suratTugas->getKey())->firstOrFail();
        $vm = $this->buildPrintVm($surat);
        return view('print.surat-tugas', $vm);
    }

    public function download(Request $request, SuratTugas $suratTugas)
    {
        $surat = $this->baseQueryByRole($request)->whereKey($suratTugas->getKey())->firstOrFail();
        $vm = $this->buildPrintVm($surat);

        $pdf = Pdf::loadView('print.surat-tugas', $vm)->setPaper('a4');

        $no = $surat->nomor_surat_tugas_resmi ?? $surat->getKey();
        return $pdf->download("Surat-Tugas-{$no}.pdf");
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

        $lokasiList = collect($surat->lokasi_kegiatan ?? []);

        $isLampiran = $personel->count() > 3 || ($personel->count() > 2 && $lokasiList->count() > 1);

        $itemsPerPage = 9;
        $needChunking = $personel->count() > $itemsPerPage;
        $personnelChunks = $needChunking ? collect(array_chunk($personel->values()->all(), $itemsPerPage)) : collect([]);

        $logoPath = public_path('images/polban.png');
        $logoData = is_file($logoPath) ? ('data:image/png;base64,'.base64_encode(file_get_contents($logoPath))) : null;

        return [
            'surat' => $surat,
            'personel' => $personel,
            'pegawaiList' => $pegawaiList,
            'mahasiswaList' => $mahasiswaList,
            'lokasiList' => $lokasiList,
            'tembusanList' => collect($surat->template_tembusan ?? [])->filter()->values(),
            'isLampiran' => $isLampiran,
            'needChunking' => $needChunking,
            'personnelChunks' => $personnelChunks,
            'logoData' => $logoData,
        ];
    }

}
