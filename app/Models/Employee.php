<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'karyawan';

    protected $fillable = [
        'id_pengguna', // user_id
        'id_departemen', // department_id
        'id_jabatan', // position_id
        'id_atasan', // supervisor_id
        'tanggal_rekrut', // hire_date
        'mulai_kontrak', // contract_start
        'akhir_kontrak', // contract_end
        'jenis_kepegawaian', // employment_type
        'status_kepegawaian', // employment_status
        'gaji_pokok', // basic_salary
        'nama_bank', // bank_name
        'rekening_bank', // bank_account
        'nama_rekening_bank', // bank_account_name
        'id_shift_standar', // default_shift_id
        'id_kantor_standar', // default_office_id
        'tipe_kerja_standar', // default_work_type
    ];

    protected $casts = [
        'tanggal_rekrut' => 'date', // hire_date
        'mulai_kontrak' => 'date', // contract_start
        'akhir_kontrak' => 'date', // contract_end
        'gaji_pokok' => 'decimal:2', // basic_salary
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'id_departemen');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'id_jabatan');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'id_atasan');
    }

    public function defaultShift()
    {
        return $this->belongsTo(\App\Models\Shift::class, 'id_shift_standar');
    }

    public function defaultOffice()
    {
        return $this->belongsTo(\App\Models\Office::class, 'id_kantor_standar');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status_kepegawaian', 'aktif'); // employment_status -> status_kepegawaian, 'active' -> 'aktif'
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('id_departemen', $departmentId); // department_id -> id_departemen
    }

    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('jenis_kepegawaian', $type); // employment_type -> jenis_kepegawaian
    }

    // Helper methods
    public function isActive()
    {
        return $this->status_kepegawaian === 'aktif'; // employment_status -> status_kepegawaian, 'active' -> 'aktif'
    }

    public function getYearsOfServiceAttribute()
    {
        return $this->tanggal_rekrut->diffInYears(now()); // hire_date -> tanggal_rekrut
    }

    public function getMonthsOfServiceAttribute()
    {
        return $this->tanggal_rekrut->diffInMonths(now()); // hire_date -> tanggal_rekrut
    }
}
