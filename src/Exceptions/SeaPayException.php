<?php

namespace SeaPay\LaravelSeaPay\Exceptions;

use RuntimeException;
use Throwable;

class SeaPayException extends RuntimeException
{
    public function __construct(
        string $message = '',
        private readonly string $errorCode = '',
        private readonly array $context = [],
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
