<?php

use App\Http\Controllers\V1\Auth\AuthenticatedController;
use App\Http\Controllers\V1\Auth\DemoLoginController;
use App\Http\Controllers\V1\Auth\RegisteredController;
use App\Http\Controllers\V1\Auth\SocialAuthenticatedController;
use App\Http\Controllers\V1\Auth\VerifyOptController;
use Illuminate\Support\Facades\Route;

Route::post('/demo-login', DemoLoginController::class)
    ->middleware('throttle:6,1')
    ->name('demo.login');

Route::post('/login', [AuthenticatedController::class, 'store'])
    ->name('login');
Route::post('/register', RegisteredController::class)
    ->name('register');
Route::post('/{driver}/callback', SocialAuthenticatedController::class)
    ->middleware('throttle:6,1')
    ->name('register.social');

Route::post('verify/opt', VerifyOptController::class)
    ->middleware('throttle:5,1')
    ->name('verify.opt');

Route::post('forgot-password', \App\Http\Controllers\V1\Auth\ForgotPasswordController::class)
    ->name('password.email');

Route::post('reset-password', \App\Http\Controllers\V1\Auth\ResetPasswordController::class)
    ->name('password.reset');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::delete('/logout', [AuthenticatedController::class, 'destroy'])
        ->name('logout');
});
