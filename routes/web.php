<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PanelAuthController;
use App\Http\Controllers\ShortlinkController;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

Route::get('/', function () {
    return view('welcome');
});

// Panel auth routes
Route::get('/panel/login', [PanelAuthController::class, 'showLogin'])->name('panel.login');
Route::post('/panel/login', [PanelAuthController::class, 'verify'])
    ->name('panel.verify')
    ->middleware('throttle:5,1');

Route::middleware('panel.auth')->group(function () {
    // Redirect dashboard to shortlinks page directly
    Route::get('/panel', fn () => redirect()->route('panel.shortlinks'))->name('panel.index');

    // Shortlink management UI
    Route::get('/panel/shortlinks', [ShortlinkController::class, 'index'])->name('panel.shortlinks');
    Route::get('/panel/shortlinks/list', [ShortlinkController::class, 'list'])->name('panel.shortlinks.list');
    Route::get('/panel/analytics', [ShortlinkController::class, 'analytics'])->name('panel.analytics');
    Route::post('/panel/shortlinks', [ShortlinkController::class, 'store'])->name('panel.shortlinks.store');
    Route::get('/panel/shortlinks/{slug}/stats', [ShortlinkController::class, 'stats'])->name('panel.shortlinks.stats');

    Route::post('/panel/logout', [PanelAuthController::class, 'logout'])->name('panel.logout');
    Route::post('/logout', [PanelAuthController::class, 'logout'])->name('logout');
    
    // API routes for AJAX calls
    Route::prefix('api')->group(function () {
        Route::get('/links', [ShortlinkController::class, 'list'])->name('api.links');
        Route::get('/analytics', [ShortlinkController::class, 'analytics'])->name('api.analytics');
        Route::post('/create', [ShortlinkController::class, 'store'])->name('api.create');
        Route::get('/domains', [App\Http\Controllers\DomainController::class, 'apiList'])->name('api.domains');
        Route::get('/debug', function () {
            return response()->json([
                'ok' => true,
                'message' => 'API is working',
                'time' => now(),
                'csrf' => csrf_token(),
                'authenticated' => session('panel_authenticated', false),
                'session_id' => session()->getId()
            ]);
        });
    });
});

// Domain management routes (authenticated)
Route::middleware('panel.auth')->prefix('panel')->group(function () {
    Route::get('/domains', [App\Http\Controllers\DomainController::class, 'index'])->name('panel.domains');
    Route::post('/domains', [App\Http\Controllers\DomainController::class, 'store'])->name('panel.domains.store');
    Route::put('/domains/{domain}', [App\Http\Controllers\DomainController::class, 'update'])->name('panel.domains.update');
    Route::delete('/domains/{domain}', [App\Http\Controllers\DomainController::class, 'destroy'])->name('panel.domains.destroy');
    Route::patch('/domains/{domain}/set-default', [App\Http\Controllers\DomainController::class, 'setDefault'])->name('panel.domains.set-default');
    Route::patch('/domains/{domain}/toggle-active', [App\Http\Controllers\DomainController::class, 'toggleActive'])->name('panel.domains.toggle-active');
    Route::post('/domains/{domain}/test', [App\Http\Controllers\DomainController::class, 'testDomain'])->name('panel.domains.test');
});

// Health check route for domain testing
Route::get('/health-check', function () {
    return response('OK', 200)->header('Content-Type', 'text/plain');
})->name('health-check');

// Public redirect route (keep at bottom, after other routes)
Route::get('/{slug}', [ShortlinkController::class, 'redirect'])
    ->where('slug', '[A-Za-z0-9_-]+')
    ->withoutMiddleware([
        StartSession::class,
        AuthenticateSession::class,
        ShareErrorsFromSession::class,
        ValidateCsrfToken::class,
    ]);
