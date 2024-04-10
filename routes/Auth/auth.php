<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::group([
    'as'     => 'auth',
    'prefix' => '/api/v1/auth',
], function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
    // Route::get('/user', [AuthController::class, 'getUser'])->middleware('auth:api', 'admin');

    Route::get('/user-auth', [AuthController::class, 'userAuth'])->middleware('auth:api');
    Route::get('/admin-auth', [AuthController::class, 'adminAuth'])->middleware('auth:api', 'admin');
    
});