<?php

namespace SeaPay\LaravelSeaPay;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SeaPay\LaravelSeaPay\Contracts\SeaPayInterface;
use SeaPay\LaravelSeaPay\DTO\PaymentRequest;
use SeaPay\LaravelSeaPay\DTO\PaymentResponse;
use SeaPay\LaravelSeaPay\DTO\RefundRequest;
use SeaPay\LaravelSeaPay\DTO\RefundResponse;
use SeaPay\LaravelSeaPay\DTO\TransactionQueryResponse;
use SeaPay\LaravelSeaPay\Events\PaymentFailed;
use SeaPay\LaravelSeaPay\Events\PaymentSucceeded;
use SeaPay\LaravelSeaPay\Events\RefundSucceeded;
use SeaPay\LaravelSeaPay\Exceptions\InvalidAccountException;
use SeaPay\LaravelSeaPay\Exceptions\PaymentException;

class SeaPayManager implements SeaPayInterface
{
    private string $currentAccount;

    /** @param array<string, mixed> $config */
    public function __construct(
        private readonly array $config,
        private readonly SeaPayClient $client,
    ) {
        $this->currentAccount = $config['default'] ?? 'main';
    }

    public function account(string $name): static
    {
        if (!isset($this->config['accounts'][$name])) {
            throw InvalidAccountException::notFound($name);
        }

        $clone                 = clone $this;
        $clone->currentAccount = $name;

        return $clone;
    }

    public function createPayment(PaymentRequest $request): PaymentResponse
    {
        $accountConfig = $this->getAccountConfig();
        $client        = $this->client->withAccount($accountConfig, $this->currentAccount);

        $this->log('info', 'Tạo yêu cầu thanh toán', [
            'account'  => $this->currentAccount,
            'order_id' => $request->orderId,
            'amount'   => $request->amount,
        ]);

        try {
            $data     = $client->post('payments/create', $request->toArray());
            $response = PaymentResponse::fromApiResponse($data, $this->currentAccount);

            $this->saveTransaction($request, $response);

            if ($response->success) {
                event(new PaymentSucceeded($response, $this->currentAccount));
            } else {
                event(new PaymentFailed($response, $this->currentAccount));
            }

            return $response;
        } catch (PaymentException $e) {
            $response = PaymentResponse::failed($e->getErrorCode(), $e->getMessage(), $request->orderId, $this->currentAccount);
            event(new PaymentFailed($response, $this->currentAccount));
            throw $e;
        }
    }

    public function queryTransaction(string $transactionId): TransactionQueryResponse
    {
        $accountConfig = $this->getAccountConfig();
        $client        = $this->client->withAccount($accountConfig, $this->currentAccount);

        $data = $client->get('payments/query', ['transaction_id' => $transactionId]);

        return TransactionQueryResponse::fromApiResponse($data);
    }

    public function refund(RefundRequest $request): RefundResponse
    {
        $accountConfig = $this->getAccountConfig();
        $client        = $this->client->withAccount($accountConfig, $this->currentAccount);

        $this->log('info', 'Yêu cầu hoàn tiền', [
            'account'        => $this->currentAccount,
            'transaction_id' => $request->transactionId,
            'amount'         => $request->amount,
        ]);

        $data     = $client->post('payments/refund', $request->toArray());
        $response = RefundResponse::fromApiResponse($data);

        if ($response->success) {
            event(new RefundSucceeded($response, $this->currentAccount));
        }

        return $response;
    }

    public function getAccounts(): array
    {
        return array_map(fn ($acc) => [
            'merchant_id' => $acc['merchant_id'] ?? null,
            'description' => $acc['description'] ?? null,
        ], $this->config['accounts'] ?? []);
    }

    public function getCurrentAccount(): string
    {
        return $this->currentAccount;
    }

    /**
     * Xác minh chữ ký webhook.
     *
     * @param array<string, mixed> $data
     */
    public function verifyWebhook(array $data): bool
    {
        $secret = $this->config['webhook']['secret'] ?? '';

        if (empty($secret)) {
            throw PaymentException::apiError('SEAPAY_WEBHOOK_SECRET chưa được cấu hình.');
        }

        return $this->client->verifyWebhookSignature($data, $secret);
    }

    /** @return array<string, mixed> */
    private function getAccountConfig(): array
    {
        if (!isset($this->config['accounts'][$this->currentAccount])) {
            throw InvalidAccountException::notFound($this->currentAccount);
        }

        return $this->config['accounts'][$this->currentAccount];
    }

    private function saveTransaction(PaymentRequest $request, PaymentResponse $response): void
    {
        if (!($this->config['database']['enabled'] ?? true)) {
            return;
        }

        try {
            $table = $this->config['database']['table_name'] ?? 'seapay_transactions';

            DB::table($table)->insert([
                'account'        => $this->currentAccount,
                'order_id'       => $request->orderId,
                'transaction_id' => $response->transactionId,
                'amount'         => $request->amount,
                'currency'       => $request->currency,
                'status'         => $response->status,
                'payment_url'    => $response->paymentUrl,
                'description'    => $request->description,
                'customer_email' => $request->customerEmail,
                'customer_phone' => $request->customerPhone,
                'metadata'       => json_encode($request->metadata),
                'raw_response'   => json_encode($response->raw),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            $this->log('warning', 'Không thể lưu giao dịch vào database: ' . $e->getMessage());
        }
    }

    /** @param array<string, mixed> $context */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->config['logging']['enabled'] ?? true) {
            Log::channel($this->config['logging']['channel'] ?? 'stack')
                ->{$level}("[SeaPay] {$message}", $context);
        }
    }
}
