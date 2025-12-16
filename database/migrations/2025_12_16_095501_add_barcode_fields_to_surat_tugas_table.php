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
        Schema::table('surat_tugas', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_tugas', 'barcode_payload')) {
                $table->text('barcode_payload')->nullable();
            }
            if (!Schema::hasColumn('surat_tugas', 'barcode_path')) {
                $table->string('barcode_path')->nullable();
            }
            if (!Schema::hasColumn('surat_tugas', 'tanggal_tte_direktur')) {
                $table->dateTime('tanggal_tte_direktur')->nullable();
            }
            if (!Schema::hasColumn('surat_tugas', 'tanggal_persetujuan_direktur')) {
                $table->dateTime('tanggal_persetujuan_direktur')->nullable();
            }
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            if (Schema::hasColumn('surat_tugas', 'barcode_payload')) {
                $table->dropColumn('barcode_payload');
            }
            if (Schema::hasColumn('surat_tugas', 'barcode_path')) {
                $table->dropColumn('barcode_path');
            }
            if (Schema::hasColumn('surat_tugas', 'tanggal_tte_direktur')) {
                $table->dropColumn('tanggal_tte_direktur');
            }
            if (Schema::hasColumn('surat_tugas', 'tanggal_persetujuan_direktur')) {
                $table->dropColumn('tanggal_persetujuan_direktur');
            }
        });
    }

};
