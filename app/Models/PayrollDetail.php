<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollDetail extends Model
{
    use HasFactory;

    protected $table = 'detail_penggajian';

    protected $fillable = [
        'id_penggajian', // payroll_id
        'id_komponen_gaji', // salary_component_id
        'jumlah', // amount
        'catatan_perhitungan', // calculation_notes
    ];

    protected $casts = [
        'jumlah' => 'decimal:2', // amount
    ];

    // Relationships
    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'id_penggajian');
    }

    public function salaryComponent()
    {
        return $this->belongsTo(SalaryComponent::class, 'id_komponen_gaji');
    }
}
