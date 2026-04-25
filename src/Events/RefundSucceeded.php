<?php

namespace SeaPay\LaravelSeaPay\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use SeaPay\LaravelSeaPay\DTO\RefundResponse;

class RefundSucceeded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly RefundResponse $response,
        public readonly string $account,
    ) {}
}
