<?php

use Illuminate\Support\Facades\Route;

Route::get('/summary', function () {
    return response()->json([
        'message' => 'Billing summary',
        'data' => [
            'subscription' => null,
            'payment_methods' => [],
            'invoices' => [],
        ],
    ]);
})->name('billing.summary');
