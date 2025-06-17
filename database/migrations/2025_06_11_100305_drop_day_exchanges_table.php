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
        Schema::dropIfExists('day_exchanges'); // Assuming original table name was not translated
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the table as 'pertukaran_hari' with translated columns
        Schema::create('pertukaran_hari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->date('tanggal_kerja_asli');
            $table->date('tanggal_pengganti');
            $table->text('alasan');
            $table->enum('status', ['menunggu', 'disetujui', 'ditoLak', 'selesai', 'dibatalkan'])->default('menunggu');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->timestamp('disetujui_pada')->nullable();
            $table->text('catatan_persetujuan')->nullable();
            $table->boolean('apakah_selesai')->default(false);
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->index(['id_pengguna', 'status']);
            $table->index(['tanggal_kerja_asli', 'tanggal_pengganti'], 'idx_pertukaran_hari_tanggal');
        });
    }
};
