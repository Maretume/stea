<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('riwayat_lokasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->decimal('lintang', 10, 8);
            $table->decimal('bujur', 11, 8);
            $table->decimal('akurasi', 8, 2)->nullable(); // GPS accuracy in meters
            $table->string('aksi', 50); // check, clock_in, clock_out, etc
            $table->timestamp('cap_waktu');
            $table->string('alamat_ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('data_tambahan')->nullable(); // For storing extra location metadata
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->index(['id_pengguna', 'cap_waktu']);
            $table->index(['lintang', 'bujur']);
            $table->index('aksi');
        });

        Schema::create('pagar_geo', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('tipe', 50); // office, restricted, custom
            $table->decimal('lintang', 10, 8);
            $table->decimal('bujur', 11, 8);
            $table->integer('radius'); // in meters
            $table->json('koordinat_poligon')->nullable(); // For complex shapes
            $table->boolean('aktif')->default(true);
            $table->text('deskripsi')->nullable();
            $table->json('pengaturan')->nullable(); // Additional geofence settings
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->index(['lintang', 'bujur']);
            $table->index(['tipe', 'aktif']);
        });

        Schema::create('pelanggaran_pagar_geo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('id_pagar_geo')->nullable()->constrained('pagar_geo')->onDelete('set null');
            $table->string('tipe_pelanggaran', 50); // outside_radius, suspicious_movement, etc
            $table->decimal('lintang', 10, 8);
            $table->decimal('bujur', 11, 8);
            $table->decimal('jarak_dari_pusat', 10, 2)->nullable();
            $table->text('deskripsi');
            $table->enum('tingkat_keparahan', ['rendah', 'sedang', 'tinggi'])->default('sedang');
            $table->boolean('sudah_diselesaikan')->default(false);
            $table->text('catatan_penyelesaian')->nullable();
            $table->timestamp('diselesaikan_pada')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->index(['id_pengguna', 'tipe_pelanggaran']);
            $table->index(['tingkat_keparahan', 'sudah_diselesaikan']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pelanggaran_pagar_geo');
        Schema::dropIfExists('pagar_geo');
        Schema::dropIfExists('riwayat_lokasi');
    }
};
