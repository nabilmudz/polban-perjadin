<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuratTugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'draft',
            'submitted_wadir_review',
            'revision_requested',
            'rejected',
            'approved_wadir',
            'pending_sekdir_numbering',
            'pending_direktur_signature',
            'published',
            'awaiting_proof_upload',
            'under_bku_review',
            'returned_for_correction',
            'completed'
        ];

        $userId = 1; // ganti sesuai user_id yang valid di table users kamu
        $now = now();

        foreach ($statuses as $i => $status) {
            DB::table('surat_tugas')->insert([
                'nomor_urutan_surat' => $i + 1,
                'kode_unit_kerja' => 'UK-' . strtoupper(Str::random(3)),
                'kode_perihal' => 'RT.01.00',
                'tahun_nomor_surat' => 2025,
                'user_id' => $userId,
                'diusulkan_kepada' => 'Wadir I',
                'nama_penyelenggara' => 'Politeknik Negeri Bandung',
                'lokasi_kegiatan' => json_encode(['Bandung', 'Jakarta']),
                'nomor_surat_usulan_jurusan' => 'ST-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT) . '/2025',
                'nomor_surat_tugas_resmi' => $status === 'published' || $status === 'completed' ? 'R-' . rand(100, 999) . '/POLBAN/2025' : null,
                'perihal_tugas' => 'Kegiatan ' . ucfirst(str_replace('_', ' ', $status)),
                'ditugaskan_sebagai' => 'Dosen Pembimbing',
                'kota_tujuan' => 'Jakarta',
                'tanggal_berangkat' => '2025-11-10',
                'tanggal_kembali' => '2025-11-13',
                'status_surat' => $status,
                'catatan_revisi' => $status === 'revision_requested' ? 'Mohon revisi bagian perihal dan tanggal.' : null,
                'path_file_surat_usulan' => '/storage/surat/usulan_' . $i . '.pdf',
                'path_file_surat_tugas_final' => $status === 'published' || $status === 'completed' ? '/storage/surat/final_' . $i . '.pdf' : null,
                'sumber_dana' => 'DIPA 2025',
                'pagu_desentralisasi' => $i % 2 === 0,
                'template_nama_kementerian' => 'Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi',
                'template_nama_direktur' => 'Dr. Ir. John Doe, M.T.',
                'template_nip_direktur' => '19651110 199001 1 001',
                'template_tembusan' => json_encode(['Bagian Keuangan', 'Bagian Umum']),
                'rendered_html' => '<p>Surat tugas untuk kegiatan ' . $status . '</p>',
                'tanggal_paraf_wadir' => in_array($status, ['approved_wadir', 'pending_sekdir_numbering', 'pending_direktur_signature', 'published', 'completed']) ? $now : null,
                'tanggal_penomoran_sekdir' => in_array($status, ['pending_direktur_signature', 'published', 'completed']) ? $now : null,
                'tanggal_persetujuan_direktur' => in_array($status, ['published', 'completed']) ? $now : null,
                'wadir_signature_data' => in_array($status, ['approved_wadir', 'pending_sekdir_numbering', 'pending_direktur_signature', 'published', 'completed']) ? 'data:image/png;base64,...' : null,
                'direktur_signature_data' => in_array($status, ['published', 'completed']) ? 'data:image/png;base64,...' : null,
                'wadir_signature_position' => json_encode(['x' => 100, 'y' => 200]),
                'direktur_signature_position' => json_encode(['x' => 120, 'y' => 220]),
                'is_surat_perintah_langsung' => false,
                'wadir_approver_id' => $userId,
                'direktur_approver_id' => $userId,
                'sekdir_processor_id' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
