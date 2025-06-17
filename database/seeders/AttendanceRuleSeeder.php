<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRule;

class AttendanceRuleSeeder extends Seeder
{
    public function run()
    {
        AttendanceRule::create([
            'nama' => 'Jam Kerja Standar',
            'jam_mulai_kerja' => '08:00:00',
            'jam_selesai_kerja' => '17:00:00',
            // 'jam_mulai_istirahat' and 'jam_selesai_istirahat' are removed
            'toleransi_keterlambatan_menit' => 15,
            'toleransi_pulang_awal_menit' => 15,
            'pengali_lembur' => 1.5,
            'standar' => true,
            'aktif' => true,
        ]);

        AttendanceRule::create([
            'nama' => 'Jam Kerja Shift Pagi',
            'jam_mulai_kerja' => '06:00:00',
            'jam_selesai_kerja' => '14:00:00',
            // 'jam_mulai_istirahat' and 'jam_selesai_istirahat' are removed
            'toleransi_keterlambatan_menit' => 10,
            'toleransi_pulang_awal_menit' => 10,
            'pengali_lembur' => 1.5,
            'standar' => false,
            'aktif' => true,
        ]);

        AttendanceRule::create([
            'nama' => 'Jam Kerja Shift Sore',
            'jam_mulai_kerja' => '14:00:00',
            'jam_selesai_kerja' => '22:00:00',
            // 'jam_mulai_istirahat' and 'jam_selesai_istirahat' are removed
            'toleransi_keterlambatan_menit' => 10,
            'toleransi_pulang_awal_menit' => 10,
            'pengali_lembur' => 2.0,
            'standar' => false,
            'aktif' => true,
        ]);
    }
}
