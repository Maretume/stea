<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shift';

    protected $fillable = [
        'nama', // name
        'waktu_mulai', // start_time
        'waktu_selesai', // end_time
        'aktif', // is_active
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime:H:i:s', // start_time
        'waktu_selesai' => 'datetime:H:i:s', // end_time
        'aktif' => 'boolean', // is_active
    ];

    // Relationships
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_shift'); // foreignKey
    }

    public function users()
    {
        // Shift -> Schedule (id_shift on schedules, id on shifts)
        // Schedule -> User (id_pengguna on schedules, id on users)
        return $this->hasManyThrough(
            User::class,
            Schedule::class,
            'id_shift',     // Foreign key on Schedule table (intermediate table)
            'id',           // Foreign key on User table (far table)
            'id',           // Local key on Shift table (this table)
            'id_pengguna'   // Local key on Schedule table (intermediate table)
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('aktif', true); // is_active -> aktif
    }

    // Helper methods
    public function calculateLateMinutes($clockInTime)
    {
        $startTime = Carbon::parse($this->waktu_mulai); // start_time -> waktu_mulai
        $clockIn = Carbon::parse($clockInTime);

        if ($clockIn->gt($startTime)) {
            // Get tolerance from default attendance rule
            // Assuming AttendanceRule model uses 'standar' and 'toleransi_keterlambatan_menit'
            $defaultRule = \App\Models\AttendanceRule::where('standar', true)->first();
            $toleranceMinutes = $defaultRule ? $defaultRule->toleransi_keterlambatan_menit : 15;

            // Hitung menit terlambat, dikurangi toleransi
            // Pastikan perhitungan yang benar: clock_in - start_time
            $diffInSeconds = $clockIn->timestamp - $startTime->timestamp;
            $lateMinutes = $diffInSeconds / 60;
            return max(0, $lateMinutes - $toleranceMinutes);
        }

        return 0;
    }

    /**
     * Calculate attendance status based on clock in time
     * Returns: 'early', 'on_time', 'late'
     */
    public function calculateAttendanceStatus($clockInTime)
    {
        $startTime = Carbon::parse($this->waktu_mulai); // start_time -> waktu_mulai
        $clockIn = Carbon::parse($clockInTime);

        // Get tolerance from default attendance rule
        // Assuming AttendanceRule model uses 'standar' and 'toleransi_keterlambatan_menit'
        $defaultRule = \App\Models\AttendanceRule::where('standar', true)->first();
        $toleranceMinutes = $defaultRule ? $defaultRule->toleransi_keterlambatan_menit : 15;

        if ($clockIn->lt($startTime)) {
            return 'early'; // Terlalu dini
        } elseif ($clockIn->eq($startTime)) {
            return 'on_time'; // Tepat waktu
        } else {
            // Hitung selisih menit setelah waktu mulai shift
            // Pastikan perhitungan yang benar: clock_in - start_time
            $diffInSeconds = $clockIn->timestamp - $startTime->timestamp;
            $lateMinutes = $diffInSeconds / 60;

            if ($lateMinutes <= $toleranceMinutes) {
                return 'on_time'; // Tepat waktu (dengan toleransi)
            } else {
                return 'late'; // Terlambat
            }
        }
    }

    /**
     * Get early minutes (how many minutes before start time)
     */
    public function calculateEarlyMinutes($clockInTime)
    {
        $startTime = Carbon::parse($this->waktu_mulai); // start_time -> waktu_mulai
        $clockIn = Carbon::parse($clockInTime);

        if ($clockIn->lt($startTime)) {
            return $startTime->diffInMinutes($clockIn);
        }

        return 0;
    }

    public function calculateEarlyLeaveMinutes($clockOutTime)
    {
        $endTime = Carbon::parse($this->waktu_selesai); // end_time -> waktu_selesai
        $clockOut = Carbon::parse($clockOutTime);

        if ($clockOut->lt($endTime)) {
            // Get tolerance from default attendance rule
            // Assuming AttendanceRule model uses 'standar' and 'toleransi_pulang_awal_menit'
            $defaultRule = \App\Models\AttendanceRule::where('standar', true)->first();
            $toleranceMinutes = $defaultRule ? $defaultRule->toleransi_pulang_awal_menit : 15;

            // Pastikan perhitungan yang benar: end_time - clock_out
            $diffInSeconds = $endTime->timestamp - $clockOut->timestamp;
            $earlyMinutes = $diffInSeconds / 60;
            return max(0, $earlyMinutes - $toleranceMinutes);
        }

        return 0;
    }

    public function calculateOvertimeMinutes($clockOutTime)
    {
        $endTime = Carbon::parse($this->waktu_selesai); // end_time -> waktu_selesai
        $clockOut = Carbon::parse($clockOutTime);

        if ($clockOut->gt($endTime)) {
            // Pastikan perhitungan yang benar: clock_out - end_time
            $diffInSeconds = $clockOut->timestamp - $endTime->timestamp;
            $overtimeMinutes = $diffInSeconds / 60;
            return max(0, $overtimeMinutes);
        }

        return 0;
    }

    public function getWorkDurationMinutes()
    {
        $startTime = Carbon::parse($this->waktu_mulai); // start_time -> waktu_mulai
        $endTime = Carbon::parse($this->waktu_selesai); // end_time -> waktu_selesai

        return $endTime->diffInMinutes($startTime);
    }

    public function getFormattedStartTimeAttribute()
    {
        return Carbon::parse($this->waktu_mulai)->format('H:i'); // start_time -> waktu_mulai
    }

    public function getFormattedEndTimeAttribute()
    {
        return Carbon::parse($this->waktu_selesai)->format('H:i'); // end_time -> waktu_selesai
    }

    public function getShiftDurationAttribute()
    {
        $startTime = Carbon::parse($this->waktu_mulai); // start_time -> waktu_mulai
        $endTime = Carbon::parse($this->waktu_selesai); // end_time -> waktu_selesai
        
        return $endTime->diff($startTime)->format('%H:%I');
    }
}
