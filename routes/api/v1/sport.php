<?php

use App\Http\Controllers\V1\Sport\SportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SportController::class, 'index'])
    ->name('list');
