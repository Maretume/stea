<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PayrollPeriod;
use App\Models\User;
use Carbon\Carbon;

class PayrollPeriodSeeder extends Seeder
{
    public function run()
    {
        // Get admin user for created_by
        $adminUser = User::whereHas('roles', function($query) {
            $query->where('nama_kunci', 'admin'); // Use translated key
        })->first();

        if (!$adminUser) {
            $this->command->error('Pengguna admin tidak ditemukan. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        $monthMap = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
            'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
            'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember',
        ];

        $now = Carbon::now();
        $nowFormatted = str_replace(array_keys($monthMap), array_values($monthMap), $now->format('F Y'));

        $prevMonth = Carbon::now()->subMonth();
        $prevMonthFormatted = str_replace(array_keys($monthMap), array_values($monthMap), $prevMonth->format('F Y'));

        $nextMonth = Carbon::now()->addMonth();
        $nextMonthFormatted = str_replace(array_keys($monthMap), array_values($monthMap), $nextMonth->format('F Y'));

        $periods = [
            [
                'nama' => 'Gaji Bulan ' . $nowFormatted,
                'tanggal_mulai' => $now->startOfMonth(),
                'tanggal_selesai' => $now->endOfMonth(),
                'tanggal_bayar' => $now->endOfMonth()->addDays(5),
                'deskripsi' => 'Periode gaji bulan ' . $nowFormatted,
                'status' => 'konsep', // draft -> konsep
                'dibuat_oleh' => $adminUser->id,
            ],
            [
                'nama' => 'Gaji Bulan ' . $prevMonthFormatted,
                'tanggal_mulai' => $prevMonth->startOfMonth(),
                'tanggal_selesai' => $prevMonth->endOfMonth(),
                'tanggal_bayar' => $prevMonth->endOfMonth()->addDays(5),
                'deskripsi' => 'Periode gaji bulan ' . $prevMonthFormatted,
                'status' => 'terhitung', // calculated -> terhitung
                'dibuat_oleh' => $adminUser->id,
            ],
            [
                'nama' => 'Gaji Bulan ' . $nextMonthFormatted,
                'tanggal_mulai' => $nextMonth->startOfMonth(),
                'tanggal_selesai' => $nextMonth->endOfMonth(),
                'tanggal_bayar' => $nextMonth->endOfMonth()->addDays(5),
                'deskripsi' => 'Periode gaji bulan ' . $nextMonthFormatted,
                'status' => 'konsep', // draft -> konsep
                'dibuat_oleh' => $adminUser->id,
            ],
        ];

        foreach ($periods as $periodData) {
            PayrollPeriod::firstOrCreate(
                [
                    'nama' => $periodData['nama'],
                    'tanggal_mulai' => $periodData['tanggal_mulai'],
                ],
                $periodData
            );
        }

        $this->command->info('✅ Periode penggajian berhasil dibuat');
    }
}
