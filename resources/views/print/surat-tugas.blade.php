<!doctype html>
<html lang="id">
<base href="{{ url('/') }}/">
<head>
  <meta charset="utf-8">
  <title>Surat Tugas</title>

  <style>
    @page { size: A4; margin: 2.5cm; }
    body { font-family: "Times New Roman", Times, serif; font-size: 11pt; margin: 0; }
    * { box-sizing: border-box; }

    .page { page-break-after: always; }
    .page:last-child { page-break-after: auto; }

    .header-table { width: 100%; border-collapse: collapse; }
    .logo-cell { width: 90px; vertical-align: top; }
    .logo { width: 80px; }

    .text-center { text-align: center; }
    .m-0 { margin: 0; }
    .mt-1 { margin-top: 4px; }
    .mt-2 { margin-top: 8px; }
    .mt-4 { margin-top: 16px; }
    .mt-6 { margin-top: 24px; }
    .mt-10 { margin-top: 40px; }
    .mt-24 { margin-top: 96px; }
    .mb-0 { margin-bottom: 0; }
    .mb-1 { margin-bottom: 4px; }
    .mb-2 { margin-bottom: 8px; }
    .mb-3 { margin-bottom: 12px; }
    .mb-4 { margin-bottom: 16px; }

    .h1 { font-size: 14pt; font-weight: bold; text-transform: uppercase; line-height: 1.1; margin: 0; }
    .h2 { font-size: 12pt; font-weight: bold; text-transform: uppercase; line-height: 1.1; margin: 3px 0 0; }
    .p-sub { font-size: 9.5pt; line-height: 1.4; margin: 2px 0 0; }

    .hr { border-top: 1.5px solid #000; margin: 5px 0 20px; }

    .title { font-size: 13pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; margin: 0; }
    .nomor { font-size: 11pt; margin: 2px 0 0; }

    .content { font-size: 11pt; line-height: 1.6; }
    .detail-table { width: 100%; border-collapse: collapse; }
    .detail-table td { padding: 1px 0; vertical-align: top; }
    .detail-label { width: 160px; }
    .detail-sep { width: 10px; }

    .table-bordered { width: 100%; border: 1px solid #000; border-collapse: collapse; }
    .table-bordered th, .table-bordered td { border: 1px solid #000; padding: 5px; }
    .table-bordered th { background: #f2f2f2; text-align: center; }

    .attachment-table { width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 11pt; }
    .attachment-table th, .attachment-table td { border: 1px solid #000; padding: 5px; vertical-align: top; }
    .attachment-table th { background: #f2f2f2; text-align: center; }

    .footer-table { width: 100%; border-collapse: collapse; }
    .tembusan-cell { width: 50%; font-size: 10pt; vertical-align: bottom; }
    .signature-cell { width: 50%; vertical-align: bottom; text-align: right; }

    .signature-inner { width: 175px; margin-left: auto; text-align: left; }
    .signature-block { height: 90px; text-align: center; }
    .barcode { width: 85px; height: 85px; object-fit: contain; display:block; margin:0 auto; }
    .text-center-cell { text-align: center; }
  </style>
</head>

@php
  use Carbon\Carbon;
  Carbon::setLocale('id');

  $fmt = function ($d) {
    return $d ? Carbon::parse($d)->translatedFormat('j F Y') : '-';
  };

  $sameDay = function ($a, $b) {
    if (!$a || !$b) return false;
    return Carbon::parse($a)->isSameDay(Carbon::parse($b));
  };

  $alamatHtml = function ($alamat) {
    if (!$alamat) return '';
    return nl2br(e($alamat));
  };

  $personelCount = $personel ? $personel->count() : 0;
@endphp

<body>
  <div class="page">
    <table class="header-table">
      <tr>
        <td class="logo-cell">
          @if(!empty($logoData))
            <img src="{{ $logoData }}" class="logo" alt="Logo" />
          @endif
        </td>
        <td class="text-center-cell">
          <h1 class="h1">
            {{ data_get($surat, 'template_nama_kementerian') ?: 'KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET DAN TEKNOLOGI' }}
          </h1>
          <h2 class="h2">POLITEKNIK NEGERI BANDUNG</h2>
          <p class="p-sub">
            Jalan Gegerkalong Hilir, Desa Ciwaruga, Bandung 40012, Kotak Pos 1234<br>
            Telepon: (022) 2013789, Faksimile: (022) 2013889<br>
            Laman: www.polban.ac.id, Pos Elektronik: polban@polban.ac.id
          </p>
        </td>
      </tr>
    </table>

    <div class="hr"></div>

    <div class="text-center">
      <h3 class="title">SURAT TUGAS</h3>
      <p class="nomor">Nomor: {{ data_get($surat,'nomor_surat_tugas_resmi') ?: '-' }}</p>
    </div>

    <div class="content mt-4">
      @if($personelCount)
        <p class="mb-2">Menugaskan kepada:</p>

        @if(!$isLampiran)
          @if($pegawaiList->count())
            <p class="mb-1"><strong>Pegawai</strong></p>
            <table class="table-bordered mb-4">
              <thead>
                <tr>
                  <th style="width:30px;">No</th>
                  <th>Nama</th>
                  <th style="width:120px;">NIP</th>
                  <th style="width:120px;">Pangkat</th>
                  <th style="width:100px;">Golongan</th>
                  <th style="width:160px;">Jabatan</th>
                </tr>
              </thead>
              <tbody>
                @foreach($pegawaiList as $idx => $p)
                  <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td>{{ data_get($p,'nama') }}</td>
                    <td>{{ data_get($p,'nip') ?: '-' }}</td>
                    <td>{{ data_get($p,'pangkat') ?: '-' }}</td>
                    <td>{{ data_get($p,'golongan') ?: '-' }}</td>
                    <td>{{ data_get($p,'jabatan') ?: '-' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif

          @if($mahasiswaList->count())
            <p class="mb-1"><strong>Mahasiswa</strong></p>
            <table class="table-bordered">
              <thead>
                <tr>
                  <th style="width:30px;">No</th>
                  <th>Nama</th>
                  <th style="width:120px;">NIM</th>
                  <th style="width:160px;">Jurusan</th>
                  <th style="width:160px;">Prodi</th>
                </tr>
              </thead>
              <tbody>
                @foreach($mahasiswaList as $idx => $m)
                  <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td>{{ data_get($m,'nama') }}</td>
                    <td>{{ data_get($m,'nim') ?: '-' }}</td>
                    <td>{{ data_get($m,'jurusan') ?: '-' }}</td>
                    <td>{{ data_get($m,'prodi') ?: '-' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        @else
          <p class="mt-1 mb-3">Daftar lengkap personel terlampir pada Lampiran Surat Tugas.</p>
        @endif
      @endif

      <p class="mt-4">
        Untuk mengikuti kegiatan <strong>{{ data_get($surat,'perihal_tugas') }}</strong>,
        diselenggarakan oleh <strong>{{ data_get($surat,'nama_penyelenggara') }}</strong> pada:
      </p>

      <table class="detail-table">
        <tr>
          <td class="detail-label">Hari/Tanggal</td>
          <td class="detail-sep">:</td>
          <td>
            @php
              $tglB = data_get($surat,'tanggal_berangkat');
              $tglK = data_get($surat,'tanggal_kembali');
            @endphp

            @if($tglB && $tglK)
              @if($sameDay($tglB, $tglK))
                {{ $fmt($tglB) }}
              @else
                {{ $fmt($tglB) }} s.d. {{ $fmt($tglK) }}
              @endif
            @else
              {{ $fmt($tglB) }} s.d. {{ $fmt($tglK) }}
            @endif
          </td>
        </tr>

        <tr>
          <td class="detail-label" style="vertical-align:top;">Tempat Kegiatan</td>
          <td class="detail-sep" style="vertical-align:top;">:</td>
          <td>
            @if($lokasiList && $lokasiList->count())
              @foreach($lokasiList as $i => $lokasi)
                <div style="margin-bottom:6px;">
                  <strong>{{ $i+1 }}. {{ data_get($lokasi,'tempat') }}</strong><br>
                  {!! $alamatHtml(data_get($lokasi,'alamat')) !!}
                </div>
              @endforeach
            @endif
          </td>
        </tr>
      </table>

      <p class="mt-4">
        Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.
      </p>
    </div>

    <table class="footer-table mt-24">
      <tr>
        <td class="tembusan-cell">
          @if($tembusanList && $tembusanList->count())
            <p class="mb-1">Tembusan:</p>
            @foreach($tembusanList as $i => $t)
              <div>{{ $i+1 }}. {{ $t }}</div>
            @endforeach
          @endif
        </td>

        <td class="signature-cell">
          <div class="signature-inner">
            <p class="m-0 mb-1">
              Bandung, {{ $fmt(data_get($surat,'tanggal_persetujuan_direktur') ?: data_get($surat,'tanggal_berangkat')) }}
            </p>
            <p class="m-0 mb-1">Direktur,</p>

            <div class="signature-block mt-2">
              @if(!empty($barcodeData))
                <img src="{{ $barcodeData }}" class="barcode" alt="QR TTE">
              @endif
            </div>

            <p class="m-0" style="font-weight:bold; margin-top:8px;">
              {{ data_get($surat,'template_nama_direktur') ?: '[Nama Direktur]' }}
            </p>
            <p class="m-0">
              NIP {{ data_get($surat,'template_nip_direktur') ?: '[NIP Direktur]' }}
            </p>
          </div>
        </td>
      </tr>
    </table>
  </div>

  {{-- LAMPIRAN --}}
  @if($isLampiran && $personelCount)
    @if(!$needChunking)
      <div class="page">
        <div class="content">
          <p class="mb-3">Lampiran: {{ data_get($surat,'nomor_surat_tugas_resmi') ?: '-' }}</p>

          @if($pegawaiList->count())
            <p><strong>1. Pegawai</strong></p>
            <table class="attachment-table">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>NIP</th>
                  <th>Pangkat</th>
                  <th>Golongan</th>
                  <th>Jabatan</th>
                </tr>
              </thead>
              <tbody>
                @foreach($pegawaiList as $p)
                  <tr>
                    <td>{{ data_get($p,'nama') }}</td>
                    <td>{{ data_get($p,'nip') ?: '-' }}</td>
                    <td>{{ data_get($p,'pangkat') ?: '-' }}</td>
                    <td>{{ data_get($p,'golongan') ?: '-' }}</td>
                    <td>{{ data_get($p,'jabatan') ?: '-' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif

          @if($mahasiswaList->count())
            <div class="mt-6"></div>
            <p><strong>2. Mahasiswa</strong></p>
            <table class="attachment-table">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>NIM</th>
                  <th>Jurusan</th>
                  <th>Prodi</th>
                </tr>
              </thead>
              <tbody>
                @foreach($mahasiswaList as $m)
                  <tr>
                    <td>{{ data_get($m,'nama') }}</td>
                    <td>{{ data_get($m,'nim') ?: '-' }}</td>
                    <td>{{ data_get($m,'jurusan') ?: '-' }}</td>
                    <td>{{ data_get($m,'prodi') ?: '-' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif

          <div class="mt-10"></div>
          <table class="footer-table">
            <tr>
              <td style="width:50%"></td>
              <td class="signature-cell">
                <div class="signature-inner">
                  <p class="m-0 mb-1">
                    Bandung, {{ $fmt(data_get($surat,'tanggal_persetujuan_direktur') ?: data_get($surat,'tanggal_berangkat')) }}
                  </p>
                  <p class="m-0 mb-1">Direktur,</p>

                  <div class="signature-block mt-2">
                    @if(data_get($surat,'direktur_signature_data'))
                      <img src="{{ data_get($surat,'direktur_signature_data') }}" style="width:100px; height:60px;">
                    @endif
                  </div>

                  <p class="m-0" style="font-weight:bold; margin-top:8px;">
                    {{ data_get($surat,'template_nama_direktur') ?: '[Nama Direktur]' }}
                  </p>
                  <p class="m-0">
                    NIP {{ data_get($surat,'template_nip_direktur') ?: '[NIP Direktur]' }}
                  </p>
                </div>
              </td>
            </tr>
          </table>
        </div>
      </div>
    @else
      @php
        $chunks = $personnelChunks instanceof \Illuminate\Support\Collection ? $personnelChunks : collect($personnelChunks);
      @endphp

      @foreach($chunks as $pageIndex => $chunk)
        @php
          $chunk = collect($chunk);
          $chunkPegawai = $chunk->filter(fn($p) => data_get($p,'type') === 'pegawai')->values();
          $chunkMahasiswa = $chunk->filter(fn($p) => data_get($p,'type') === 'mahasiswa')->values();
          $isLast = $pageIndex === ($chunks->count() - 1);
        @endphp

        <div class="page">
          <div class="content">
            <p class="mb-3">Lampiran: {{ data_get($surat,'nomor_surat_tugas_resmi') ?: '-' }}</p>

            @if($chunkPegawai->count())
              <p><strong>1. Pegawai</strong></p>
              <table class="attachment-table">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Pangkat</th>
                    <th>Golongan</th>
                    <th>Jabatan</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($chunkPegawai as $p)
                    <tr>
                      <td>{{ data_get($p,'nama') }}</td>
                      <td>{{ data_get($p,'nip') ?: '-' }}</td>
                      <td>{{ data_get($p,'pangkat') ?: '-' }}</td>
                      <td>{{ data_get($p,'golongan') ?: '-' }}</td>
                      <td>{{ data_get($p,'jabatan') ?: '-' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif

            @if($chunkMahasiswa->count())
              <div class="mt-6"></div>
              <p><strong>2. Mahasiswa</strong></p>
              <table class="attachment-table">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Jurusan</th>
                    <th>Prodi</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($chunkMahasiswa as $m)
                    <tr>
                      <td>{{ data_get($m,'nama') }}</td>
                      <td>{{ data_get($m,'nim') ?: '-' }}</td>
                      <td>{{ data_get($m,'jurusan') ?: '-' }}</td>
                      <td>{{ data_get($m,'prodi') ?: '-' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif

            @if($isLast)
              <div class="mt-10"></div>
              <table class="footer-table">
                <tr>
                  <td style="width:50%"></td>
                  <td class="signature-cell">
                    <div class="signature-inner">
                      <p class="m-0 mb-1">
                        Bandung, {{ $fmt(data_get($surat,'tanggal_persetujuan_direktur') ?: data_get($surat,'tanggal_berangkat')) }}
                      </p>
                      <p class="m-0 mb-1">Direktur,</p>

                      <div class="signature-block mt-2">
                        @if(data_get($surat,'direktur_signature_data'))
                          <img src="{{ data_get($surat,'direktur_signature_data') }}" style="width:100px; height:60px;">
                        @endif
                      </div>

                      <p class="m-0" style="font-weight:bold; margin-top:8px;">
                        {{ data_get($surat,'template_nama_direktur') ?: '[Nama Direktur]' }}
                      </p>
                      <p class="m-0">
                        NIP {{ data_get($surat,'template_nip_direktur') ?: '[NIP Direktur]' }}
                      </p>
                    </div>
                  </td>
                </tr>
              </table>
            @endif
          </div>
        </div>
      @endforeach
    @endif
  @endif
</body>
</html>
