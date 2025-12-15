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
            'nama_kementerian' => 'required|string',
            'nama_direktur'    => 'required|string',
            'nip_direktur'     => 'required|string',
            'status'           => ['required', Rule::in([0, 1])],
        ]);

        $this->service->create($data);

        return redirect()->back(); 
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nama_kementerian' => 'required|string',
            'nama_direktur'    => 'required|string',
            'nip_direktur'     => 'required|string',
            'status'           => ['required', Rule::in([0, 1])],
        ]);

        $this->service->update($id, $data);

        return redirect()->back(); 
    }

    public function toggleStatus($id)
    {
        $this->service->toggleStatus($id);

        return redirect()->back(); 
    }
}
