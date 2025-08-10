<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePanelAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('panel_authenticated')) {
            return redirect()->route('panel.login');
        }

        return $next($request);
    }
}
