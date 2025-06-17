<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class FixScheduleMenuSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🔧 Memperbaiki Izin Menu Jadwal...');

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

        $this->command->info('✅ Izin dibuat/diperbarui');

        // Get all permissions
        $allPermissions = Permission::whereIn('nama_kunci', array_column($permissions, 'nama_kunci'))->get();

        // Assign to Admin role
        $adminRole = Role::where('nama_kunci', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching($allPermissions);
            $this->command->info('✅ Peran Admin diperbarui dengan izin jadwal');
        }

        // Assign to CEO role
        $ceoRole = Role::where('nama_kunci', 'ceo')->first();
        if ($ceoRole) {
            $ceoRole->permissions()->syncWithoutDetaching($allPermissions);
            $this->command->info('✅ Peran CEO diperbarui dengan izin jadwal');
        }

        // Assign to HR role
        $hrRole = Role::where('nama_kunci', 'hr')->first();
        if ($hrRole) {
            $hrRole->permissions()->syncWithoutDetaching($allPermissions);
            $this->command->info('✅ Peran HR diperbarui dengan izin jadwal');
        }

        // Assign to HRD role
        $hrdRole = Role::where('nama_kunci', 'hrd')->first();
        if ($hrdRole) {
            $hrdRole->permissions()->syncWithoutDetaching($allPermissions);
            $this->command->info('✅ Peran HRD diperbarui dengan izin jadwal');
        }

        // Assign view permissions to Manager role
        $managerRole = Role::where('nama_kunci', 'manager')->first();
        if ($managerRole) {
            $managerPermissions = Permission::whereIn('nama_kunci', [
                'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.approve',
                'offices.view', 'shifts.view'
            ])->get();
            $managerRole->permissions()->syncWithoutDetaching($managerPermissions);
            $this->command->info('✅ Peran Manajer diperbarui dengan izin jadwal');
        }

        // Assign view permissions to Karyawan role
        $karyawanRole = Role::where('nama_kunci', 'karyawan')->first();
        if ($karyawanRole) {
            $karyawanPermissions = Permission::whereIn('nama_kunci', [
                'schedules.view', 'shifts.view'
            ])->get();
            $karyawanRole->permissions()->syncWithoutDetaching($karyawanPermissions);
            $this->command->info('✅ Peran Karyawan diperbarui dengan izin jadwal');
        }

        // Give all users with admin role the permissions
        $adminUsers = User::whereHas('roles', function($query) {
            $query->whereIn('nama_kunci', ['admin', 'ceo']);
        })->get();

        foreach ($adminUsers as $user) {
            // Assuming User model has full_name or similar attribute
            $this->command->info("✅ Pengguna admin {$user->nama_depan} {$user->nama_belakang} memiliki akses ke menu jadwal");
        }

        // Show current user info if logged in
        if (auth()->check()) {
            $currentUser = auth()->user();
            $userRoles = $currentUser->roles->pluck('nama_kunci')->toArray(); // Use nama_kunci
            $hasSchedulePermission = $currentUser->hasPermission('schedules.view'); // Permission name is key
            $hasShiftPermission = $currentUser->hasPermission('shifts.view');     // Permission name is key
            
            $this->command->info("📋 Info Pengguna Saat Ini:");
            $this->command->info("   Nama: {$currentUser->nama_depan} {$currentUser->nama_belakang}");
            $this->command->info("   Peran: " . implode(', ', $userRoles));
            $this->command->info("   Izin Jadwal: " . ($hasSchedulePermission ? '✅ Ya' : '❌ Tidak'));
            $this->command->info("   Izin Shift: " . ($hasShiftPermission ? '✅ Ya' : '❌ Tidak'));
        }

        $this->command->info('🎉 Izin menu jadwal berhasil diperbaiki!');
        $this->command->info('📝 Item menu sekarang seharusnya terlihat berdasarkan peran pengguna:');
        $this->command->info('   - Admin/CEO/HR/HRD: Akses penuh ke semua fitur jadwal');
        $this->command->info('   - Manajer: Dapat melihat dan mengelola jadwal');
        $this->command->info('   - Karyawan: Dapat melihat jadwal');
    }
}
