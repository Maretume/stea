<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'jadwal';

    protected $fillable = [
        'id_pengguna', // user_id
        'id_shift', // shift_id
        'id_kantor', // office_id
        'tanggal_jadwal', // schedule_date
        'tipe_kerja', // work_type
        'status',
        'catatan', // notes
        'dibuat_oleh', // created_by
        'disetujui_oleh', // approved_by
        'disetujui_pada', // approved_at
    ];

    protected $casts = [
        'tanggal_jadwal' => 'date', // schedule_date
        'disetujui_pada' => 'datetime', // approved_at
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'id_shift');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'id_kantor');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('id_pengguna', $userId); // user_id -> id_pengguna
    }

    public function scopeByShift($query, $shiftId)
    {
        return $query->where('id_shift', $shiftId); // shift_id -> id_shift
    }

    public function scopeByOffice($query, $officeId)
    {
        return $query->where('id_kantor', $officeId); // office_id -> id_kantor
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('tanggal_jadwal', $date); // schedule_date -> tanggal_jadwal
    }

    public function scopeByWorkType($query, $workType)
    {
        return $query->where('work_type', $workType);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    public function scopeWFO($query)
    {
        return $query->where('work_type', 'WFO');
    }

    public function scopeWFA($query)
    {
        return $query->where('work_type', 'WFA');
    }

    // Helper methods
    public function canClockInAtLocation($latitude, $longitude)
    {
        // WFA can clock in from anywhere
        if ($this->work_type === 'WFA') {
            return true;
        }

        // WFO must be within office radius
        if ($this->work_type === 'WFO' && $this->office) {
            return $this->office->isWithinRadius($latitude, $longitude);
        }

        return false;
    }

    public function getDistanceFromOffice($latitude, $longitude)
    {
        if (!$this->office) {
            return null;
        }

        return $this->office->calculateDistance($latitude, $longitude);
    }

    public function isWFO()
    {
        return $this->work_type === 'WFO';
    }

    public function isWFA()
    {
        return $this->work_type === 'WFA';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isScheduled()
    {
        return $this->status === 'scheduled';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function requiresLocationValidation()
    {
        return $this->work_type === 'WFO';
    }

    public function getWorkTypeDisplayAttribute()
    {
        return $this->work_type === 'WFO' ? 'Work From Office' : 'Work From Anywhere';
    }
}
