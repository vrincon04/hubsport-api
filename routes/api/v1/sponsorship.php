<?php

use App\Http\Controllers\V1\Sponsorship\SponsorshipController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SponsorshipController::class, 'index'])->name('index');
Route::post('/', [SponsorshipController::class, 'store'])->name('store');
Route::get('/{id}', [SponsorshipController::class, 'show'])->name('show');
