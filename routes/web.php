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
        Route::post('/reset-visitors/{slug}', [ShortlinkController::class, 'resetVisitors'])->name('api.reset-visitors');
        Route::post('/reset-all-visitors', [ShortlinkController::class, 'resetAllVisitors'])->name('api.reset-all-visitors');
        Route::delete('/delete/{slug}', [ShortlinkController::class, 'destroy'])->name('api.delete-shortlink');
        Route::put('/rotator/{slug}', [ShortlinkController::class, 'updateRotator'])->name('api.update-rotator');
        Route::get('/rotator/{slug}', [ShortlinkController::class, 'getRotator'])->name('api.get-rotator');
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

// Health check route for domain testing
Route::get('/health-check', function () {
    return response('OK', 200)->header('Content-Type', 'text/plain');
})->name('health-check');

// JS human verification endpoint used by public redirect challenge
Route::post('/_human_verify', [ShortlinkController::class, 'verifyHuman'])
    ->name('public.human_verify')
    ->withoutMiddleware([ValidateCsrfToken::class]);

// Public redirect route (keep at bottom, after other routes)
Route::get('/{slug}', [ShortlinkController::class, 'redirect'])
    ->where('slug', '[A-Za-z0-9_-]+')
    ->withoutMiddleware([
        StartSession::class,
        AuthenticateSession::class,
        ShareErrorsFromSession::class,
        ValidateCsrfToken::class,
    ]);
