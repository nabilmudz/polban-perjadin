<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use Illuminate\Http\Request;
use App\Services\SuratTugasService;

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
            $updated = $this->service->updateStatus(
                $surat_tugas,
                $validated['status_surat'],
                $validated['catatan_revisi'] ?? null,
                $request->user()->role
            );

            return response()->json([
                'message' => 'Status surat berhasil diperbarui.',
                'data'    => $updated,
            ]);

        } catch (\DomainException $e) {

            return response()->json([
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui status.',
            ], 500);
        }
    }
}
