<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortlinkVisitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'shortlink_id', 
        'ip', 
        'hits', 
        'first_seen', 
        'last_seen', 
        'is_bot', 
        'country', 
        'city', 
        'asn', 
        'org'
    ];

    protected $casts = [
        'first_seen' => 'datetime',
        'last_seen' => 'datetime',
        'is_bot' => 'boolean',
        'hits' => 'integer',
    ];

    /**
     * Boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($visitor) {
            if (empty($visitor->first_seen)) {
                $visitor->first_seen = now();
            }
            if (empty($visitor->last_seen)) {
                $visitor->last_seen = now();
            }
            if (empty($visitor->hits)) {
                $visitor->hits = 1;
            }
        });

        static::updating(function ($visitor) {
            $visitor->last_seen = now();
        });
    }

    /**
     * Get the shortlink that this visitor belongs to
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
}
