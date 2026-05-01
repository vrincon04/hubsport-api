<?php

use App\Http\Controllers\V1\Event\EventController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventController::class, 'index'])->name('index');
Route::post('/', [EventController::class, 'store'])->name('store');
Route::get('/{id}', [EventController::class, 'show'])->name('show');
Route::post('/{id}/participate', [EventController::class, 'participate'])->name('participate');
Route::post('/{id}/save', [EventController::class, 'save'])->name('save');
Route::delete('/{id}/unsave', [EventController::class, 'unsave'])->name('unsave');
