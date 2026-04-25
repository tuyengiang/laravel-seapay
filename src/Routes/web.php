<?php

use Illuminate\Support\Facades\Route;
use SeaPay\LaravelSeaPay\Http\Controllers\SeaPayPaymentController;
use SeaPay\LaravelSeaPay\Http\Controllers\SeaPayWebhookController;

// Webhook
$webhookPath        = config('seapay.webhook.path', 'seapay/webhook');
$webhookMiddlewares = config('seapay.webhook.middlewares', []);

Route::post($webhookPath, [SeaPayWebhookController::class, 'handle'])
    ->middleware($webhookMiddlewares)
    ->name('seapay.webhook');

// Trang thanh toán
Route::prefix('seapay/pay')->group(function () {
    Route::get('{token}',         [SeaPayPaymentController::class, 'show'])        ->name('seapay.payment.show');
    Route::get('{token}/status',  [SeaPayPaymentController::class, 'checkStatus']) ->name('seapay.payment.status');
});
