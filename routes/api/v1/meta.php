<?php

use App\Http\Controllers\V1\Meta\ContactController;
use App\Http\Controllers\V1\Meta\HelpController;
use Illuminate\Support\Facades\Route;

Route::get('/help', HelpController::class)
    ->name('meta.help');

Route::get('/countries', \App\Http\Controllers\V1\Meta\CountryController::class)
    ->name('meta.countries');

Route::post('/contact', ContactController::class)
    ->name('meta.contact');
