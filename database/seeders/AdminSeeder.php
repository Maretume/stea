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
            'nama_kunci' => 'admin' // name -> nama_kunci
        ], [
            'nama_tampilan' => 'Administrator Sistem',
            'deskripsi' => 'Super admin dengan akses penuh ke semua sistem dan konfigurasi',
            'aktif' => true, // is_active -> aktif (already done in Role model, but good to be explicit)
        ]);

        // Give admin role all permissions
        $allPermissions = Permission::all();
        // Assuming Role model permissions() relation correctly uses peran_izin pivot
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        // Create admin user if not exists
        // User model now uses translated keys, this part is already correct from previous steps.
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
        // User model roles() relation uses 'pengguna_peran' and translated pivot keys
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id => [
            'ditetapkan_pada' => now(), // assigned_at -> ditetapkan_pada
            'aktif' => true,           // is_active -> aktif
        ]]);

        // Create employee record for admin
        $itDepartment = Department::where('kode', 'IT')->first(); // code -> kode
        $devPosition = Position::where('kode', 'DEV')->first();   // code -> kode

        if ($itDepartment && $devPosition) {
            Employee::firstOrCreate([
                'id_pengguna' => $adminUser->id // user_id -> id_pengguna
            ], [
                'id_departemen' => $itDepartment->id, // department_id -> id_departemen
                'id_jabatan' => $devPosition->id,     // position_id -> id_jabatan
                'id_atasan' => null,                 // supervisor_id -> id_atasan
                'tanggal_rekrut' => now()->subYears(5), // hire_date -> tanggal_rekrut
                'jenis_kepegawaian' => 'tetap',       // employment_type -> jenis_kepegawaian
                'status_kepegawaian' => 'aktif',      // employment_status -> status_kepegawaian
                'gaji_pokok' => 25000000,            // basic_salary -> gaji_pokok
                'nama_bank' => 'Bank Mandiri',        // bank_name -> nama_bank
                'rekening_bank' => '1234567890001',   // bank_account -> rekening_bank
                'nama_rekening_bank' => $adminUser->nama_depan . ' ' . $adminUser->nama_belakang, // bank_account_name -> nama_rekening_bank
            ]);
        }

        $this->command->info('Pengguna admin berhasil dibuat!');
        $this->command->info('Nama Pengguna: admin');
        $this->command->info('Kata Sandi: admin123');
        $this->command->info('Email: admin@stea.co.id');
    }
}
