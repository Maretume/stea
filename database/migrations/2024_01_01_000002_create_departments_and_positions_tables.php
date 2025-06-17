<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('departemen', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });

        Schema::create('jabatan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->foreignId('id_departemen')->constrained('departemen')->onDelete('cascade');
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->integer('tingkat')->default(1);
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });

        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('id_departemen')->constrained('departemen');
            $table->foreignId('id_jabatan')->constrained('jabatan');
            $table->foreignId('id_atasan')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->date('tanggal_rekrut');
            $table->date('mulai_kontrak')->nullable();
            $table->date('akhir_kontrak')->nullable();
            $table->enum('jenis_kepegawaian', ['tetap', 'kontrak', 'magang', 'paruh_waktu']);
            $table->enum('status_kepegawaian', ['aktif', 'mengundurkan_diri', 'diberhentikan', 'pensiun']);
            $table->decimal('gaji_pokok', 15, 2);
            $table->string('nama_bank', 50)->nullable();
            $table->string('rekening_bank', 30)->nullable();
            $table->string('nama_rekening_bank', 100)->nullable();
            $table->string('npwp', 30)->nullable(); // NPWP
            $table->string('bpjs', 30)->nullable(); // BPJS
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            
            $table->unique('id_pengguna');
            $table->index(['id_departemen', 'status_kepegawaian']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('karyawan');
        Schema::dropIfExists('jabatan');
        Schema::dropIfExists('departemen');
    }
};
