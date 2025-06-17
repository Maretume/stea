<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove overtime type settings from permit_settings table
        DB::table('pengaturan_izin_kerja') // permit_settings -> pengaturan_izin_kerja
            ->whereIn('kunci', ['overtime_rate_weekday', 'overtime_rate_weekend']) // key -> kunci, but values are English
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the overtime type settings if needed for rollback
        DB::table('pengaturan_izin_kerja')->insert([ // permit_settings -> pengaturan_izin_kerja
            [
                'kunci' => 'overtime_rate_weekday', // key -> kunci
                'nilai' => '1.5', // value -> nilai
                'tipe' => 'float', // type -> tipe
                'deskripsi' => 'Pengali tarif lembur untuk hari kerja', // description translated
                'dibuat_pada' => now(), // created_at -> dibuat_pada
                'diperbarui_pada' => now(), // updated_at -> diperbarui_pada
            ],
            [
                'kunci' => 'overtime_rate_weekend',
                'nilai' => '2.0',
                'tipe' => 'float',
                'deskripsi' => 'Pengali tarif lembur untuk akhir pekan',
                'dibuat_pada' => now(),
                'diperbarui_pada' => now(),
            ],
        ]);
    }
};
