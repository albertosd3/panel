<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PanelAuthController;

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

    Route::post('/panel/logout', [PanelAuthController::class, 'logout'])->name('panel.logout');
});
