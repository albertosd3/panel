<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shortlink extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'destination', 'clicks', 'active', 'meta', 'domain_id'
    ];

    protected $casts = [
        'active' => 'boolean',
        'meta' => 'array',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShortlinkEvent::class);
    }

    public function getFullUrlAttribute(): string
    {
        if ($this->domain) {
            return $this->domain->url . '/' . $this->slug;
        }
        
        $defaultDomain = config('app.url', 'http://localhost');
        return rtrim($defaultDomain, '/') . '/' . $this->slug;
    }

    public function getQrCodeUrlAttribute(): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($this->full_url);
    }
}
