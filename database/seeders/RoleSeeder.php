<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            [
                'nama_kunci' => 'admin',
                'nama_tampilan' => 'Administrator Sistem',
                'deskripsi' => 'Super admin dengan akses penuh ke semua sistem dan konfigurasi',
                'aktif' => true,
            ],
            [
                'nama_kunci' => 'ceo',
                'nama_tampilan' => 'Direktur Utama',
                'deskripsi' => 'Akses penuh ke semua modul sistem',
                'aktif' => true,
            ],
            [
                'nama_kunci' => 'cfo',
                'nama_tampilan' => 'Direktur Keuangan',
                'deskripsi' => 'Akses ke laporan keuangan, persetujuan gaji, dan anggaran',
                'aktif' => true,
            ],
            [
                'nama_kunci' => 'hrd',
                'nama_tampilan' => 'Pengembangan Sumber Daya Manusia',
                'deskripsi' => 'Manajemen karyawan, absensi, dan penggajian',
                'aktif' => true,
            ],
            [
                'nama_kunci' => 'personalia',
                'nama_tampilan' => 'Personalia',
                'deskripsi' => 'Input data karyawan dan pemantauan absensi',
                'aktif' => true,
            ],
            [
                'nama_kunci' => 'karyawan',
                'nama_tampilan' => 'Karyawan',
                'deskripsi' => 'Akses terbatas untuk melihat slip gaji dan absensi pribadi',
                'aktif' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
