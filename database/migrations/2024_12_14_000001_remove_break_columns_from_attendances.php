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
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn([
                'mulai_istirahat',
                'selesai_istirahat',
                'total_menit_istirahat'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->time('mulai_istirahat')->nullable()->after('jam_keluar');
            $table->time('selesai_istirahat')->nullable()->after('mulai_istirahat');
            $table->integer('total_menit_istirahat')->default(0)->after('total_menit_kerja');
        });
    }
};
