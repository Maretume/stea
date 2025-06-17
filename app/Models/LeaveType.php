<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $table = 'jenis_cuti';

    protected $fillable = [
        'nama', // name
        'kode', // code
        'maks_hari_per_tahun', // max_days_per_year
        'dibayar', // is_paid
        'perlu_persetujuan', // requires_approval
        'aktif', // is_active
    ];

    protected $casts = [
        'dibayar' => 'boolean', // is_paid
        'perlu_persetujuan' => 'boolean', // requires_approval
        'aktif' => 'boolean', // is_active
    ];

    // Relationships
    public function leaves() // Relates to the old Leave model (cuti table)
    {
        return $this->hasMany(Leave::class, 'id_jenis_cuti');
    }

    public function leaveRequests() // Relates to the new LeaveRequest model (pengajuan_cuti table)
    {
        return $this->hasMany(LeaveRequest::class, 'id_jenis_cuti');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('aktif', true); // is_active -> aktif
    }

    public function scopePaid($query)
    {
        return $query->where('dibayar', true); // is_paid -> dibayar
    }

    // Helper methods
    public function getRemainingDays($userId, $year = null)
    {
        $year = $year ?? now()->year;
        
        // Using leaveRequests() as it's the new standard as per subtask context
        $usedDays = $this->leaveRequests()
                         ->where('id_pengguna', $userId) // user_id -> id_pengguna
                         ->whereYear('tanggal_mulai', $year) // start_date -> tanggal_mulai
                         ->where('status', 'disetujui') // approved -> disetujui
                         ->sum('total_hari'); // total_days -> total_hari
                         
        return max(0, $this->maks_hari_per_tahun - $usedDays); // max_days_per_year -> maks_hari_per_tahun
    }
}
