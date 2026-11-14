<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'json',
        'is_public' => 'boolean',
    ];

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $settings = self::getAll();
        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @param string $type
     * @return bool
     */
    public static function set($key, $value, $group = 'general', $type = 'string')
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'type' => $type,
            ]
        );

        // Clear cache
        Cache::forget('settings_all');

        return $setting;
    }

    /**
     * Get all settings as key-value pair.
     *
     * @return array
     */
    public static function getAll()
    {
        return Cache::remember('settings_all', 3600, function () {
            return self::all()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get settings by group.
     *
     * @param string $group
     * @return array
     */
    public static function getByGroup($group)
    {
        $settings = self::where('group', $group)->get();
        return $settings->pluck('value', 'key')->toArray();
    }

    /**
     * Delete a setting by key.
     *
     * @param string $key
     * @return bool
     */
    public static function deleteKey($key)
    {
        $deleted = self::where('key', $key)->delete();
        
        if ($deleted) {
            Cache::forget('settings_all');
            return true;
        }

        return false;
    }

    /**
     * Get setting type.
     *
     * @return string
     */
    public function getTypeAttribute($value)
    {
        return $value ?? 'string';
    }

    /**
     * Mutator for value based on type.
     *
     * @param mixed $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        if ($this->type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        if ($this->type === 'integer') {
            return (int) $value;
        }

        if ($this->type === 'float') {
            return (float) $value;
        }

        if ($this->type === 'array' || $this->type === 'json') {
            return json_decode($value, true);
        }

        return $value;
    }
}