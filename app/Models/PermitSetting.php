<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermitSetting extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_izin_kerja';

    protected $fillable = [
        'kunci', // key
        'nilai', // value
        'tipe', // type
        'deskripsi', // description
    ];

    // Helper methods to get typed values
    public function getTypedValue()
    {
        switch ($this->tipe) { // type -> tipe
            case 'boolean':
                return filter_var($this->nilai, FILTER_VALIDATE_BOOLEAN); // value -> nilai
            case 'integer':
                return (int) $this->nilai; // value -> nilai
            case 'float':
                return (float) $this->nilai; // value -> nilai
            case 'json':
                return json_decode($this->nilai, true); // value -> nilai
            default:
                return $this->nilai; // value -> nilai
        }
    }

    public function setTypedValue($value)
    {
        switch ($this->tipe) { // type -> tipe
            case 'boolean':
                $this->nilai = $value ? '1' : '0'; // value -> nilai
                break;
            case 'json':
                $this->nilai = json_encode($value); // value -> nilai
                break;
            default:
                $this->nilai = (string) $value; // value -> nilai
        }

        return $this;
    }

    // Static helper methods
    public static function get($key, $default = null)
    {
        $setting = static::where('kunci', $key)->first(); // key -> kunci
        
        if (!$setting) {
            return $default;
        }

        return $setting->getTypedValue();
    }

    public static function set($key, $value, $type = 'string', $description = null)
    {
        $setting = static::firstOrNew(['kunci' => $key]); // key -> kunci
        $setting->tipe = $type; // type -> tipe
        $setting->deskripsi = $description; // description -> deskripsi
        $setting->setTypedValue($value);
        $setting->save();

        return $setting;
    }

    public static function getMultiple(array $keys)
    {
        $settings = static::whereIn('kunci', $keys)->get(); // key -> kunci
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->kunci] = $setting->getTypedValue(); // key -> kunci
        }

        // Fill missing keys with null
        foreach ($keys as $key) {
            if (!isset($result[$key])) {
                $result[$key] = null;
            }
        }

        return $result;
    }

    public static function setMultiple(array $settings)
    {
        foreach ($settings as $key => $config) {
            if (is_array($config)) {
                static::set(
                    $key,
                    $config['value'],
                    $config['type'] ?? 'string',
                    $config['description'] ?? null
                );
            } else {
                static::set($key, $config);
            }
        }
    }

    // Default permit settings
    public static function getDefaultSettings()
    {
        return [
            'max_overtime_hours_per_month' => [
                'value' => 40,
                'type' => 'integer',
                'description' => 'Maximum overtime hours per month per employee'
            ],


            'require_approval_overtime' => [
                'value' => true,
                'type' => 'boolean',
                'description' => 'Require approval for overtime requests'
            ],

            'auto_approve_sick_leave' => [
                'value' => false,
                'type' => 'boolean',
                'description' => 'Automatically approve sick leave with medical certificate'
            ],
            'max_leave_days_advance' => [
                'value' => 30,
                'type' => 'integer',
                'description' => 'Maximum days in advance to request leave'
            ],
        ];
    }

    public static function initializeDefaults()
    {
        $defaults = static::getDefaultSettings();
        
        foreach ($defaults as $key => $config) {
            $existing = static::where('key', $key)->first();
            if (!$existing) {
                static::set($key, $config['value'], $config['type'], $config['description']);
            }
        }
    }
}
