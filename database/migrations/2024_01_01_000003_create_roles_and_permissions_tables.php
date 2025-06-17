<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('peran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kunci', 50)->unique();
            $table->string('nama_tampilan', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });

        Schema::create('izin', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kunci', 100)->unique();
            $table->string('nama_tampilan', 150);
            $table->string('modul', 50);
            $table->text('deskripsi')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });

        Schema::create('peran_izin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_peran')->constrained('peran')->onDelete('cascade');
            $table->foreignId('id_izin')->constrained('izin')->onDelete('cascade');
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            
            $table->unique(['id_peran', 'id_izin']);
        });

        Schema::create('pengguna_peran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('id_peran')->constrained('peran')->onDelete('cascade');
            $table->timestamp('ditetapkan_pada')->useCurrent();
            $table->timestamp('kadaluarsa_pada')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            
            $table->unique(['id_pengguna', 'id_peran']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengguna_peran');
        Schema::dropIfExists('peran_izin');
        Schema::dropIfExists('izin');
        Schema::dropIfExists('peran');
    }
};
