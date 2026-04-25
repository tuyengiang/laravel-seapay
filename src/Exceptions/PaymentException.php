<?php

namespace SeaPay\LaravelSeaPay\Exceptions;

class PaymentException extends SeaPayException
{
    public static function apiError(string $message, string $errorCode = '', array $context = []): static
    {
        return new static(
            message:   "SeaPay API lỗi: {$message}",
            errorCode: $errorCode ?: 'API_ERROR',
            context:   $context,
        );
    }

    public static function invalidSignature(): static
    {
        return new static(
            message:   'Chữ ký webhook không hợp lệ.',
            errorCode: 'INVALID_SIGNATURE',
        );
    }

    public static function networkError(string $message): static
    {
        return new static(
            message:   "Lỗi kết nối tới SeaPay: {$message}",
            errorCode: 'NETWORK_ERROR',
        );
    }
}
