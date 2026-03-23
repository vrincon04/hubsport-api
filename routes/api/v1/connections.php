<?php

use App\Http\Controllers\V1\Connection\ConnectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ConnectionController::class, 'index'])
    ->name('connections.index');
Route::get('/requests/incoming', [ConnectionController::class, 'incoming'])
    ->name('connections.incoming');
Route::get('/requests/outgoing', [ConnectionController::class, 'outgoing'])
    ->name('connections.outgoing');
Route::post('/', [ConnectionController::class, 'store'])
    ->name('connections.store');
Route::post('/{connection}/accept', [ConnectionController::class, 'accept'])
    ->name('connections.accept');
Route::post('/{connection}/reject', [ConnectionController::class, 'reject'])
    ->name('connections.reject');
Route::delete('/{connection}', [ConnectionController::class, 'destroy'])
    ->name('connections.destroy');
