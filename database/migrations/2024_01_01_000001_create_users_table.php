<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id();
            $table->string('id_karyawan', 20)->unique();
            $table->string('nama_pengguna', 50)->unique();
            $table->string('surel', 100)->unique();
            $table->timestamp('surel_diverifikasi_pada')->nullable();
            $table->string('kata_sandi');
            $table->string('nama_depan', 50);
            $table->string('nama_belakang', 50);
            $table->string('telepon', 20)->nullable();
            $table->enum('jenis_kelamin', ['pria', 'wanita']);
            $table->date('tanggal_lahir');
            $table->text('alamat')->nullable();
            $table->string('foto_profil', 255)->nullable();
            $table->enum('status', ['aktif', 'tidak_aktif', 'ditangguhkan'])->default('aktif');
            $table->timestamp('login_terakhir_pada')->nullable();
            $table->string('ip_login_terakhir', 45)->nullable();
            $table->boolean('paksa_ganti_kata_sandi')->default(false);
            $table->rememberToken('token_ingat_saya');
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->index(['status', 'id_karyawan']);
            $table->index('surel');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengguna');
    }
};
