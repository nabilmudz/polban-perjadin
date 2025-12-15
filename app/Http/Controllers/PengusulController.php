<?php

namespace App\Http\Controllers;

use App\Services\SuratTugasService;
use App\Services\PegawaiService;
use App\Services\MahasiswaService;
use Illuminate\Http\Request;
use App\Models\SuratTugas;
use Inertia\Inertia;
use App\Models\Mahasiswa;
use App\Models\Pegawai;

class PengusulController extends Controller
{
    protected $suratTugasService;
    protected $pegawaiService;
    protected $mahasiswaService;

    public function __construct(
        SuratTugasService $suratTugasService,
        PegawaiService $pegawaiService,
        MahasiswaService $mahasiswaService
        )
        {
            $this->suratTugasService = $suratTugasService;
            $this->pegawaiService = $pegawaiService;
            $this->mahasiswaService = $mahasiswaService;
        }
    
    public function dashboardPengusulan(Request $request)
    {
        $user = $request->user();

        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'from'   => $request->get('from'),
            'to'     => $request->get('to'),
            'page'   => $request->get('page'),
        ];

        // pastikan service kamu eager-load detailPelaksanaTugas.personable
        $surat = $this->suratTugasService->getAll($filters, $user);

        $mapped = [
            'data' => $surat->getCollection()->transform(function (SuratTugas $item) {
                // kalau service belum with(), bisa pakai:
                $item->loadMissing('detailPelaksanaTugas.personable');

                $personel = $item->detailPelaksanaTugas->map(function ($d) {
                    $p = $d->personable;
                    $isMhs = str_contains($d->personable_type, 'Mahasiswa');

                    return [
                        'id'       => $p->id,
                        'type'     => $isMhs ? 'mahasiswa' : 'pegawai',
                        'nama'     => $p->nama,
                        'nip'      => $isMhs ? null      : ($p->nip ?? null),
                        'nim'      => $isMhs ? ($p->nim ?? null) : null,
                        'pangkat'  => $p->pangkat ?? null,
                        'golongan' => $p->golongan ?? null,
                        'jabatan'  => $p->jabatan ?? null,
                        'jurusan'  => $p->jurusan ?? null,
                        'prodi'    => $p->prodi ?? null,
                    ];
                });

                return [
                    ...$item->toArray(),              // semua field surat
                    'personel'   => $personel,        // <-- ini yang dipakai LaporanSurat
                    'created_at' => $item->created_at?->format('Y-m-d'),
                ];
            }),
            'meta' => [
                'current_page' => $surat->currentPage(),
                'last_page'    => $surat->lastPage(),
                'per_page'     => $surat->perPage(),
                'from'         => $surat->firstItem(),
                'to'           => $surat->lastItem(),
                'total'        => $surat->total(),
            ],
            'links' => [
                'prev' => $surat->previousPageUrl(),
                'next' => $surat->nextPageUrl(),
            ],
        ];

        $statusCounts = SuratTugas::where('user_id', $user->id)
            ->selectRaw('status_surat, COUNT(*) as count')
            ->groupBy('status_surat')
            ->pluck('count', 'status_surat')
            ->toArray();

        $today = now()->toDateString();

        $onDuty = SuratTugas::where('user_id', $user->id)
            ->where('status_surat', 'published')
            ->whereDate('tanggal_berangkat', '<=', $today)
            ->whereDate('tanggal_kembali', '>=', $today)
            ->count();

        return inertia('Pengusul/PengusulDashboard', [
            'suratTugas' => $mapped,
            'filters' => $filters,
            'statusCounts' => array_merge($statusCounts, ['on_duty' => $onDuty]),
        ]);
    }

    public function daftarPengusulan(Request $request)
    {
        $user = $request->user();

        $filters = $request->only(['search', 'status', 'from', 'to', 'page']);
        $filters['from'] = $filters['from'] ?? null;
        $filters['to']   = $filters['to'] ?? null;

        $surat = $this->suratTugasService->getAll($filters, $user);

        $mapped = [
            'data' => $surat->getCollection()->transform(function (SuratTugas $item) {
                // pastikan relasi kebawa ke Vue
                $item->loadMissing('detailPelaksanaTugas.personable');

                $personel = $item->detailPelaksanaTugas->map(function ($d) {
                    $p     = $d->personable;
                    $isMhs = str_contains($d->personable_type, 'Mahasiswa');

                    return [
                        'id'       => $p->id,
                        'type'     => $isMhs ? 'mahasiswa' : 'pegawai',
                        'nama'     => $p->nama,
                        'nip'      => $isMhs ? null : ($p->nip ?? null),
                        'nim'      => $isMhs ? ($p->nim ?? null) : null,
                        'pangkat'  => $p->pangkat ?? null,
                        'golongan' => $p->golongan ?? null,
                        'jabatan'  => $p->jabatan ?? null,
                        'jurusan'  => $p->jurusan ?? null,
                        'prodi'    => $p->prodi ?? null,
                    ];
                });

                return [
                    ...$item->toArray(),

                    'created_at'        => $item->created_at?->format('Y-m-d'),
                    'tanggal_berangkat' => $item->tanggal_berangkat?->format('Y-m-d'),

                    'no_usulan_surat' => sprintf(
                        '%s/%s/%s',
                        $item->nomor_urutan_surat,
                        $item->kode_perihal,
                        $item->tahun_nomor_surat
                    ),

                    'personel' => $personel,
                ];
            }),
            'meta' => [
                'current_page' => $surat->currentPage(),
                'last_page'    => $surat->lastPage(),
                'per_page'     => $surat->perPage(),
                'from'         => $surat->firstItem(),
                'to'           => $surat->lastItem(),
                'total'        => $surat->total(),
            ],
            'links' => [
                'prev' => $surat->previousPageUrl(),
                'next' => $surat->nextPageUrl(),
            ],
        ];

        return inertia('Pengusul/DaftarPengusulan', [
            'suratTugas' => $mapped,
            'filters'    => $filters,
        ]);
    }

    public function draftPengusulan(Request $request)
    {
        $user = $request->user();

        $filters = $request->only(['search', 'from', 'to', 'page']);
        $filters['status'] = 'draft';
        $filters['from']   = $filters['from'] ?? null;
        $filters['to']     = $filters['to'] ?? null;

        $surat = $this->suratTugasService->getAll($filters, $user);

        $mapped = [
            'data' => $surat->getCollection()->transform(function (SuratTugas $item) {
                $item->loadMissing('detailPelaksanaTugas.personable');

                $personel = $item->detailPelaksanaTugas->map(function ($d) {
                    $p     = $d->personable;
                    $isMhs = str_contains($d->personable_type, 'Mahasiswa');

                    return [
                        'id'       => $p->id,
                        'type'     => $isMhs ? 'mahasiswa' : 'pegawai',
                        'nama'     => $p->nama,
                        'nip'      => $isMhs ? null : ($p->nip ?? null),
                        'nim'      => $isMhs ? ($p->nim ?? null) : null,
                        'pangkat'  => $p->pangkat ?? null,
                        'golongan' => $p->golongan ?? null,
                        'jabatan'  => $p->jabatan ?? null,
                        'jurusan'  => $p->jurusan ?? null,
                        'prodi'    => $p->prodi ?? null,
                    ];
                });

                return [
                    ...$item->toArray(),

                    'created_at'        => $item->created_at?->format('Y-m-d'),
                    'tanggal_berangkat' => $item->tanggal_berangkat?->format('Y-m-d'),

                    'no_usulan_surat' => sprintf(
                        '%s/%s/%s',
                        $item->nomor_urutan_surat,
                        $item->kode_perihal,
                        $item->tahun_nomor_surat
                    ),

                    'personel' => $personel,
                ];
            }),
            'meta' => [
                'current_page' => $surat->currentPage(),
                'last_page'    => $surat->lastPage(),
                'per_page'     => $surat->perPage(),
                'from'         => $surat->firstItem(),
                'to'           => $surat->lastItem(),
                'total'        => $surat->total(),
            ],
            'links' => [
                'prev' => $surat->previousPageUrl(),
                'next' => $surat->nextPageUrl(),
            ],
        ];

        return inertia('Pengusul/DraftPengusulan', [
            'suratTugas' => $mapped,
            'filters'    => $filters,
        ]);
    }
    public function formPengusulan(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page', 'tab']);
        $tab = $filters['tab'] ?? 'pegawai';

        $data = $tab === 'pegawai'
            ? $this->getPegawai($filters)
            : $this->getMahasiswa($filters);

        return Inertia::render('Pengusul/PengusulanWizard', [
            'personel' => $data,
            'filters'  => $filters,
            'tab'      => $tab,
        ]);
    }
    
    public function personel(Request $request)
    {
        $filters = $request->only(['search', 'status', 'from', 'to', 'page', 'tab']);
        $tab = $request->get('tab', 'pegawai');

        $data = $tab === 'pegawai'
            ? $this->getPegawai($filters)
            : $this->getMahasiswa($filters);

        return Inertia::render('Pengusul/FormPersonel', [
            'personel' => $data,
            'filters'  => $filters,
            'tab'      => $tab,
        ]);
    }

    private function getMahasiswa(array $filters = [])
    {
        $query = Mahasiswa::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('jurusan', 'like', "%{$search}%")
                    ->orWhere('prodi', 'like', "%{$search}%");
            });
        }

        $paginate = $query->latest()->paginate(10)->withQueryString();

        return $this->formatPagination($paginate);
    }

    private function getPegawai(array $filters = [])
    {
        $query = Pegawai::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('pangkat', 'like', "%{$search}%")
                    ->orWhere('golongan', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->whereBetween('created_at', [$filters['from'], $filters['to']]);
        }

        $paginate = $query->latest()->paginate(10)->withQueryString();

        return $this->formatPagination($paginate);
    }

    private function formatPagination($paginate)
    {
        return [
            'data' => $paginate->items(),
            'meta' => [
                'current_page' => $paginate->currentPage(),
                'last_page' => $paginate->lastPage(),
                'per_page' => $paginate->perPage(),
                'from' => $paginate->firstItem(),
                'to' => $paginate->lastItem(),
                'total' => $paginate->total(),
            ],
            'links' => [
                'prev' => $paginate->previousPageUrl(),
                'next' => $paginate->nextPageUrl(),
            ],
        ];
    }

    public function submitPengusulan(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'pengusulan.nama_kegiatan'       => 'required|string|max:255',
            'pengusulan.diajukan_kepada'     => 'required|string|max:255',
            'pengusulan.penyelenggara'       => 'required|string|max:50',
            'pengusulan.nama_penyelenggara'  => 'required|string|max:255',
            'pengusulan.tanggal_berangkat' => 'required|date',
            'pengusulan.tanggal_kembali'   => 'required|date|after_or_equal:pengusulan.tanggal_berangkat',
            'pengusulan.provinsi'            => 'required|string|max:255',
            'pengusulan.hasPagu'             => 'boolean',
            'pengusulan.nominal_pagu'        => 'nullable|numeric',
            'pengusulan.lokasiList'          => 'required|array|min:1',
            'pengusulan.lokasiList.*.tempat' => 'nullable|string|max:255',
            'pengusulan.lokasiList.*.alamat' => 'nullable|string|max:255',
            'pengusulan.surat_undangan'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'personel'                       => 'required|array|min:1',
            'personel.*.id'                  => 'required|integer',
            'personel.*.type'                => 'required|in:pegawai,mahasiswa',
            'personel.*.nama'                => 'required|string',
        ]);

        $pengusulan = $validated['pengusulan'];
        $personel   = $validated['personel'];

        try {
            $pathSuratUndangan = null;

            if ($request->hasFile('pengusulan.surat_undangan')) {
                $pathSuratUndangan = $request
                    ->file('pengusulan.surat_undangan')
                    ->store('surat-usulan', 'public');
            }

            $nomorSuratUsulanJurusan = $request->input('nomor_surat_usulan_jurusan');

            $suratData = [
                'user_id'                    => $user->id,
                'diusulkan_kepada'           => $pengusulan['diajukan_kepada'],
                'nama_penyelenggara'         => $pengusulan['nama_penyelenggara'],
                'lokasi_kegiatan'            => $pengusulan['lokasiList'],
                'nomor_surat_usulan_jurusan' => $nomorSuratUsulanJurusan ?? 'TEMP/' . now()->timestamp,
                'perihal_tugas'              => $pengusulan['nama_kegiatan'],
                'ditugaskan_sebagai'         => 'Peserta',
                'kota_tujuan'                => $pengusulan['provinsi'] ?? null,
                'tanggal_berangkat'          => $pengusulan['tanggal_berangkat'],
                'tanggal_kembali'            => $pengusulan['tanggal_kembali'],
                'status_surat'               => 'draft',
                'path_file_surat_usulan'     => $pathSuratUndangan,
                'sumber_dana'                => $pengusulan['hasPagu'] ? 'Pagu Desentralisasi' : 'Non Pagu',
                'pagu_desentralisasi'        => $pengusulan['hasPagu'],
                'template_tembusan'          => ['-'],
            ];

            $this->suratTugasService->createWithPersonel($suratData, $personel);

            return redirect()
                ->route('pengusul.dashboard')
                ->with('success', 'Pengusulan berhasil disimpan sebagai draft.');
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Terjadi kesalahan saat menyimpan pengusulan.'])
                ->withInput();
        }
    }
}
