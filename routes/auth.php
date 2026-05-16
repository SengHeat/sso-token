<?php

use Illuminate\Support\Facades\Route;
use SengHeat\LaravelSso\Http\Controllers\FormAuthController;

// ── Web form routes ────────────────────────────────────────────────────────────
Route::middleware(['web', 'throttle:20,1'])
    ->prefix('sso')
    ->name('sso.')
    ->group(function () {
        Route::get('/login',    [FormAuthController::class, 'showLogin'])->name('login.form');
        Route::post('/login',   [FormAuthController::class, 'login'])->name('login');
        Route::post('/logout',  [FormAuthController::class, 'logout'])->name('form.logout');

        Route::get('/register',  [FormAuthController::class, 'showRegister'])->name('register.form');
        Route::post('/register', [FormAuthController::class, 'register'])->name('register');
    });

// ── API JSON routes ────────────────────────────────────────────────────────────
Route::middleware(['api', 'throttle:60,1'])
    ->prefix('api/sso')
    ->name('sso.api.')
    ->group(function () {
        Route::post('/login',    [FormAuthController::class, 'login'])->name('login');
        Route::post('/register', [FormAuthController::class, 'register'])->name('register');
        Route::post('/exchange', [FormAuthController::class, 'exchange'])->name('exchange');
        Route::post('/logout',   [FormAuthController::class, 'logout'])->middleware('auth:api')->name('logout');
        Route::get('/user',      [FormAuthController::class, 'user'])->middleware('auth:api')->name('user');
    });
