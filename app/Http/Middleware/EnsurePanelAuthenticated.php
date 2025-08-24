<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnsurePanelAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Check if user is authenticated
            if (!session('panel_authenticated')) {
                Log::info('Unauthorized access attempt', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->url()
                ]);
                
                return redirect()->route('panel.login')
                    ->with('error', 'Silakan login terlebih dahulu.');
            }

            // Check session timeout (optional - 8 hours)
            $loginTime = session('panel_login_time');
            if ($loginTime && now()->diffInHours($loginTime) > 8) {
                Log::info('Session expired', [
                    'ip' => $request->ip(),
                    'login_time' => $loginTime
                ]);
                
                // Clear session
                $request->session()->forget('panel_authenticated');
                $request->session()->forget('panel_login_time');
                
                return redirect()->route('panel.login')
                    ->with('error', 'Session telah berakhir. Silakan login ulang.');
            }

            // User is authenticated, continue
            return $next($request);
            
        } catch (\Exception $e) {
            Log::error('Authentication middleware error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'url' => $request->url()
            ]);
            
            // Clear session on error
            $request->session()->forget('panel_authenticated');
            $request->session()->forget('panel_login_time');
            
            return redirect()->route('panel.login')
                ->with('error', 'Terjadi kesalahan sistem. Silakan login ulang.');
        }
    }
}
