<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SuratTugasBulkSeeder extends Seeder
{
    public function run(): void
    {
        $now  = Carbon::now();
        $year = (int) $now->format('Y');

        // Try to pick real users by role; fallback to IDs if your seed data differs
        $pengusulId  = DB::table('users')->where('role', 'pengusul')->value('id') ?? 1;
        $wadirId     = DB::table('users')->where('role', 'wadir')->value('id') ?? 2;
        $sekdirId    = DB::table('users')->where('role', 'sekdir')->value('id') ?? 3;
        $direkturId  = DB::table('users')->where('role', 'direktur')->value('id') ?? 4;

        $statuses = [
            'draft',
            'submitted_wadir_review',
            'revision_requested',
            'rejected',
            'approved_wadir',
            'pending_sekdir_numbering',
            'sekdir_revision_requested',
            'pending_direktur_signature',
            'direktur_revision_requested',
            'published',
            'awaiting_proof_upload',
            'under_bku_review',
            'returned_for_correction',
            'completed',
        ];

        $rows = [];
        $seq  = 1;

        foreach ($statuses as $sIndex => $status) {
            for ($i = 1; $i <= 2; $i++) {

                $createdAt = $now->copy()->subDays(($sIndex * 2) + $i);
                $updatedAt = $createdAt->copy()->addHours(2);

                // Unique nomor surat usulan jurusan (UNI)
                $nomorUsulan = sprintf(
                    'USL-JUR/%s/%d/%04d-%s-%d',
                    'RT.01.00',
                    $year,
                    $seq,
                    strtoupper(Str::slug($status, '')),
                    $i
                );

                // Dates (must be NOT NULL in schema)
                $tglBerangkat = $createdAt->copy()->addDays(7)->toDateString();
                $tglKembali   = $createdAt->copy()->addDays(9)->toDateString();

                // JSON-ish longtext fields used by your VM logic
                $lokasiJson = json_encode([
                    ['tempat' => 'Polban - Gedung Direktorat', 'alamat' => "Jl. Gegerkalong Hilir\nBandung 40012"],
                    ['tempat' => 'Hotel Mitra', 'alamat' => "Jl. Contoh No. 10\nBandung"],
                ], JSON_UNESCAPED_UNICODE);

                $tembusanJson = json_encode(['Ketua Jurusan', 'Arsip'], JSON_UNESCAPED_UNICODE);

                // Base row: include EVERY column (except auto_increment PK)
                $row = [
                    // PK omitted: surat_tugas_id (auto_increment)

                    'nomor_urutan_surat'              => null,
                    'kode_unit_kerja'                 => 'JUR-RT',
                    'kode_perihal'                    => 'RT.01.00',
                    'tahun_nomor_surat'               => null,

                    'user_id'                         => $pengusulId,
                    'diusulkan_kepada'                => 'Direktur Polban',

                    'penyelenggara'                   => ($seq % 3 === 1) ? 'polban' : (($seq % 3 === 2) ? 'penyelenggara' : 'kedua'),
                    'nama_penyelenggara'              => 'Politeknik Negeri Bandung',

                    'lokasi_kegiatan'                 => $lokasiJson,

                    'nomor_surat_usulan_jurusan'      => $nomorUsulan,
                    'nomor_surat_tugas_resmi'         => null,

                    'perihal_tugas'                   => "Kegiatan Dinas Contoh ({$status}) #{$seq}",
                    'ditugaskan_sebagai'              => 'Peserta',
                    'kota_tujuan'                     => 'Bandung',

                    'tanggal_berangkat'               => $tglBerangkat,
                    'tanggal_kembali'                 => $tglKembali,

                    'status_surat'                    => $status,
                    'catatan_revisi'                  => null,

                    'path_file_surat_usulan'          => null,
                    'path_file_surat_tugas_final'     => null,

                    'sumber_dana'                     => 'DIPA / Operasional',
                    'pagu_desentralisasi'             => 0,
                    'nominal_dana'                    => 2500000.00,

                    'template_nama_kementerian'       => 'KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET DAN TEKNOLOGI',
                    'template_nama_direktur'          => 'Nama Direktur Contoh',
                    'template_nip_direktur'           => '198001012005011001',
                    'template_tembusan'               => $tembusanJson,

                    'rendered_html'                   => null,

                    'tanggal_paraf_wadir'             => null,
                    'tanggal_persetujuan_direktur'    => null,
                    'tanggal_penomoran_sekdir'        => null,

                    'wadir_signature_data'            => null,
                    'direktur_signature_data'         => null,

                    'wadir_signature_position'        => null,
                    'direktur_signature_position'     => null,

                    'is_surat_perintah_langsung'      => 0,

                    'created_at'                      => $createdAt,
                    'updated_at'                      => $updatedAt,

                    'wadir_approver_id'               => null,
                    'direktur_approver_id'            => null,
                    'sekdir_processor_id'             => null,

                    'barcode_payload'                 => null,
                    'barcode_path'                    => null,
                    'tanggal_tte_direktur'            => null,
                    'barcode_token'                   => null,
                ];

                // Status-based conditions (workflow realism)
                $afterSubmit = in_array($status, [
                    'submitted_wadir_review','revision_requested','rejected','approved_wadir',
                    'pending_sekdir_numbering','sekdir_revision_requested',
                    'pending_direktur_signature','direktur_revision_requested',
                    'published','awaiting_proof_upload','under_bku_review','returned_for_correction','completed'
                ], true);

                if ($afterSubmit) {
                    $row['path_file_surat_usulan'] = "uploads/usulan/{$nomorUsulan}.pdf";
                }

                if (in_array($status, ['revision_requested','sekdir_revision_requested','direktur_revision_requested','returned_for_correction'], true)) {
                    $row['catatan_revisi'] = "Catatan revisi untuk status {$status} (seed).";
                }

                if (in_array($status, ['rejected'], true)) {
                    $row['catatan_revisi'] = "Ditolak (seed) - alasan contoh.";
                }

                // Approved by Wadir => paraf + approver + optional signature meta
                if (in_array($status, ['approved_wadir','pending_sekdir_numbering','sekdir_revision_requested','pending_direktur_signature','direktur_revision_requested','published','awaiting_proof_upload','under_bku_review','returned_for_correction','completed'], true)) {
                    $row['wadir_approver_id']   = $wadirId;
                    $row['tanggal_paraf_wadir'] = $createdAt->copy()->addDays(1);

                    $row['wadir_signature_position'] = json_encode([
                        'page' => 1, 'x' => 420, 'y' => 690, 'w' => 120, 'h' => 60
                    ]);
                }

                // Sekdir numbering step
                if (in_array($status, ['pending_direktur_signature','direktur_revision_requested','published','awaiting_proof_upload','under_bku_review','returned_for_correction','completed'], true)) {
                    $row['sekdir_processor_id']    = $sekdirId;
                    $row['tanggal_penomoran_sekdir'] = $createdAt->copy()->addDays(2);

                    $row['tahun_nomor_surat']      = $year;
                    $row['nomor_urutan_surat']     = 1000 + $seq;

                    $row['nomor_surat_tugas_resmi'] = sprintf(
                        'B/%04d/%s/%d',
                        $row['nomor_urutan_surat'],
                        $row['kode_unit_kerja'],
                        $year
                    );
                }

                // Published and beyond => director approval + barcode + final file
                if (in_array($status, ['published','awaiting_proof_upload','under_bku_review','returned_for_correction','completed'], true)) {
                    $row['direktur_approver_id']      = $direkturId;
                    $row['tanggal_persetujuan_direktur'] = $createdAt->copy()->addDays(3);
                    $row['tanggal_tte_direktur']      = $createdAt->copy()->addDays(3)->addHours(2);

                    $token = Str::uuid()->toString();
                    $row['barcode_token']   = $token;
                    $row['barcode_payload'] = json_encode([
                        'token' => $token,
                        'surat_tugas_id' => null, // will exist after insert; keep as placeholder
                        'nomor' => $row['nomor_surat_tugas_resmi'],
                    ], JSON_UNESCAPED_UNICODE);

                    $row['barcode_path'] = "barcodes/{$token}.png";

                    if (!empty($row['nomor_surat_tugas_resmi'])) {
                        $safe = preg_replace('/[^A-Za-z0-9._-]+/', '-', $row['nomor_surat_tugas_resmi']);
                        $row['path_file_surat_tugas_final'] = "uploads/surat-final/Surat-Tugas-{$safe}.pdf";
                    }

                    // Optional rendered snapshot
                    $row['rendered_html'] = "<!-- rendered html seed for {$row['nomor_surat_tugas_resmi']} -->";
                }

                $rows[] = $row;
                $seq++;
            }
        }

        DB::table('surat_tugas')->insert($rows);
    }
}
