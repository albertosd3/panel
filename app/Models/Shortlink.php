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
        // 1) Prefer the default active domain from DB
        try {
            /** @var \App\Models\Domain|null $default */
            $default = Domain::getDefault();
            if ($default) {
                return rtrim($default->url, '/') . '/' . $this->slug;
            }
        } catch (\Throwable $e) {
            // Ignore and try fallbacks below
        }

        // 2) Fallback to configured default domain
        $cfgDomain = trim((string) config('panel.default_domain', ''));
        if ($cfgDomain !== '') {
            $hasScheme = (bool) preg_match('#^https?://#i', $cfgDomain);
            $scheme = $hasScheme ? '' : (config('panel.default_force_https', true) ? 'https://' : 'http://');
            return rtrim($scheme . $cfgDomain, '/') . '/' . $this->slug;
        }

        // 3) Use APP_URL only if it's not localhost
        $appUrl = (string) config('app.url', '');
        if ($appUrl !== '' && !preg_match('/localhost|127\\.0\\.0\\.1/i', $appUrl)) {
            return rtrim($appUrl, '/') . '/' . $this->slug;
        }

        // 4) Use current request host if available (last local fallback)
        try {
            if (function_exists('request')) {
                $req = request();
                if ($req) {
                    $host = $req->getSchemeAndHttpHost();
                    if (!empty($host)) {
                        return rtrim($host, '/') . '/' . $this->slug;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        // 5) Final fallback
        return url($this->slug);
    }

    public function getQrCodeUrlAttribute(): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($this->full_url);
    }
}
