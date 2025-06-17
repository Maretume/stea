<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->foreignId('id_shift_standar')->nullable()->constrained('shift')->onDelete('set null');
            $table->foreignId('id_kantor_standar')->nullable()->constrained('kantor')->onDelete('set null');
            $table->enum('tipe_kerja_standar', ['WFO', 'WFA'])->default('WFO');
        });
    }

    public function down()
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropForeign(['id_shift_standar']);
            $table->dropForeign(['id_kantor_standar']);
            $table->dropColumn(['id_shift_standar', 'id_kantor_standar', 'tipe_kerja_standar']);
        });
    }
};
