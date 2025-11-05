<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('surat_tugas', function (Blueprint $table) {
            $table->id('surat_tugas_id');
            $table->integer('nomor_urutan_surat')->nullable();
            $table->string('kode_unit_kerja')->nullable();
            $table->string('kode_perihal')->default('RT.01.00');
            $table->integer('tahun_nomor_surat')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('diusulkan_kepada');
            $table->string('nama_penyelenggara');
            $table->json('lokasi_kegiatan')->nullable();
            $table->string('nomor_surat_usulan_jurusan')->unique();
            $table->string('nomor_surat_tugas_resmi')->nullable();
            $table->text('perihal_tugas');
            $table->string('ditugaskan_sebagai');
            $table->string('kota_tujuan')->nullable();
            $table->date('tanggal_berangkat');
            $table->date('tanggal_kembali');
            $table->enum('status_surat', [
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
            ])->default('draft');
            $table->text('catatan_revisi')->nullable();
            $table->string('path_file_surat_usulan')->nullable();
            $table->string('path_file_surat_tugas_final')->nullable();
            $table->string('sumber_dana');
            $table->boolean('pagu_desentralisasi')->default(false);
            $table->string('template_nama_kementerian')->nullable();
            $table->string('template_nama_direktur')->nullable();
            $table->string('template_nip_direktur')->nullable();
            $table->json('template_tembusan')->nullable();
            $table->longText('rendered_html')->nullable();
            $table->timestamp('tanggal_paraf_wadir')->nullable();
            $table->timestamp('tanggal_persetujuan_direktur')->nullable();
            $table->timestamp('tanggal_penomoran_sekdir')->nullable();
            $table->text('wadir_signature_data')->nullable();
            $table->text('direktur_signature_data')->nullable();
            $table->json('wadir_signature_position')->nullable();
            $table->json('direktur_signature_position')->nullable();
            $table->boolean('is_surat_perintah_langsung')->default(false);
            $table->timestamps();
            $table->foreignId('wadir_approver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('direktur_approver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('sekdir_processor_id')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_tugas');
    }
};
