<?php

namespace App\Http\Controllers\BKU;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HistoryPerjalananDinasController extends Controller
{
    public function index(Request $request)
    {
        $items = [
            [
                'id' => 1,
                'tanggal_pengusulan' => '2025-01-10',
                'tanggal_berangkat' => '2025-01-12',
                'nomor_pengantar' => 'SP-001',
                'nomor_tugas' => 'ST-001',
                'tanggal_diterbitkan' => '2025-01-11',
                'diusulkan_kepada' => 'Wadir 2',
                'status_surat' => 'Selesai',
            ],
            [
                'id' => 2,
                'tanggal_pengusulan' => '2025-01-20',
                'tanggal_berangkat' => '2025-02-02',
                'nomor_pengantar' => 'SP-002',
                'nomor_tugas' => 'ST-002',
                'tanggal_diterbitkan' => '2025-01-22',
                'diusulkan_kepada' => 'Direktur',
                'status_surat' => 'Menunggu Persetujuan',
            ],
            [
                'id' => 3,
                'tanggal_pengusulan' => '2025-03-01',
                'tanggal_berangkat' => '2025-03-15',
                'nomor_pengantar' => 'SP-003',
                'nomor_tugas' => 'ST-003',
                'tanggal_diterbitkan' => '2025-03-05',
                'diusulkan_kepada' => 'Wadir 1',
                'status_surat' => 'Diproses',
            ],
        ];

        // Fake pagination structure 
        $history = [
            'data' => $items,
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 10,
                'total' => count($items),
            ],
            'links' => [
                ['url' => null, 'label' => '&laquo; Sebelumnya', 'active' => false],
                ['url' => null, 'label' => '1', 'active' => true],
                ['url' => null, 'label' => 'Berikutnya &raquo;', 'active' => false],
            ],
        ];

        return Inertia::render('BKU/HistoryPerjalananDinas', [
            'history'   => $history,
            'filters'   => $request->only(['search', 'from', 'to']),
            'role'      => 'bku',
        ]);
    }
}
