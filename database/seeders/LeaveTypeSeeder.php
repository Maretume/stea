<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    public function run()
    {
        $leaveTypes = [
            [
                'nama' => 'Cuti Tahunan',
                'kode' => 'ANNUAL',
                'maks_hari_per_tahun' => 12,
                'dibayar' => true,
                'perlu_persetujuan' => true,
                'aktif' => true,
            ],
            [
                'nama' => 'Cuti Sakit',
                'kode' => 'SICK',
                'maks_hari_per_tahun' => 30,
                'dibayar' => true,
                'perlu_persetujuan' => false,
                'aktif' => true,
            ],
            [
                'nama' => 'Cuti Melahirkan',
                'kode' => 'MATERNITY',
                'maks_hari_per_tahun' => 90,
                'dibayar' => true,
                'perlu_persetujuan' => true,
                'aktif' => true,
            ],
            [
                'nama' => 'Cuti Menikah',
                'kode' => 'MARRIAGE',
                'maks_hari_per_tahun' => 3,
                'dibayar' => true,
                'perlu_persetujuan' => true,
                'aktif' => true,
            ],
            [
                'nama' => 'Cuti Kematian Keluarga',
                'kode' => 'BEREAVEMENT',
                'maks_hari_per_tahun' => 3,
                'dibayar' => true,
                'perlu_persetujuan' => true,
                'aktif' => true,
            ],
            [
                'nama' => 'Cuti Khitan/Baptis Anak',
                'kode' => 'CHILD_CEREMONY',
                'maks_hari_per_tahun' => 2,
                'dibayar' => true,
                'perlu_persetujuan' => true,
                'aktif' => true,
            ],
            [
                'nama' => 'Cuti Haji/Umroh',
                'kode' => 'PILGRIMAGE',
                'maks_hari_per_tahun' => 40,
                'dibayar' => false,
                'perlu_persetujuan' => true,
                'aktif' => true,
            ],
            [
                'nama' => 'Cuti Tanpa Gaji',
                'kode' => 'UNPAID',
                'maks_hari_per_tahun' => 365,
                'dibayar' => false,
                'perlu_persetujuan' => true,
                'aktif' => true,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::create($leaveType);
        }
    }
}
