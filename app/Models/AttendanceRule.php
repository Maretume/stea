<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRule extends Model
{
    use HasFactory;

    protected $table = 'aturan_absensi';

    protected $fillable = [
        'nama', // name
        'jam_mulai_kerja', // work_start_time
        'jam_selesai_kerja', // work_end_time
        'toleransi_keterlambatan_menit', // late_tolerance_minutes
        'toleransi_pulang_awal_menit', // early_leave_tolerance_minutes
        'pengali_lembur', // overtime_multiplier
        'standar', // is_default
        'aktif', // is_active
        // break_start_time and break_end_time are removed as per migrations
    ];

    protected $casts = [
        'jam_mulai_kerja' => 'datetime:H:i:s', // work_start_time
        'jam_selesai_kerja' => 'datetime:H:i:s', // work_end_time
        'pengali_lembur' => 'decimal:2', // overtime_multiplier
        'standar' => 'boolean', // is_default
        'aktif' => 'boolean', // is_active
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('aktif', true); // is_active -> aktif
    }

    public function scopeDefault($query)
    {
        return $query->where('standar', true); // is_default -> standar
    }

    // Helper methods
    public function getWorkingHoursAttribute()
    {
        $start = \Carbon\Carbon::parse($this->jam_mulai_kerja); // work_start_time -> jam_mulai_kerja
        $end = \Carbon\Carbon::parse($this->jam_selesai_kerja); // work_end_time -> jam_selesai_kerja
        
        $totalMinutes = $end->diffInMinutes($start);
        // Break time columns were removed, so logic for subtracting breakMinutes is removed.
        
        return round($totalMinutes / 60, 2);
    }

    public function calculateLateMinutes($clockInTime)
    {
        $workStart = \Carbon\Carbon::parse($this->jam_mulai_kerja); // work_start_time -> jam_mulai_kerja
        $clockIn = \Carbon\Carbon::parse($clockInTime);
        
        if ($clockIn->gt($workStart)) {
            $lateMinutes = $clockIn->diffInMinutes($workStart);
            return max(0, $lateMinutes - $this->toleransi_keterlambatan_menit); // late_tolerance_minutes -> toleransi_keterlambatan_menit
        }
        
        return 0;
    }

    public function calculateEarlyLeaveMinutes($clockOutTime)
    {
        $workEnd = \Carbon\Carbon::parse($this->jam_selesai_kerja); // work_end_time -> jam_selesai_kerja
        $clockOut = \Carbon\Carbon::parse($clockOutTime);
        
        if ($clockOut->lt($workEnd)) {
            $earlyMinutes = $workEnd->diffInMinutes($clockOut);
            return max(0, $earlyMinutes - $this->toleransi_pulang_awal_menit); // early_leave_tolerance_minutes -> toleransi_pulang_awal_menit
        }
        
        return 0;
    }

    public function calculateOvertimeMinutes($clockOutTime)
    {
        $workEnd = \Carbon\Carbon::parse($this->jam_selesai_kerja); // work_end_time -> jam_selesai_kerja
        $clockOut = \Carbon\Carbon::parse($clockOutTime);
        
        if ($clockOut->gt($workEnd)) {
            return $clockOut->diffInMinutes($workEnd);
        }
        
        return 0;
    }
}
