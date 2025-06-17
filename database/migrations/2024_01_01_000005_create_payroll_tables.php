<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('komponen_gaji', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('kode', 20)->unique();
            $table->enum('tipe', ['tunjangan', 'potongan', 'manfaat']);
            $table->enum('tipe_perhitungan', ['tetap', 'persentase', 'rumus']);
            $table->decimal('jumlah_standar', 15, 2)->default(0);
            $table->decimal('persentase', 5, 2)->nullable();
            $table->text('rumus')->nullable();
            $table->boolean('kena_pajak')->default(true);
            $table->boolean('aktif')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });

        Schema::create('komponen_gaji_karyawan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('id_komponen_gaji')->constrained('komponen_gaji')->onDelete('cascade');
            $table->decimal('jumlah', 15, 2);
            $table->date('tanggal_efektif');
            $table->date('tanggal_berakhir')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            
            $table->index(['id_pengguna', 'tanggal_efektif']);
        });

        Schema::create('periode_penggajian', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->date('tanggal_bayar');
            $table->enum('status', ['konsep', 'terhitung', 'disetujui', 'dibayar'])->default('konsep');
            $table->foreignId('dibuat_oleh')->constrained('pengguna');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->timestamp('disetujui_pada')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });

        Schema::create('penggajian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_periode_penggajian')->constrained('periode_penggajian')->onDelete('cascade');
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->decimal('gaji_pokok', 15, 2);
            $table->decimal('total_tunjangan', 15, 2)->default(0);
            $table->decimal('total_potongan', 15, 2)->default(0);
            $table->decimal('jumlah_lembur', 15, 2)->default(0);
            $table->decimal('gaji_kotor', 15, 2);
            $table->decimal('jumlah_pajak', 15, 2)->default(0);
            $table->decimal('gaji_bersih', 15, 2);
            $table->integer('total_hari_kerja');
            $table->integer('total_hari_hadir');
            $table->integer('total_hari_absen');
            $table->integer('total_hari_terlambat');
            $table->integer('total_jam_lembur');
            $table->text('catatan')->nullable();
            $table->enum('status', ['konsep', 'disetujui', 'dibayar'])->default('konsep');
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            
            $table->unique(['id_periode_penggajian', 'id_pengguna']);
            $table->index(['id_pengguna', 'status']);
        });

        Schema::create('detail_penggajian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_penggajian')->constrained('penggajian')->onDelete('cascade');
            $table->foreignId('id_komponen_gaji')->constrained('komponen_gaji');
            $table->decimal('jumlah', 15, 2);
            $table->text('catatan_perhitungan')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('detail_penggajian');
        Schema::dropIfExists('penggajian');
        Schema::dropIfExists('periode_penggajian');
        Schema::dropIfExists('komponen_gaji_karyawan');
        Schema::dropIfExists('komponen_gaji');
    }
};
