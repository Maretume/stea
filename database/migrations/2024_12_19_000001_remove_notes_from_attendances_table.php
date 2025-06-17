<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('absensi', function (Blueprint $table) {
            // Assuming 'notes' would be 'catatan' if it existed and was translated.
            // The original migration 2024_01_01_000004 did not include a notes/catatan column.
            // This check will prevent errors if the column doesn't exist.
            if (Schema::hasColumn('absensi', 'catatan')) {
                $table->dropColumn('catatan');
            }
        });
    }

    public function down()
    {
        Schema::table('absensi', function (Blueprint $table) {
            // This will add 'catatan' if the migration is rolled back.
            $table->text('catatan')->nullable();
        });
    }
};
