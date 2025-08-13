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

    // Ensure computed attribute is included in JSON responses
    protected $appends = ['full_url'];

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
        // Prefer explicitly associated domain
        if ($this->domain) {
            return rtrim($this->domain->url, '/') . '/' . $this->slug;
        }

        // Fallback to default active domain from domains table if available
        try {
            /** @var \App\Models\Domain|null $default */
            $default = Domain::getDefault();
            if ($default) {
                return rtrim($default->url, '/') . '/' . $this->slug;
            }
        } catch (\Throwable $e) {
            // Ignore and fallback below
        }
        
        // Final fallback to APP_URL (avoid hardcoded localhost)
        $appUrl = (string) config('app.url', '');
        if ($appUrl !== '') {
            return rtrim($appUrl, '/') . '/' . $this->slug;
        }
        
        // As a last resort, use current application URL helper
        return url($this->slug);
    }

    public function getQrCodeUrlAttribute(): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($this->full_url);
    }
}
