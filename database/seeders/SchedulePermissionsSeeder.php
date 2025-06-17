<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class SchedulePermissionsSeeder extends Seeder
{
    public function run()
    {
        // Create new permissions
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

        // Assign permissions to roles
        $adminRole = Role::where('nama_kunci', 'admin')->first();
        $hrRole = Role::where('nama_kunci', 'hr')->first();
        $managerRole = Role::where('nama_kunci', 'manager')->first();
        $karyawanRole = Role::where('nama_kunci', 'karyawan')->first();

        if ($adminRole) {
            // Admin gets all permissions
            $allPermissions = Permission::whereIn('nama_kunci', array_column($permissions, 'nama_kunci'))->get();
            $adminRole->permissions()->syncWithoutDetaching($allPermissions);
            $this->command->info("Izin jadwal ditambahkan ke peran Admin.");
        }

        if ($hrRole) {
            // HR gets schedule and office management permissions
            $hrPermissions = Permission::whereIn('nama_kunci', [
                'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.approve',
                'offices.view', 'offices.create', 'offices.edit',
                'shifts.view', 'shifts.create', 'shifts.edit'
            ])->get();
            $hrRole->permissions()->syncWithoutDetaching($hrPermissions);
            $this->command->info("Izin jadwal ditambahkan ke peran HR.");
        }

        if ($managerRole) {
            // Manager gets view and approve permissions
            $managerPermissions = Permission::whereIn('nama_kunci', [
                'schedules.view', 'schedules.create', 'schedules.approve',
                'offices.view', 'shifts.view'
            ])->get();
            $managerRole->permissions()->syncWithoutDetaching($managerPermissions);
            $this->command->info("Izin jadwal ditambahkan ke peran Manajer.");
        }

        if ($karyawanRole) {
            // Karyawan only gets view permission for their own schedules
            $karyawanPermissions = Permission::whereIn('nama_kunci', [
                'schedules.view'
            ])->get();
            $karyawanRole->permissions()->syncWithoutDetaching($karyawanPermissions);
            $this->command->info("Izin jadwal ditambahkan ke peran Karyawan.");
        }
         $this->command->info('Pengaturan izin jadwal selesai.');
    }
}
