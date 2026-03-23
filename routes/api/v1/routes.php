<?php

use Illuminate\Support\Facades\Route;

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
