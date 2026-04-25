<?php

namespace SeaPay\LaravelSeaPay\DTO;

class PaymentResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $transactionId,
        public readonly string $orderId,
        public readonly string $status,
        public readonly float $amount,
        public readonly string $currency,
        public readonly ?string $paymentUrl = null,
        public readonly ?string $qrCode = null,
        public readonly ?string $deeplink = null,
        public readonly ?string $message = null,
        public readonly ?string $errorCode = null,
        public readonly ?string $account = null,
        public readonly ?string $paidAt = null,
        /** @var array<string, mixed> */
        public readonly array $raw = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromApiResponse(array $data, string $account = ''): static
    {
        return new static(
            success:       ($data['status'] ?? '') === 'success' || ($data['code'] ?? '') === '00',
            transactionId: $data['transaction_id'] ?? $data['txn_id'] ?? '',
            orderId:       $data['order_id'] ?? '',
            status:        $data['status'] ?? 'unknown',
            amount:        (float) ($data['amount'] ?? 0),
            currency:      $data['currency'] ?? 'VND',
            paymentUrl:    $data['payment_url'] ?? $data['checkout_url'] ?? null,
            qrCode:        $data['qr_code'] ?? $data['qr_data'] ?? null,
            deeplink:      $data['deeplink'] ?? null,
            message:       $data['message'] ?? $data['msg'] ?? null,
            errorCode:     $data['error_code'] ?? $data['code'] ?? null,
            account:       $account,
            paidAt:        $data['paid_at'] ?? $data['payment_time'] ?? null,
            raw:           $data,
        );
    }

    public static function failed(string $errorCode, string $message, string $orderId = '', string $account = ''): static
    {
        return new static(
            success:       false,
            transactionId: '',
            orderId:       $orderId,
            status:        'failed',
            amount:        0,
            currency:      'VND',
            message:       $message,
            errorCode:     $errorCode,
            account:       $account,
        );
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'processing', 'waiting']);
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['success', 'paid', 'completed']);
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'cancelled', 'expired', 'error']);
    }
}
