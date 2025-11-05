<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\SuratTugasService;
use App\Http\Requests\FilterSuratTugasRequest;

class SuratTugasController extends Controller
{
    protected $service;

    public function __construct(SuratTugasService $service)
    {
        $this->service = $service;
    }
    

    public function index(FilterSuratTugasRequest $request)
    {
        $user = auth()->user();
        $filters = $request->validated();

        $query = SuratTugas::query()
            ->where('user_id', $user->id);

        if (!empty($filters['search'])) {
            $query->where('perihal', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->whereBetween('tanggal_berangkat', [$filters['from'], $filters['to']]);
        }

        $suratTugas = $query->latest()->paginate(10)->withQueryString();

        return inertia('Dashboards/PengusulDashboard', [
            'suratTugas' => $suratTugas,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */

    public function show($id)
    {
        // $suratTugas = $this->service->getDetail($id);
        // return Inertia::render('Pengusul/DetailSuratTugas', [
        //     'suratTugas' => $suratTugas,
        // ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SuratTugas $suratTugas)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SuratTugas $suratTugas)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(SuratTugas $suratTugas)
    {
        $suratTugas->delete();
        return back()->with('success', 'Surat Tugas deleted successfully.');
    }
}
