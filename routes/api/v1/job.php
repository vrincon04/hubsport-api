<?php

use App\Http\Controllers\V1\Job\JobOfferController;
use Illuminate\Support\Facades\Route;

Route::get('/', [JobOfferController::class, 'index'])
    ->name('index');
Route::post('/', [JobOfferController::class, 'store'])
    ->name('store');
Route::get('/my-offers', [JobOfferController::class, 'myOffers'])
    ->name('myOffers');
Route::get('/my-applications', [JobOfferController::class, 'myApplications'])
    ->name('myApplications');
Route::get('/my-saved', [JobOfferController::class, 'mySaved'])
    ->name('mySaved');
Route::get('/{id}', [JobOfferController::class, 'show'])
    ->name('show');
Route::get('/{id}/applicants', [JobOfferController::class, 'applicants'])
    ->name('applicants');
Route::post('/{id}/apply', [JobOfferController::class, 'apply'])
    ->name('apply');
Route::delete('/{id}/apply', [JobOfferController::class, 'cancelApplication'])
    ->name('cancelApplication');
Route::post('/{id}/save', [JobOfferController::class, 'save'])
    ->name('save');
Route::delete('/{id}/unsave', [JobOfferController::class, 'unsave'])
    ->name('unsave');
