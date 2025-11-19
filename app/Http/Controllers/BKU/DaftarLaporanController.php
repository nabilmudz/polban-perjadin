<?php

namespace App\Http\Controllers\BKU;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DaftarLaporanController extends Controller
{
    public function index(Request $request)
    {
        // Example of static data — might replace later with the actual data model 
        $laporan = [
            ['id' => 1, 'nama_perjalanan' => 'Perjalanan Dinas Bandung', 'status' => 'Selesai'],
            ['id' => 2, 'nama_perjalanan' => 'Audit Keuangan Jakarta', 'status' => 'Proses'],
            ['id' => 3, 'nama_perjalanan' => 'Kunjungan Kerja Bali', 'status' => 'Menunggu Persetujuan'],
        ];

        return Inertia::render('BKU/DaftarLaporan&Perjalanan', [
            'role' => 'bku',
            'laporan' => $laporan,
        ]);
    }
}
