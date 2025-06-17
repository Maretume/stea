<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Office;
use App\Models\Shift;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;

class OfficeShiftScheduleSeeder extends Seeder
{
    public function run()
    {
        // Create Offices
        $offices = [
            [
                'nama' => 'Kantor Pusat Jakarta',
                'lintang' => -6.2088,
                'bujur' => 106.8456,
                'radius' => 100,
                'aktif' => true,
            ],
            [
                'nama' => 'Kantor Cabang Bandung',
                'lintang' => -6.9175,
                'bujur' => 107.6191,
                'radius' => 150,
                'aktif' => true,
            ],
            [
                'nama' => 'Kantor Cabang Surabaya',
                'lintang' => -7.2575,
                'bujur' => 112.7521,
                'radius' => 120,
                'aktif' => true,
            ],
        ];

        foreach ($offices as $office) {
            Office::create($office);
        }

        // Create Shifts
        $shifts = [
            [
                'nama' => 'Shift Pagi',
                'waktu_mulai' => '08:00:00',
                'waktu_selesai' => '17:00:00',
                'aktif' => true,
            ],
            [
                'nama' => 'Shift Siang',
                'waktu_mulai' => '13:00:00',
                'waktu_selesai' => '22:00:00',
                'aktif' => true,
            ],
            [
                'nama' => 'Shift Malam',
                'waktu_mulai' => '22:00:00',
                'waktu_selesai' => '07:00:00',
                'aktif' => true,
            ],
            [
                'nama' => 'Shift Fleksibel',
                'waktu_mulai' => '09:00:00',
                'waktu_selesai' => '18:00:00',
                'aktif' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }

        // Create sample schedules for existing users
        $adminUser = User::whereHas('roles', function($q) {
            $q->where('nama_kunci', 'admin');
        })->first();
        $adminUserId = $adminUser ? $adminUser->id : User::first()->id; // Fallback to first user if admin not found

        $users = User::whereHas('employee')->take(5)->get(); // Assuming 'employee' relation exists
        $officeIds = Office::pluck('id')->toArray();
        $shiftIds = Shift::pluck('id')->toArray();
        $workTypes = ['WFO', 'WFA']; // These are likely system keys, keep as is.

        foreach ($users as $user) {
            // Create schedules for the next 30 days
            for ($i = 0; $i < 30; $i++) {
                $scheduleDate = Carbon::today()->addDays($i);
                
                // Skip weekends for this example
                if ($scheduleDate->isWeekend()) {
                    continue;
                }

                $workType = $workTypes[array_rand($workTypes)];
                $officeId = $workType === 'WFO' ? $officeIds[array_rand($officeIds)] : null;
                $shiftId = $shiftIds[array_rand($shiftIds)];

                Schedule::create([
                    'id_pengguna' => $user->id,
                    'id_shift' => $shiftId,
                    'id_kantor' => $officeId,
                    'tanggal_jadwal' => $scheduleDate,
                    'tipe_kerja' => $workType,
                    'status' => 'disetujui', // approved -> disetujui
                    'catatan' => $workType === 'WFA' ? 'Kerja jarak jauh' : 'Kerja di kantor', // Remote work day / Office work day
                    'dibuat_oleh' => $adminUserId,
                    'disetujui_oleh' => $adminUserId,
                    'disetujui_pada' => now(),
                ]);
            }
        }
    }
}
