<?php

namespace SeaPay\LaravelSeaPay\Facades;

use Illuminate\Support\Facades\Facade;
use SeaPay\LaravelSeaPay\Contracts\SeaPayInterface;
use SeaPay\LaravelSeaPay\DTO\PaymentRequest;
use SeaPay\LaravelSeaPay\DTO\PaymentResponse;
use SeaPay\LaravelSeaPay\DTO\RefundRequest;
use SeaPay\LaravelSeaPay\DTO\RefundResponse;
use SeaPay\LaravelSeaPay\DTO\TransactionQueryResponse;
use SeaPay\LaravelSeaPay\SeaPayManager;

/**
 * @method static SeaPayManager account(string $name)
 * @method static PaymentResponse createPayment(PaymentRequest $request)
 * @method static TransactionQueryResponse queryTransaction(string $transactionId)
 * @method static RefundResponse refund(RefundRequest $request)
 * @method static array getAccounts()
 * @method static string getCurrentAccount()
 * @method static bool verifyWebhook(array $data)
 * @method static \Illuminate\Http\RedirectResponse paymentPage(PaymentResponse $response, PaymentRequest $originalRequest)
 *
 * @see SeaPayManager
 */
class SeaPay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SeaPayInterface::class;
    }
}
