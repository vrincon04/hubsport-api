<?php

use App\Http\Controllers\V1\Reference\ReferenceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ReferenceController::class, 'index'])->name('index');
Route::post('/requests', [ReferenceController::class, 'store'])->name('store');
Route::get('/users/{userId}', [ReferenceController::class, 'publishedForUser'])->name('publishedForUser');
Route::post('/requests/{id}/accept', [ReferenceController::class, 'accept'])->name('accept');
Route::post('/requests/{id}/reject', [ReferenceController::class, 'reject'])->name('reject');
Route::post('/requests/{id}/write', [ReferenceController::class, 'write'])->name('write');
Route::post('/requests/{id}/confirm', [ReferenceController::class, 'confirm'])->name('confirm');
