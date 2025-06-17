<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\Office;

class UpdateEmployeeDefaultScheduleSeeder extends Seeder
{
    public function run()
    {
        // Get default shift and office
        $defaultShift = Shift::where('aktif', true)->first(); // is_active -> aktif
        $defaultOffice = Office::where('aktif', true)->first(); // is_active -> aktif

        if (!$defaultShift) {
            $this->command->error('Tidak ada shift aktif ditemukan. Buat shift terlebih dahulu.');
            return;
        }

        if (!$defaultOffice) {
            $this->command->error('Tidak ada kantor aktif ditemukan. Buat kantor terlebih dahulu.');
            return;
        }

        // Update all employees without default schedule settings
        $employees = Employee::whereNull('id_shift_standar')->get(); // default_shift_id -> id_shift_standar

        foreach ($employees as $employee) {
            $employee->update([
                'id_shift_standar' => $defaultShift->id,
                'id_kantor_standar' => $defaultOffice->id,
                'tipe_kerja_standar' => 'WFO', // default_work_type -> tipe_kerja_standar
            ]);
            // Assuming user relation exists and has nama_depan, nama_belakang
            $this->command->info("Karyawan diperbarui: {$employee->user->nama_depan} {$employee->user->nama_belakang}");
        }

        $this->command->info("Memperbarui {$employees->count()} karyawan dengan pengaturan jadwal standar.");
    }
}
