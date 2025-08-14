<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Webhook Stripe (pas de middleware CSRF/auth)
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle']);
