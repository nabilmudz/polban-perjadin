<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\SuratTugas;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DaftarPersetujuanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        return Inertia::render('Direktur/DaftarPersetujuan', [
            'role' => 'direktur',
            'filters' => $request->only('search')
        ]);
    }
}
