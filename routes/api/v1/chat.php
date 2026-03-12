<?php

use App\Http\Controllers\V1\Chat\ConversationController;
use App\Http\Controllers\V1\Chat\MessageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ConversationController::class , 'index']);
Route::post('/', [ConversationController::class , 'store']);

Route::get('/{conversation}', [MessageController::class , 'index']);
Route::post('/{conversation}/messages', [MessageController::class , 'store']);