<?php

namespace App\Http\Controllers\Wadir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WadirController extends Controller
{
    public function index(Request $request)
    {
        $role = auth()->user()->role; // wadir1, wadir2, wadir3, wadir4

        // Data dummy per wadir
        $dataWadir = [
            'wadir1' => [
                'stats' => [
                    'total' => 12,
                    'pending' => 4,
                    'approved' => 5,
                    'rejected' => 3,
                ],
                'suratTugas' => [
                    ['id' => 1, 'perihal_tugas' => 'Audit Internal', 'created_at' => '13-11-2024', 'status_surat' => 'pending'],
                    ['id' => 2, 'perihal_tugas' => 'Rapat Kurikulum','created_at' => '28-9-2020', 'status_surat' => 'approved'],
                ]
            ],

            'wadir2' => [
                'stats' => [
                    'total' => 20,
                    'pending' => 6,
                    'approved' => 10,
                    'rejected' => 4,
                ],
                'suratTugas' => [
                    ['id' => 3, 'perihal_tugas' => 'Monitoring Prodi', 'created_at' => '13-11-2024', 'status_surat' => 'pending'],
                    ['id' => 4, 'perihal_tugas' => 'Workshop Akreditasi', 'created_at' => '13-11-2024', 'status_surat' => 'rejected'],
                ]
            ],

            'wadir3' => [
                'stats' => [
                    'total' => 8,
                    'pending' => 2,
                    'approved' => 5,
                    'rejected' => 1,
                ],
                'suratTugas' => [
                    ['id' => 5, 'perihal_tugas' => 'Kerjasama Industri', 'status_surat' => 'approved'],
                ]
            ],

            'wadir4' => [
                'stats' => [
                    'total' => 17,
                    'pending' => 7,
                    'approved' => 8,
                    'rejected' => 2],
                'suratTugas' => [
                    ['id' => 6, 'perihal_tugas' => 'Pengabdian Masyarakat', 'status_surat' => 'pending'],
                    ['id' => 7, 'perihal_tugas' => 'Kunjungan SMK', 'status_surat' => 'approved'],
                ]
            ],
        ];

        $wadir = $dataWadir[$role];

                return Inertia::render('Dashboards/WadirDashboard', [
                'stats' => $wadir['stats'],
                'suratTugas' => [
                    'data' => $wadir['suratTugas'],
                    'meta' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => count($wadir['suratTugas']),
                        'total' => count($wadir['suratTugas']),
                        'from' => 1,
                        'to' => count($wadir['suratTugas']),
                    ],
                    'links' => []
                ],
                'filters' => $request->all(),
            ]);
    }

    public function persetujuan()
    {
        $dataPersetujuan = [

            'wadir1' => [
                ['id' => 1, 'pengusul' => 'Doni', 'nama_kegiatan' => 'Audit Internal', 'tanggal_pelaksanaan' => '2024-11-22', 'pembiayaan' => 'Dana Dipa', 'surat_undangan' => 'ada.pdf', 'status_surat' => 'pending'],
                ['id' => 2, 'pengusul' => 'Rani', 'nama_kegiatan' => 'Rapat Kurikulum', 'tanggal_pelaksanaan' => '2024-11-30', 'pembiayaan' => 'BLU', 'surat_undangan' => 'undangan.pdf', 'status_surat' => 'approved'],
            ],

            'wadir2' => [
                ['id' => 3, 'pengusul' => 'Sari', 'nama_kegiatan' => 'Monitoring Prodi', 'tanggal_pelaksanaan' => '2024-12-02', 'pembiayaan' => 'Prodi', 'surat_undangan' => 'monitoring.pdf', 'status_surat' => 'pending'],
                ['id' => 4, 'pengusul' => 'Bima', 'nama_kegiatan' => 'Workshop Akreditasi', 'tanggal_pelaksanaan' => '2024-12-12', 'pembiayaan' => 'DIPA', 'surat_undangan' => null, 'status_surat' => 'rejected'],
            ],

            'wadir3' => [
                ['id' => 5, 'pengusul' => 'Tika', 'nama_kegiatan' => 'Kerjasama Industri', 'tanggal_pelaksanaan' => '2024-11-20', 'pembiayaan' => 'Industri', 'surat_undangan' => 'kerjasama.pdf', 'status_surat' => 'approved'],
            ],

            'wadir4' => [
                ['id' => 6, 'pengusul' => 'Yoga', 'nama_kegiatan' => 'Pengabdian Masyarakat', 'tanggal_pelaksanaan' => '2024-12-10', 'pembiayaan' => 'BLU', 'surat_undangan' => 'pengmas.pdf', 'status_surat' => 'pending'],
                ['id' => 7, 'pengusul' => 'Wina', 'nama_kegiatan' => 'Kunjungan SMK', 'tanggal_pelaksanaan' => '2024-11-18', 'pembiayaan' => 'DIPA', 'surat_undangan' => null, 'status_surat' => 'approved'],
            ],

        ];

        $role = auth()->user()->role;
        $persetujuan = $dataPersetujuan[$role] ?? [];

        return inertia('Wadir/Persetujuan', [
            'suratTugas' => [
                'data'  => $persetujuan,
                'meta'  => [
                    'total' => count($persetujuan),
                    'per_page' => count($persetujuan),
                    'current_page' => 1,
                    'last_page' => 1,
                ],
                'links' => [],
            ],
            'filters' => [
                'search' => null,
            ],
        ]);
    }
}
