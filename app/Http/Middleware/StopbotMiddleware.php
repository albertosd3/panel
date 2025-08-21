<?php

namespace App\Http\Middleware;

use App\Services\StopbotService;
use Closure;
use Illuminate\Http\Request;

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
        if (!$this->stopbot->isEnabled()) {
            return $next($request);
        }

        // Get real IP (considering CloudFlare and proxies)
        $ip = $this->getRealIp($request);
        $userAgent = $request->userAgent() ?? '';
        $requestUri = $request->getRequestUri();

        // Check if IP should be blocked
        if ($this->stopbot->shouldBlock($ip, $userAgent, $requestUri)) {
            $redirectUrl = $this->stopbot->getRedirectUrl();
            
            if (!empty($redirectUrl)) {
                return redirect()->away($redirectUrl);
            } else {
                abort(404);
            }
        }

        return $next($request);
    }

    /**
     * Get the real IP address considering CloudFlare and other proxies
     */
    protected function getRealIp(Request $request): string
    {
        // Check CloudFlare connecting IP
        if ($cfIp = $request->header('CF-Connecting-IP')) {
            return $cfIp;
        }

        // Check other proxy headers
        if ($clientIp = $request->header('HTTP_CLIENT_IP')) {
            if (filter_var($clientIp, FILTER_VALIDATE_IP)) {
                return $clientIp;
            }
        }

        if ($forwardedIp = $request->header('HTTP_X_FORWARDED_FOR')) {
            if (filter_var($forwardedIp, FILTER_VALIDATE_IP)) {
                return $forwardedIp;
            }
        }

        // Fallback to default IP
        return $request->ip();
    }
}
