<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Create admin role if not exists
        $adminRole = Role::firstOrCreate([
            'name' => 'admin'
        ], [
            'nama_tampilan' => 'Administrator Sistem',
            'description' => 'Super admin dengan akses penuh ke semua sistem dan konfigurasi',
            'is_active' => true,
        ]);

        // Give admin role all permissions
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        // Create admin user if not exists
        $adminUser = User::firstOrCreate([
            'id_karyawan' => 'ADM001'
        ], [
            'nama_pengguna' => 'admin',
            'surel' => 'admin@stea.co.id',
            'kata_sandi' => Hash::make('admin123'),
            'nama_depan' => 'Sistem',
            'nama_belakang' => 'Administrator',
            'telepon' => '081234567888',
            'jenis_kelamin' => 'pria',
            'tanggal_lahir' => '1980-01-01',
            'alamat' => 'Administrator Sistem',
            'status' => 'aktif',
        ]);

        // Assign admin role to user
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id => [
            'assigned_at' => now(),
            'is_active' => true,
        ]]);

        // Create employee record for admin
        $itDepartment = Department::where('code', 'IT')->first();
        $devPosition = Position::where('code', 'DEV')->first();

        if ($itDepartment && $devPosition) {
            Employee::firstOrCreate([
                'user_id' => $adminUser->id
            ], [
                'department_id' => $itDepartment->id,
                'position_id' => $devPosition->id,
                'supervisor_id' => null, // Admin has no supervisor
                'hire_date' => now()->subYears(5),
                'employment_type' => 'tetap',
                'employment_status' => 'aktif',
                'basic_salary' => 25000000,
                'bank_name' => 'Bank Mandiri',
                'bank_account' => '1234567890001',
                'bank_account_name' => $adminUser->nama_depan . ' ' . $adminUser->nama_belakang,
            ]);
        }

        $this->command->info('Pengguna admin berhasil dibuat!');
        $this->command->info('Nama Pengguna: admin');
        $this->command->info('Kata Sandi: admin123');
        $this->command->info('Email: admin@stea.co.id');
    }
}
