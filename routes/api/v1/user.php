<?php

use App\Http\Controllers\V1\User\MeController;
use App\Http\Controllers\V1\User\ShowController;
use App\Http\Controllers\V1\User\UpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/me', MeController::class)
->name('me');

Route::prefix('/{user}')
    ->group(function () {
        Route::get('/', ShowController::class)
            ->name('show');
        Route::put('/', UpdateController::class)
            ->name('update');
    });
