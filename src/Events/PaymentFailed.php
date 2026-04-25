<?php

namespace SeaPay\LaravelSeaPay\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use SeaPay\LaravelSeaPay\DTO\PaymentResponse;

class PaymentFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly PaymentResponse $response,
        public readonly string $account,
        /** @var array<string, mixed> */
        public readonly array $webhookData = [],
    ) {}
}
