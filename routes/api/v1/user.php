<?php

use App\Http\Controllers\V1\User\MeController;
use App\Http\Controllers\V1\User\ShowController;
use Illuminate\Support\Facades\Route;

Route::get('/me', MeController::class)
->name('me');
Route::get('/{user}', ShowController::class)
->name('show');
