<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class FixSchedulePermissionsSeeder extends Seeder
{
    public function run()
    {
        // Get all roles
        $adminRole = Role::where('nama_kunci', 'admin')->first();
        $hrRole = Role::where('nama_kunci', 'hr')->first();
        $hrdRole = Role::where('nama_kunci', 'hrd')->first();
        $ceoRole = Role::where('nama_kunci', 'ceo')->first();
        $managerRole = Role::where('nama_kunci', 'manager')->first();
        $karyawanRole = Role::where('nama_kunci', 'karyawan')->first();

        // Define permissions for each role (permission names are keys, remain English)
        $adminPermissions = [
            'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete', 'schedules.approve',
            'offices.view', 'offices.create', 'offices.edit', 'offices.delete',
            'shifts.view', 'shifts.create', 'shifts.edit', 'shifts.delete'
        ];

        $hrPermissions = [
            'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete', 'schedules.approve',
            'offices.view', 'offices.create', 'offices.edit', 'offices.delete',
            'shifts.view', 'shifts.create', 'shifts.edit', 'shifts.delete'
        ];

        $managerPermissions = [
            'schedules.view', 'schedules.approve',
            'offices.view', 'shifts.view'
        ];

        $karyawanPermissions = [
            'schedules.view' // Only view their own schedules
        ];

        // Apply permissions to roles
        if ($adminRole) {
            $permissions = Permission::whereIn('nama_kunci', $adminPermissions)->get();
            $adminRole->permissions()->sync($permissions);
            $this->command->info("Izin admin diperbarui");
        }

        if ($hrRole) {
            $permissions = Permission::whereIn('nama_kunci', $hrPermissions)->get();
            $hrRole->permissions()->sync($permissions);
            $this->command->info("Izin HR diperbarui");
        }

        if ($hrdRole) {
            $permissions = Permission::whereIn('nama_kunci', $hrPermissions)->get(); // HRD gets same as HR
            $hrdRole->permissions()->sync($permissions);
            $this->command->info("Izin HRD diperbarui");
        }

        if ($ceoRole) {
            $permissions = Permission::whereIn('nama_kunci', $adminPermissions)->get(); // CEO gets same as Admin
            $ceoRole->permissions()->sync($permissions);
            $this->command->info("Izin CEO diperbarui");
        }

        if ($managerRole) {
            $permissions = Permission::whereIn('nama_kunci', $managerPermissions)->get();
            $managerRole->permissions()->sync($permissions);
            $this->command->info("Izin Manajer diperbarui");
        }

        if ($karyawanRole) {
            $permissions = Permission::whereIn('nama_kunci', $karyawanPermissions)->get();
            $karyawanRole->permissions()->sync($permissions);
            $this->command->info("Izin Karyawan diperbarui - hanya lihat jadwal");
        }

        $this->command->info("Izin jadwal telah dikonfigurasi dengan benar!");
    }
}
