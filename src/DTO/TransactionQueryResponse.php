<?php

namespace SeaPay\LaravelSeaPay\DTO;

class TransactionQueryResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $transactionId,
        public readonly string $orderId,
        public readonly string $status,
        public readonly float $amount,
        public readonly string $currency,
        public readonly ?string $paymentMethod = null,
        public readonly ?string $customerName = null,
        public readonly ?string $customerPhone = null,
        public readonly ?string $message = null,
        public readonly ?string $errorCode = null,
        public readonly ?string $paidAt = null,
        public readonly ?string $createdAt = null,
        /** @var array<string, mixed> */
        public readonly array $raw = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromApiResponse(array $data): static
    {
        return new static(
            success:        ($data['code'] ?? '') === '00' || ($data['status'] ?? '') === 'success',
            transactionId:  $data['transaction_id'] ?? $data['txn_id'] ?? '',
            orderId:        $data['order_id'] ?? '',
            status:         $data['status'] ?? 'unknown',
            amount:         (float) ($data['amount'] ?? 0),
            currency:       $data['currency'] ?? 'VND',
            paymentMethod:  $data['payment_method'] ?? null,
            customerName:   $data['customer_name'] ?? null,
            customerPhone:  $data['customer_phone'] ?? null,
            message:        $data['message'] ?? null,
            errorCode:      $data['error_code'] ?? $data['code'] ?? null,
            paidAt:         $data['paid_at'] ?? $data['payment_time'] ?? null,
            createdAt:      $data['created_at'] ?? null,
            raw:            $data,
        );
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['success', 'paid', 'completed']);
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'processing', 'waiting']);
    }
}
