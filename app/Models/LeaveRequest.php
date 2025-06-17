<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_cuti';

    protected $fillable = [
        'id_pengguna', // user_id
        'id_jenis_cuti', // leave_type_id
        'tanggal_mulai', // start_date
        'tanggal_selesai', // end_date
        'total_hari', // total_days
        'alasan', // reason
        'catatan', // notes
        'kontak_darurat', // emergency_contact
        'telepon_darurat', // emergency_phone
        'serah_terima_pekerjaan', // work_handover
        'status',
        'disetujui_oleh', // approved_by
        'disetujui_pada', // approved_at
        'catatan_persetujuan', // approval_notes
        'lampiran', // attachments
        'setengah_hari', // is_half_day
        'tipe_setengah_hari', // half_day_type
    ];

    protected $casts = [
        'tanggal_mulai' => 'date', // start_date
        'tanggal_selesai' => 'date', // end_date
        'disetujui_pada' => 'datetime', // approved_at
        'lampiran' => 'array', // attachments
        'setengah_hari' => 'boolean', // is_half_day
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'id_jenis_cuti');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function approvals()
    {
        // The morphable name 'approvable' might need to change if the columns in 'persetujuan_izin_kerja'
        // (id_persetujuan, tipe_persetujuan) were intended to be reflected here.
        // For now, keeping 'approvable' as it's a common convention for Laravel's morphs.
        // If PermitApproval model uses custom morph keys, this needs to align.
        return $this->morphMany(PermitApproval::class, 'approvable');
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('id_pengguna', $userId); // user_id -> id_pengguna
    }

    public function scopeByStatus($query, $status)
    {
        // Assuming $status is already the translated value
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

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('tanggal_mulai', [$startDate, $endDate]) // start_date -> tanggal_mulai
              ->orWhereBetween('tanggal_selesai', [$startDate, $endDate]) // end_date -> tanggal_selesai
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('tanggal_mulai', '<=', $startDate) // start_date -> tanggal_mulai
                     ->where('tanggal_selesai', '>=', $endDate); // end_date -> tanggal_selesai
              });
        });
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('tanggal_mulai', now()->year); // start_date -> tanggal_mulai
    }

    public function scopeByLeaveType($query, $leaveTypeId)
    {
        return $query->where('id_jenis_cuti', $leaveTypeId); // leave_type_id -> id_jenis_cuti
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

    public function isCancelled()
    {
        return $this->status === 'dibatalkan'; // cancelled -> dibatalkan
    }

    public function canBeEdited()
    {
        // Assumes start_date is available as a Carbon instance due to casts
        return $this->status === 'menunggu' && $this->tanggal_mulai->gt(today()); // pending -> menunggu, start_date -> tanggal_mulai
    }

    public function canBeApproved()
    {
        return $this->status === 'menunggu'; // pending -> menunggu
    }

    public function canBeCancelled()
    {
        // Assumes start_date is available as a Carbon instance
        return in_array($this->status, ['menunggu', 'disetujui']) && $this->tanggal_mulai->gt(today()); // pending -> menunggu, approved -> disetujui, start_date -> tanggal_mulai
    }

    public function getStatusBadgeAttribute()
    {
        // Keys should be the Indonesian ENUM values
        $badges = [
            'menunggu' => 'warning', // pending
            'disetujui' => 'success', // approved
            'ditoLak' => 'danger', // rejected (note: ditoLak from migration)
            'dibatalkan' => 'secondary', // cancelled
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
            'dibatalkan' => 'Dibatalkan',
        ];

        return $texts[$this->status] ?? 'Tidak Diketahui'; // Unknown -> Tidak Diketahui
    }

    // Calculation methods
    public function calculateTotalDays()
    {
        if ($this->setengah_hari) { // is_half_day -> setengah_hari
            return 0.5;
        }

        if (!$this->tanggal_mulai || !$this->tanggal_selesai) { // start_date -> tanggal_mulai, end_date -> tanggal_selesai
            return 0;
        }

        $start = Carbon::parse($this->tanggal_mulai); // start_date -> tanggal_mulai
        $end = Carbon::parse($this->tanggal_selesai); // end_date -> tanggal_selesai

        // Count only working days (Monday to Friday)
        $totalDays = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($current->isWeekday()) {
                $totalDays++;
            }
            $current->addDay();
        }

        return $totalDays;
    }

    public function updateTotalDays()
    {
        $this->total_hari = $this->calculateTotalDays(); // total_days -> total_hari
        $this->save();
        return $this->total_hari; // total_days -> total_hari
    }

    // Validation methods
    public function isValidRequest()
    {
        // Check if start date is not in the past
        if ($this->tanggal_mulai->lt(today())) { // start_date -> tanggal_mulai
            return false;
        }

        // Check if end date is after start date
        if ($this->tanggal_selesai->lt($this->tanggal_mulai)) { // end_date -> tanggal_selesai, start_date -> tanggal_mulai
            return false;
        }

        // Check leave balance
        if (!$this->hasEnoughBalance()) {
            return false;
        }

        return true;
    }

    public function hasEnoughBalance()
    {
        $leaveType = $this->leaveType; // This relationship should use translated foreign key 'id_jenis_cuti'
        if (!$leaveType) {
            return false;
        }
        // user_id -> id_pengguna, start_date -> tanggal_mulai, total_days -> total_hari
        // LeaveType's getRemainingDays method itself needs to be updated for translated attributes
        $remainingDays = $leaveType->getRemainingDays($this->id_pengguna, $this->tanggal_mulai->year);
        return $remainingDays >= $this->total_hari;
    }

    public function hasConflict()
    {
        // Check for overlapping leave requests
        return static::where('id_pengguna', $this->id_pengguna) // user_id -> id_pengguna
                    ->where('id', '!=', $this->id)
                    ->where(function ($query) {
                        // start_date -> tanggal_mulai, end_date -> tanggal_selesai
                        $query->whereBetween('tanggal_mulai', [$this->tanggal_mulai, $this->tanggal_selesai])
                              ->orWhereBetween('tanggal_selesai', [$this->tanggal_mulai, $this->tanggal_selesai])
                              ->orWhere(function ($q) {
                                  $q->where('tanggal_mulai', '<=', $this->tanggal_mulai)
                                    ->where('tanggal_selesai', '>=', $this->tanggal_selesai);
                              });
                    })
                    ->whereIn('status', ['menunggu', 'disetujui']) // pending -> menunggu, approved -> disetujui
                    ->exists();
    }

    public function getFormattedDurationAttribute()
    {
        if ($this->setengah_hari) { // is_half_day -> setengah_hari
            // half_day_type -> tipe_setengah_hari, ENUM 'pagi', 'siang'
            $tipe = $this->tipe_setengah_hari === 'pagi' ? 'Pagi' : ($this->tipe_setengah_hari === 'siang' ? 'Siang' : '');
            return '0.5 hari (' . $tipe . ')';
        }

        return $this->total_hari . ' hari'; // total_days -> total_hari
    }

    public function getFormattedDateRangeAttribute()
    {
        if ($this->tanggal_mulai->eq($this->tanggal_selesai)) { // start_date -> tanggal_mulai, end_date -> tanggal_selesai
            return $this->tanggal_mulai->format('d M Y');
        }

        return $this->tanggal_mulai->format('d M Y') . ' - ' . $this->tanggal_selesai->format('d M Y');
    }

    // File attachment methods
    public function addAttachment($filename, $originalName, $size)
    {
        $attachments = $this->lampiran ?? []; // attachments -> lampiran
        $attachments[] = [
            'filename' => $filename,
            'original_name' => $originalName,
            'size' => $size,
            'uploaded_at' => now()->toISOString(),
        ];
        
        $this->lampiran = $attachments; // attachments -> lampiran
        $this->save();
    }

    public function removeAttachment($filename)
    {
        $attachments = $this->lampiran ?? []; // attachments -> lampiran
        $this->lampiran = array_filter($attachments, function ($attachment) use ($filename) {
            return $attachment['filename'] !== $filename;
        });
        $this->save();
    }

    public function hasAttachments()
    {
        return !empty($this->lampiran); // attachments -> lampiran
    }
}
