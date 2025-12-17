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
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\TemplateSurat;
use Illuminate\Validation\ValidationException;

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
    private function templateSnapshotForNewSurat(): array
    {
        $tpl = TemplateSurat::query()
            ->where('status', 1)
            ->orderByDesc('id')
            ->first();

        return [
            'template_nama_kementerian' => $tpl?->nama_kementerian,
            'template_nama_direktur'    => $tpl?->nama_direktur,
            'template_nip_direktur'     => $tpl?->nip_direktur,
            'template_tembusan'         => $tpl?->tembusan_default ?? [],
        ];
    }

    private function mapSuratPaginator($surat)
    {
        return [
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
    }
    public function nomorTerpakai(Request $request)
    {
        $user = $request->user();

        $days  = (int) $request->query('days', 30);
        $days  = max(1, min($days, 365)); // safety
        $from  = now()->subDays($days)->startOfDay();

        $kodePengusul = $user->kode_pengusul;

        $q = SuratTugas::query()
            ->where('created_at', '>=', $from)
            ->whereNotNull('nomor_surat_usulan_jurusan')
            ->where('nomor_surat_usulan_jurusan', '!=', '')
            ->where('nomor_surat_usulan_jurusan', 'not like', 'TEMP/%');

        if (!empty($kodePengusul)) {
            $q->where('nomor_surat_usulan_jurusan', 'like', '%/' . $kodePengusul . '/%');
        }

        if ($request->filled('tahun')) {
            $q->where('tahun_nomor_surat', (int) $request->query('tahun'));
        }
        if ($request->filled('kode_perihal')) {
            $q->where('kode_perihal', $request->query('kode_perihal'));
        }

        $rows = $q->select(['nomor_surat_usulan_jurusan', 'status_surat', 'created_at'])
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return response()->json([
            'meta' => [
                'from' => $from->toDateString(),
                'days' => $days,
                'count' => $rows->count(),
            ],
            'data' => $rows->map(fn ($r) => [
                'nomor'   => $r->nomor_surat_usulan_jurusan,
                'status'  => $r->status_surat,
                'tanggal' => optional($r->created_at)->format('Y-m-d'),
            ])->values(),
        ]);
    }

    public function dashboardPengusulan(Request $request)
    {
        $user = $request->user();

        $filters = $request->only(['search', 'status', 'from', 'to', 'page']);
        $filters['from'] = $filters['from'] ?? null;
        $filters['to']   = $filters['to'] ?? null;

        $surat  = $this->suratTugasService->getAll($filters, $user);
        $mapped = $this->mapSuratPaginator($surat);

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
            'suratTugas'    => $mapped,
            'filters'       => $filters,
            'statusCounts'  => array_merge($statusCounts, ['on_duty' => $onDuty]),
        ]);
    }

    public function daftarPengusulan(Request $request)
    {
        $user = $request->user();

        $filters = $request->only(['search', 'status', 'from', 'to', 'page']);
        $filters['from'] = $filters['from'] ?? null;
        $filters['to']   = $filters['to'] ?? null;

        $surat  = $this->suratTugasService->getAll($filters, $user);
        $mapped = $this->mapSuratPaginator($surat);

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

        $mapped = $this->mapSuratPaginator($surat);

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
            'pengusulan.nomor_urutan_surat'  => 'required|integer',
            'pengusulan.kode_perihal'        => 'required',
            'pengusulan.tahun_nomor_surat'   => 'required|integer',
            'pengusulan.tanggal_berangkat'   => 'required|date',
            'pengusulan.tanggal_kembali'     => 'required|date|after_or_equal:pengusulan.tanggal_berangkat',
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
                ...$this->templateSnapshotForNewSurat(),
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

    public function saveDraft(Request $request)
    {
    $user = $request->user();
    $validated = $this->validatePengusulan($request);

    $pengusulan = $validated['pengusulan'];
    $personel   = $validated['personel'];

    $pathSuratUndangan = $this->storeSuratUndangan($request);

    $nomorUsulan = $this->buildNomorUsulan($user, $pengusulan);

    $suratData = [
        'user_id'                    => $user->id,
        'diusulkan_kepada'           => $pengusulan['diajukan_kepada'],
        'nama_penyelenggara'         => $pengusulan['nama_penyelenggara'],
        'lokasi_kegiatan'            => $pengusulan['lokasiList'],
        'nomor_urutan_surat'         => $pengusulan['nomor_urutan_surat'],
        'kode_perihal'               => $pengusulan['kode_perihal'],
        'tahun_nomor_surat'          => $pengusulan['tahun_nomor_surat'],
        'nomor_surat_usulan_jurusan' => $nomorUsulan,
        'perihal_tugas'              => $pengusulan['nama_kegiatan'],
        'ditugaskan_sebagai'         => 'Peserta',
        'kota_tujuan'                => $pengusulan['provinsi'] ?? null,
        'tanggal_berangkat'          => $pengusulan['tanggal_berangkat'],
        'tanggal_kembali'            => $pengusulan['tanggal_kembali'],
        'status_surat'               => 'draft',
        'path_file_surat_usulan'     => $pathSuratUndangan,
        'sumber_dana'                => $pengusulan['hasPagu'] ? 'Pagu Desentralisasi' : 'Non Pagu',
        'pagu_desentralisasi'        => $pengusulan['hasPagu'],
        'nominal_dana'               => $pengusulan['nominal_pagu'] ?? null,
        ...$this->templateSnapshotForNewSurat(),
    ];

    $this->suratTugasService->createWithPersonel($suratData, $personel);

    return redirect()->route('pengusul.dashboard')
        ->with('success', 'Draft berhasil disimpan.');
    }

    public function submitToWadir(Request $request)
    {
        $user = $request->user();
        $validated = $this->validatePengusulan($request);

        $pengusulan = $validated['pengusulan'];
        $personel   = $validated['personel'];

        $pathSuratUndangan = $this->storeSuratUndangan($request);
        $nomorUsulan = $this->buildNomorUsulan($user, $pengusulan);

        if (SuratTugas::where('nomor_surat_usulan_jurusan', $nomorUsulan)->exists()) {
            return back()->withErrors(['message' => 'Nomor Surat Usulan sudah dipakai.'])->withInput();
        }

        $suratData = [
            'user_id'                    => $user->id,
            'diusulkan_kepada'           => $pengusulan['diajukan_kepada'],
            'nama_penyelenggara'         => $pengusulan['nama_penyelenggara'],
            'lokasi_kegiatan'            => $pengusulan['lokasiList'],
            'nomor_urutan_surat'         => $pengusulan['nomor_urutan_surat'],
            'kode_perihal'               => $pengusulan['kode_perihal'],
            'tahun_nomor_surat'          => $pengusulan['tahun_nomor_surat'],
            'nomor_surat_usulan_jurusan' => $nomorUsulan,
            'perihal_tugas'              => $pengusulan['nama_kegiatan'],
            'ditugaskan_sebagai'         => 'Peserta',
            'kota_tujuan'                => $pengusulan['provinsi'] ?? null,
            'tanggal_berangkat'          => $pengusulan['tanggal_berangkat'],
            'tanggal_kembali'            => $pengusulan['tanggal_kembali'],
            'status_surat'               => 'submitted_wadir_review',
            'path_file_surat_usulan'     => $pathSuratUndangan,
            'sumber_dana'                => $pengusulan['hasPagu'] ? 'Pagu Desentralisasi' : 'Non Pagu',
            'pagu_desentralisasi'        => $pengusulan['hasPagu'],
            'nominal_dana'               => $pengusulan['nominal_pagu'] ?? null,
            ...$this->templateSnapshotForNewSurat(),
        ];

        $surat = $this->suratTugasService->createWithPersonel($suratData, $personel);

        // TODO (wajib untuk requirement lengkap):
        // 1) audit log: action SUBMITTED, actor $user->id, surat_tugas_id
        // 2) notif: kirim ke user Wadir sesuai $pengusulan['diajukan_kepada']

    return redirect()->route('pengusul.dashboard')
        ->with('success', 'Pengusulan berhasil dikirim ke Wadir.');
    }

    private function validatePengusulan(Request $request): array
    {
        return $request->validate([
            'pengusulan.nama_kegiatan'       => 'required|string|max:255',
            'pengusulan.diajukan_kepada'     => 'required|string|max:255',
            'pengusulan.penyelenggara'       => 'required|string|max:50',
            'pengusulan.nama_penyelenggara'  => 'required|string|max:255',
            'pengusulan.tanggal_berangkat'   => 'required|date',
            'pengusulan.tanggal_kembali'     => 'required|date|after_or_equal:pengusulan.tanggal_berangkat',
            'pengusulan.provinsi'            => 'required|string|max:255',
            'pengusulan.hasPagu'             => 'boolean',
            'pengusulan.nominal_pagu'        => 'nullable|numeric',
            'pengusulan.lokasiList'          => 'required|array|min:1',
            'pengusulan.lokasiList.*.tempat' => 'nullable|string|max:255',
            'pengusulan.lokasiList.*.alamat' => 'nullable|string|max:255',
            'pengusulan.surat_undangan'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',

            'pengusulan.nomor_urutan_surat'  => 'required|integer|min:1',
            'pengusulan.kode_perihal'        => 'required|string|max:50',
            'pengusulan.tahun_nomor_surat'   => 'required|integer|min:2000|max:2100',

            'personel'                       => 'required|array|min:1',
            'personel.*.id'                  => 'required|integer',
            'personel.*.type'                => 'required|in:pegawai,mahasiswa',
            'personel.*.nama'                => 'required|string',
        ]);
    }

    public function editDraft(Request $request, SuratTugas $suratTugas)
    {
        $user = $request->user();

        if ((int) $suratTugas->user_id !== (int) $user->id) {
            abort(403);
        }
        if (!in_array($suratTugas->status_surat, [
            'draft',
            'revision_requested',
            'sekdir_revision_requested',
            'direktur_revision_requested',
            ], true)) {
            abort(403, 'Only draft or revision-requested can be edited.');
        }

        $suratTugas->loadMissing('detailPelaksanaTugas.personable');

        $lokasi = $suratTugas->lokasi_kegiatan;
        if (is_string($lokasi)) {
            $decoded = json_decode($lokasi, true);
            $lokasi  = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }
        if (!is_array($lokasi)) $lokasi = [];

        $personelSelected = $suratTugas->detailPelaksanaTugas->map(function ($d) {
            $p = $d->personable;
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
        })->values()->all();

        $initialPengusulan = [
            'nama_kegiatan'      => $suratTugas->perihal_tugas,
            'diajukan_kepada'    => $suratTugas->diusulkan_kepada,
            'penyelenggara'      => $suratTugas->penyelenggara ?? '',
            'nama_penyelenggara' => $suratTugas->nama_penyelenggara,
            'tanggal_berangkat'  => optional($suratTugas->tanggal_berangkat)->format('Y-m-d'),
            'tanggal_kembali'    => optional($suratTugas->tanggal_kembali)->format('Y-m-d'),
            'hasPagu'            => (bool) $suratTugas->pagu_desentralisasi,
            'nominal_pagu'       => $suratTugas->nominal_dana,
            'provinsi'           => $suratTugas->kota_tujuan,
            'lokasiList'         => $lokasi,
            'nomor_urutan_surat' => $suratTugas->nomor_urutan_surat,
            'kode_perihal'       => $suratTugas->kode_perihal,
            'tahun_nomor_surat'  => $suratTugas->tahun_nomor_surat,
            'kode_pengusul'      => $user->kode_pengusul ?? '',
            'surat_undangan'     => null,
            'catatan_revisi'     => $suratTugas->catatan_revisi,
        ];

        $filters = $request->only(['search', 'status', 'from', 'to', 'page', 'tab']);
        $tab = $filters['tab'] ?? 'pegawai';

        $datatable = $tab === 'pegawai'
            ? $this->getPegawai($filters)
            : $this->getMahasiswa($filters);

        return Inertia::render('Pengusul/PengusulanWizard', [
            'mode'    => 'edit_draft',
            'draftId' => $suratTugas->getKey(),
            'templateSnapshot' => [
                'nama_kementerian'  => $suratTugas->template_nama_kementerian,
                'nama_direktur'     => $suratTugas->template_nama_direktur,
                'nip_direktur'      => $suratTugas->template_nip_direktur,
                'tembusan_default'  => $suratTugas->template_tembusan ?? [],
            ],
            'initial' => [
                'pengusulan' => $initialPengusulan,
                'personel'   => $personelSelected,
            ],
            'personel' => $datatable,
            'filters'  => $filters,
            'tab'      => $tab,
        ]);
    }

    public function updateDraft(Request $request, SuratTugas $suratTugas)
    {
        $user = $request->user();

        if ((int) $suratTugas->user_id !== (int) $user->id) abort(403);
       
        if (!in_array($suratTugas->status_surat, [
            'draft',
            'revision_requested',
            'sekdir_revision_requested',
            'direktur_revision_requested',
            ], true)) {
            abort(403, 'Only draft or revision-requested can be edited.');
        }

        $validated = $this->validatePengusulan($request);
        $pengusulan = $validated['pengusulan'];
        $personel = $validated['personel'];

        DB::transaction(function () use ($request, $user, $suratTugas, $pengusulan, $personel) {

            $pathSuratUndangan = $this->storeSuratUndangan($request);
            $nomorUsulan = $this->buildNomorUsulan($user, $pengusulan);

            $exists = SuratTugas::where('nomor_surat_usulan_jurusan', $nomorUsulan)
                ->where($suratTugas->getKeyName(), '!=', $suratTugas->getKey())
                ->exists();
            if ($exists) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'pengusulan.nomor_urutan_surat' => 'Nomor Surat Usulan sudah dipakai.',
                ]);
            }

            $prevStatus = $suratTugas->status_surat;

            $suratTugas->update([
                'diusulkan_kepada'           => $pengusulan['diajukan_kepada'],
                'nama_penyelenggara'         => $pengusulan['nama_penyelenggara'],
                'lokasi_kegiatan'            => $pengusulan['lokasiList'],
                'nomor_urutan_surat'         => $pengusulan['nomor_urutan_surat'],
                'kode_perihal'               => $pengusulan['kode_perihal'],
                'tahun_nomor_surat'          => $pengusulan['tahun_nomor_surat'],
                'nomor_surat_usulan_jurusan' => $nomorUsulan,
                'perihal_tugas'              => $pengusulan['nama_kegiatan'],
                'kota_tujuan'                => $pengusulan['provinsi'] ?? null,
                'tanggal_berangkat'          => $pengusulan['tanggal_berangkat'],
                'tanggal_kembali'            => $pengusulan['tanggal_kembali'],
                'path_file_surat_usulan'     => $pathSuratUndangan ?? $suratTugas->path_file_surat_usulan,
                'sumber_dana'                => $pengusulan['hasPagu'] ? 'Pagu Desentralisasi' : 'Non Pagu',
                'pagu_desentralisasi'        => $pengusulan['hasPagu'],
                'nominal_dana'               => $pengusulan['nominal_pagu'] ?? null,
                'status_surat' => in_array($prevStatus, [
                    'revision_requested',
                    'sekdir_revision_requested',
                    'direktur_revision_requested',
                ], true) ? $prevStatus : 'draft',
                'catatan_revisi' => in_array($prevStatus, [
                    'revision_requested',
                    'sekdir_revision_requested',
                    'direktur_revision_requested',
                ], true) ? null : $suratTugas->catatan_revisi,

            ]);

            $suratTugas->detailPelaksanaTugas()->delete();

            foreach ($personel as $p) {
                $suratTugas->detailPelaksanaTugas()->create([
                    'personable_id'   => $p['id'],
                    'personable_type' => $p['type'] === 'mahasiswa'
                        ? Mahasiswa::class
                        : Pegawai::class,

                    'status_sebagai'  => $p['status_sebagai'] ?? 'Peserta',
                ]);
            }

        });

        return redirect()->route('pengusul.draft')->with('success', 'Draft berhasil diperbarui.');
    }

    public function submitDraftToWadir(Request $request, SuratTugas $suratTugas)
    {
        $user = $request->user();

        if ((int) $suratTugas->user_id !== (int) $user->id) {
            abort(403);
        }

        if (!in_array($suratTugas->status_surat, [
            'draft',
            'revision_requested',
            'sekdir_revision_requested',
            'direktur_revision_requested',
        ], true)) {
            abort(409, 'Draft tidak dapat dikirim karena status tidak valid.');
        }

        $fromStatus = $suratTugas->status_surat;

        $validated  = $this->validatePengusulan($request);
        $pengusulan = $validated['pengusulan'];
        $personel   = $validated['personel'];

        $nextStatus = match ($fromStatus) {
            'sekdir_revision_requested'    => 'pending_sekdir_numbering',
            'direktur_revision_requested' => 'pending_direktur_signature',
            default                        => 'submitted_wadir_review',
        };

        DB::transaction(function () use ($request, $user, $suratTugas, $pengusulan, $personel, $nextStatus) {

            $pathSuratUndangan = $this->storeSuratUndangan($request);
            $nomorUsulan       = $this->buildNomorUsulan($user, $pengusulan);

            if (
                SuratTugas::where('nomor_surat_usulan_jurusan', $nomorUsulan)
                    ->where($suratTugas->getKeyName(), '!=', $suratTugas->getKey())
                    ->exists()
            ) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'pengusulan.nomor_urutan_surat' => 'Nomor Surat Usulan sudah dipakai.',
                ]);
            }

            $suratTugas->update([
                'diusulkan_kepada'           => $pengusulan['diajukan_kepada'],
                'nama_penyelenggara'         => $pengusulan['nama_penyelenggara'],
                'lokasi_kegiatan'            => $pengusulan['lokasiList'],
                'nomor_urutan_surat'         => $pengusulan['nomor_urutan_surat'],
                'kode_perihal'               => $pengusulan['kode_perihal'],
                'tahun_nomor_surat'          => $pengusulan['tahun_nomor_surat'],
                'nomor_surat_usulan_jurusan' => $nomorUsulan,
                'perihal_tugas'              => $pengusulan['nama_kegiatan'],
                'kota_tujuan'                => $pengusulan['provinsi'] ?? null,
                'tanggal_berangkat'          => $pengusulan['tanggal_berangkat'],
                'tanggal_kembali'            => $pengusulan['tanggal_kembali'],
                'path_file_surat_usulan'     => $pathSuratUndangan ?? $suratTugas->path_file_surat_usulan,
                'sumber_dana'                => $pengusulan['hasPagu'] ? 'Pagu Desentralisasi' : 'Non Pagu',
                'pagu_desentralisasi'        => $pengusulan['hasPagu'],
                'nominal_dana'               => $pengusulan['nominal_pagu'] ?? null,
                'catatan_revisi'             => null,
            ]);

            $suratTugas->detailPelaksanaTugas()->delete();

            foreach ($personel as $p) {
                $suratTugas->detailPelaksanaTugas()->create([
                    'personable_id'   => $p['id'],
                    'personable_type' => $p['type'] === 'mahasiswa' ? Mahasiswa::class : Pegawai::class,
                    'status_sebagai'  => $p['status_sebagai'] ?? 'Peserta',
                ]);
            }

            $this->suratTugasService->updateStatus(
                $suratTugas->fresh(),
                $nextStatus,
                null,
                'pengusul'
            );
        });

        $msg = match ($nextStatus) {
            'pending_sekdir_numbering'    => 'Revisi Sekdir sudah dikirim kembali ke Sekdir untuk penomoran.',
            'pending_direktur_signature' => 'Revisi Direktur sudah dikirim kembali ke Direktur untuk ditinjau.',
            default                       => 'Draft berhasil dikirim ke Wadir.',
        };

        return redirect()->route('pengusul.dashboard')->with('success', $msg);
    }

    private function storeSuratUndangan(Request $request): ?string
    {
        if (!$request->hasFile('pengusulan.surat_undangan')) return null;

        return $request->file('pengusulan.surat_undangan')
            ->store('surat-usulan', 'public');
    }

    private function buildNomorUsulan(User $user, array $pengusulan): string
    {
        $nomor = (int) $pengusulan['nomor_urutan_surat'];
        $kodePengusul = $user->kode_pengusul ?? 'UNKNOWN';
        $kodePerihal = $pengusulan['kode_perihal'];
        $tahun = (int) $pengusulan['tahun_nomor_surat'];

        return sprintf('%03d/%s/%s/%d', $nomor, $kodePengusul, $kodePerihal, $tahun);
    }
    
    public function destroyDraft(Request $request, SuratTugas $suratTugas)
    {
        $user = $request->user();

        if ((int) $suratTugas->user_id !== (int) $user->id) {
            abort(403);
        }

        if (!in_array($suratTugas->status_surat, ['draft'], true)) {
            abort(403, 'Only draft can be deleted.');
        }

        DB::transaction(function () use ($suratTugas) {
            $suratTugas->detailPelaksanaTugas()->delete();

            if (!empty($suratTugas->path_file_surat_usulan)) {
                Storage::disk('public')->delete($suratTugas->path_file_surat_usulan);
            }

            $suratTugas->delete();
        });

        return back()->with('success', 'Draft berhasil dihapus.');
    }

}
