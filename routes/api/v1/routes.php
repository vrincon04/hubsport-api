<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->as('auth:')
    ->group(
        base_path('routes/api/v1/auth.php')
    );
