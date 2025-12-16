<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('template_surat', 'status')) {
            Schema::table('template_surat', function (Blueprint $table) {
                $table->boolean('status')->default(true)->after('nip_direktur');
            });
        }

        if (!Schema::hasColumn('template_surat', 'created_at') && !Schema::hasColumn('template_surat', 'updated_at')) {
            Schema::table('template_surat', function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('template_surat', 'status')) {
            Schema::table('template_surat', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('template_surat', 'created_at') && Schema::hasColumn('template_surat', 'updated_at')) {
            Schema::table('template_surat', function (Blueprint $table) {
                $table->dropTimestamps();
            });
        }
    }
};
