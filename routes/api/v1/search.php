<?php

use App\Http\Controllers\V1\Search\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', SearchController::class)
    ->name('search');
