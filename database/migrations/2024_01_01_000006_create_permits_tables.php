<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Permit Types Table
        Schema::create('jenis_izin_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
            $table->string('kode', 20)->unique();
            $table->text('deskripsi')->nullable();
            $table->boolean('perlu_persetujuan')->default(true);
            $table->boolean('pengaruhi_absensi')->default(false);
            $table->boolean('aktif')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });

        // Permits Table
        Schema::create('izin_kerja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('id_jenis_izin_kerja')->constrained('jenis_izin_kerja')->onDelete('cascade');
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->decimal('durasi_jam', 5, 2)->nullable();
            $table->text('alasan');
            $table->text('catatan')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditoLak', 'dibatalkan'])->default('menunggu');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->timestamp('disetujui_pada')->nullable();
            $table->text('catatan_persetujuan')->nullable();
            $table->json('lampiran')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->index(['id_pengguna', 'status']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });

        // Overtime Requests Table
        Schema::create('pengajuan_lembur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->date('tanggal_lembur');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->decimal('jam_direncanakan', 5, 2);
            $table->decimal('jam_aktual', 5, 2)->nullable();
            $table->text('deskripsi_pekerjaan');
            $table->text('alasan');
            $table->enum('status', ['menunggu', 'disetujui', 'ditoLak', 'selesai'])->default('menunggu');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->timestamp('disetujui_pada')->nullable();
            $table->text('catatan_persetujuan')->nullable();
            $table->boolean('apakah_selesai')->default(false);
            $table->timestamp('selesai_pada')->nullable();
            $table->decimal('tarif_lembur', 8, 2)->nullable();
            $table->decimal('jumlah_lembur', 12, 2)->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->index(['id_pengguna', 'status']);
            $table->index(['tanggal_lembur']);
        });

        // Leave Requests Table (Enhanced)
        Schema::create('pengajuan_cuti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('id_jenis_cuti')->constrained('jenis_cuti')->onDelete('cascade');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('total_hari');
            $table->text('alasan');
            $table->text('catatan')->nullable();
            $table->string('kontak_darurat')->nullable();
            $table->string('telepon_darurat')->nullable();
            $table->text('serah_terima_pekerjaan')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditoLak', 'dibatalkan'])->default('menunggu');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->timestamp('disetujui_pada')->nullable();
            $table->text('catatan_persetujuan')->nullable();
            $table->json('lampiran')->nullable();
            $table->boolean('setengah_hari')->default(false);
            $table->enum('tipe_setengah_hari', ['pagi', 'siang'])->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->index(['id_pengguna', 'status']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
            $table->index(['id_jenis_cuti']);
        });

        // Permit Approvals Table (Multi-level approval)
        Schema::create('persetujuan_izin_kerja', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable', 'idx_persetujuan_izin_kerja_approvable');
            $table->foreignId('id_penyetuju')->constrained('pengguna')->onDelete('cascade');
            $table->integer('tingkat_persetujuan')->default(1);
            $table->enum('status', ['menunggu', 'disetujui', 'ditoLak'])->default('menunggu');
            $table->timestamp('disetujui_pada')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->index(['id_penyetuju', 'status'], 'idx_persetujuan_izin_kerja_penyetuju_status');
        });

        // Permit Settings Table
        Schema::create('pengaturan_izin_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('kunci')->unique();
            $table->text('nilai');
            $table->string('tipe')->default('string');
            $table->text('deskripsi')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengaturan_izin_kerja');
        Schema::dropIfExists('persetujuan_izin_kerja');
        Schema::dropIfExists('pengajuan_cuti');
        Schema::dropIfExists('pengajuan_lembur');
        Schema::dropIfExists('izin_kerja');
        Schema::dropIfExists('jenis_izin_kerja');
    }
};
