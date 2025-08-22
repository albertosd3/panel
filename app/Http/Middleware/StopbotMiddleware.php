<?php

namespace App\Http\Middleware;

use App\Services\StopbotService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StopbotMiddleware
{
    protected $stopbot;

    public function __construct(StopbotService $stopbot)
    {
        $this->stopbot = $stopbot;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Get real IP first for logging
        $ip = $this->getRealIp($request);
        $userAgent = $request->userAgent() ?? '';
        $requestUri = $request->getRequestUri();

        Log::info('=== STOPBOT MIDDLEWARE START ===', [
            'ip' => $ip,
            'user_agent' => $userAgent,
            'uri' => $requestUri,
            'enabled' => $this->stopbot->isEnabled(),
            'timestamp' => now()->toISOString()
        ]);

        if (!$this->stopbot->isEnabled()) {
            Log::info('Stopbot disabled, proceeding with request', ['ip' => $ip]);
            return $next($request);
        }

        // Check if IP should be blocked
        try {
            $shouldBlock = $this->stopbot->shouldBlock($ip, $userAgent, $requestUri);
            
            Log::info('Stopbot check result', [
                'ip' => $ip,
                'should_block' => $shouldBlock,
                'user_agent' => $userAgent
            ]);

            if ($shouldBlock) {
                $redirectUrl = $this->stopbot->getRedirectUrl();
                
                Log::warning('IP blocked by Stopbot', [
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                    'redirect_url' => $redirectUrl
                ]);
                
                if (!empty($redirectUrl)) {
                    return redirect()->away($redirectUrl);
                } else {
                    abort(404, 'Access denied');
                }
            }
        } catch (\Throwable $e) {
            Log::error('Stopbot check failed, allowing request', [
                'ip' => $ip,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't block if Stopbot fails
        }

        Log::info('=== STOPBOT MIDDLEWARE COMPLETED ===', [
            'ip' => $ip,
            'proceeding' => true,
            'timestamp' => now()->toISOString()
        ]);

        return $next($request);
    }

    /**
     * Get the real IP address considering CloudFlare and other proxies
     */
    protected function getRealIp(Request $request): string
    {
        // Check CloudFlare connecting IP
        if ($cfIp = $request->header('CF-Connecting-IP')) {
            if (filter_var($cfIp, FILTER_VALIDATE_IP)) {
                Log::debug('Using CloudFlare IP', ['cf_ip' => $cfIp]);
                return $cfIp;
            }
        }

        // Check other proxy headers
        if ($clientIp = $request->header('HTTP_CLIENT_IP')) {
            if (filter_var($clientIp, FILTER_VALIDATE_IP)) {
                Log::debug('Using HTTP_CLIENT_IP', ['client_ip' => $clientIp]);
                return $clientIp;
            }
        }

        if ($forwardedIp = $request->header('HTTP_X_FORWARDED_FOR')) {
            if (filter_var($forwardedIp, FILTER_VALIDATE_IP)) {
                Log::debug('Using HTTP_X_FORWARDED_FOR', ['forwarded_ip' => $forwardedIp]);
                return $forwardedIp;
            }
        }

        // Fallback to default IP
        $defaultIp = $request->ip();
        Log::debug('Using default IP', ['default_ip' => $defaultIp]);
        return $defaultIp;
    }
}
