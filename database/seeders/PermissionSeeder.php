<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Dashboard
            ['nama_kunci' => 'dashboard.view', 'nama_tampilan' => 'Lihat Dasbor', 'modul' => 'dasbor'],
            ['nama_kunci' => 'dashboard.analytics', 'nama_tampilan' => 'Lihat Analitik', 'modul' => 'dasbor'],
            
            // User Management
            ['nama_kunci' => 'users.view', 'nama_tampilan' => 'Lihat Pengguna', 'modul' => 'pengguna'],
            ['nama_kunci' => 'users.create', 'nama_tampilan' => 'Buat Pengguna', 'modul' => 'pengguna'],
            ['nama_kunci' => 'users.edit', 'nama_tampilan' => 'Ubah Pengguna', 'modul' => 'pengguna'],
            ['nama_kunci' => 'users.delete', 'nama_tampilan' => 'Hapus Pengguna', 'modul' => 'pengguna'],
            ['nama_kunci' => 'users.manage_roles', 'nama_tampilan' => 'Kelola Peran Pengguna', 'modul' => 'pengguna'],
            
            // Employee Management
            ['nama_kunci' => 'employees.view', 'nama_tampilan' => 'Lihat Karyawan', 'modul' => 'karyawan'],
            ['nama_kunci' => 'employees.create', 'nama_tampilan' => 'Buat Karyawan', 'modul' => 'karyawan'],
            ['nama_kunci' => 'employees.edit', 'nama_tampilan' => 'Ubah Karyawan', 'modul' => 'karyawan'],
            ['nama_kunci' => 'employees.delete', 'nama_tampilan' => 'Hapus Karyawan', 'modul' => 'karyawan'],
            ['nama_kunci' => 'employees.view_salary', 'nama_tampilan' => 'Lihat Gaji Karyawan', 'modul' => 'karyawan'],
            
            // Department Management
            ['nama_kunci' => 'departments.view', 'nama_tampilan' => 'Lihat Departemen', 'modul' => 'departemen'],
            ['nama_kunci' => 'departments.create', 'nama_tampilan' => 'Buat Departemen', 'modul' => 'departemen'],
            ['nama_kunci' => 'departments.edit', 'nama_tampilan' => 'Ubah Departemen', 'modul' => 'departemen'],
            ['nama_kunci' => 'departments.delete', 'nama_tampilan' => 'Hapus Departemen', 'modul' => 'departemen'],
            
            // Position Management
            ['nama_kunci' => 'positions.view', 'nama_tampilan' => 'Lihat Jabatan', 'modul' => 'jabatan'],
            ['nama_kunci' => 'positions.create', 'nama_tampilan' => 'Buat Jabatan', 'modul' => 'jabatan'],
            ['nama_kunci' => 'positions.edit', 'nama_tampilan' => 'Ubah Jabatan', 'modul' => 'jabatan'],
            ['nama_kunci' => 'positions.delete', 'nama_tampilan' => 'Hapus Jabatan', 'modul' => 'jabatan'],
            
            // Attendance Management
            ['nama_kunci' => 'attendance.view', 'nama_tampilan' => 'Lihat Absensi', 'modul' => 'absensi'],
            ['nama_kunci' => 'attendance.view_all', 'nama_tampilan' => 'Lihat Semua Absensi', 'modul' => 'absensi'],
            ['nama_kunci' => 'attendance.edit', 'nama_tampilan' => 'Ubah Absensi', 'modul' => 'absensi'],
            ['nama_kunci' => 'attendance.clock_in_out', 'nama_tampilan' => 'Masuk/Keluar Kerja', 'modul' => 'absensi'],
            ['nama_kunci' => 'attendance.reports', 'nama_tampilan' => 'Lihat Laporan Absensi', 'modul' => 'absensi'],
            
            // Leave Management
            ['nama_kunci' => 'leaves.view', 'nama_tampilan' => 'Lihat Cuti', 'modul' => 'cuti'],
            ['nama_kunci' => 'leaves.view_all', 'nama_tampilan' => 'Lihat Semua Cuti', 'modul' => 'cuti'],
            ['nama_kunci' => 'leaves.create', 'nama_tampilan' => 'Buat Permintaan Cuti', 'modul' => 'cuti'],
            ['nama_kunci' => 'leaves.edit', 'nama_tampilan' => 'Ubah Permintaan Cuti', 'modul' => 'cuti'],
            ['nama_kunci' => 'leaves.approve', 'nama_tampilan' => 'Setujui Permintaan Cuti', 'modul' => 'cuti'],
            ['nama_kunci' => 'leaves.reject', 'nama_tampilan' => 'Tolak Permintaan Cuti', 'modul' => 'cuti'],
            
            // Payroll Management
            ['nama_kunci' => 'payroll.view', 'nama_tampilan' => 'Lihat Penggajian', 'modul' => 'penggajian'],
            ['nama_kunci' => 'payroll.view_all', 'nama_tampilan' => 'Lihat Semua Penggajian', 'modul' => 'penggajian'],
            ['nama_kunci' => 'payroll.create', 'nama_tampilan' => 'Buat Penggajian', 'modul' => 'penggajian'],
            ['nama_kunci' => 'payroll.edit', 'nama_tampilan' => 'Ubah Penggajian', 'modul' => 'penggajian'],
            ['nama_kunci' => 'payroll.approve', 'nama_tampilan' => 'Setujui Penggajian', 'modul' => 'penggajian'],
            ['nama_kunci' => 'payroll.process', 'nama_tampilan' => 'Proses Penggajian', 'modul' => 'penggajian'],
            ['nama_kunci' => 'payroll.reports', 'nama_tampilan' => 'Lihat Laporan Penggajian', 'modul' => 'penggajian'],
            
            // Salary Components
            ['nama_kunci' => 'salary_components.view', 'nama_tampilan' => 'Lihat Komponen Gaji', 'modul' => 'komponen_gaji'],
            ['nama_kunci' => 'salary_components.create', 'nama_tampilan' => 'Buat Komponen Gaji', 'modul' => 'komponen_gaji'],
            ['nama_kunci' => 'salary_components.edit', 'nama_tampilan' => 'Ubah Komponen Gaji', 'modul' => 'komponen_gaji'],
            ['nama_kunci' => 'salary_components.delete', 'nama_tampilan' => 'Hapus Komponen Gaji', 'modul' => 'komponen_gaji'],
            
            // Reports
            ['nama_kunci' => 'reports.view', 'nama_tampilan' => 'Lihat Laporan', 'modul' => 'laporan'],
            ['nama_kunci' => 'reports.financial', 'nama_tampilan' => 'Lihat Laporan Keuangan', 'modul' => 'laporan'],
            ['nama_kunci' => 'reports.hr', 'nama_tampilan' => 'Lihat Laporan SDM', 'modul' => 'laporan'],
            ['nama_kunci' => 'reports.export', 'nama_tampilan' => 'Ekspor Laporan', 'modul' => 'laporan'],
            
            // Settings
            ['nama_kunci' => 'settings.view', 'nama_tampilan' => 'Lihat Pengaturan', 'modul' => 'pengaturan'],
            ['nama_kunci' => 'settings.edit', 'nama_tampilan' => 'Ubah Pengaturan', 'modul' => 'pengaturan'],
            ['nama_kunci' => 'settings.system', 'nama_tampilan' => 'Pengaturan Sistem', 'modul' => 'pengaturan'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
