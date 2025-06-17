<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pemberitahuan', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Standard for Laravel notifications
            $table->string('tipe');
            $table->morphs('notifiable', 'idx_pemberitahuan_notifiable'); // Renamed morphs index
            $table->text('data');
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });

        Schema::create('pengaturan_pemberitahuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->string('tipe'); // email, push, sms
            $table->string('acara'); // schedule_reminder, schedule_approved, etc
            $table->boolean('aktif')->default(true);
            $table->json('pengaturan_tambahan')->nullable(); // additional settings like time, frequency
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->unique(['id_pengguna', 'tipe', 'acara'], 'idx_unik_pengaturan_pemberitahuan');
        });

        Schema::create('langganan_push', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->string('endpoint');
            $table->string('kunci_publik')->nullable();
            $table->string('token_autentikasi')->nullable();
            $table->string('enkoding_konten')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->unique(['id_pengguna', 'endpoint'], 'idx_unik_langganan_push');
        });
    }

    public function down()
    {
        Schema::dropIfExists('langganan_push');
        Schema::dropIfExists('pengaturan_pemberitahuan');
        Schema::dropIfExists('pemberitahuan');
    }
};
