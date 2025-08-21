<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PanelSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "panel_setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $type = 'string', string $group = 'general', string $description = null): bool
    {
        $stringValue = self::valueToString($value, $type);
        
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'type' => $type,
                'group' => $group,
                'description' => $description
            ]
        );

        // Clear cache
        Cache::forget("panel_setting_{$key}");
        
        return $setting->wasRecentlyCreated || $setting->wasChanged();
    }

    /**
     * Get all settings by group
     */
    public static function getGroup(string $group): array
    {
        $settings = self::where('group', $group)->get();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting->key] = self::castValue($setting->value, $setting->type);
        }
        
        return $result;
    }

    /**
     * Cast string value to proper type
     */
    protected static function castValue(?string $value, string $type)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'boolean':
                return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Convert value to string for storage
     */
    protected static function valueToString($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'json':
                return json_encode($value);
            default:
                return (string) $value;
        }
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget("panel_setting_{$setting->key}");
        }
    }
}
