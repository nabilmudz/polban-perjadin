<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->decimal('nominal_dana', 15, 2)
                ->nullable()
                ->after('pagu_desentralisasi');
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->dropColumn('nominal_dana');
        });
    }
};
