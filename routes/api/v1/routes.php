<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->as('auth:')
    ->group(
        base_path('routes/api/v1/auth.php')
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
