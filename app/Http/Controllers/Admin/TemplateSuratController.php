<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TemplateSuratService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class TemplateSuratController extends Controller
{
    protected TemplateSuratService $service;

    public function __construct(TemplateSuratService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return Inertia::render('Admin/TemplateSurat', [
            'templates' => $this->service->getAll(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kementerian'   => 'required|string|max:255',
            'nama_direktur'      => 'required|string|max:255',
            'nip_direktur'       => 'required|string|max:255',
            'status'             => ['required', Rule::in([0, 1])],
            'tembusan_default'   => 'nullable|array',
            'tembusan_default.*' => 'string|max:255',
        ]);

        $this->service->create($data);

        return redirect()->back(); 
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nama_kementerian'   => 'required|string|max:255',
            'nama_direktur'      => 'required|string|max:255',
            'nip_direktur'       => 'required|string|max:255',
            'status'             => ['required', Rule::in([0, 1])],
            'tembusan_default'   => 'nullable|array',
            'tembusan_default.*' => 'string|max:255',
        ]);

        $this->service->update((int)$id, $data);
        return redirect()->back();
    }

    public function toggleStatus($id)
    {
        $this->service->toggleStatus($id);

        return redirect()->back(); 
    }
}
