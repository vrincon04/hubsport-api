<?php

use App\Http\Controllers\V1\Role\IndexController;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexController::class)
    ->name('list');
