<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            [
                'kode' => 'BOD',
                'nama' => 'Dewan Direksi',
                'deskripsi' => 'Dewan Direksi dan Komisaris',
                'aktif' => true,
            ],
            [
                'kode' => 'FIN',
                'nama' => 'Keuangan',
                'deskripsi' => 'Departemen Keuangan dan Akuntansi',
                'aktif' => true,
            ],
            [
                'kode' => 'HR',
                'nama' => 'Sumber Daya Manusia',
                'deskripsi' => 'Departemen Sumber Daya Manusia',
                'aktif' => true,
            ],
            [
                'kode' => 'IT',
                'nama' => 'Teknologi Informasi',
                'deskripsi' => 'Departemen Teknologi Informasi',
                'aktif' => true,
            ],
            [
                'kode' => 'OPS',
                'nama' => 'Operasional',
                'deskripsi' => 'Departemen Operasional',
                'aktif' => true,
            ],
            [
                'kode' => 'MKT',
                'nama' => 'Pemasaran',
                'deskripsi' => 'Departemen Pemasaran',
                'aktif' => true,
            ],
            [
                'kode' => 'SALES',
                'nama' => 'Penjualan',
                'deskripsi' => 'Departemen Penjualan',
                'aktif' => true,
            ],
            [
                'kode' => 'ADM',
                'nama' => 'Administrasi',
                'deskripsi' => 'Departemen Administrasi Umum',
                'aktif' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
