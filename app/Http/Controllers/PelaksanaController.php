<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PelaksanaController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        // Dummy data untuk testing
        $dummyTugas = [
            ['id' => 1, 'nomor' => 1, 'nama_kegiatan' => 'osfd', 'tanggal_pelaksanaan' => '30 Oct 2025', 'status_surat' => 'awaiting_proof_upload'],
            ['id' => 2, 'nomor' => 2, 'nama_kegiatan' => 'fa', 'tanggal_pelaksanaan' => '29 Oct 2025', 'status_surat' => 'awaiting_proof_upload'],
            ['id' => 3, 'nomor' => 3, 'nama_kegiatan' => 'Dinas Luar Monev Anggaran', 'tanggal_pelaksanaan' => '29 Oct 2025 → 31 Oct 2025', 'status_surat' => 'awaiting_proof_upload'],
            ['id' => 4, 'nomor' => 4, 'nama_kegiatan' => 'Kegiatan 1', 'tanggal_pelaksanaan' => '28 Oct 2025', 'status_surat' => 'completed'],
            ['id' => 5, 'nomor' => 5, 'nama_kegiatan' => 'asf', 'tanggal_pelaksanaan' => '27 Oct 2025', 'status_surat' => 'completed'],
            ['id' => 6, 'nomor' => 6, 'nama_kegiatan' => 'osdfasf', 'tanggal_pelaksanaan' => '27 Oct 2025', 'status_surat' => 'awaiting_proof_upload'],
        ];

        return Inertia::render('Dashboards/PelaksanaDashboard', [
            'auth' => ['user' => $user],
            'suratTugas' => [
                'data' => $dummyTugas,
                'meta' => [
                    'total' => count($dummyTugas),
                    'per_page' => 10,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
                'links' => [
                    'first' => '#',
                    'last' => '#',
                    'next' => null,
                    'prev' => null,
                ],
            ],
            'filters' => [
                'status' => null,
                'search' => null,
                'from' => null,
                'to' => null,
            ],
            'role' => 'pelaksana',
        ]);
    }

    public function daftarLaporan()
    {
        $user = auth()->user();

        $dummyLaporan = [
            ['id' => 1, 'pengusul' => 'Saepul', 'wadir' => 'Intan Pertama (Wadir 2)', 'no_surat_resmi' => '211', 'nama_kegiatan' => 'Cek Sepeda', 'tanggal_pelaksanaan' => '18-12-2020', 'sumber_dana' => 'Kampus', 'status' => 'Proses'],
            ['id' => 2, 'pengusul' => 'Nabila', 'wadir' => 'Faras Rama Mahadika (Wadir 1)', 'no_surat_resmi' => '212', 'nama_kegiatan' => 'Kegiatan B', 'tanggal_pelaksanaan' => '25-12-2020', 'sumber_dana' => 'DIPA', 'status'=> 'Selesai'],
        ];

        return Inertia::render('Pelaksana/DaftarLaporan', [
            'auth' => ['user' => $user],
            'laporan' => $dummyLaporan,
            'role' => 'pelaksana',
        ]);
    }
}
