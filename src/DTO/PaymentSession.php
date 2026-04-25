<?php

namespace SeaPay\LaravelSeaPay\DTO;

use Illuminate\Support\Str;

class PaymentSession
{
    public function __construct(
        public readonly string  $token,
        public readonly string  $account,
        public readonly string  $transactionId,
        public readonly string  $orderId,
        public readonly float   $amount,
        public readonly string  $currency,
        public readonly string  $description,
        public readonly ?string $qrCode,
        public readonly ?string $paymentUrl,
        public readonly ?string $deeplink,
        public readonly ?string $returnUrl,
        public readonly ?string $cancelUrl,
        public readonly ?string $expiredAt,
        public readonly ?string $customerName,
        public readonly array   $items = [],
        public readonly array   $bankInfo = [],
        public string           $status = 'pending',
        public readonly string  $createdAt = '',
    ) {}

    public static function create(
        PaymentResponse $response,
        PaymentRequest $request,
        string $account,
    ): static {
        return new static(
            token:         Str::uuid()->toString(),
            account:       $account,
            transactionId: $response->transactionId,
            orderId:       $response->orderId ?: $request->orderId,
            amount:        $request->amount,
            currency:      $request->currency,
            description:   $request->description,
            qrCode:        $response->qrCode,
            paymentUrl:    $response->paymentUrl,
            deeplink:      $response->deeplink,
            returnUrl:     $request->returnUrl,
            cancelUrl:     $request->cancelUrl,
            expiredAt:     $request->expiredAt ?? now()->addMinutes(15)->toIso8601String(),
            customerName:  $request->customerName,
            items:         $request->items,
            bankInfo:      static::extractBankInfo($response),
            status:        $response->status,
            createdAt:     now()->toIso8601String(),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'token'          => $this->token,
            'account'        => $this->account,
            'transaction_id' => $this->transactionId,
            'order_id'       => $this->orderId,
            'amount'         => $this->amount,
            'currency'       => $this->currency,
            'description'    => $this->description,
            'qr_code'        => $this->qrCode,
            'payment_url'    => $this->paymentUrl,
            'deeplink'       => $this->deeplink,
            'return_url'     => $this->returnUrl,
            'cancel_url'     => $this->cancelUrl,
            'expired_at'     => $this->expiredAt,
            'customer_name'  => $this->customerName,
            'items'          => $this->items,
            'bank_info'      => $this->bankInfo,
            'status'         => $this->status,
            'created_at'     => $this->createdAt,
        ];
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new static(
            token:         $data['token'],
            account:       $data['account'],
            transactionId: $data['transaction_id'],
            orderId:       $data['order_id'],
            amount:        (float) $data['amount'],
            currency:      $data['currency'],
            description:   $data['description'],
            qrCode:        $data['qr_code']       ?? null,
            paymentUrl:    $data['payment_url']   ?? null,
            deeplink:      $data['deeplink']      ?? null,
            returnUrl:     $data['return_url']    ?? null,
            cancelUrl:     $data['cancel_url']    ?? null,
            expiredAt:     $data['expired_at']    ?? null,
            customerName:  $data['customer_name'] ?? null,
            items:         $data['items']         ?? [],
            bankInfo:      $data['bank_info']     ?? [],
            status:        $data['status']        ?? 'pending',
            createdAt:     $data['created_at']    ?? '',
        );
    }

    /** @return array<string, mixed> */
    private static function extractBankInfo(PaymentResponse $response): array
    {
        $raw = $response->raw;

        return [
            'bank_name'         => $raw['bank_name']         ?? $raw['bank']             ?? null,
            'bank_code'         => $raw['bank_code']         ?? null,
            'bank_account'      => $raw['bank_account']      ?? $raw['account_number']   ?? null,
            'bank_account_name' => $raw['bank_account_name'] ?? $raw['beneficiary_name'] ?? null,
            'transfer_content'  => $raw['transfer_content']  ?? $raw['memo']             ?? null,
        ];
    }
}
