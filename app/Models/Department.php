<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departemen';

    protected $fillable = [
        'kode', // code
        'nama', // name
        'deskripsi', // description
        'aktif', // is_active
    ];

    protected $casts = [
        'aktif' => 'boolean', // is_active
    ];

    // Relationships
    public function employees()
    {
        return $this->hasMany(Employee::class, 'id_departemen'); // foreignKey
    }

    public function positions()
    {
        return $this->hasMany(Position::class, 'id_departemen'); // foreignKey
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('aktif', true); // is_active -> aktif
    }

    // Helper methods
    public function getActiveEmployeesCount()
    {
        // Assuming Employee model uses 'status_kepegawaian' and 'aktif'
        return $this->employees()->where('status_kepegawaian', 'aktif')->count();
    }
}
