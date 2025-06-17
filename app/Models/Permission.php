<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'izin';

    protected $fillable = [
        'nama_kunci', // name
        'nama_tampilan', // display_name
        'modul', // module
        'deskripsi', // description
    ];

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'peran_izin', 'id_izin', 'id_peran') // role_permissions -> peran_izin
                    ->withTimestamps('dibuat_pada', 'diperbarui_pada');
    }

    // Scopes
    public function scopeByModule($query, $module)
    {
        return $query->where('modul', $module); // module column name is already 'modul'
    }
}
