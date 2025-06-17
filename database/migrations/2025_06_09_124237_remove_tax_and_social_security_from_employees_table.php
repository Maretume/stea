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
        Schema::table('karyawan', function (Blueprint $table) {
            // These columns were translated to 'npwp' and 'bpjs' in 2024_01_01_000002_create_departments_and_positions_tables.php
            $table->dropColumn(['npwp', 'bpjs']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->string('npwp', 30)->nullable()->comment('NPWP');
            $table->string('bpjs', 30)->nullable()->comment('BPJS');
        });
    }
};
