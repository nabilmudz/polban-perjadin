<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Detail Pelaksana Tugas
        Schema::create('detail_pelaksana_tugas', function (Blueprint $table) {
            $table->id('detail_pelaksana_id');
            $table->unsignedBigInteger('surat_tugas_id');
            $table->string('personable_type');
            $table->unsignedBigInteger('personable_id');
            $table->string('status_sebagai');
            $table->timestamps();

            $table->foreign('surat_tugas_id')
                  ->references('surat_tugas_id')
                  ->on('surat_tugas')
                  ->onDelete('cascade');

            $table->index(['personable_type', 'personable_id']);
        });

        // Digital Paraf
        Schema::create('digital_parafs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('original_name');
            $table->string('file_mime_type');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });

        // Laporan Perjalanan Dinas
        Schema::create('laporan_perjalanan_dinas', function (Blueprint $table) {
            $table->id('laporan_id');
            $table->unsignedBigInteger('surat_tugas_id');
            $table->unsignedBigInteger('user_id');
            $table->date('tanggal_pengumpulan_laporan');
            $table->enum('status_laporan', ['draft', 'submitted', 'approved'])->default('draft');
            $table->text('catatan_verifikasi_bku')->nullable();
            $table->date('tanggal_verifikasi_bku')->nullable();
            $table->unsignedBigInteger('verifikator_bku_user_id')->nullable();
            $table->timestamps();

            $table->foreign('surat_tugas_id')
                  ->references('surat_tugas_id')
                  ->on('surat_tugas')
                  ->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('verifikator_bku_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });

        // Dokumen Lampiran Laporan
        Schema::create('dokumen_lampiran_laporan', function (Blueprint $table) {
            $table->id('dokumen_lampiran_id');
            $table->unsignedBigInteger('laporan_id');
            $table->string('jenis_dokumen');
            $table->string('nama_file');
            $table->string('path_file');
            $table->timestamp('tanggal_unggah')->useCurrent();
            $table->timestamps();

            $table->foreign('laporan_id')
                  ->references('laporan_id')
                  ->on('laporan_perjalanan_dinas')
                  ->onDelete('cascade');
        });

        // Template Surat
        Schema::create('template_surat', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kementerian')->nullable();
            $table->string('nama_direktur')->nullable();
            $table->string('nip_direktur')->nullable();
            $table->json('tembusan_default')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pelaksana_tugas');
        Schema::dropIfExists('digital_parafs');
        Schema::dropIfExists('laporan_perjalanan_dinas');
        Schema::dropIfExists('dokumen_lampiran_laporan');
        Schema::dropIfExists('template_surat');
    }
};
