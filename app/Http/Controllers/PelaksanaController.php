<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PelaksanaController extends Controller
{
    /**
     * Helper untuk mem-format pagination & tanggal
     */
    private function mapPagination($paginate)
    {
        return [
            'data' => $paginate->getCollection()->transform(function ($item) {
                return [
                    ...$item->toArray(),

                    'created_at'        => $item->created_at?->format('Y-m-d'),
                    'updated_at'        => $item->updated_at?->format('Y-m-d'),
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

    // DASHBOARD PELAKSANA
    public function dashboard()
    {
        $user = auth()->user();
        $list = SuratTugas::latest()->paginate(10);

        return Inertia::render('Dashboards/PelaksanaDashboard', [
            'auth' => ['user' => $user],
            'suratTugas' => $this->mapPagination($list),
            'filters' => [],
            'role' => 'pelaksana',
        ]);
    }

    // DAFTAR LAPORAN
    public function daftarLaporan()
    {
        $user = auth()->user();
        $list = SuratTugas::latest()->paginate(10);

        return Inertia::render('Pelaksana/DaftarLaporan', [
            'auth' => ['user' => $user],
            'suratTugas' => $this->mapPagination($list),
            'filters' => [],
            'role' => 'pelaksana',
        ]);
    }

    // HISTORY PELAKSANA
    public function historypelaksana()
    {
        $user = auth()->user();
        $list = SuratTugas::latest()->paginate(10);

        return Inertia::render('Pelaksana/HistoryPelaksana', [
            'auth' => ['user' => $user],
            'suratTugas' => $this->mapPagination($list),
            'filters' => [],
            'role' => 'pelaksana',
        ]);
    }

    // STATUS LAPORAN PELAKSANA
    public function statusLaporan()
    {
        $user = auth()->user();
        $list = SuratTugas::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // transform data supaya Vue bisa render status_laporan
        $suratTugas = $list->getCollection()->transform(function ($item) {
            return [
                ...$item->toArray(),

                'created_at'        => $item->created_at?->format('Y-m-d'),
                'updated_at'        => $item->updated_at?->format('Y-m-d'),
                'tanggal_berangkat' => $item->tanggal_berangkat?->format('Y-m-d'),
                'tanggal_kembali'   => $item->tanggal_kembali?->format('Y-m-d'),

                // key tambahan untuk tabel Status Laporan
                'status_laporan'    => $item->status_surat ?? 'draft',
            ];
        });

        return Inertia::render('Pelaksana/StatusLaporan', [
            'auth' => ['user' => $user],
            'suratTugas' => [
                'data' => $suratTugas,
                'meta' => [
                    'current_page' => $list->currentPage(),
                    'last_page'    => $list->lastPage(),
                    'per_page'     => $list->perPage(),
                    'from'         => $list->firstItem(),
                    'to'           => $list->lastItem(),
                    'total'        => $list->total(),
                ],
                'links' => [
                    'prev' => $list->previousPageUrl(),
                    'next' => $list->nextPageUrl(),
                ],
            ],
            'filters' => [],
            'role' => 'pelaksana',
            'statusOptions' => [
                'awaiting_proof_upload',
                'under_bku_review',
                'returned_for_correction',
                'completed',
            ],
        ]);
    }
}
