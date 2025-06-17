<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OvertimeRequest extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_lembur';

    protected $fillable = [
        'id_pengguna', // user_id
        'tanggal_lembur', // overtime_date
        'waktu_mulai', // start_time
        'waktu_selesai', // end_time
        'jam_direncanakan', // planned_hours
        'jam_aktual', // actual_hours
        'deskripsi_pekerjaan', // work_description
        'alasan', // reason
        'status',
        'disetujui_oleh', // approved_by
        'disetujui_pada', // approved_at
        'catatan_persetujuan', // approval_notes
        'apakah_selesai', // is_completed
        'selesai_pada', // completed_at
        'tarif_lembur', // overtime_rate
        'jumlah_lembur', // overtime_amount
    ];

    protected $casts = [
        'tanggal_lembur' => 'date',
        'waktu_mulai' => 'datetime:H:i:s',
        'waktu_selesai' => 'datetime:H:i:s',
        'jam_direncanakan' => 'decimal:2',
        'jam_aktual' => 'decimal:2',
        'disetujui_pada' => 'datetime',
        'selesai_pada' => 'datetime',
        'apakah_selesai' => 'boolean',
        'tarif_lembur' => 'decimal:2',
        'jumlah_lembur' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function approvals()
    {
        // As per previous model, assuming 'approvable' is standard.
        // If PermitApproval model's morph keys are 'id_persetujuan', 'tipe_persetujuan',
        // this would be morphMany(PermitApproval::class, 'persetujuan', 'tipe_persetujuan', 'id_persetujuan');
        // For now, keeping it as 'approvable'.
        return $this->morphMany(PermitApproval::class, 'approvable');
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('id_pengguna', $userId); // user_id -> id_pengguna
    }

    public function scopeByStatus($query, $status)
    {
        // Assuming $status is already translated
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'menunggu'); // pending -> menunggu
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'disetujui'); // approved -> disetujui
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'selesai'); // completed -> selesai
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_lembur', [$startDate, $endDate]); // overtime_date -> tanggal_lembur
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('tanggal_lembur', now()->month) // overtime_date -> tanggal_lembur
                    ->whereYear('tanggal_lembur', now()->year); // overtime_date -> tanggal_lembur
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'menunggu'; // pending -> menunggu
    }

    public function isApproved()
    {
        return $this->status === 'disetujui'; // approved -> disetujui
    }

    public function isRejected()
    {
        return $this->status === 'ditoLak'; // rejected -> ditoLak
    }

    public function isCompleted()
    {
        // completed -> selesai, is_completed -> apakah_selesai
        return $this->status === 'selesai' || $this->apakah_selesai;
    }

    public function canBeEdited()
    {
        // pending -> menunggu, overtime_date -> tanggal_lembur
        return $this->status === 'menunggu' && $this->tanggal_lembur->gte(today());
    }

    public function canBeApproved()
    {
        return $this->status === 'menunggu'; // pending -> menunggu
    }

    public function canBeCompleted()
    {
        // approved -> disetujui, is_completed -> apakah_selesai
        return $this->status === 'disetujui' && !$this->apakah_selesai;
    }

    public function getStatusBadgeAttribute()
    {
        // Keys should be the Indonesian ENUM values
        $badges = [
            'menunggu' => 'warning',
            'disetujui' => 'success',
            'ditoLak' => 'danger',
            'selesai' => 'info',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        // Keys should be the Indonesian ENUM values
        $texts = [
            'menunggu' => 'Menunggu Persetujuan',
            'disetujui' => 'Disetujui',
            'ditoLak' => 'Ditolak',
            'selesai' => 'Selesai',
        ];

        return $texts[$this->status] ?? 'Tidak Diketahui';
    }

    // Calculation methods
    public function calculatePlannedHours()
    {
        if (!$this->waktu_mulai || !$this->waktu_selesai) { // start_time -> waktu_mulai, end_time -> waktu_selesai
            return 0;
        }

        $start = Carbon::parse($this->waktu_mulai); // start_time -> waktu_mulai
        $end = Carbon::parse($this->waktu_selesai); // end_time -> waktu_selesai

        // If end time is before start time, assume it's next day
        if ($end->lt($start)) {
            $end->addDay();
        }

        return $end->diffInHours($start, true);
    }

    public function calculateOvertimeAmount($hourlyRate = null)
    {
        $hours = $this->jam_aktual ?? $this->jam_direncanakan; // actual_hours -> jam_aktual, planned_hours -> jam_direncanakan

        if (!$hours) {
            return 0;
        }

        if (!$hourlyRate) {
            $hourlyRate = $this->calculateHourlyRate();
        }

        // Apply overtime multiplier (1.5x for regular overtime)
        $overtimeRate = $hourlyRate * 1.5;

        return $hours * $overtimeRate;
    }

    public function calculateHourlyRate()
    {
        // Calculate hourly rate from basic salary
        // Assumes User model has 'employee' relation and Employee model has 'gaji_pokok'
        $basicSalary = $this->user->employee->gaji_pokok ?? 0;

        if ($basicSalary <= 0) {
            return 0;
        }

        // 173 = average working hours per month (22 days * 8 hours - 3 hours break)
        return $basicSalary / 173;
    }

    public function updateOvertimeAmount()
    {
        $hourlyRate = $this->calculateHourlyRate();
        $amount = $this->calculateOvertimeAmount($hourlyRate);

        $this->update([
            'jumlah_lembur' => $amount, // overtime_amount -> jumlah_lembur
            'tarif_lembur' => $hourlyRate * 1.5, // overtime_rate -> tarif_lembur
        ]);

        return $amount;
    }

    // Validation methods
    public function isValidRequest()
    {
        // Check if overtime date is not in the past (except today)
        if ($this->tanggal_lembur->lt(today())) { // overtime_date -> tanggal_lembur
            return false;
        }

        // Check if planned hours is reasonable (max 8 hours)
        if ($this->jam_direncanakan > 8) { // planned_hours -> jam_direncanakan
            return false;
        }

        // Check if start time is after normal working hours
        $normalEndTime = Carbon::parse('17:00');
        $startTime = Carbon::parse($this->waktu_mulai); // start_time -> waktu_mulai
        
        if ($startTime->lt($normalEndTime)) {
            return false;
        }

        return true;
    }

    public function hasConflict()
    {
        // Check for existing overtime requests on the same date
        return static::where('id_pengguna', $this->id_pengguna) // user_id -> id_pengguna
                    ->where('id', '!=', $this->id)
                    ->where('tanggal_lembur', $this->tanggal_lembur) // overtime_date -> tanggal_lembur
                    ->whereIn('status', ['menunggu', 'disetujui']) // pending -> menunggu, approved -> disetujui
                    ->exists();
    }

    public function getFormattedDurationAttribute()
    {
        $hours = $this->jam_aktual ?? $this->jam_direncanakan; // actual_hours -> jam_aktual, planned_hours -> jam_direncanakan
        $wholeHours = floor($hours);
        $minutes = ($hours - $wholeHours) * 60;
        
        if ($minutes > 0) {
            return $wholeHours . 'j ' . round($minutes) . 'm';
        }
        
        return $wholeHours . ' jam';
    }
}
