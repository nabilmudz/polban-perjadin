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
            if (!Schema::hasColumn('surat_tugas', 'barcode_token')) {
                $table->string('barcode_token')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            if (Schema::hasColumn('surat_tugas', 'barcode_token')) {
                $table->dropColumn('barcode_token');
            }
        });
    }
};
