<?php

namespace App\Services;

use App\Models\PanelSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StopbotService
{
    protected $config;

    public function __construct()
    {
        // Get settings from database instead of config file
        $this->config = [
            'enabled' => PanelSetting::get('stopbot_enabled', false),
            'api_key' => PanelSetting::get('stopbot_api_key', ''),
            'redirect_url' => PanelSetting::get('stopbot_redirect_url', 'https://www.google.com'),
            'log_enabled' => PanelSetting::get('stopbot_log_enabled', true),
            'timeout' => PanelSetting::get('stopbot_timeout', 5),
        ];
    }

    /**
     * Check if IP should be blocked using Stopbot.net API
     */
    public function shouldBlock(string $ip, string $userAgent, string $requestUri): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (empty($this->config['api_key'])) {
            $this->logError('Stopbot API key not configured');
            return false;
        }

        try {
            $response = Http::timeout($this->config['timeout'] ?? 5)
                ->get('https://stopbot.net/api/blocker', [
                    'apikey' => $this->config['api_key'],
                    'ip' => $ip,
                    'ua' => $userAgent,
                    'url' => $requestUri,
                    'rand' => rand(1, 1000000)
                ]);

            if (!$response->successful()) {
                $this->logError('Stopbot API request failed: HTTP ' . $response->status());
                return false;
            }

            $data = $response->json();

            if (!$data) {
                $this->logError('Stopbot API returned invalid JSON');
                return false;
            }

            switch ($data['status'] ?? null) {
                case 'error':
                    $this->logError('Stopbot API error: ' . ($data['message'] ?? 'Unknown error'));
                    return false;

                case 'success':
                    $blockAccess = $data['IPStatus']['BlockAccess'] ?? 0;
                    
                    if ($blockAccess == 1) {
                        $this->logInfo("Stopbot blocked IP: {$ip}");
                        return true;
                    }
                    
                    return false;

                default:
                    $this->logError('Stopbot API returned unknown status: ' . ($data['status'] ?? 'null'));
                    return false;
            }

        } catch (\Exception $e) {
            $this->logError('Stopbot API exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get redirect URL for blocked requests
     */
    public function getRedirectUrl(): ?string
    {
        return $this->config['redirect_url'] ?? null;
    }

    /**
     * Check if Stopbot is enabled
     */
    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    /**
     * Log error message
     */
    protected function logError(string $message): void
    {
        if ($this->config['log_enabled'] ?? true) {
            Log::error('[Stopbot] ' . $message);
        }
    }

    /**
     * Log info message
     */
    protected function logInfo(string $message): void
    {
        if ($this->config['log_enabled'] ?? true) {
            Log::info('[Stopbot] ' . $message);
        }
    }
}
