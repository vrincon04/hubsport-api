<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\Job\JobOfferController;

Route::get('/', [JobOfferController::class, 'index']);
Route::post('/', [JobOfferController::class, 'store']);
Route::get('/my-offers', [JobOfferController::class, 'myOffers']);
Route::get('/my-applications', [JobOfferController::class, 'myApplications']);
Route::get('/my-saved', [JobOfferController::class, 'mySaved']);
Route::get('/{id}', [JobOfferController::class, 'show']);
Route::post('/{id}/apply', [JobOfferController::class, 'apply']);
Route::post('/{id}/save', [JobOfferController::class, 'save']);
Route::delete('/{id}/unsave', [JobOfferController::class, 'unsave']);
