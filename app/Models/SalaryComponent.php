<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryComponent extends Model
{
    use HasFactory;

    protected $table = 'komponen_gaji';

    protected $fillable = [
        'nama', // name
        'kode', // code
        'tipe', // type
        'tipe_perhitungan', // calculation_type
        'jumlah_standar', // default_amount
        'persentase', // percentage
        'rumus', // formula
        'kena_pajak', // is_taxable
        'aktif', // is_active
        'urutan', // sort_order
    ];

    protected $casts = [
        'jumlah_standar' => 'decimal:2', // default_amount
        'persentase' => 'decimal:2', // percentage
        'kena_pajak' => 'boolean', // is_taxable
        'aktif' => 'boolean', // is_active
    ];

    // Relationships
    public function employees()
    {
        // User model's salaryComponents relationship uses:
        // ->withPivot(['jumlah', 'tanggal_efektif', 'tanggal_berakhir', 'aktif'])
        // ->withTimestamps('dibuat_pada', 'diperbarui_pada');
        return $this->belongsToMany(User::class, 'komponen_gaji_karyawan', 'id_komponen_gaji', 'id_pengguna')
                    ->withPivot(['jumlah', 'tanggal_efektif', 'tanggal_berakhir', 'aktif']) // Assuming these pivot column names are correct
                    ->withTimestamps('dibuat_pada', 'diperbarui_pada');
    }

    public function payrollDetails()
    {
        return $this->hasMany(PayrollDetail::class, 'id_komponen_gaji');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('aktif', true); // is_active -> aktif
    }

    public function scopeByType($query, $type)
    {
        // Assuming $type is already translated if coming from outside
        return $query->where('tipe', $type); // type -> tipe
    }

    public function scopeAllowances($query)
    {
        return $query->where('tipe', 'tunjangan'); // type -> tipe, allowance -> tunjangan
    }

    public function scopeDeductions($query)
    {
        return $query->where('tipe', 'potongan'); // type -> tipe, deduction -> potongan
    }

    public function scopeBenefits($query)
    {
        return $query->where('tipe', 'manfaat'); // type -> tipe, benefit -> manfaat
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('nama'); // sort_order -> urutan, name -> nama
    }

    // Helper methods
    public function isAllowance()
    {
        return $this->tipe === 'tunjangan'; // type -> tipe, allowance -> tunjangan
    }

    public function isDeduction()
    {
        return $this->tipe === 'potongan'; // type -> tipe, deduction -> potongan
    }

    public function isBenefit()
    {
        return $this->tipe === 'manfaat'; // type -> tipe, benefit -> manfaat
    }

    public function calculateAmount($basicSalary, $customAmount = null)
    {
        if ($customAmount !== null) {
            return $customAmount;
        }

        switch ($this->tipe_perhitungan) { // calculation_type -> tipe_perhitungan
            case 'tetap': // fixed -> tetap
                return $this->jumlah_standar; // default_amount -> jumlah_standar
            case 'persentase': // percentage -> persentase
                return ($basicSalary * $this->persentase) / 100; // percentage -> persentase
            case 'rumus': // formula -> rumus
                // Implement formula calculation logic here
                return $this->evaluateFormula($basicSalary);
            default:
                return 0;
        }
    }

    private function evaluateFormula($basicSalary)
    {
        // Simple formula evaluation - can be extended
        // Assuming 'basic_salary' in formula string refers to the variable, not a column name in this model
        $formula = str_replace('basic_salary', $basicSalary, $this->rumus); // formula -> rumus

        // For security, only allow basic math operations
        if (preg_match('/^[\d\+\-\*\/\(\)\.\s]+$/', $formula)) {
            try {
                return eval("return $formula;");
            } catch (\Exception $e) {
                return 0;
            }
        }

        return 0;
    }
}
