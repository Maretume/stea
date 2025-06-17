<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class DepartmentPermissionSeeder extends Seeder
{
    public function run()
    {
        // Ensure department permissions exist
        $departmentPermissions = [
            [
                'name' => 'departments.view', // This is the key used for lookup, should remain English
                'display_name' => 'Lihat Departemen',
                'module' => 'departemen', // This should be the translated module name
                'description' => 'Dapat melihat daftar dan detail departemen'
            ],
            [
                'name' => 'departments.create',
                'display_name' => 'Buat Departemen',
                'module' => 'departemen',
                'description' => 'Dapat membuat departemen baru'
            ],
            [
                'name' => 'departments.edit',
                'display_name' => 'Edit Departemen',
                'module' => 'departemen',
                'description' => 'Dapat mengedit data departemen'
            ],
            [
                'name' => 'departments.delete',
                'display_name' => 'Hapus Departemen',
                'module' => 'departemen',
                'description' => 'Dapat menghapus departemen'
            ],
        ];

        // Create permissions if they don't exist
        foreach ($departmentPermissions as $permissionData) {
            Permission::firstOrCreate(
                ['nama_kunci' => $permissionData['name']],
                [
                    'nama_tampilan' => $permissionData['display_name'],
                    'modul' => $permissionData['module'], // Use the translated module from the array
                    'deskripsi' => $permissionData['description']
                ]
            );
        }

        // Get all department permissions
        $permissions = Permission::whereIn('nama_kunci', [ // Query by 'nama_kunci'
            'departments.view',
            'departments.create', 
            'departments.edit',
            'departments.delete'
        ])->get();

        // Assign to Admin role
        $adminRole = Role::where('nama_kunci', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching($permissions->pluck('id'));
            $this->command->info('✅ Peran Admin diperbarui dengan izin departemen');
        }

        // Assign to CEO role
        $ceoRole = Role::where('nama_kunci', 'ceo')->first();
        if ($ceoRole) {
            $ceoRole->permissions()->syncWithoutDetaching($permissions->pluck('id'));
            $this->command->info('✅ Peran CEO diperbarui dengan izin departemen');
        }

        // Also assign to HRD role (they should be able to manage departments)
        $hrdRole = Role::where('nama_kunci', 'hrd')->first();
        if ($hrdRole) {
            $hrdRole->permissions()->syncWithoutDetaching($permissions->pluck('id'));
            $this->command->info('✅ Peran HRD diperbarui dengan izin departemen');
        }

        // Verify admin users have the permissions
        // User model might need to be updated to use nama_kunci for roles, and full_name might need to use nama_depan, nama_belakang
        // For now, this part of verification might show original values or error if model assumptions are not met
        $adminUsers = User::whereHas('roles', function($query) {
            $query->whereIn('nama_kunci', ['admin', 'ceo']);
        })->get();

        foreach ($adminUsers as $user) {
            // Assuming User model has methods like full_name, employee_id, and roles relationship correctly set up
            // and that roles have 'nama_kunci'
            // $userRoles = $user->roles->pluck('nama_kunci')->toArray();
            // $hasPermission = $user->hasPermissionTo('departments.edit'); // hasPermissionTo is a common Spatie/Laravel-Permission method
            
            // The output below is simplified as the exact model structure and methods are not modified here
            $this->command->info("Pengguna: {$user->nama_pengguna} ({$user->id_karyawan})"); // Using translated user fields
            // $this->command->info("Peran: " . implode(', ', $userRoles));
            // $this->command->info("Dapat mengubah departemen: " . ($hasPermission ? 'YA' : 'TIDAK'));
            $this->command->info("--- (Verifikasi manual mungkin diperlukan jika model belum sepenuhnya disesuaikan) ---");
        }

        $this->command->info('🎉 Pengaturan izin departemen selesai!');
    }
}
