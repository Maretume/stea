<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('departemen', function (Blueprint $table) {
            // Assuming manager_id was not translated in its original creation migration
            // If it was, this should be 'id_manajer'
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departemen', function (Blueprint $table) {
            $table->foreignId('id_manajer')->nullable()->constrained('pengguna')->onDelete('set null');
        });
    }
};
