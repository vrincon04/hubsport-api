<?php

use App\Http\Controllers\V1\Sport\SportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SportController::class , 'index'])
    ->name('list');

// News
Route::get('/news', [\App\Http\Controllers\V1\News\NewsController::class , 'index'])
    ->name('news.index');
Route::get('/news/{news}', [\App\Http\Controllers\V1\News\NewsController::class , 'show'])
    ->name('news.show');

// Match Results
Route::get('/results', [\App\Http\Controllers\V1\Sport\MatchResultController::class , 'index'])
    ->name('results.index');