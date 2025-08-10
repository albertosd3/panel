<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PanelAuthController;
use App\Http\Controllers\ShortlinkController;
use Illuminate\Session\Middleware\StartSession;

Route::get('/', function () {
    return view('welcome');
});

// Panel auth routes
Route::get('/panel/login', [PanelAuthController::class, 'showLogin'])->name('panel.login');
Route::post('/panel/login', [PanelAuthController::class, 'verify'])
    ->name('panel.verify')
    ->middleware('throttle:5,1');

Route::middleware('panel.auth')->group(function () {
    Route::get('/panel', function () {
        return view('panel.index');
    })->name('panel.index');

    // Shortlink management UI
    Route::get('/panel/shortlinks', [ShortlinkController::class, 'index'])->name('panel.shortlinks');
    Route::post('/panel/shortlinks', [ShortlinkController::class, 'store'])->name('panel.shortlinks.store');
    Route::get('/panel/shortlinks/{slug}/stats', [ShortlinkController::class, 'stats'])->name('panel.shortlinks.stats');

    Route::post('/panel/logout', [PanelAuthController::class, 'logout'])->name('panel.logout');
});

// Public redirect route (keep at bottom, after other routes)
Route::get('/{slug}', [ShortlinkController::class, 'redirect'])
    ->where('slug', '[A-Za-z0-9_-]+')
    ->withoutMiddleware([StartSession::class]);
