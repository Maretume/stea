<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add 'early_leave' to the status enum
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('hadir', 'absen', 'terlambat', 'pulang_awal', 'setengah_hari', 'sakit', 'cuti', 'libur') DEFAULT 'absen'");
    }

    public function down()
    {
        // Remove 'early_leave' from the status enum
        DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('hadir', 'absen', 'terlambat', 'setengah_hari', 'sakit', 'cuti', 'libur') DEFAULT 'absen'");
    }
};
