<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create permissions if they don't exist
        $permissions = [
            // Schedule permissions
            ['nama_kunci' => 'schedules.view', 'nama_tampilan' => 'Lihat Jadwal', 'modul' => 'jadwal', 'deskripsi' => 'Melihat jadwal'],
            ['nama_kunci' => 'schedules.create', 'nama_tampilan' => 'Buat Jadwal', 'modul' => 'jadwal', 'deskripsi' => 'Membuat jadwal'],
            ['nama_kunci' => 'schedules.edit', 'nama_tampilan' => 'Ubah Jadwal', 'modul' => 'jadwal', 'deskripsi' => 'Mengubah jadwal'],
            ['nama_kunci' => 'schedules.delete', 'nama_tampilan' => 'Hapus Jadwal', 'modul' => 'jadwal', 'deskripsi' => 'Menghapus jadwal'],
            ['nama_kunci' => 'schedules.approve', 'nama_tampilan' => 'Setujui Jadwal', 'modul' => 'jadwal', 'deskripsi' => 'Menyetujui jadwal'],

            // Office permissions
            ['nama_kunci' => 'offices.view', 'nama_tampilan' => 'Lihat Kantor', 'modul' => 'kantor', 'deskripsi' => 'Melihat kantor'],
            ['nama_kunci' => 'offices.create', 'nama_tampilan' => 'Buat Kantor', 'modul' => 'kantor', 'deskripsi' => 'Membuat kantor'],
            ['nama_kunci' => 'offices.edit', 'nama_tampilan' => 'Ubah Kantor', 'modul' => 'kantor', 'deskripsi' => 'Mengubah kantor'],
            ['nama_kunci' => 'offices.delete', 'nama_tampilan' => 'Hapus Kantor', 'modul' => 'kantor', 'deskripsi' => 'Menghapus kantor'],

            // Shift permissions
            ['nama_kunci' => 'shifts.view', 'nama_tampilan' => 'Lihat Shift', 'modul' => 'shift', 'deskripsi' => 'Melihat shift'],
            ['nama_kunci' => 'shifts.create', 'nama_tampilan' => 'Buat Shift', 'modul' => 'shift', 'deskripsi' => 'Membuat shift'],
            ['nama_kunci' => 'shifts.edit', 'nama_tampilan' => 'Ubah Shift', 'modul' => 'shift', 'deskripsi' => 'Mengubah shift'],
            ['nama_kunci' => 'shifts.delete', 'nama_tampilan' => 'Hapus Shift', 'modul' => 'shift', 'deskripsi' => 'Menghapus shift'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['nama_kunci' => $permission['nama_kunci']],
                [
                    'nama_tampilan' => $permission['nama_tampilan'],
                    'modul' => $permission['modul'],
                    'deskripsi' => $permission['deskripsi']
                ]
            );
        }

        // Get all permissions
        $allPermissions = Permission::whereIn('nama_kunci', array_column($permissions, 'nama_kunci'))->get();

        // Give all permissions to admin roles
        $adminRoles = Role::whereIn('nama_kunci', ['admin', 'ceo', 'hr', 'hrd'])->get();
        foreach ($adminRoles as $role) {
            $role->permissions()->syncWithoutDetaching($allPermissions);
        }

        // Give view permissions to manager and karyawan
        $viewPermissions = Permission::whereIn('nama_kunci', ['schedules.view', 'shifts.view', 'offices.view'])->get();
        $otherRoles = Role::whereIn('nama_kunci', ['manager', 'karyawan', 'personalia'])->get();
        foreach ($otherRoles as $role) {
            $role->permissions()->syncWithoutDetaching($viewPermissions);
        }

        // Give edit permissions to manager
        $managerRole = Role::where('nama_kunci', 'manager')->first();
        if ($managerRole) {
            $managerPermissions = Permission::whereIn('nama_kunci', [
                'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.approve'
            ])->get();
            $managerRole->permissions()->syncWithoutDetaching($managerPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove permissions
        Permission::whereIn('nama_kunci', [ // Query by nama_kunci
            'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete', 'schedules.approve',
            'offices.view', 'offices.create', 'offices.edit', 'offices.delete',
            'shifts.view', 'shifts.create', 'shifts.edit', 'shifts.delete'
        ])->delete();
    }
};
