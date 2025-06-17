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
        Schema::table('aturan_absensi', function (Blueprint $table) {
            $table->dropColumn([
                'jam_mulai_istirahat',
                'jam_selesai_istirahat'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aturan_absensi', function (Blueprint $table) {
            $table->time('jam_mulai_istirahat')->nullable()->after('jam_selesai_kerja');
            $table->time('jam_selesai_istirahat')->nullable()->after('jam_mulai_istirahat');
        });
    }
};
