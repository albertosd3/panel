<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    protected $fillable = [
        'domain',
        'display_name',
        'is_active',
        'is_default',
        'force_https',
        'settings',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'force_https' => 'boolean',
        'settings' => 'json',
        'last_used_at' => 'datetime',
    ];

    public function shortlinks(): HasMany
    {
        return $this->hasMany(Shortlink::class);
    }

    public function getUrlAttribute(): string
    {
        $protocol = $this->force_https ? 'https' : 'http';
        return "{$protocol}://{$this->domain}";
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->where('is_active', true)->first()
            ?? static::where('is_active', true)->orderBy('id')->first();
    }

    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)->orderBy('is_default', 'desc')->orderBy('domain')->get();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
