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
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'module' => 'dashboard'],
            ['name' => 'dashboard.analytics', 'display_name' => 'View Analytics', 'module' => 'dashboard'],
            
            // User Management
            ['name' => 'users.view', 'display_name' => 'View Users', 'module' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'module' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'module' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'module' => 'users'],
            ['name' => 'users.manage_roles', 'display_name' => 'Manage User Roles', 'module' => 'users'],
            
            // Employee Management
            ['name' => 'employees.view', 'display_name' => 'View Employees', 'module' => 'employees'],
            ['name' => 'employees.create', 'display_name' => 'Create Employees', 'module' => 'employees'],
            ['name' => 'employees.edit', 'display_name' => 'Edit Employees', 'module' => 'employees'],
            ['name' => 'employees.delete', 'display_name' => 'Delete Employees', 'module' => 'employees'],
            ['name' => 'employees.view_salary', 'display_name' => 'View Employee Salary', 'module' => 'employees'],
            
            // Department Management
            ['name' => 'departments.view', 'display_name' => 'View Departments', 'module' => 'departments'],
            ['name' => 'departments.create', 'display_name' => 'Create Departments', 'module' => 'departments'],
            ['name' => 'departments.edit', 'display_name' => 'Edit Departments', 'module' => 'departments'],
            ['name' => 'departments.delete', 'display_name' => 'Delete Departments', 'module' => 'departments'],
            
            // Position Management
            ['name' => 'positions.view', 'display_name' => 'View Positions', 'module' => 'positions'],
            ['name' => 'positions.create', 'display_name' => 'Create Positions', 'module' => 'positions'],
            ['name' => 'positions.edit', 'display_name' => 'Edit Positions', 'module' => 'positions'],
            ['name' => 'positions.delete', 'display_name' => 'Delete Positions', 'module' => 'positions'],
            
            // Attendance Management
            ['name' => 'attendance.view', 'display_name' => 'View Attendance', 'module' => 'attendance'],
            ['name' => 'attendance.view_all', 'display_name' => 'View All Attendance', 'module' => 'attendance'],
            ['name' => 'attendance.edit', 'display_name' => 'Edit Attendance', 'module' => 'attendance'],
            ['name' => 'attendance.clock_in_out', 'display_name' => 'Clock In/Out', 'module' => 'attendance'],
            ['name' => 'attendance.reports', 'display_name' => 'View Attendance Reports', 'module' => 'attendance'],
            
            // Leave Management
            ['name' => 'leaves.view', 'display_name' => 'View Leaves', 'module' => 'leaves'],
            ['name' => 'leaves.view_all', 'display_name' => 'View All Leaves', 'module' => 'leaves'],
            ['name' => 'leaves.create', 'display_name' => 'Create Leave Request', 'module' => 'leaves'],
            ['name' => 'leaves.edit', 'display_name' => 'Edit Leave Request', 'module' => 'leaves'],
            ['name' => 'leaves.approve', 'display_name' => 'Approve Leave Request', 'module' => 'leaves'],
            ['name' => 'leaves.reject', 'display_name' => 'Reject Leave Request', 'module' => 'leaves'],
            
            // Payroll Management
            ['name' => 'payroll.view', 'display_name' => 'View Payroll', 'module' => 'payroll'],
            ['name' => 'payroll.view_all', 'display_name' => 'View All Payroll', 'module' => 'payroll'],
            ['name' => 'payroll.create', 'display_name' => 'Create Payroll', 'module' => 'payroll'],
            ['name' => 'payroll.edit', 'display_name' => 'Edit Payroll', 'module' => 'payroll'],
            ['name' => 'payroll.approve', 'display_name' => 'Approve Payroll', 'module' => 'payroll'],
            ['name' => 'payroll.process', 'display_name' => 'Process Payroll', 'module' => 'payroll'],
            ['name' => 'payroll.reports', 'display_name' => 'View Payroll Reports', 'module' => 'payroll'],
            
            // Salary Components
            ['name' => 'salary_components.view', 'display_name' => 'View Salary Components', 'module' => 'salary_components'],
            ['name' => 'salary_components.create', 'display_name' => 'Create Salary Components', 'module' => 'salary_components'],
            ['name' => 'salary_components.edit', 'display_name' => 'Edit Salary Components', 'module' => 'salary_components'],
            ['name' => 'salary_components.delete', 'display_name' => 'Delete Salary Components', 'module' => 'salary_components'],
            
            // Reports
            ['name' => 'reports.view', 'display_name' => 'View Reports', 'module' => 'reports'],
            ['name' => 'reports.financial', 'display_name' => 'View Financial Reports', 'module' => 'reports'],
            ['name' => 'reports.hr', 'display_name' => 'View HR Reports', 'module' => 'reports'],
            ['name' => 'reports.leaves', 'display_name' => 'View Leaves Reports', 'module' => 'reports'],
            ['name' => 'reports.export', 'display_name' => 'Export Reports', 'module' => 'reports'],
            
            // Settings
            ['name' => 'settings.view', 'display_name' => 'View Settings', 'module' => 'settings'],
            ['name' => 'settings.edit', 'display_name' => 'Edit Settings', 'module' => 'settings'],
            ['name' => 'settings.system', 'display_name' => 'System Settings', 'module' => 'settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
