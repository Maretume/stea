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
        // Update existing 'scheduled' records to 'approved' and set approval fields
        DB::table('jadwal') // schedules -> jadwal
            ->where('status', 'terjadwal') // scheduled -> terjadwal
            ->update([
                'status' => 'disetujui', // approved -> disetujui
                'disetujui_oleh' => DB::raw('dibuat_oleh'), // approved_by -> disetujui_oleh, created_by -> dibuat_oleh
                'disetujui_pada' => DB::raw('dibuat_pada'), // approved_at -> disetujui_pada, created_at -> dibuat_pada
                'diperbarui_pada' => now() // updated_at -> diperbarui_pada
            ]);

        // Update the enum to remove 'scheduled' status and change default to 'approved'
        Schema::table('jadwal', function (Blueprint $table) { // schedules -> jadwal
            $table->enum('status', ['disetujui', 'dibatalkan'])->default('disetujui')->change(); // approved -> disetujui, cancelled -> dibatalkan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the original enum with 'scheduled' status and default
        Schema::table('jadwal', function (Blueprint $table) { // schedules -> jadwal
            $table->enum('status', ['terjadwal', 'disetujui', 'dibatalkan'])->default('terjadwal')->change(); // scheduled -> terjadwal, approved -> disetujui, cancelled -> dibatalkan
        });

        // Optionally, you could revert approved records back to scheduled
        // but this might not be desired in most cases
    }
};
