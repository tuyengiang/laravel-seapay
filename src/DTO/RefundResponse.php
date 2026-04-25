<?php

namespace SeaPay\LaravelSeaPay\DTO;

class RefundResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $refundId,
        public readonly string $transactionId,
        public readonly string $status,
        public readonly float $amount,
        public readonly ?string $message = null,
        public readonly ?string $errorCode = null,
        public readonly ?string $refundedAt = null,
        /** @var array<string, mixed> */
        public readonly array $raw = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromApiResponse(array $data): static
    {
        return new static(
            success:       ($data['status'] ?? '') === 'success' || ($data['code'] ?? '') === '00',
            refundId:      $data['refund_id'] ?? '',
            transactionId: $data['transaction_id'] ?? '',
            status:        $data['status'] ?? 'unknown',
            amount:        (float) ($data['amount'] ?? 0),
            message:       $data['message'] ?? null,
            errorCode:     $data['error_code'] ?? $data['code'] ?? null,
            refundedAt:    $data['refunded_at'] ?? null,
            raw:           $data,
        );
    }
}
