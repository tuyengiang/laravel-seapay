<?php

use Illuminate\Support\Facades\Route;
use SeaPay\LaravelSeaPay\Http\Controllers\SeaPayWebhookController;

$webhookPath        = config('seapay.webhook.path', 'seapay/webhook');
$webhookMiddlewares = config('seapay.webhook.middlewares', []);

Route::post($webhookPath, [SeaPayWebhookController::class, 'handle'])
    ->middleware($webhookMiddlewares)
    ->name('seapay.webhook');
