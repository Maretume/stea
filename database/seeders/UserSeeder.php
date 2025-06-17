<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'id_karyawan' => 'ADM001',
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
                'role' => 'admin',
            ],
            [
                'id_karyawan' => 'EMP001',
                'nama_pengguna' => 'ceo.stea',
                'surel' => 'ceo@stea.co.id',
                'kata_sandi' => Hash::make('password123'),
                'nama_depan' => 'Budi',
                'nama_belakang' => 'Santoso',
                'telepon' => '081234567890',
                'jenis_kelamin' => 'pria',
                'tanggal_lahir' => '1975-05-15',
                'alamat' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'status' => 'aktif',
                'role' => 'ceo',
            ],
            [
                'id_karyawan' => 'EMP002',
                'nama_pengguna' => 'cfo.stea',
                'surel' => 'cfo@stea.co.id',
                'kata_sandi' => Hash::make('password123'),
                'nama_depan' => 'Sari',
                'nama_belakang' => 'Wijaya',
                'telepon' => '081234567891',
                'jenis_kelamin' => 'wanita',
                'tanggal_lahir' => '1980-08-20',
                'alamat' => 'Jl. Thamrin No. 456, Jakarta Pusat',
                'status' => 'aktif',
                'role' => 'cfo',
            ],
            [
                'id_karyawan' => 'EMP003',
                'nama_pengguna' => 'hrd.stea',
                'surel' => 'hrd@stea.co.id',
                'kata_sandi' => Hash::make('password123'),
                'nama_depan' => 'Andi',
                'nama_belakang' => 'Pratama',
                'telepon' => '081234567892',
                'jenis_kelamin' => 'pria',
                'tanggal_lahir' => '1985-03-10',
                'alamat' => 'Jl. Gatot Subroto No. 789, Jakarta Selatan',
                'status' => 'aktif',
                'role' => 'hrd',
            ],
            [
                'id_karyawan' => 'EMP004',
                'nama_pengguna' => 'personalia.stea',
                'surel' => 'personalia@stea.co.id',
                'kata_sandi' => Hash::make('password123'),
                'nama_depan' => 'Maya',
                'nama_belakang' => 'Sari',
                'telepon' => '081234567893',
                'jenis_kelamin' => 'wanita',
                'tanggal_lahir' => '1990-12-05',
                'alamat' => 'Jl. Kuningan No. 321, Jakarta Selatan',
                'status' => 'aktif',
                'role' => 'personalia',
            ],
            [
                'id_karyawan' => 'EMP005',
                'nama_pengguna' => 'john.doe',
                'surel' => 'john.doe@stea.co.id',
                'kata_sandi' => Hash::make('password123'),
                'nama_depan' => 'John',
                'nama_belakang' => 'Doe',
                'telepon' => '081234567894',
                'jenis_kelamin' => 'pria',
                'tanggal_lahir' => '1992-07-18',
                'alamat' => 'Jl. Kemang No. 654, Jakarta Selatan',
                'status' => 'aktif',
                'role' => 'karyawan',
            ],
            [
                'id_karyawan' => 'EMP006',
                'nama_pengguna' => 'jane.smith',
                'surel' => 'jane.smith@stea.co.id',
                'kata_sandi' => Hash::make('password123'),
                'nama_depan' => 'Jane',
                'nama_belakang' => 'Smith',
                'telepon' => '081234567895',
                'jenis_kelamin' => 'wanita',
                'tanggal_lahir' => '1988-11-25',
                'alamat' => 'Jl. Pondok Indah No. 987, Jakarta Selatan',
                'status' => 'aktif',
                'role' => 'karyawan',
            ],
            [
                'id_karyawan' => 'EMP007',
                'nama_pengguna' => 'ahmad.rizki',
                'surel' => 'ahmad.rizki@stea.co.id',
                'kata_sandi' => Hash::make('password123'),
                'nama_depan' => 'Ahmad',
                'nama_belakang' => 'Rizki',
                'telepon' => '081234567896',
                'jenis_kelamin' => 'pria',
                'tanggal_lahir' => '1991-04-12',
                'alamat' => 'Jl. Senayan No. 147, Jakarta Pusat',
                'status' => 'aktif',
                'role' => 'karyawan',
            ],
            [
                'id_karyawan' => 'EMP008',
                'nama_pengguna' => 'lisa.amanda',
                'surel' => 'lisa.amanda@stea.co.id',
                'kata_sandi' => Hash::make('password123'),
                'nama_depan' => 'Lisa',
                'nama_belakang' => 'Amanda',
                'telepon' => '081234567897',
                'jenis_kelamin' => 'wanita',
                'tanggal_lahir' => '1993-09-30',
                'alamat' => 'Jl. Menteng No. 258, Jakarta Pusat',
                'status' => 'aktif',
                'role' => 'karyawan',
            ],
        ];

        foreach ($users as $userData) {
            $role = Role::where('name', $userData['role'])->first();
            unset($userData['role']);
            
            $user = User::create($userData);
            $user->roles()->attach($role->id, [
                'assigned_at' => now(),
                'is_active' => true,
            ]);
        }
    }
}
