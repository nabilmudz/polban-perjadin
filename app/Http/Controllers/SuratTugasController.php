<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use Illuminate\Http\Request;
use App\Services\SuratTugasService;
use Illuminate\Validation\ValidationException;

class SuratTugasController extends Controller
{
    protected $service;

    public function __construct(SuratTugasService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'start_date', 'end_date']);
        $data = $this->service->getAll($filters, $request->user());
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'tanggal'  => 'required|date',
            'lokasi'   => 'required|string',
        ]);

        $data = $this->service->create($validated);

        return response()->json($data, 201);
    }

    public function show($id)
    {
        $data = $this->service->getById($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title'   => 'sometimes|string|max:255',
            'tanggal' => 'sometimes|date',
            'lokasi'  => 'sometimes|string',
        ]);

        $data = $this->service->update($id, $validated);
        return response()->json($data);
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function updateStatus(Request $request, SuratTugas $surat_tugas)
    {
        $validated = $request->validate([
            'status_surat'   => 'required|string',
            'catatan_revisi' => 'nullable|string',
        ]);

        try {
            $this->service->updateStatus(
                $surat_tugas,
                $validated['status_surat'],
                $validated['catatan_revisi'] ?? null,
                $request->user()->role
            );

            $role = $request->user()->role;

            return redirect()
                ->route("{$role}.dashboard")
                ->with('success', 'Status surat berhasil diperbarui.');

        } catch (\DomainException $e) {

            abort(422, $e->getMessage());

        } catch (\Throwable $e) {
            report($e);

            abort(500, 'Terjadi kesalahan saat memperbarui status.');
        }
    }
}
