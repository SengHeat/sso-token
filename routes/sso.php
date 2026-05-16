<?php

use Illuminate\Support\Facades\Route;
use SengHeat\LaravelSso\Http\Controllers\SSOController;

Route::middleware(['web', 'throttle:20,1'])
    ->prefix('sso')
    ->name('sso.')
    ->group(function () {
        Route::get('/{provider}/redirect', [SSOController::class, 'redirect'])->name('redirect');
        Route::get('/{provider}/callback', [SSOController::class, 'callback'])->name('callback');
        Route::post('/logout',             [SSOController::class, 'logout'])->name('logout');
    });
