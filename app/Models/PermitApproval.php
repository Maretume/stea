<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermitApproval extends Model
{
    use HasFactory;

    protected $table = 'persetujuan_izin_kerja';

    protected $fillable = [
        'tipe_persetujuan', // approvable_type
        'id_persetujuan', // approvable_id
        'id_penyetuju', // approver_id
        'tingkat_persetujuan', // approval_level
        'status',
        'disetujui_pada', // approved_at
        'catatan', // notes
    ];

    protected $casts = [
        'disetujui_pada' => 'datetime', // approved_at
    ];

    // Polymorphic relationship
    public function approvable()
    {
        // Using translated type and id column names for the morphTo relationship
        return $this->morphTo(null, 'tipe_persetujuan', 'id_persetujuan');
    }

    // Relationships
    public function approver()
    {
        return $this->belongsTo(User::class, 'id_penyetuju'); // approver_id -> id_penyetuju
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'menunggu'); // pending -> menunggu
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'disetujui'); // approved -> disetujui
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'ditoLak'); // rejected -> ditoLak
    }

    public function scopeByApprover($query, $approverId)
    {
        return $query->where('id_penyetuju', $approverId); // approver_id -> id_penyetuju
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('approval_level', $level);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function approve($notes = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'notes' => $notes,
        ]);

        return $this;
    }

    public function reject($notes)
    {
        $this->update([
            'status' => 'rejected',
            'approved_at' => now(),
            'notes' => $notes,
        ]);

        return $this;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        $texts = [
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];

        return $texts[$this->status] ?? 'Unknown';
    }
}
