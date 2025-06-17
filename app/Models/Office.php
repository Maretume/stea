<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $table = 'kantor';

    protected $fillable = [
        'nama', // name
        'lintang', // latitude
        'bujur', // longitude
        'radius',
        'aktif', // is_active
    ];

    protected $casts = [
        'lintang' => 'decimal:8', // latitude
        'bujur' => 'decimal:8', // longitude
        'aktif' => 'boolean', // is_active
    ];

    // Relationships
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'id_kantor'); // foreignKey
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'id_kantor'); // foreignKey
    }

    public function users()
    {
        // Office -> Schedule (id_kantor on schedules, id on offices)
        // Schedule -> User (id_pengguna on schedules, id on users)
        return $this->hasManyThrough(
            User::class,
            Schedule::class,
            'id_kantor', // Foreign key on Schedule table (intermediate table)
            'id',        // Foreign key on User table (far table)
            'id',        // Local key on Office table (this table)
            'id_pengguna'  // Local key on Schedule table (intermediate table)
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('aktif', true); // is_active -> aktif
    }

    // Helper methods
    public function isWithinRadius($latitude, $longitude)
    {
        $distance = $this->calculateDistance($latitude, $longitude);
        return $distance <= $this->radius;
    }

    public function calculateDistance($latitude, $longitude)
    {
        // Haversine formula to calculate distance between two points
        $earthRadius = 6371000; // Earth's radius in meters

        $lat1 = deg2rad($this->latitude);
        $lon1 = deg2rad($this->longitude);
        $lat2 = deg2rad($latitude);
        $lon2 = deg2rad($longitude);

        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in meters
    }

    public function getCoordinatesAttribute()
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ];
    }
}
