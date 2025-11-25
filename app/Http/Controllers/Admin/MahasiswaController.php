<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Services\MahasiswaService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MahasiswaController extends Controller
{
    protected $mahasiswaService;

    public function __construct(MahasiswaService $mahasiswaService)
    {
        $this->mahasiswaService = $mahasiswaService;
    }

    public function index(Request $request)
    {
        $mahasiswa = $this->mahasiswaService->getMahasiswa($request);
        return Inertia::render('Admin/Mahasiswa/DaftarMahasiswa', [
            'mahasiswa' => $mahasiswa,
            'filters' => $request->only(['search', 'sort_by', 'sort_direction'])
        ]);
    }

    public function store(Request $request)
    {
        $this->mahasiswaService->createMahasiswa($request);

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Mahasiswa created successfully.');
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $this->mahasiswaService->updateMahasiswa($request, $mahasiswa);

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Mahasiswa updated successfully.');
    }

    public function destroy(Mahasiswa $mahasiswa)
    {
        $this->mahasiswaService->deleteMahasiswa($mahasiswa);

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Mahasiswa deleted successfully.');
    }

    public function import(Request $request)
    {
        try {
            $this->mahasiswaService->importMahasiswa($request);
            return redirect()->route('admin.mahasiswa.index')->with('success', 'Mahasiswa imported successfully.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorRows = [];
            foreach ($failures as $failure) {
                $errorRows[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values()
                ];
            }
            return redirect()->route('admin.mahasiswa.index')->with('import_errors', $errorRows);
        }
    }
}
