<?php

use Illuminate\Support\Facades\Route;

Route::prefix('demo')
    ->as('demo:')
    ->middleware(['auth:sanctum', 'throttle:30,1'])
    ->group(function () {
        Route::get('/showcase', \App\Http\Controllers\V1\Demo\ShowcaseController::class)
            ->name('showcase');
        Route::get('/invoices/{invoice}/pdf', \App\Http\Controllers\V1\Demo\InvoicePdfController::class)
            ->middleware('throttle:10,1')
            ->name('invoices.pdf');
        Route::post('/groups/{id}/join', [\App\Http\Controllers\V1\Demo\ExperienceController::class, 'joinGroup'])
            ->name('groups.join');
        Route::post('/groups/{id}/leave', [\App\Http\Controllers\V1\Demo\ExperienceController::class, 'leaveGroup'])
            ->name('groups.leave');
        Route::post('/groups/{id}/posts', [\App\Http\Controllers\V1\Demo\ExperienceController::class, 'postInGroup'])
            ->name('groups.posts');
        Route::post('/store/checkout', [\App\Http\Controllers\V1\Demo\ExperienceController::class, 'checkout'])
            ->name('store.checkout');
        Route::post('/sponsorships/{id}/apply', [\App\Http\Controllers\V1\Demo\ExperienceController::class, 'applySponsorship'])
            ->name('sponsorships.apply');
    });

Route::prefix('auth')
    ->as('auth:')
    ->group(
        base_path('routes/api/v1/auth.php')
    );

Route::prefix('user')
    ->as('user:')
    ->middleware(['auth:sanctum'])
    ->group(
        base_path('routes/api/v1/user.php')
    );

Route::prefix('sport')
    ->as('sport:')
    ->group(
        base_path('routes/api/v1/sport.php')
    );

Route::prefix('role')
    ->as('role:')
    ->group(
        base_path('routes/api/v1/role.php')
    );

Route::prefix('posts')
    ->as('posts:')
    ->middleware(['auth:sanctum'])
    ->group(
        base_path('routes/api/v1/post.php')
    );

Route::prefix('chat')
    ->as('chat:')
    ->middleware(['auth:sanctum'])
    ->group(
        base_path('routes/api/v1/chat.php')
    );

Route::prefix('stories')
    ->as('stories:')
    ->middleware(['auth:sanctum'])
    ->group(
        base_path('routes/api/v1/stories.php')
    );

Route::prefix('connections')
    ->as('connections:')
    ->middleware(['auth:sanctum'])
    ->group(
        base_path('routes/api/v1/connections.php')
    );

Route::group([], base_path('routes/api/v1/meta.php'));

Route::prefix('billing')
    ->as('billing:')
    ->middleware(['auth:sanctum'])
    ->group(
        base_path('routes/api/v1/billing.php')
    );

Route::prefix('search')
    ->as('search:')
    ->middleware(['auth:sanctum'])
    ->group(
        base_path('routes/api/v1/search.php')
    );

Route::prefix('jobs')
    ->as('jobs:')
    ->middleware(['auth:sanctum'])
    ->group(
        base_path('routes/api/v1/job.php')
    );

Route::prefix('references')
    ->as('references:')
    ->middleware(['auth:sanctum'])
    ->group(
        base_path('routes/api/v1/reference.php')
    );

Route::get('/reports', [\App\Http\Controllers\V1\Safety\ReportController::class, 'index'])
    ->middleware(['auth:sanctum', 'throttle:30,1'])
    ->name('reports.index');
Route::post('/reports', [\App\Http\Controllers\V1\Safety\ReportController::class, 'store'])
    ->middleware(['auth:sanctum', 'throttle:20,1'])
    ->name('reports.store');

Route::prefix('events')
    ->as('events:')
    ->middleware(['auth:sanctum'])
    ->group(
        base_path('routes/api/v1/event.php')
    );

Route::prefix('sponsorships')
    ->as('sponsorships:')
    ->middleware(['auth:sanctum'])
    ->group(
        base_path('routes/api/v1/sponsorship.php')
    );

Route::prefix('public')
    ->as('public:')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::get('/posts/{post}', [\App\Http\Controllers\V1\Public\PublicShareController::class, 'post'])
            ->name('posts.show');
        Route::get('/sport/news/{news}', [\App\Http\Controllers\V1\Public\PublicShareController::class, 'news'])
            ->name('news.show');
    });
