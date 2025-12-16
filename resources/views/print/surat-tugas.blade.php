<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Surat Tugas</title>
  <style>
    body { font-family: "Times New Roman", Times, serif; font-size: 12pt; }
  </style>
</head>
<body>
  <h2 style="text-align:center; margin:0;">SURAT TUGAS</h2>
  <p style="text-align:center; margin-top:6px;">
    Nomor: {{ $surat->nomor_surat_tugas_resmi ?? '-' }}
  </p>

  <hr>

  <p><b>Kegiatan:</b> {{ $surat->perihal_tugas ?? '-' }}</p>
  <p><b>Penyelenggara:</b> {{ $surat->nama_penyelenggara ?? '-' }}</p>
</body>
</html>
