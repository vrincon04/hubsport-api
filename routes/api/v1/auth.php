<?php

use App\Http\Controllers\V1\Auth\AuthenticatedController;
use App\Http\Controllers\V1\Auth\RegisteredController;
use App\Http\Controllers\V1\Auth\SocialAuthenticatedController;
use App\Http\Controllers\V1\Auth\VerifyOptController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthenticatedController::class, 'store'])
    ->name('login');
Route::post('/register', RegisteredController::class)
    ->name('register');
Route::post('/{driver}/callback', SocialAuthenticatedController::class)
    ->middleware('throttle:6,1')
    ->name('register.social');

Route::post('verify/opt', VerifyOptController::class);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::delete('/logout', [AuthenticatedController::class, 'destroy'])
        ->name('logout');
});
