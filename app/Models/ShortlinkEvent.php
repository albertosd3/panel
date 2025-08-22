<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortlinkEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'shortlink_id',
        'ip',
        'country',
        'city',
        'asn',
        'org',
        'device',
        'platform',
        'browser',
        'referrer',
        'is_bot',
        'clicked_at'
    ];

    protected $casts = [
        'is_bot' => 'boolean',
        'clicked_at' => 'datetime',
    ];

    /**
     * Boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->clicked_at)) {
                $event->clicked_at = now();
            }
        });
    }

    /**
     * Get the shortlink that this event belongs to
     */
    public function shortlink()
    {
        return $this->belongsTo(Shortlink::class);
    }

    /**
     * Scope to filter by bot status
     */
    public function scopeBots($query, $isBot = true)
    {
        return $query->where('is_bot', $isBot);
    }

    /**
     * Scope to filter by country
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope to filter by IP
     */
    public function scopeByIp($query, $ip)
    {
        return $query->where('ip', $ip);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('clicked_at', [$startDate, $endDate]);
    }
}
