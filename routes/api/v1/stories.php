<?php

use Illuminate\Support\Facades\Route;

Route::get('/stories', [\App\Http\Controllers\V1\Post\StoryController::class , 'index'])
    ->middleware('auth:sanctum')
    ->name('stories.index');

Route::post('/stories', [\App\Http\Controllers\V1\Post\StoryController::class , 'store'])
    ->middleware('auth:sanctum')
    ->name('stories.store');