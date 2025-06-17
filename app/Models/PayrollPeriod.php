<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $table = 'periode_penggajian';

    protected $fillable = [
        'nama', // name
        'tanggal_mulai', // start_date
        'tanggal_selesai', // end_date
        'tanggal_bayar', // pay_date
        'status',
        'dibuat_oleh', // created_by
        'disetujui_oleh', // approved_by
        'disetujui_pada', // approved_at
        'deskripsi', // description - added in a later migration
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_bayar' => 'date',
        'disetujui_pada' => 'datetime',
    ];

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'id_periode_penggajian');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        // Assuming $status is already translated if coming from outside
        return $query->where('status', $status);
    }

    public function scopeCurrent($query)
    {
        return $query->where('tanggal_mulai', '<=', now()) // start_date -> tanggal_mulai
                    ->where('tanggal_selesai', '>=', now()); // end_date -> tanggal_selesai
    }

    // Helper methods
    public function isActive()
    {
        return now()->between($this->tanggal_mulai, $this->tanggal_selesai); // start_date -> tanggal_mulai, end_date -> tanggal_selesai
    }

    public function canBeEdited()
    {
        // draft -> konsep, calculated -> terhitung
        return in_array($this->status, ['konsep', 'terhitung']);
    }

    public function getTotalPayrollsAttribute()
    {
        return $this->payrolls()->count();
    }

    public function getTotalNetSalaryAttribute()
    {
        // Assuming Payroll model's net_salary is translated to gaji_bersih
        return $this->payrolls()->sum('gaji_bersih');
    }
}
