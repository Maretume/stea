<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;
use App\Models\Permission;

return new class extends Migration
{
    public function up()
    {
        // Get karyawan role
        $karyawanRole = Role::where('nama_kunci', 'karyawan')->first();
        
        if ($karyawanRole) {
            // Remove all schedule management permissions except view from karyawan role
            // Permission names (name column) are keys and remain in English
            $permissionsToRemove = Permission::whereIn('name', [
                'schedules.create', 'schedules.edit', 'schedules.delete', 'schedules.approve',
                'offices.view', 'offices.create', 'offices.edit', 'offices.delete',
                'shifts.view', 'shifts.create', 'shifts.edit', 'shifts.delete'
            ])->get();

            $karyawanRole->permissions()->detach($permissionsToRemove);

            // Keep only schedules.view permission for karyawan (to view their own schedules)
            $viewSchedulePermission = Permission::where('name', 'schedules.view')->first();
            if ($viewSchedulePermission) {
                $karyawanRole->permissions()->syncWithoutDetaching([$viewSchedulePermission->id]);
            }

            echo "Izin manajemen jadwal dihapus dari peran karyawan, izin lihat dipertahankan\n";
        }
        
        // Ensure only admin, hr, hrd, and ceo have schedule management permissions
        $adminRoles = Role::whereIn('nama_kunci', ['admin', 'hr', 'hrd', 'ceo'])->get();
        // Permission names (name column) are keys and remain in English
        $allSchedulePermissions = Permission::whereIn('name', [
            'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete', 'schedules.approve',
            'offices.view', 'offices.create', 'offices.edit', 'offices.delete',
            'shifts.view', 'shifts.create', 'shifts.edit', 'shifts.delete'
        ])->get();
        
        foreach ($adminRoles as $role) {
            $role->permissions()->syncWithoutDetaching($allSchedulePermissions);
        }
        
        // Give limited permissions to manager (view and approve only)
        $managerRole = Role::where('nama_kunci', 'manager')->first();
        if ($managerRole) {
            // Permission names (name column) are keys and remain in English
            $managerPermissions = Permission::whereIn('name', [
                'schedules.view', 'schedules.approve',
                'offices.view', 'shifts.view'
            ])->get();
            
            // Remove all schedule permissions first
            // Permission names (name column) are keys and remain in English
            $allSchedulePermissions = Permission::whereIn('name', [
                'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete', 'schedules.approve',
                'offices.view', 'offices.create', 'offices.edit', 'offices.delete',
                'shifts.view', 'shifts.create', 'shifts.edit', 'shifts.delete'
            ])->get();
            $managerRole->permissions()->detach($allSchedulePermissions);
            
            // Then add only the allowed ones
            $managerRole->permissions()->syncWithoutDetaching($managerPermissions);
        }
    }

    public function down()
    {
        // Restore view permissions to karyawan role
        $karyawanRole = Role::where('nama_kunci', 'karyawan')->first();
        
        if ($karyawanRole) {
            // Permission names (name column) are keys and remain in English
            $viewPermissions = Permission::whereIn('name', [
                'schedules.view', 'offices.view', 'shifts.view'
            ])->get();
            
            $karyawanRole->permissions()->syncWithoutDetaching($viewPermissions);
        }
    }
};
