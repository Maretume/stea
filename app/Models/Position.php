<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $table = 'jabatan';

    protected $fillable = [
        'kode', // code
        'nama', // name
        'deskripsi', // description
        'id_departemen', // department_id
        'gaji_pokok', // base_salary
        'tingkat', // level
        'aktif', // is_active
    ];

    protected $casts = [
        'gaji_pokok' => 'decimal:2', // base_salary
        'aktif' => 'boolean', // is_active
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class, 'id_departemen'); // foreignKey
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'id_jabatan'); // foreignKey
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('aktif', true); // is_active -> aktif
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('id_departemen', $departmentId); // department_id -> id_departemen
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }
}
