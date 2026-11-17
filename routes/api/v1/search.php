<?php

use App\Http\Controllers\V1\Search\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', SearchController::class)
    ->middleware('throttle:30,1')
    ->name('search');
