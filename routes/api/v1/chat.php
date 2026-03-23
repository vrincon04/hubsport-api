<?php

use App\Http\Controllers\V1\Chat\ConversationController;
use App\Http\Controllers\V1\Chat\MessageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ConversationController::class, 'index'])
    ->name('conversations.index');
Route::post('/', [ConversationController::class, 'store'])
    ->name('conversations.store');

Route::get('/{conversation}', [MessageController::class, 'index'])
    ->name('messages.index');
Route::post('/{conversation}/messages', [MessageController::class, 'store'])
    ->name('messages.store');
