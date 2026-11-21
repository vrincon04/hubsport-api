<?php

use App\Http\Controllers\V1\User\MeController;
use App\Http\Controllers\V1\User\ShowController;
use App\Http\Controllers\V1\User\UpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/me', MeController::class)
    ->name('me');

Route::get('/settings', [\App\Http\Controllers\V1\User\SettingsController::class , 'show'])
    ->name('settings.show');
Route::patch('/settings', [\App\Http\Controllers\V1\User\SettingsController::class , 'update'])
    ->name('settings.update');
Route::post('/settings/change-password', [\App\Http\Controllers\V1\User\SettingsController::class , 'changePassword'])
    ->name('settings.changePassword');
Route::get('/settings/sessions', [\App\Http\Controllers\V1\User\SettingsController::class , 'sessions'])
    ->name('settings.sessions');
Route::delete('/settings/sessions/{id}', [\App\Http\Controllers\V1\User\SettingsController::class , 'destroySession'])
    ->name('settings.sessions.destroy');

Route::get('/notifications', [\App\Http\Controllers\V1\User\NotificationController::class , 'index'])
    ->name('notifications.index');
Route::patch('/notifications/{id}/read', [\App\Http\Controllers\V1\User\NotificationController::class , 'markAsRead'])
    ->name('notifications.read');
Route::post('/notifications/read-all', [\App\Http\Controllers\V1\User\NotificationController::class , 'markAllAsRead'])
    ->name('notifications.readAll');

Route::get('/me/metrics', [\App\Http\Controllers\V1\User\ScoutMetricsController::class, 'metrics'])
    ->name('me.metrics');
Route::put('/me/metrics', [\App\Http\Controllers\V1\User\ScoutMetricsController::class, 'upsertMetrics'])
    ->name('me.metrics.upsert');
Route::get('/me/competition-results', [\App\Http\Controllers\V1\User\ScoutMetricsController::class, 'competitionResults'])
    ->name('me.competition');
Route::post('/me/competition-results', [\App\Http\Controllers\V1\User\ScoutMetricsController::class, 'storeCompetitionResult'])
    ->name('me.competition.store');
Route::get('/me/videos', [\App\Http\Controllers\V1\User\ScoutMetricsController::class, 'videos'])
    ->name('me.videos');
Route::post('/me/videos', [\App\Http\Controllers\V1\User\ScoutMetricsController::class, 'storeVideo'])
    ->name('me.videos.store');
Route::delete('/me/videos/{video}', [\App\Http\Controllers\V1\User\ScoutMetricsController::class, 'destroyVideo'])
    ->name('me.videos.destroy');

Route::prefix('/{user}')
    ->group(function () {
        Route::get('/', ShowController::class)
            ->name('show');
        Route::put('/', UpdateController::class)
            ->name('update');
        Route::get('/metrics', [\App\Http\Controllers\V1\User\ScoutMetricsController::class, 'metrics'])
            ->name('metrics');
        Route::patch('/metrics/{metricValue}/verify', [\App\Http\Controllers\V1\User\ScoutMetricsController::class, 'verifyMetric'])
            ->name('metrics.verify');
        Route::get('/competition-results', [\App\Http\Controllers\V1\User\ScoutMetricsController::class, 'competitionResults'])
            ->name('competition');
        Route::get('/videos', [\App\Http\Controllers\V1\User\ScoutMetricsController::class, 'videos'])
            ->name('videos');
    });