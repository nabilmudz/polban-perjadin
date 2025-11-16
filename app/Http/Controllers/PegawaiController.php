<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PegawaiService;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PegawaiController extends Controller
{
    protected PegawaiService $service;

    public function __construct(PegawaiService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page']);
        $pegawai = $this->service->getAll($filters);

        return Inertia::render('Admin/DaftarPegawai', [
            'pegawai' => $pegawai,
            'filters' => $filters,
            'authUser' => $request->user(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:pegawai,nip',
            'pangkat' => 'nullable|string|max:100',
            'golongan' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:100',
            'status' => ['nullable', Rule::in([0, 1])],
        ]);

        $pegawai = $this->service->create($validated);
        return response()->json($pegawai, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'nip' => "sometimes|string|max:50|unique:pegawai,nip,{$id}",
            'pangkat' => 'sometimes|string|max:100',
            'golongan' => 'sometimes|string|max:50',
            'jabatan' => 'sometimes|string|max:100',
            'status' => ['sometimes', Rule::in([0, 1])],
        ]);

        $pegawai = $this->service->update($id, $validated);
        return response()->json($pegawai);
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function toggleStatus($id, Request $request)
    {
        $this->service->toggleStatus($id);
        return redirect()->back();
    }

}
