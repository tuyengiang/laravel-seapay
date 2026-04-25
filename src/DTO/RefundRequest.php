<?php

namespace SeaPay\LaravelSeaPay\DTO;

use InvalidArgumentException;

class RefundRequest
{
    public function __construct(
        public readonly string $transactionId,
        public readonly float $amount,
        public readonly string $reason = '',
        public readonly ?string $refundOrderId = null,
    ) {
        if (empty($this->transactionId)) {
            throw new InvalidArgumentException('transactionId không được để trống.');
        }
        if ($this->amount <= 0) {
            throw new InvalidArgumentException('Số tiền hoàn phải lớn hơn 0.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'transaction_id'  => $this->transactionId,
            'amount'          => $this->amount,
            'reason'          => $this->reason,
            'refund_order_id' => $this->refundOrderId,
        ], fn ($v) => $v !== null && $v !== '');
    }
}
