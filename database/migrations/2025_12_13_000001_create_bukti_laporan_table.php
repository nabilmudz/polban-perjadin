<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bukti_laporan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_tugas_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('kategori', 50);
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->text('keterangan')->nullable();
            $table->decimal('nominal', 15, 2)->nullable();
            $table->timestamps();
            $table->foreign('surat_tugas_id')
                ->references('surat_tugas_id')
                ->on('surat_tugas')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukti_laporan');
    }
};

/* 
lieur error wae pas mau bikin migration, cek ini aja
CREATE TABLE bukti_laporan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    surat_tugas_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    kategori VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NULL,
    keterangan TEXT NULL,
    nominal DECIMAL(15,2) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
*/

