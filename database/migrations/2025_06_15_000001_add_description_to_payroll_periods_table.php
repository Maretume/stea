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
        Schema::table('periode_penggajian', function (Blueprint $table) {
            $table->text('deskripsi')->nullable()->after('tanggal_bayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periode_penggajian', function (Blueprint $table) {
            $table->dropColumn('deskripsi');
        });
    }
};
