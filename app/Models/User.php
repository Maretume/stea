<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'id_karyawan', // employee_id
        'nama_pengguna', // username
        'surel', // email
        'kata_sandi', // password
        'nama_depan', // first_name
        'nama_belakang', // last_name
        'telepon', // phone
        'jenis_kelamin', // gender
        'tanggal_lahir', // birth_date
        'alamat', // address
        'foto_profil', // profile_photo
        'status',
        'login_terakhir_pada', // last_login_at
        'ip_login_terakhir', // last_login_ip
        'paksa_ganti_kata_sandi', // force_password_change
    ];

    protected $hidden = [
        'kata_sandi', // password
        'token_ingat_saya', // remember_token
    ];

    protected $casts = [
        'surel_diverifikasi_pada' => 'datetime', // email_verified_at
        'tanggal_lahir' => 'date', // birth_date
        'login_terakhir_pada' => 'datetime', // last_login_at
        'paksa_ganti_kata_sandi' => 'boolean', // force_password_change
        'kata_sandi' => 'hashed', // password
    ];

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'pengguna_peran', 'id_pengguna', 'id_peran') // user_roles -> pengguna_peran
                    ->withPivot(['ditetapkan_pada', 'kadaluarsa_pada', 'aktif']) // assigned_at, expires_at, is_active
                    ->withTimestamps('dibuat_pada', 'diperbarui_pada'); // created_at, updated_at
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'id_pengguna'); // foreignKey
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'id_pengguna'); // foreignKey
    }

    public function leaves()
    {
        // Assuming Leave model will point to 'cuti' or 'pengajuan_cuti' table
        // For now, just updating the foreign key. The Leave model itself needs to be updated.
        return $this->hasMany(Leave::class, 'id_pengguna'); // foreignKey
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'id_pengguna'); // foreignKey
    }

    public function salaryComponents()
    {
        return $this->belongsToMany(SalaryComponent::class, 'komponen_gaji_karyawan', 'id_pengguna', 'id_komponen_gaji') // employee_salary_components -> komponen_gaji_karyawan
                    ->withPivot(['jumlah', 'tanggal_efektif', 'tanggal_berakhir', 'aktif']) // amount, effective_date, end_date, is_active
                    ->withTimestamps('dibuat_pada', 'diperbarui_pada');
    }

    public function supervisedEmployees()
    {
        return $this->hasMany(Employee::class, 'id_atasan'); // supervisor_id -> id_atasan
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_pengguna'); // foreignKey
    }

    public function createdSchedules()
    {
        return $this->hasMany(Schedule::class, 'dibuat_oleh'); // created_by -> dibuat_oleh
    }

    public function approvedSchedules()
    {
        return $this->hasMany(Schedule::class, 'disetujui_oleh'); // approved_by -> disetujui_oleh
    }

    public function schedule()
    {
        // This might need to be latestOfMany or similar if a user can have multiple schedules over time.
        // For now, translating the direct hasOne.
        return $this->hasOne(Schedule::class, 'id_pengguna'); // foreignKey
    }

    public function shift()
    {
        // Ensure Schedule and Shift models use translated keys if this is to work.
        return $this->hasOneThrough(
            Shift::class,
            Schedule::class,
            'id_pengguna', // Foreign key on Schedule table
            'id',          // Foreign key on Shift table (local key on Schedule table for shift_id)
            'id',          // Local key on User table
            'id_shift'     // Local key on Schedule table
        );
    }

    public function office()
    {
        // Ensure Schedule and Office models use translated keys.
        return $this->hasOneThrough(
            Office::class,
            Schedule::class,
            'id_pengguna', // Foreign key on Schedule table
            'id',          // Foreign key on Office table (local key on Schedule table for office_id)
            'id',          // Local key on User table
            'id_kantor'    // Local key on Schedule table
        );
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->nama_depan . ' ' . $this->nama_belakang; // first_name -> nama_depan, last_name -> nama_belakang
    }

    // Helper methods
    public function hasRole($roleName)
    {
        // Assuming Role model's 'name' column is now 'nama_kunci'
        return $this->roles()->where('nama_kunci', $roleName)->exists();
    }

    public function hasPermission($permissionName)
    {
        // Assuming Permission model's 'name' column is now 'nama_kunci'
        // and Role model's permissions() relationship is correctly set up.
        return $this->roles()
                    ->whereHas('permissions', function ($query) use ($permissionName) {
                        $query->where('nama_kunci', $permissionName);
                    })->exists();
    }

    public function hasAnyRole($roles)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }
        // Assuming Role model's 'name' column is now 'nama_kunci'
        return $this->roles()->whereIn('nama_kunci', $roles)->exists();
    }

    public function isActive()
    {
        return $this->status === 'aktif'; // active
    }

    public function getCurrentSalaryComponents()
    {
        return $this->salaryComponents()
                    ->where('komponen_gaji.aktif', true) // salary_components.is_active -> komponen_gaji.aktif
                    ->wherePivot('aktif', true) // is_active
                    ->wherePivot('tanggal_efektif', '<=', now()); // effective_date -> tanggal_efektif
    }

    // Schedule helper methods
    public function getTodaySchedule()
    {
        return $this->schedules()
                    ->with(['shift', 'office']) // Assuming Shift and Office model relations are correct
                    ->where('tanggal_jadwal', today()) // schedule_date -> tanggal_jadwal
                    ->where('status', '!=', 'dibatalkan') // cancelled -> dibatalkan
                    ->first();
    }

    public function getScheduleForDate($date)
    {
        return $this->schedules()
                    ->where('tanggal_jadwal', $date) // schedule_date -> tanggal_jadwal
                    ->where('status', '!=', 'dibatalkan') // cancelled -> dibatalkan
                    ->first();
    }

    public function hasScheduleForDate($date)
    {
        return $this->schedules()
                    ->where('tanggal_jadwal', $date) // schedule_date -> tanggal_jadwal
                    ->where('status', '!=', 'dibatalkan') // cancelled -> dibatalkan
                    ->exists();
    }

    public function canClockInToday()
    {
        $todaySchedule = $this->getTodaySchedule();
        if ($todaySchedule) {
            return true;
        }

        // Check if can create schedule using default settings
        // Assuming employee relation and its default fields are translated
        if (!$this->employee || !$this->employee->id_shift_standar) { // default_shift_id -> id_shift_standar
            return false;
        }

        // For WFO, office is required
        if ($this->employee->tipe_kerja_standar === 'WFO' && !$this->employee->id_kantor_standar) { // default_work_type -> tipe_kerja_standar, default_office_id -> id_kantor_standar
            return false;
        }

        return true;
    }

    public function getTodayWorkType()
    {
        $todaySchedule = $this->getTodaySchedule();
        return $todaySchedule ? $todaySchedule->tipe_kerja : null; // work_type -> tipe_kerja
    }

    public function getOrCreateTodaySchedule()
    {
        // First try to get existing schedule
        $todaySchedule = $this->getTodaySchedule();
        if ($todaySchedule) {
            return $todaySchedule;
        }

        // If no schedule exists, create one using employee's default settings
        if (!$this->employee) {
            return null;
        }

        $employee = $this->employee;

        // Check if employee has default shift and office (for WFO)
        if (!$employee->id_shift_standar) { // default_shift_id -> id_shift_standar
            return null;
        }

        // For WFO, office is required
        if ($employee->tipe_kerja_standar === 'WFO' && !$employee->id_kantor_standar) { // default_work_type -> tipe_kerja_standar, default_office_id -> id_kantor_standar
            return null;
        }

        // Create new schedule for today
        // Assuming Schedule model's fields are translated
        $scheduleData = [
            'id_pengguna' => $this->id, // user_id -> id_pengguna
            'id_shift' => $employee->id_shift_standar, // shift_id -> id_shift
            'id_kantor' => $employee->tipe_kerja_standar === 'WFO' ? $employee->id_kantor_standar : null, // office_id -> id_kantor
            'tanggal_jadwal' => today(), // schedule_date -> tanggal_jadwal
            'tipe_kerja' => $employee->tipe_kerja_standar, // work_type -> tipe_kerja
            'status' => 'disetujui', // approved -> disetujui
            'catatan' => 'Jadwal dibuat otomatis', // Auto-generated schedule -> Jadwal dibuat otomatis
            'dibuat_oleh' => $this->id, // created_by -> dibuat_oleh
            'disetujui_oleh' => $this->id, // approved_by -> disetujui_oleh
            'disetujui_pada' => now(), // approved_at -> disetujui_pada
        ];

        $schedule = \App\Models\Schedule::create($scheduleData);

        // Load relationships
        return $schedule->load(['shift', 'office']);
    }

    public function notificationSettings()
    {
        return $this->hasMany(NotificationSetting::class, 'id_pengguna'); // foreignKey
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class, 'id_pengguna'); // foreignKey
    }
}
    use HasFactory, Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'id_karyawan', // employee_id
        'nama_pengguna', // username
        'surel', // email
        'kata_sandi', // password
        'nama_depan', // first_name
        'nama_belakang', // last_name
        'telepon', // phone
        'jenis_kelamin', // gender
        'tanggal_lahir', // birth_date
        'alamat', // address
        'foto_profil', // profile_photo
        'status',
        'login_terakhir_pada', // last_login_at
        'ip_login_terakhir', // last_login_ip
        'paksa_ganti_kata_sandi', // force_password_change
    ];

    protected $hidden = [
        'kata_sandi', // password
        'token_ingat_saya', // remember_token
    ];

    protected $casts = [
        'surel_diverifikasi_pada' => 'datetime', // email_verified_at
        'tanggal_lahir' => 'date', // birth_date
        'login_terakhir_pada' => 'datetime', // last_login_at
        'paksa_ganti_kata_sandi' => 'boolean', // force_password_change
        'kata_sandi' => 'hashed', // password
    ];

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'pengguna_peran', 'id_pengguna', 'id_peran') // user_roles -> pengguna_peran
                    ->withPivot(['ditetapkan_pada', 'kadaluarsa_pada', 'aktif']) // assigned_at, expires_at, is_active
                    ->withTimestamps('dibuat_pada', 'diperbarui_pada'); // created_at, updated_at
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'id_pengguna'); // foreignKey
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'id_pengguna'); // foreignKey
    }

    public function leaves()
    {
        // Assuming Leave model will point to 'cuti' or 'pengajuan_cuti' table
        // For now, just updating the foreign key. The Leave model itself needs to be updated.
        return $this->hasMany(Leave::class, 'id_pengguna'); // foreignKey
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'id_pengguna'); // foreignKey
    }

    public function salaryComponents()
    {
        return $this->belongsToMany(SalaryComponent::class, 'komponen_gaji_karyawan', 'id_pengguna', 'id_komponen_gaji') // employee_salary_components -> komponen_gaji_karyawan
                    ->withPivot(['jumlah', 'tanggal_efektif', 'tanggal_berakhir', 'aktif']) // amount, effective_date, end_date, is_active
                    ->withTimestamps('dibuat_pada', 'diperbarui_pada');
    }

    public function supervisedEmployees()
    {
        return $this->hasMany(Employee::class, 'id_atasan'); // supervisor_id -> id_atasan
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_pengguna'); // foreignKey
    }

    public function createdSchedules()
    {
        return $this->hasMany(Schedule::class, 'dibuat_oleh'); // created_by -> dibuat_oleh
    }

    public function approvedSchedules()
    {
        return $this->hasMany(Schedule::class, 'disetujui_oleh'); // approved_by -> disetujui_oleh
    }

    public function schedule()
    {
        // This might need to be latestOfMany or similar if a user can have multiple schedules over time.
        // For now, translating the direct hasOne.
        return $this->hasOne(Schedule::class, 'id_pengguna'); // foreignKey
    }

    public function shift()
    {
        // Ensure Schedule and Shift models use translated keys if this is to work.
        return $this->hasOneThrough(
            Shift::class,
            Schedule::class,
            'id_pengguna', // Foreign key on Schedule table
            'id',          // Foreign key on Shift table (local key on Schedule table for shift_id)
            'id',          // Local key on User table
            'id_shift'     // Local key on Schedule table
        );
    }

    public function office()
    {
        // Ensure Schedule and Office models use translated keys.
        return $this->hasOneThrough(
            Office::class,
            Schedule::class,
            'id_pengguna', // Foreign key on Schedule table
            'id',          // Foreign key on Office table (local key on Schedule table for office_id)
            'id',          // Local key on User table
            'id_kantor'    // Local key on Schedule table
        );
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->nama_depan . ' ' . $this->nama_belakang; // first_name -> nama_depan, last_name -> nama_belakang
    }

    // Helper methods
    public function hasRole($roleName)
    {
        // Assuming Role model's 'name' column is now 'nama_kunci'
        return $this->roles()->where('nama_kunci', $roleName)->exists();
    }

    public function hasPermission($permissionName)
    {
        // Assuming Permission model's 'name' column is now 'nama_kunci'
        // and Role model's permissions() relationship is correctly set up.
        return $this->roles()
                    ->whereHas('permissions', function ($query) use ($permissionName) {
                        $query->where('nama_kunci', $permissionName);
                    })->exists();
    }

    public function hasAnyRole($roles)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }
        // Assuming Role model's 'name' column is now 'nama_kunci'
        return $this->roles()->whereIn('nama_kunci', $roles)->exists();
    }

    public function isActive()
    {
        return $this->status === 'aktif'; // active
    }

    public function getCurrentSalaryComponents()
    {
        return $this->salaryComponents()
                    ->where('komponen_gaji.aktif', true) // salary_components.is_active -> komponen_gaji.aktif
                    ->wherePivot('aktif', true) // is_active
                    ->wherePivot('tanggal_efektif', '<=', now()); // effective_date -> tanggal_efektif
    }

    // Schedule helper methods
    public function getTodaySchedule()
    {
        return $this->schedules()
                    ->with(['shift', 'office']) // Assuming Shift and Office model relations are correct
                    ->where('tanggal_jadwal', today()) // schedule_date -> tanggal_jadwal
                    ->where('status', '!=', 'dibatalkan') // cancelled -> dibatalkan
                    ->first();
    }

    public function getScheduleForDate($date)
    {
        return $this->schedules()
                    ->where('tanggal_jadwal', $date) // schedule_date -> tanggal_jadwal
                    ->where('status', '!=', 'dibatalkan') // cancelled -> dibatalkan
                    ->first();
    }

    public function hasScheduleForDate($date)
    {
        return $this->schedules()
                    ->where('tanggal_jadwal', $date) // schedule_date -> tanggal_jadwal
                    ->where('status', '!=', 'dibatalkan') // cancelled -> dibatalkan
                    ->exists();
    }

    public function canClockInToday()
    {
        $todaySchedule = $this->getTodaySchedule();
        if ($todaySchedule) {
            return true;
        }

        // Check if can create schedule using default settings
        // Assuming employee relation and its default fields are translated
        if (!$this->employee || !$this->employee->id_shift_standar) { // default_shift_id -> id_shift_standar
            return false;
        }

        // For WFO, office is required
        if ($this->employee->tipe_kerja_standar === 'WFO' && !$this->employee->id_kantor_standar) { // default_work_type -> tipe_kerja_standar, default_office_id -> id_kantor_standar
            return false;
        }

        return true;
    }

    public function getTodayWorkType()
    {
        $todaySchedule = $this->getTodaySchedule();
        return $todaySchedule ? $todaySchedule->tipe_kerja : null; // work_type -> tipe_kerja
    }

    public function getOrCreateTodaySchedule()
    {
        // First try to get existing schedule
        $todaySchedule = $this->getTodaySchedule();
        if ($todaySchedule) {
            return $todaySchedule;
        }

        // If no schedule exists, create one using employee's default settings
        if (!$this->employee) {
            return null;
        }

        $employee = $this->employee;

        // Check if employee has default shift and office (for WFO)
        if (!$employee->id_shift_standar) { // default_shift_id -> id_shift_standar
            return null;
        }

        // For WFO, office is required
        if ($employee->tipe_kerja_standar === 'WFO' && !$employee->id_kantor_standar) { // default_work_type -> tipe_kerja_standar, default_office_id -> id_kantor_standar
            return null;
        }

        // Create new schedule for today
        // Assuming Schedule model's fields are translated
        $scheduleData = [
            'id_pengguna' => $this->id, // user_id -> id_pengguna
            'id_shift' => $employee->id_shift_standar, // shift_id -> id_shift
            'id_kantor' => $employee->tipe_kerja_standar === 'WFO' ? $employee->id_kantor_standar : null, // office_id -> id_kantor
            'tanggal_jadwal' => today(), // schedule_date -> tanggal_jadwal
            'tipe_kerja' => $employee->tipe_kerja_standar, // work_type -> tipe_kerja
            'status' => 'disetujui', // approved -> disetujui
            'catatan' => 'Jadwal dibuat otomatis', // Auto-generated schedule -> Jadwal dibuat otomatis
            'dibuat_oleh' => $this->id, // created_by -> dibuat_oleh
            'disetujui_oleh' => $this->id, // approved_by -> disetujui_oleh
            'disetujui_pada' => now(), // approved_at -> disetujui_pada
        ];

        $schedule = \App\Models\Schedule::create($scheduleData);

        // Load relationships
        return $schedule->load(['shift', 'office']);
    }

    public function notificationSettings()
    {
        return $this->hasMany(NotificationSetting::class, 'id_pengguna'); // foreignKey
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class, 'id_pengguna'); // foreignKey
    }
}
