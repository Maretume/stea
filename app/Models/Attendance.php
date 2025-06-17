<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    protected $fillable = [
        'id_pengguna', // user_id
        'tanggal', // date
        'jam_masuk', // clock_in
        'jam_keluar', // clock_out
        'total_menit_kerja', // total_work_minutes
        'menit_terlambat', // late_minutes
        'menit_masuk_awal', // early_minutes (assuming this was the 'early_minutes' from migration 2025_06_11_051703)
        'menit_pulang_awal', // early_leave_minutes
        'menit_lembur', // overtime_minutes
        'status',
        'ip_jam_masuk', // clock_in_ip
        'ip_jam_keluar', // clock_out_ip
        'lat_jam_masuk', // clock_in_lat
        'lng_jam_masuk', // clock_in_lng
        'lat_jam_keluar', // clock_out_lat
        'lng_jam_keluar', // clock_out_lng
        'id_kantor', // office_id
    ];

    protected $casts = [
        'tanggal' => 'date', // date
        'jam_masuk' => 'datetime:H:i:s', // clock_in
        'jam_keluar' => 'datetime:H:i:s', // clock_out
        'lat_jam_masuk' => 'decimal:8', // clock_in_lat
        'lng_jam_masuk' => 'decimal:8', // clock_in_lng
        'lat_jam_keluar' => 'decimal:8', // clock_out_lat
        'lng_jam_keluar' => 'decimal:8', // clock_out_lng
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'id_kantor');
    }

    public function schedule()
    {
        // Assuming Schedule model and its columns 'id_pengguna' and 'tanggal_jadwal' are translated.
        // $this->tanggal refers to the 'tanggal' attribute of this Attendance (absensi) model.
        return $this->hasOne(Schedule::class, 'id_pengguna', 'id_pengguna')
                    ->where('tanggal_jadwal', $this->tanggal);
    }

    // Scopes
    public function scopeByDate($query, $date)
    {
        return $query->where('tanggal', $date); // date -> tanggal
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]); // date -> tanggal
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('id_pengguna', $userId); // user_id -> id_pengguna
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Helper methods
    public function isPresent()
    {
        // Enum: ['hadir', 'absen', 'terlambat', 'pulang_awal', 'setengah_hari', 'sakit', 'cuti', 'libur']
        return in_array($this->status, ['hadir', 'terlambat', 'pulang_awal', 'setengah_hari']);
    }

    public function isLate()
    {
        return $this->menit_terlambat > 0 || $this->status === 'terlambat';
    }

    public function isEarly() // Refers to clocking in early
    {
        return $this->menit_masuk_awal > 0;
    }

    public function isEarlyLeave()
    {
        return $this->menit_pulang_awal > 0 || $this->status === 'pulang_awal';
    }

    public function hasOvertime()
    {
        return $this->menit_lembur > 0;
    }

    public function getStatusIndonesian()
    {
        // Keys are the Indonesian ENUM values stored in the database
        $statusLabels = [
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'pulang_awal' => 'Pulang Awal',
            'absen' => 'Alpha',
            'sakit' => 'Sakit',
            'cuti' => 'Cuti',
            'libur' => 'Libur',
            'setengah_hari' => 'Setengah Hari'
        ];

        return $statusLabels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColor()
    {
        // Keys are the Indonesian ENUM values
        $statusColors = [
            'hadir' => 'success',
            'terlambat' => 'warning',
            'pulang_awal' => 'info',
            'absen' => 'danger',
            'sakit' => 'secondary',
            'cuti' => 'primary',
            'libur' => 'dark',
            'setengah_hari' => 'light'
        ];

        return $statusColors[$this->status] ?? 'secondary';
    }

    public function getTotalWorkHoursAttribute()
    {
        return round($this->total_menit_kerja / 60, 2); // total_work_minutes -> total_menit_kerja
    }

    public function getTotalOvertimeHoursAttribute()
    {
        return round($this->menit_lembur / 60, 2); // overtime_minutes -> menit_lembur
    }

    public function getWorkDurationAttribute()
    {
        if (!$this->jam_masuk || !$this->jam_keluar) { // clock_in -> jam_masuk, clock_out -> jam_keluar
            return null;
        }

        $clockIn = Carbon::parse($this->jam_masuk); // clock_in -> jam_masuk
        $clockOut = Carbon::parse($this->jam_keluar); // clock_out -> jam_keluar
        
        return $clockOut->diff($clockIn)->format('%H:%I');
    }
}
