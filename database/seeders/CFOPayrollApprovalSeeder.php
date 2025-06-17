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

class CFOPayrollApprovalSeeder extends Seeder
{
    public function run()
    {
        // Ensure CFO role exists and has payroll.approve permission
        $cfoRole = Role::where('nama_kunci', 'cfo')->first();
        
        if (!$cfoRole) {
            $cfoRole = Role::create([
                'nama_kunci' => 'cfo',
                'nama_tampilan' => 'Direktur Keuangan',
                'deskripsi' => 'Akses ke laporan keuangan, persetujuan gaji, dan anggaran', // Already Indonesian-like, slight adjustment
                'aktif' => true,
            ]);
        }

        // Ensure payroll.approve permission exists
        $approvePermission = Permission::where('nama_kunci', 'payroll.approve')->first();
        
        if (!$approvePermission) {
            $approvePermission = Permission::create([
                'nama_kunci' => 'payroll.approve',
                'nama_tampilan' => 'Setujui Penggajian',
                'modul' => 'penggajian',
                'deskripsi' => 'Menyetujui penggajian untuk karyawan'
            ]);
        }

        // Give CFO role the payroll.approve permission
        if (!$cfoRole->hasPermission('payroll.approve')) { // hasPermission still uses name/nama_kunci
            $cfoRole->givePermissionTo($approvePermission);
            $this->command->info('✅ Peran CFO diberikan izin payroll.approve');
        }

        // Ensure CFO has other necessary permissions
        $cfoPermissions = [ // These are nama_kunci, so they remain in English
            'dashboard.view', 'dashboard.analytics',
            'employees.view', 'employees.view_salary',
            'payroll.view_all', 'payroll.approve', 'payroll.reports',
            'reports.view', 'reports.financial', 'reports.export',
            'attendance.reports',
        ];

        $permissions = Permission::whereIn('nama_kunci', $cfoPermissions)->get();
        $cfoRole->permissions()->syncWithoutDetaching($permissions->pluck('id'));

        // Create a test CFO user if it doesn't exist
        $cfoUser = User::where('id_karyawan', 'CFO001')->first(); // Use translated key
        
        if (!$cfoUser) {
            $cfoUser = User::create([
                'id_karyawan' => 'CFO001',
                'nama_pengguna' => 'cfo',
                'surel' => 'cfo@stea.co.id',
                'kata_sandi' => Hash::make('cfo123'),
                'nama_depan' => 'Kepala Keuangan', // Chief Financial
                'nama_belakang' => 'Pejabat',     // Officer
                'telepon' => '081234567890',
                'jenis_kelamin' => 'pria', // male
                'tanggal_lahir' => '1975-01-01',
                'alamat' => 'Jakarta',
                'status' => 'aktif', // active
            ]);

            // Assign CFO role to user
            $cfoUser->roles()->attach($cfoRole->id, [
                'ditetapkan_pada' => now(), // assigned_at
                'aktif' => true,           // is_active
            ]);

            // Create employee record
            // Department and Position lookup by original code
            $department = Department::where('kode', 'BOD')->first();
            $position = Position::where('kode', 'CFO')->first();

            if ($department && $position) {
                Employee::create([
                    'id_pengguna' => $cfoUser->id,
                    'id_departemen' => $department->id,
                    'id_jabatan' => $position->id,
                    'tanggal_rekrut' => now()->subYears(2),
                    'status_kepegawaian' => 'aktif', // active
                    'jenis_kepegawaian' => 'tetap', // permanent
                    'gaji_pokok' => 40000000,
                ]);
            }

            $this->command->info('✅ Pengguna uji CFO dibuat: cfo@stea.co.id / cfo123');
        } else {
            // Ensure existing CFO user has the role
            if (!$cfoUser->hasRole('cfo')) { // hasRole still uses name/nama_kunci
                $cfoUser->roles()->attach($cfoRole->id, [
                    'ditetapkan_pada' => now(), // assigned_at
                    'aktif' => true,           // is_active
                ]);
                $this->command->info('✅ Peran CFO ditetapkan ke pengguna yang ada');
            }
        }

        $this->command->info('✅ Pengaturan persetujuan penggajian CFO selesai');
    }
}
