<?php

use App\Http\Controllers\V1\Event\EventController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventController::class, 'index'])->name('index');
Route::post('/', [EventController::class, 'store'])->name('store');
Route::get('/{id}', [EventController::class, 'show'])->name('show');
Route::get('/{id}/calendar.ics', [EventController::class, 'calendar'])->name('calendar');
Route::post('/{id}/participate', [EventController::class, 'participate'])->name('participate');
Route::delete('/{id}/participate', [EventController::class, 'leave'])->name('leave');
Route::post('/{id}/participants/me/activity', [EventController::class, 'linkActivity'])->name('link-activity');
Route::post('/{id}/save', [EventController::class, 'save'])->name('save');
Route::delete('/{id}/unsave', [EventController::class, 'unsave'])->name('unsave');
