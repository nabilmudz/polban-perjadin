<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PegawaiService;
use Inertia\Inertia;

class AdminController extends Controller
{
    protected $pegawaiService;

    public function __construct(PegawaiService $pegawaiService)
    {
        $this->pegawaiService = $pegawaiService;
    }

    public function pegawai(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page']);
        $pegawai = $this->pegawaiService->getAll($filters);

        return Inertia::render('Admin/DaftarPegawai', [
            'pegawai' => $pegawai,
            'filters' => $filters,
            'authUser' => $request->user(),
        ]);
    }

}
