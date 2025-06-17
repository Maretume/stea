<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'penggajian';

    protected $fillable = [
        'id_periode_penggajian', // payroll_period_id
        'id_pengguna', // user_id
        'gaji_pokok', // basic_salary
        'total_tunjangan', // total_allowances
        'total_potongan', // total_deductions
        'jumlah_lembur', // overtime_amount
        'gaji_kotor', // gross_salary
        'jumlah_pajak', // tax_amount
        'gaji_bersih', // net_salary
        'total_hari_kerja', // total_working_days
        'total_hari_hadir', // total_present_days
        'total_hari_absen', // total_absent_days
        'total_hari_terlambat', // total_late_days
        'total_jam_lembur', // total_overtime_hours
        'catatan', // notes
        'status',
        'disetujui_oleh_payroll', // approved_by (from add_approval_fields_to_payrolls_table migration)
        'disetujui_pada_payroll', // approved_at (from add_approval_fields_to_payrolls_table migration)
    ];

    protected $casts = [
        'gaji_pokok' => 'decimal:2',
        'total_tunjangan' => 'decimal:2',
        'total_potongan' => 'decimal:2',
        'jumlah_lembur' => 'decimal:2',
        'gaji_kotor' => 'decimal:2',
        'jumlah_pajak' => 'decimal:2',
        'gaji_bersih' => 'decimal:2',
        'disetujui_pada_payroll' => 'datetime', // approved_at -> disetujui_pada_payroll
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class, 'id_periode_penggajian');
    }

    public function details()
    {
        return $this->hasMany(PayrollDetail::class, 'id_penggajian');
    }

    public function approvedBy() // This now refers to the specific approval on payrolls table
    {
        return $this->belongsTo(User::class, 'disetujui_oleh_payroll');
    }

    // Scopes
    public function scopeByPeriod($query, $periodId)
    {
        return $query->where('id_periode_penggajian', $periodId); // payroll_period_id -> id_periode_penggajian
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
    public function getAttendanceRateAttribute()
    {
        if ($this->total_working_days == 0) {
            return 0;
        }
        
        return round(($this->total_present_days / $this->total_working_days) * 100, 2);
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isApproved()
    {
        return in_array($this->status, ['approved', 'paid']);
    }
}
