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
        Schema::table('attendance_rules', function (Blueprint $table) {
            $table->dropColumn([
                'break_start_time',
                'break_end_time'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_rules', function (Blueprint $table) {
            $table->time('break_start_time')->nullable()->after('work_end_time');
            $table->time('break_end_time')->nullable()->after('break_start_time');
        });
    }
};
