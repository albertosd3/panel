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
        'slug', 'destination', 'clicks', 'active', 'meta', 'domain_id',
        'is_rotator', 'rotation_type', 'destinations', 'current_index'
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_rotator' => 'boolean',
        'meta' => 'array',
        'destinations' => 'array',
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

    /**
     * Get the next destination URL for rotation
     */
    public function getNextDestination(): string
    {
        if (!$this->is_rotator || empty($this->destinations)) {
            return $this->destination;
        }

        $destinations = $this->destinations;
        
        switch ($this->rotation_type) {
            case 'sequential':
                return $this->getSequentialDestination($destinations);
            case 'weighted':
                return $this->getWeightedDestination($destinations);
            case 'random':
            default:
                return $this->getRandomDestination($destinations);
        }
    }

    /**
     * Get random destination
     */
    private function getRandomDestination(array $destinations): string
    {
        $activeDestinations = array_filter($destinations, fn($dest) => $dest['active'] ?? true);
        if (empty($activeDestinations)) {
            return $this->destination;
        }
        
        $randomKey = array_rand($activeDestinations);
        return $activeDestinations[$randomKey]['url'];
    }

    /**
     * Get sequential destination and update index
     */
    private function getSequentialDestination(array $destinations): string
    {
        $activeDestinations = array_values(array_filter($destinations, fn($dest) => $dest['active'] ?? true));
        if (empty($activeDestinations)) {
            return $this->destination;
        }

        $currentIndex = $this->current_index % count($activeDestinations);
        $destination = $activeDestinations[$currentIndex]['url'];
        
        // Update index for next request
        $this->increment('current_index');
        
        return $destination;
    }

    /**
     * Get weighted destination
     */
    private function getWeightedDestination(array $destinations): string
    {
        $activeDestinations = array_filter($destinations, fn($dest) => $dest['active'] ?? true);
        if (empty($activeDestinations)) {
            return $this->destination;
        }

        $totalWeight = array_sum(array_column($activeDestinations, 'weight'));
        if ($totalWeight <= 0) {
            return $this->getRandomDestination($activeDestinations);
        }

        $random = mt_rand(1, $totalWeight);
        $currentWeight = 0;

        foreach ($activeDestinations as $dest) {
            $currentWeight += $dest['weight'] ?? 1;
            if ($random <= $currentWeight) {
                return $dest['url'];
            }
        }

        return $activeDestinations[0]['url'];
    }

    /**
     * Get all destinations count
     */
    public function getDestinationsCountAttribute(): int
    {
        if (!$this->is_rotator || empty($this->destinations)) {
            return 1;
        }
        
        return count(array_filter($this->destinations, fn($dest) => $dest['active'] ?? true));
    }

    /**
     * Get rotation summary for display
     */
    public function getRotationSummaryAttribute(): string
    {
        if (!$this->is_rotator) {
            return 'Single destination';
        }

        $count = $this->destinations_count;
        $type = ucfirst($this->rotation_type);
        
        return "{$count} destinations • {$type} rotation";
    }
}
