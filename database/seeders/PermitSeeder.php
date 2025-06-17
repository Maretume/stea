<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermitType;

use App\Models\OvertimeRequest;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;

class PermitSeeder extends Seeder
{
    public function run()
    {
        // Create Permit Types
        $permitTypes = [
            [
                'nama' => 'Tukar Hari',
                'kode' => 'DAY_EXCHANGE',
                'deskripsi' => 'Pertukaran hari kerja dengan hari libur',
                'perlu_persetujuan' => true,
                'pengaruhi_absensi' => true,
                'aktif' => true,
                'urutan' => 1,
            ],
            [
                'nama' => 'Lembur',
                'kode' => 'OVERTIME',
                'deskripsi' => 'Kerja lembur di luar jam kerja normal',
                'perlu_persetujuan' => true,
                'pengaruhi_absensi' => false,
                'aktif' => true,
                'urutan' => 2,
            ],
            [
                'nama' => 'Izin Keluar',
                'kode' => 'LEAVE_EARLY',
                'deskripsi' => 'Izin keluar sebelum jam kerja selesai',
                'perlu_persetujuan' => true,
                'pengaruhi_absensi' => true,
                'aktif' => true,
                'urutan' => 3,
            ],
            [
                'nama' => 'Izin Datang Terlambat',
                'kode' => 'LATE_ARRIVAL',
                'deskripsi' => 'Izin datang terlambat dengan alasan tertentu',
                'perlu_persetujuan' => true,
                'pengaruhi_absensi' => true,
                'aktif' => true,
                'urutan' => 4,
            ],
            [
                'nama' => 'Izin Tidak Masuk',
                'kode' => 'ABSENT',
                'deskripsi' => 'Izin tidak masuk kerja (bukan cuti)',
                'perlu_persetujuan' => true,
                'pengaruhi_absensi' => true,
                'aktif' => true,
                'urutan' => 5,
            ],
        ];

        foreach ($permitTypes as $type) {
            PermitType::create($type);
        }

        // Get sample users
        $employees = User::whereHas('employee')->take(5)->get(); // Assuming 'employee' relation exists
        
        if ($employees->count() > 0) {
            // Create sample Overtime requests
            foreach ($employees->take(4) as $employee) {
                OvertimeRequest::create([
                    'id_pengguna' => $employee->id,
                    'tanggal_lembur' => now()->addDays(rand(1, 7)),
                    'waktu_mulai' => '17:00',
                    'waktu_selesai' => '20:00',
                    'jam_direncanakan' => 3,
                    'deskripsi_pekerjaan' => 'Menyelesaikan laporan bulanan dan persiapan presentasi untuk klien.',
                    'alasan' => 'Deadline laporan yang mendesak dan perlu diselesaikan segera.',
                    'status' => 'menunggu', // pending
                ]);

                OvertimeRequest::create([
                    'id_pengguna' => $employee->id,
                    'tanggal_lembur' => now()->subDays(rand(1, 7)),
                    'waktu_mulai' => '17:30',
                    'waktu_selesai' => '21:30',
                    'jam_direncanakan' => 4,
                    'jam_aktual' => 4,
                    'deskripsi_pekerjaan' => 'Maintenance server dan backup database.',
                    'alasan' => 'Maintenance rutin yang harus dilakukan di luar jam kerja.',
                    'status' => 'selesai', // completed
                    'disetujui_oleh' => User::whereHas('roles', function($q) {
                        $q->where('nama_kunci', 'hrd'); // name -> nama_kunci
                    })->first()->id ?? null,
                    'disetujui_pada' => now()->subDays(rand(1, 5)),
                    'catatan_persetujuan' => 'Disetujui. Pastikan dokumentasi maintenance lengkap.',
                    'apakah_selesai' => true,
                    'selesai_pada' => now()->subDays(rand(1, 3)),
                    'tarif_lembur' => 25000,
                    'jumlah_lembur' => 100000,
                ]);
            }

            // Create sample Leave requests
            foreach ($employees as $employee) {
                LeaveRequest::create([
                    'id_pengguna' => $employee->id,
                    'id_jenis_cuti' => 1, // Assuming ID 1 for Cuti Tahunan from LeaveTypeSeeder
                    'tanggal_mulai' => now()->addDays(rand(10, 30)),
                    'tanggal_selesai' => now()->addDays(rand(31, 35)),
                    'total_hari' => 3,
                    'alasan' => 'Liburan keluarga yang sudah direncanakan sejak lama.',
                    'catatan' => 'Sudah koordinasi dengan tim untuk backup pekerjaan.',
                    'kontak_darurat' => 'Istri - Sarah',
                    'telepon_darurat' => '081234567890',
                    'serah_terima_pekerjaan' => 'Pekerjaan harian sudah didelegasikan ke rekan tim. Laporan mingguan akan diselesaikan sebelum cuti.',
                    'status' => 'menunggu', // pending
                ]);

                LeaveRequest::create([
                    'id_pengguna' => $employee->id,
                    'id_jenis_cuti' => 2, // Assuming ID 2 for Cuti Sakit
                    'tanggal_mulai' => now()->subDays(rand(5, 10)),
                    'tanggal_selesai' => now()->subDays(rand(3, 4)),
                    'total_hari' => 2,
                    'alasan' => 'Sakit demam dan flu yang cukup parah.',
                    'catatan' => 'Sudah periksa ke dokter dan disarankan istirahat total.',
                    'status' => 'disetujui', // approved
                    'disetujui_oleh' => User::whereHas('roles', function($q) {
                        $q->where('nama_kunci', 'hrd'); // name -> nama_kunci
                    })->first()->id ?? null,
                    'disetujui_pada' => now()->subDays(rand(1, 3)),
                    'catatan_persetujuan' => 'Disetujui. Harap istirahat yang cukup dan segera sembuh.',
                    'lampiran' => [
                        [
                            'filename' => 'surat_dokter_' . time() . '.pdf',
                            'original_name' => 'Surat Keterangan Dokter.pdf',
                            'size' => 245760,
                            'uploaded_at' => now()->subDays(rand(1, 3))->toISOString(),
                        ]
                    ],
                ]);

                // Half day leave
                LeaveRequest::create([
                    'id_pengguna' => $employee->id,
                    'id_jenis_cuti' => 1, // Cuti Tahunan
                    'tanggal_mulai' => now()->addDays(rand(5, 15)),
                    'tanggal_selesai' => now()->addDays(rand(5, 15)),
                    'total_hari' => 0.5, // total_days is numeric
                    'alasan' => 'Ada keperluan keluarga yang mendesak.',
                    'status' => 'menunggu', // pending
                    'setengah_hari' => true,
                    'tipe_setengah_hari' => 'siang', // afternoon -> siang
                ]);
            }
        }

        $this->command->info('Data izin berhasil di-seed!');
    }
}
