<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Admin - Super Admin Full Access
        $admin = Role::where('name', 'admin')->first();
        $allPermissions = Permission::all();
        $admin->permissions()->attach($allPermissions->pluck('id'));

        // CEO - Full Access
        $ceo = Role::where('name', 'ceo')->first();
        $ceoPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'reports.view',
            'reports.financial',
            'reports.hr',
            'reports.export',
            'attendance.reports', // Specific report permission
            'payroll.reports',    // Specific report permission
            'reports.leaves',     // New permission for leaves report
        ])->get();
        if ($ceo && $ceoPermissions->isNotEmpty()) {
            $ceo->permissions()->sync($ceoPermissions->pluck('id')); // Use sync to remove old permissions and add new ones
        }

        // CFO - Financial Focus
        $cfo = Role::where('name', 'cfo')->first();
        $cfoPermissions = Permission::whereIn('name', [
            'dashboard.view', 'dashboard.analytics',
            'employees.view', 'employees.view_salary',
            'payroll.view_all', 'payroll.approve', 'payroll.reports', 'payroll.create',
            'reports.view', 'reports.financial', 'reports.export',
            'attendance.reports',
        ])->get();
        $cfo->permissions()->attach($cfoPermissions->pluck('id'));

        // HRD - HR Management
        $hrd = Role::where('name', 'hrd')->first();
        $hrdPermissions = Permission::whereIn('name', [
            'dashboard.view', 'dashboard.analytics',
            'users.view', 'users.create', 'users.edit', 'users.manage_roles',
            'employees.view', 'employees.create', 'employees.edit', 'employees.view_salary',
            'departments.view', 'departments.create', 'departments.edit',
            'positions.view', 'positions.create', 'positions.edit',
            'attendance.view_all', 'attendance.edit', 'attendance.reports',
            'leaves.view_all', 'leaves.approve', 'leaves.reject',
            'payroll.view_all', 'payroll.create', 'payroll.edit', 'payroll.process', 'payroll.reports',
            'salary_components.view', 'salary_components.create', 'salary_components.edit',
            'reports.view', 'reports.hr', 'reports.export',
            'settings.view', 'settings.edit',
        ])->get();
        $hrd->permissions()->attach($hrdPermissions->pluck('id'));

        // Personalia - Data Entry & Basic HR
        $personalia = Role::where('name', 'personalia')->first();
        $personaliaPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'employees.view', 'employees.create', 'employees.edit',
            'departments.view', 'positions.view',
            'attendance.view_all', 'attendance.edit',
            'leaves.view_all',
            'payroll.view',
        ])->get();
        $personalia->permissions()->attach($personaliaPermissions->pluck('id'));

        // Karyawan - Self Service
        $karyawan = Role::where('name', 'karyawan')->first();
        $karyawanPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'attendance.view', 'attendance.clock_in_out',
            'leaves.view', 'leaves.create', 'leaves.edit',
            'payroll.view',
        ])->get();
        $karyawan->permissions()->attach($karyawanPermissions->pluck('id'));
    }
}
