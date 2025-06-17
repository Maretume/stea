<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_pemberitahuan';

    protected $fillable = [
        'id_pengguna', // user_id
        'tipe', // type
        'acara', // event
        'aktif', // enabled
        'pengaturan_tambahan', // settings
    ];

    protected $casts = [
        'aktif' => 'boolean', // enabled
        'pengaturan_tambahan' => 'array', // settings
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    // Scopes
    public function scopeEnabled($query) // Name kept as is, logic changed
    {
        return $query->where('aktif', true); // enabled -> aktif
    }

    public function scopeByType($query, $type) // Name kept as is, logic changed
    {
        return $query->where('tipe', $type); // type -> tipe
    }

    public function scopeByEvent($query, $event) // Name kept as is, logic changed
    {
        return $query->where('acara', $event); // event -> acara
    }

    // Helper methods
    public static function getAvailableTypes()
    {
        return [
            'email' => 'Email',
            'push' => 'Push Notification',
            'in_app' => 'In-App Notification',
        ];
    }

    public static function getAvailableEvents()
    {
        return [
            'schedule_reminder' => 'Pengingat Jadwal Kerja',
            'schedule_approved' => 'Jadwal Disetujui',
            'schedule_cancelled' => 'Jadwal Dibatalkan',
            'attendance_reminder' => 'Pengingat Absensi',
            'late_attendance' => 'Terlambat Absensi',
            'missing_attendance' => 'Tidak Absen',
            'overtime_alert' => 'Alert Lembur',
        ];
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        return $this;
    }
}
