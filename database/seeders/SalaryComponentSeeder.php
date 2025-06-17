<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalaryComponent;

class SalaryComponentSeeder extends Seeder
{
    public function run()
    {
        $components = [
            // Allowances
            [
                'nama' => 'Tunjangan Transport',
                'kode' => 'TRANSPORT',
                'tipe' => 'tunjangan', // allowance
                'tipe_perhitungan' => 'tetap', // fixed
                'jumlah_standar' => 500000,
                'kena_pajak' => false,
                'urutan' => 1,
            ],
            [
                'nama' => 'Tunjangan Makan',
                'kode' => 'MEAL',
                'tipe' => 'tunjangan', // allowance
                'tipe_perhitungan' => 'tetap', // fixed
                'jumlah_standar' => 600000,
                'kena_pajak' => false,
                'urutan' => 2,
            ],
            [
                'nama' => 'Tunjangan Komunikasi',
                'kode' => 'COMMUNICATION',
                'tipe' => 'tunjangan', // allowance
                'tipe_perhitungan' => 'tetap', // fixed
                'jumlah_standar' => 300000,
                'kena_pajak' => true,
                'urutan' => 3,
            ],
            [
                'nama' => 'Tunjangan Jabatan',
                'kode' => 'POSITION',
                'tipe' => 'tunjangan', // allowance
                'tipe_perhitungan' => 'persentase', // percentage
                'persentase' => 20,
                'kena_pajak' => true,
                'urutan' => 4,
            ],
            [
                'nama' => 'Tunjangan Keluarga',
                'kode' => 'FAMILY',
                'tipe' => 'tunjangan', // allowance
                'tipe_perhitungan' => 'persentase', // percentage
                'persentase' => 10,
                'kena_pajak' => true,
                'urutan' => 5,
            ],
            [
                'nama' => 'Bonus Kinerja',
                'kode' => 'PERFORMANCE',
                'tipe' => 'tunjangan', // allowance
                'tipe_perhitungan' => 'tetap', // fixed
                'jumlah_standar' => 0,
                'kena_pajak' => true,
                'urutan' => 6,
            ],
            [
                'nama' => 'Lembur',
                'kode' => 'OVERTIME',
                'tipe' => 'tunjangan', // allowance
                'tipe_perhitungan' => 'rumus', // formula
                'rumus' => '(basic_salary / 173) * overtime_hours * 1.5',
                'kena_pajak' => true,
                'urutan' => 7,
            ],

            // Deductions
            [
                'nama' => 'BPJS Kesehatan (Karyawan)',
                'kode' => 'BPJS_HEALTH_EMP',
                'tipe' => 'potongan', // deduction
                'tipe_perhitungan' => 'persentase', // percentage
                'persentase' => 1,
                'kena_pajak' => false,
                'urutan' => 10,
            ],
            [
                'nama' => 'BPJS Ketenagakerjaan (Karyawan)',
                'kode' => 'BPJS_WORK_EMP',
                'tipe' => 'potongan', // deduction
                'tipe_perhitungan' => 'persentase', // percentage
                'persentase' => 2,
                'kena_pajak' => false,
                'urutan' => 11,
            ],
            [
                'nama' => 'PPh 21',
                'kode' => 'TAX_PPH21',
                'tipe' => 'potongan', // deduction
                'tipe_perhitungan' => 'rumus', // formula
                'rumus' => 'calculate_pph21(taxable_income)',
                'kena_pajak' => false,
                'urutan' => 12,
            ],
            [
                'nama' => 'Potongan Keterlambatan',
                'kode' => 'LATE_DEDUCTION',
                'tipe' => 'potongan', // deduction
                'tipe_perhitungan' => 'tetap', // fixed
                'jumlah_standar' => 0,
                'kena_pajak' => false,
                'urutan' => 13,
            ],
            [
                'nama' => 'Potongan Alpha',
                'kode' => 'ABSENT_DEDUCTION',
                'tipe' => 'potongan', // deduction
                'tipe_perhitungan' => 'rumus', // formula
                'rumus' => '(basic_salary / working_days) * absent_days',
                'kena_pajak' => false,
                'urutan' => 14,
            ],
            [
                'nama' => 'Pinjaman Karyawan',
                'kode' => 'LOAN',
                'tipe' => 'potongan', // deduction
                'tipe_perhitungan' => 'tetap', // fixed
                'jumlah_standar' => 0,
                'kena_pajak' => false,
                'urutan' => 15,
            ],

            // Benefits (Company contributions)
            [
                'nama' => 'BPJS Kesehatan (Perusahaan)',
                'kode' => 'BPJS_HEALTH_COMP',
                'tipe' => 'manfaat', // benefit
                'tipe_perhitungan' => 'persentase', // percentage
                'persentase' => 4,
                'kena_pajak' => false,
                'urutan' => 20,
            ],
            [
                'nama' => 'BPJS Ketenagakerjaan (Perusahaan)',
                'kode' => 'BPJS_WORK_COMP',
                'tipe' => 'manfaat', // benefit
                'tipe_perhitungan' => 'persentase', // percentage
                'persentase' => 3.7,
                'kena_pajak' => false,
                'urutan' => 21,
            ],
        ];

        foreach ($components as $component) {
            SalaryComponent::create($component);
        }
    }
}
