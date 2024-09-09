<?php

use App\Http\Controllers\V1\Post\DestroyController;
use App\Http\Controllers\V1\Post\IndexController;
use App\Http\Controllers\V1\Post\LikeController;
use App\Http\Controllers\V1\Post\ShowController;
use App\Http\Controllers\V1\Post\StoreController;
use App\Http\Controllers\V1\Post\UnlikeController;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexController::class)
    ->name('list');
Route::post('/', StoreController::class)
    ->name('store');
Route::prefix('/{post}')
    ->group(function () {
        Route::get('/', ShowController::class)
            ->name('show');
        Route::delete('/', DestroyController::class)
            ->name('destroy');
        Route::post('like', LikeController::class)
            ->name('like');
        Route::delete('unlike', UnlikeController::class)
            ->name('unlike');
    });

