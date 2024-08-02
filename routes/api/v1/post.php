<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Http\Controllers\V1\Post\IndexController::class)
    ->name('list');
Route::get('/{post}', \App\Http\Controllers\V1\Post\ShowController::class)
    ->name('show');
Route::post('/', \App\Http\Controllers\V1\Post\StoreController::class)
    ->name('store');

