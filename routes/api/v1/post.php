<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Http\Controllers\V1\Post\IndexController::class)
    ->name('list');
Route::post('/', \App\Http\Controllers\V1\Post\StoreController::class)
    ->name('store');
Route::prefix('/{post}')
    ->group(function () {
        Route::get('/', \App\Http\Controllers\V1\Post\ShowController::class)
            ->name('show');
        Route::delete('/', \App\Http\Controllers\V1\Post\DestroyController::class)
            ->name('destroy');
        Route::post('like', \App\Http\Controllers\V1\Post\LikeController::class)
            ->name('like');
        Route::delete('unlike', \App\Http\Controllers\V1\Post\UnlikeController::class)
            ->name('unlike');
    });

