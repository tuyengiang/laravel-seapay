<?php

namespace SeaPay\LaravelSeaPay\DTO;

use InvalidArgumentException;

class PaymentRequest
{
    /**
     * @param array<string, mixed> $metadata Dữ liệu bổ sung tuỳ chọn
     * @param array<string, string> $items Danh sách sản phẩm [{name, quantity, price}]
     */
    public function __construct(
        public readonly string $orderId,
        public readonly float $amount,
        public readonly string $description,
        public readonly string $returnUrl,
        public readonly string $cancelUrl,
        public readonly string $currency = 'VND',
        public readonly ?string $customerName = null,
        public readonly ?string $customerEmail = null,
        public readonly ?string $customerPhone = null,
        public readonly array $items = [],
        public readonly array $metadata = [],
        public readonly ?string $expiredAt = null,
        public readonly ?string $notifyUrl = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->orderId)) {
            throw new InvalidArgumentException('orderId không được để trống.');
        }

        if ($this->amount <= 0) {
            throw new InvalidArgumentException('Số tiền phải lớn hơn 0.');
        }

        if (empty($this->returnUrl)) {
            throw new InvalidArgumentException('returnUrl không được để trống.');
        }

        if (!filter_var($this->returnUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('returnUrl không hợp lệ.');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new static(
            orderId:       $data['order_id'],
            amount:        (float) $data['amount'],
            description:   $data['description'] ?? '',
            returnUrl:     $data['return_url'],
            cancelUrl:     $data['cancel_url'] ?? $data['return_url'],
            currency:      $data['currency'] ?? 'VND',
            customerName:  $data['customer_name'] ?? null,
            customerEmail: $data['customer_email'] ?? null,
            customerPhone: $data['customer_phone'] ?? null,
            items:         $data['items'] ?? [],
            metadata:      $data['metadata'] ?? [],
            expiredAt:     $data['expired_at'] ?? null,
            notifyUrl:     $data['notify_url'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'order_id'       => $this->orderId,
            'amount'         => $this->amount,
            'currency'       => $this->currency,
            'description'    => $this->description,
            'return_url'     => $this->returnUrl,
            'cancel_url'     => $this->cancelUrl,
            'customer_name'  => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_phone' => $this->customerPhone,
            'items'          => $this->items ?: null,
            'metadata'       => $this->metadata ?: null,
            'expired_at'     => $this->expiredAt,
            'notify_url'     => $this->notifyUrl,
        ], fn ($v) => $v !== null);
    }
}
