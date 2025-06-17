<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('aturan_absensi', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->time('jam_mulai_kerja');
            $table->time('jam_selesai_kerja');
            $table->time('jam_mulai_istirahat')->nullable();
            $table->time('jam_selesai_istirahat')->nullable();
            $table->integer('toleransi_keterlambatan_menit')->default(15);
            $table->integer('toleransi_pulang_awal_menit')->default(15);
            $table->decimal('pengali_lembur', 3, 2)->default(1.5);
            $table->boolean('standar')->default(false);
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });

        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->time('mulai_istirahat')->nullable();
            $table->time('selesai_istirahat')->nullable();
            $table->integer('total_menit_kerja')->default(0);
            $table->integer('total_menit_istirahat')->default(0);
            $table->integer('menit_terlambat')->default(0);
            $table->integer('menit_pulang_awal')->default(0);
            $table->integer('menit_lembur')->default(0);
            $table->enum('status', ['hadir', 'absen', 'terlambat', 'pulang_awal', 'setengah_hari', 'sakit', 'cuti', 'libur'])->default('absen');
            $table->string('ip_jam_masuk', 45)->nullable();
            $table->string('ip_jam_keluar', 45)->nullable();
            $table->decimal('lat_jam_masuk', 10, 8)->nullable();
            $table->decimal('lng_jam_masuk', 11, 8)->nullable();
            $table->decimal('lat_jam_keluar', 10, 8)->nullable();
            $table->decimal('lng_jam_keluar', 11, 8)->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            
            $table->unique(['id_pengguna', 'tanggal']);
            $table->index(['tanggal', 'status']);
        });

        Schema::create('jenis_cuti', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
            $table->string('kode', 20)->unique();
            $table->integer('maks_hari_per_tahun')->default(12);
            $table->boolean('dibayar')->default(true);
            $table->boolean('perlu_persetujuan')->default(true);
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });

        Schema::create('cuti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('id_jenis_cuti')->constrained('jenis_cuti');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('total_hari');
            $table->text('alasan');
            $table->enum('status', ['menunggu', 'disetujui', 'ditoLak'])->default('menunggu');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->timestamp('disetujui_pada')->nullable();
            $table->text('catatan_persetujuan')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            
            $table->index(['id_pengguna', 'status']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cuti');
        Schema::dropIfExists('jenis_cuti');
        Schema::dropIfExists('absensi');
        Schema::dropIfExists('aturan_absensi');
    }
};
