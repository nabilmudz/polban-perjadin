<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PelaksanaController extends Controller
{
    private function mapPagination($paginate)
    {
        return [
            'data' => $paginate->getCollection()->transform(function ($item) {
                return [
                    ...$item->toArray(),
                    'created_at'        => $item->created_at?->format('Y-m-d'),
                    'tanggal_berangkat' => $item->tanggal_berangkat?->format('Y-m-d'),
                    'tanggal_kembali'   => $item->tanggal_kembali?->format('Y-m-d'),
                ];
            }),

            'meta' => [
                'current_page' => $paginate->currentPage(),
                'last_page'    => $paginate->lastPage(),
                'from'         => $paginate->firstItem(),
                'to'           => $paginate->lastItem(),
                'total'        => $paginate->total(),
                'per_page'     => $paginate->perPage(),
            ],

            'links' => [
                'prev' => $paginate->previousPageUrl(),
                'next' => $paginate->nextPageUrl(),
            ],
        ];
    }

    private function baseQueryForPelaksana()
    {
        $user = auth()->user();
        $pegawaiId = $user->pegawai_id;

        return SuratTugas::whereHas('detailPelaksanaTugas', function ($q) use ($pegawaiId) {
            $q->where('personable_type', 'App\\Models\\Pegawai')
              ->where('personable_id', $pegawaiId);
        });
    }

    public function dashboard(Request $request)
    {
        $list = $this->baseQueryForPelaksana()
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return Inertia::render('Dashboards/PelaksanaDashboard', [
            'auth'       => ['user' => auth()->user()],
            'suratTugas' => $this->mapPagination($list),
            'filters'    => [],
            'role'       => 'pelaksana',
        ]);
    }

    public function daftarLaporan(Request $request)
    {
        $list = $this->baseQueryForPelaksana()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Pelaksana/DaftarLaporan', [
            'auth'       => ['user' => auth()->user()],
            'suratTugas' => $this->mapPagination($list),
            'filters'    => [],
            'role'       => 'pelaksana',
        ]);
    }

    public function historypelaksana(Request $request)
    {
        $list = $this->baseQueryForPelaksana()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Pelaksana/HistoryPelaksana', [
            'auth'       => ['user' => auth()->user()],
            'suratTugas' => $this->mapPagination($list),
            'filters'    => [],
            'role'       => 'pelaksana',
        ]);
    }
}
