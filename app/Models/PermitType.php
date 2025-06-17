<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermitType extends Model
{
    use HasFactory;

    protected $table = 'jenis_izin_kerja';

    protected $fillable = [
        'nama', // name
        'kode', // code
        'deskripsi', // description
        'perlu_persetujuan', // requires_approval
        'pengaruhi_absensi', // affects_attendance
        'aktif', // is_active
        'urutan', // sort_order
    ];

    protected $casts = [
        'perlu_persetujuan' => 'boolean',
        'pengaruhi_absensi' => 'boolean',
        'aktif' => 'boolean',
    ];

    // Relationships
    public function permits()
    {
        // Assuming Permit model will point to 'izin_kerja' table
        return $this->hasMany(\App\Models\Permit::class, 'id_jenis_izin_kerja');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('aktif', true); // is_active -> aktif
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('nama'); // sort_order -> urutan, name -> nama
    }

    // Helper methods
    public function isActive()
    {
        return $this->aktif; // is_active -> aktif
    }

    public function requiresApproval()
    {
        return $this->perlu_persetujuan; // requires_approval -> perlu_persetujuan
    }

    public function affectsAttendance()
    {
        return $this->pengaruhi_absensi; // affects_attendance -> pengaruhi_absensi
    }
}
