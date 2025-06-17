<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $table = 'cuti';

    protected $fillable = [
        'id_pengguna', // user_id
        'id_jenis_cuti', // leave_type_id
        'tanggal_mulai', // start_date
        'tanggal_selesai', // end_date
        'total_hari', // total_days
        'alasan', // reason
        'status',
        'disetujui_oleh', // approved_by
        'disetujui_pada', // approved_at
        'catatan_persetujuan', // approval_notes
    ];

    protected $casts = [
        'tanggal_mulai' => 'date', // start_date
        'tanggal_selesai' => 'date', // end_date
        'disetujui_pada' => 'datetime', // approved_at
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

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('id_pengguna', $userId); // user_id -> id_pengguna
    }

    public function scopeByStatus($query, $status)
    {
        // Assuming $status is already the translated value if coming from outside
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

    public function canBeEdited()
    {
        return $this->status === 'menunggu'; // pending -> menunggu
    }

    public function getDurationInDaysAttribute()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
}
