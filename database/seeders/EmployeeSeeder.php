<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        // Assuming Department and Position models will use 'kode' internally or have accessors for 'code'
        // For this seeder, we rely on the keyBy using the translated 'kode' if the model's $primaryKey or find method is adapted.
        // If not, this might need adjustment depending on how models handle the 'kode' field after translation.
        $departments = Department::all()->keyBy('kode');
        $positions = Position::all()->keyBy('kode');

        $employeeData = [
            // Assuming User's employee_id (now id_karyawan) values remain ADM001, EMP001, etc.
            'ADM001' => ['department_code' => 'IT', 'position_code' => 'DEV', 'salary' => 25000000],
            'EMP001' => ['department_code' => 'BOD', 'position_code' => 'CEO', 'salary' => 50000000],
            'EMP002' => ['department_code' => 'BOD', 'position_code' => 'CFO', 'salary' => 40000000],
            'EMP003' => ['department_code' => 'HR', 'position_code' => 'HRM', 'salary' => 20000000],
            'EMP004' => ['department_code' => 'HR', 'position_code' => 'PER', 'salary' => 9000000],
            'EMP005' => ['department_code' => 'IT', 'position_code' => 'DEV', 'salary' => 15000000],
            'EMP006' => ['department_code' => 'MKT', 'position_code' => 'MKS', 'salary' => 12000000],
            'EMP007' => ['department_code' => 'SALES', 'position_code' => 'SR', 'salary' => 10000000],
            'EMP008' => ['department_code' => 'ADM', 'position_code' => 'ADMS', 'salary' => 8000000],
        ];

        foreach ($users as $user) {
            // Assuming $user->id_karyawan holds the original employee_id string like 'ADM001'
            if (isset($employeeData[$user->id_karyawan])) {
                $data = $employeeData[$user->id_karyawan];
                
                Employee::create([
                    'id_pengguna' => $user->id,
                    // Accessing department/position by their original English codes
                    'id_departemen' => $departments[$data['department_code']]->id,
                    'id_jabatan' => $positions[$data['position_code']]->id,
                    'id_atasan' => $this->getSupervisorId($user->id_karyawan, $users),
                    'tanggal_rekrut' => now()->subYears(rand(1, 5)),
                    'jenis_kepegawaian' => 'tetap', // permanent -> tetap
                    'status_kepegawaian' => 'aktif', // active -> aktif
                    'gaji_pokok' => $data['salary'],
                    'nama_bank' => 'Bank Mandiri',
                    'rekening_bank' => '1234567890' . substr($user->id_karyawan, -3),
                    // Assuming User model has an accessor `full_name` that uses nama_depan and nama_belakang
                    'nama_rekening_bank' => $user->nama_depan . ' ' . $user->nama_belakang,
                ]);
            }
        }
    }

    private function getSupervisorId($employeeId, $users)
    {
        // Simple supervisor assignment logic
        $supervisors = [
            'EMP002' => 'EMP001', // CFO reports to CEO
            'EMP003' => 'EMP001', // HRM reports to CEO
            'EMP004' => 'EMP003', // Personalia reports to HRM
            'EMP005' => 'EMP001', // IT Dev reports to CEO (no IT Manager in this example)
            'EMP006' => 'EMP001', // Marketing reports to CEO
            'EMP007' => 'EMP001', // Sales reports to CEO
            'EMP008' => 'EMP003', // Admin reports to HRM
        ];

        if (isset($supervisors[$employeeId])) {
            // Assuming $user->id_karyawan holds the original employee_id string
            $supervisor = $users->where('id_karyawan', $supervisors[$employeeId])->first();
            return $supervisor ? $supervisor->id : null;
        }

        return null;
    }
}
