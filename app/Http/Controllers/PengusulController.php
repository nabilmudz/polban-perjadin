<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratTugas;
use Inertia\Inertia;

class PengusulController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();

        $query = SuratTugas::where('user_id', $user->id);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('perihal_tugas', 'like', "%{$request->search}%")
                ->orWhere('status_surat', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status_surat', $request->status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        }

        $suratTugas = $query->latest()->paginate(10)
        ->through(function($item) {
            return [
                ...$item->toArray(),
                'created_at' => $item->created_at->format('Y-m-d'),
            ];
        })
        ->withQueryString();

        return inertia('Pengusul/PengusulDashboard', [
            'suratTugas' => [
                'data' => $suratTugas->items(),
                'meta' => [
                    'current_page' => $suratTugas->currentPage(),
                    'last_page' => $suratTugas->lastPage(),
                    'per_page' => $suratTugas->perPage(),
                    'from' => $suratTugas->firstItem(),
                    'to' => $suratTugas->lastItem(),
                    'total' => $suratTugas->total(),
                ],
                'links' => [
                    'prev' => $suratTugas->previousPageUrl(),
                    'next' => $suratTugas->nextPageUrl(),
                ],
            ],
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
                'from' => $request->from,
                'to' => $request->to ?: now()->toDateString(),
            ],
        ]);
    }

    public function daftarPengusulan(Request $request)
    {
        $user = auth()->user();

        $query = SuratTugas::where('user_id', $user->id);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('perihal_tugas', 'like', "%{$request->search}%")
                ->orWhere('status_surat', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status_surat', $request->status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        }

        $suratTugas = $query->latest()->paginate(10)
        ->through(function($item) {
            return [
                ...$item->toArray(),
                'created_at' => $item->created_at->format('Y-m-d'),
            ];
        })
        ->withQueryString();

        return inertia('Pengusul/PengusulDashboard', [
            'suratTugas' => [
                'data' => $suratTugas->items(),
                'meta' => [
                    'current_page' => $suratTugas->currentPage(),
                    'last_page' => $suratTugas->lastPage(),
                    'per_page' => $suratTugas->perPage(),
                    'from' => $suratTugas->firstItem(),
                    'to' => $suratTugas->lastItem(),
                    'total' => $suratTugas->total(),
                ],
                'links' => [
                    'prev' => $suratTugas->previousPageUrl(),
                    'next' => $suratTugas->nextPageUrl(),
                ],
            ],
            'filters' => $request->only(['search','status','from','to','page']),
        ]);
    }
}
