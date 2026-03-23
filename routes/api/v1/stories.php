<?php

use App\Http\Controllers\V1\Post\StoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoryController::class, 'index'])
    ->name('stories.index');
Route::post('/', [StoryController::class, 'store'])
    ->name('stories.store');
