<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'peran';

    protected $fillable = [
        'nama_kunci', // name
        'nama_tampilan', // display_name
        'deskripsi', // description
        'aktif', // is_active
    ];

    protected $casts = [
        'aktif' => 'boolean', // is_active
    ];

    // Relationships
    public function users()
    {
        return $this->belongsToMany(User::class, 'pengguna_peran', 'id_peran', 'id_pengguna') // user_roles -> pengguna_peran
                    ->withPivot(['ditetapkan_pada', 'kadaluarsa_pada', 'aktif']) // assigned_at, expires_at, is_active
                    ->withTimestamps('dibuat_pada', 'diperbarui_pada'); // created_at, updated_at
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'peran_izin', 'id_peran', 'id_izin') // role_permissions -> peran_izin
                    ->withTimestamps('dibuat_pada', 'diperbarui_pada');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('aktif', true); // is_active -> aktif
    }

    // Helper methods
    public function hasPermission($permissionName)
    {
        // Assuming Permission model's 'name' column is now 'nama_kunci'
        return $this->permissions()->where('nama_kunci', $permissionName)->exists();
    }

    public function givePermissionTo($permission)
    {
        if (is_string($permission)) {
            // Assuming Permission model's 'name' column is now 'nama_kunci'
            $permission = Permission::where('nama_kunci', $permission)->first();
        }

        // Use the (potentially translated) name from the permission object if it exists
        if ($permission && !$this->hasPermission($permission->nama_kunci)) {
            $this->permissions()->attach($permission->id);
        }

        return $this;
    }

    public function revokePermissionTo($permission)
    {
        if (is_string($permission)) {
            // Assuming Permission model's 'name' column is now 'nama_kunci'
            $permission = Permission::where('nama_kunci', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->detach($permission->id);
        }

        return $this;
    }
}
