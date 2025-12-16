<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;

class VerifikasiController extends Controller
{
    public function suratTugas(string $token)
    {
        $surat = SuratTugas::query()
            ->where('barcode_token', $token)
            ->firstOrFail();

        return inertia('Verifikasi/SuratTugas', [
            'surat' => [
                'id' => $surat->getKey(),
                'nomor' => $surat->nomor_surat_tugas_resmi,
                'perihal' => $surat->perihal_tugas,
                'tanggal_berangkat' => optional($surat->tanggal_berangkat)->format('Y-m-d'),
                'tanggal_kembali' => optional($surat->tanggal_kembali)->format('Y-m-d'),
                'status' => $surat->status_surat,
                'published_at' => optional($surat->tanggal_tte_direktur)->toDateTimeString(),
            ],
        ]);
    }
}
