<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;
use App\Models\Department;

class PositionSeeder extends Seeder
{
    public function run()
    {
        $departments = Department::all()->keyBy('kode'); // Assuming Department model now uses 'kode'

        $positions = [
            // Board of Directors
            ['kode' => 'CEO', 'nama' => 'Direktur Utama', 'department_code' => 'BOD', 'gaji_pokok' => 50000000, 'tingkat' => 1],
            ['kode' => 'CFO', 'nama' => 'Direktur Keuangan', 'department_code' => 'BOD', 'gaji_pokok' => 40000000, 'tingkat' => 2],
            
            // Finance
            ['kode' => 'FM', 'nama' => 'Manajer Keuangan', 'department_code' => 'FIN', 'gaji_pokok' => 25000000, 'tingkat' => 3],
            ['kode' => 'ACC', 'nama' => 'Akuntan', 'department_code' => 'FIN', 'gaji_pokok' => 12000000, 'tingkat' => 4],
            ['kode' => 'ACCA', 'nama' => 'Asisten Akuntansi', 'department_code' => 'FIN', 'gaji_pokok' => 8000000, 'tingkat' => 5],
            
            // Human Resources
            ['kode' => 'HRM', 'nama' => 'Manajer SDM', 'department_code' => 'HR', 'gaji_pokok' => 20000000, 'tingkat' => 3],
            ['kode' => 'HRS', 'nama' => 'Spesialis SDM', 'department_code' => 'HR', 'gaji_pokok' => 12000000, 'tingkat' => 4],
            ['kode' => 'PER', 'nama' => 'Personalia', 'department_code' => 'HR', 'gaji_pokok' => 9000000, 'tingkat' => 5],
            
            // Information Technology
            ['kode' => 'ITM', 'nama' => 'Manajer TI', 'department_code' => 'IT', 'gaji_pokok' => 25000000, 'tingkat' => 3],
            ['kode' => 'DEV', 'nama' => 'Pengembang Perangkat Lunak', 'department_code' => 'IT', 'gaji_pokok' => 15000000, 'tingkat' => 4],
            ['kode' => 'SYS', 'nama' => 'Administrator Sistem', 'department_code' => 'IT', 'gaji_pokok' => 12000000, 'tingkat' => 4],
            ['kode' => 'SUP', 'nama' => 'Dukungan TI', 'department_code' => 'IT', 'gaji_pokok' => 8000000, 'tingkat' => 5],
            
            // Operations
            ['kode' => 'OPM', 'nama' => 'Manajer Operasional', 'department_code' => 'OPS', 'gaji_pokok' => 20000000, 'tingkat' => 3],
            ['kode' => 'OPS', 'nama' => 'Staf Operasional', 'department_code' => 'OPS', 'gaji_pokok' => 10000000, 'tingkat' => 4],
            
            // Marketing
            ['kode' => 'MKM', 'nama' => 'Manajer Pemasaran', 'department_code' => 'MKT', 'gaji_pokok' => 20000000, 'tingkat' => 3],
            ['kode' => 'MKS', 'nama' => 'Spesialis Pemasaran', 'department_code' => 'MKT', 'gaji_pokok' => 12000000, 'tingkat' => 4],
            
            // Sales
            ['kode' => 'SM', 'nama' => 'Manajer Penjualan', 'department_code' => 'SALES', 'gaji_pokok' => 18000000, 'tingkat' => 3],
            ['kode' => 'SR', 'nama' => 'Perwakilan Penjualan', 'department_code' => 'SALES', 'gaji_pokok' => 10000000, 'tingkat' => 4],
            
            // Administration
            ['kode' => 'ADM', 'nama' => 'Manajer Administrasi', 'department_code' => 'ADM', 'gaji_pokok' => 15000000, 'tingkat' => 3],
            ['kode' => 'ADMS', 'nama' => 'Staf Administrasi', 'department_code' => 'ADM', 'gaji_pokok' => 8000000, 'tingkat' => 5],
        ];

        foreach ($positions as $position) {
            Position::create([
                'kode' => $position['kode'],
                'nama' => $position['nama'],
                'id_departemen' => $departments[$position['department_code']]->id,
                'gaji_pokok' => $position['gaji_pokok'],
                'tingkat' => $position['tingkat'],
                'deskripsi' => 'Posisi ' . $position['nama'],
                'aktif' => true,
            ]);
        }
    }
}
