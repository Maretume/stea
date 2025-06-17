<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create offices table
        Schema::create('kantor', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->decimal('lintang', 10, 8);
            $table->decimal('bujur', 11, 8);
            $table->integer('radius')->default(100); // radius in meters
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            
            $table->index(['aktif']);
        });

        // Create shifts table
        Schema::create('shift', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            
            $table->index(['aktif']);
        });

        // Create schedules table
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('id_shift')->constrained('shift')->onDelete('cascade');
            $table->foreignId('id_kantor')->nullable()->constrained('kantor')->onDelete('set null');
            $table->date('tanggal_jadwal');
            $table->enum('tipe_kerja', ['WFO', 'WFA'])->default('WFO'); // Work From Office / Work From Anywhere
            $table->enum('status', ['terjadwal', 'disetujui', 'dibatalkan'])->default('terjadwal');
            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->timestamp('disetujui_pada')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->unique(['id_pengguna', 'tanggal_jadwal']);
            $table->index(['tanggal_jadwal', 'tipe_kerja']);
            $table->index(['id_shift', 'id_kantor']);
            $table->index(['status']);
        });

        // Add office_id to attendances table for tracking where attendance was recorded
        Schema::table('absensi', function (Blueprint $table) { // Changed from attendances to absensi
            $table->foreignId('id_kantor')->nullable()->constrained('kantor')->onDelete('set null');
            $table->index(['id_kantor']);
        });
    }

    public function down()
    {
        Schema::table('absensi', function (Blueprint $table) { // Changed from attendances to absensi
            $table->dropForeign(['id_kantor']);
            $table->dropColumn('id_kantor');
        });
        
        Schema::dropIfExists('jadwal');
        Schema::dropIfExists('shift');
        Schema::dropIfExists('kantor');
    }
};
