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
        Schema::table('penggajian', function (Blueprint $table) {
            $table->unsignedBigInteger('disetujui_oleh_payroll')->nullable()->after('status');
            $table->timestamp('disetujui_pada_payroll')->nullable()->after('disetujui_oleh_payroll');
            
            $table->foreign('disetujui_oleh_payroll')->references('id')->on('pengguna')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajian', function (Blueprint $table) {
            $table->dropForeign(['disetujui_oleh_payroll']);
            $table->dropColumn(['disetujui_oleh_payroll', 'disetujui_pada_payroll']);
        });
    }
};
