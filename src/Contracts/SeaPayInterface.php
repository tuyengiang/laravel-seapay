<?php

namespace SeaPay\LaravelSeaPay\Contracts;

use SeaPay\LaravelSeaPay\DTO\PaymentRequest;
use SeaPay\LaravelSeaPay\DTO\PaymentResponse;
use SeaPay\LaravelSeaPay\DTO\RefundRequest;
use SeaPay\LaravelSeaPay\DTO\RefundResponse;
use SeaPay\LaravelSeaPay\DTO\TransactionQueryResponse;

interface SeaPayInterface
{
    /**
     * Chọn tài khoản SeaPay để thực hiện giao dịch.
     */
    public function account(string $name): static;

    /**
     * Tạo yêu cầu thanh toán mới.
     */
    public function createPayment(PaymentRequest $request): PaymentResponse;

    /**
     * Truy vấn trạng thái giao dịch.
     */
    public function queryTransaction(string $transactionId): TransactionQueryResponse;

    /**
     * Hoàn tiền một giao dịch.
     */
    public function refund(RefundRequest $request): RefundResponse;

    /**
     * Lấy danh sách tài khoản đã cấu hình.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAccounts(): array;

    /**
     * Lấy tên tài khoản đang được sử dụng.
     */
    public function getCurrentAccount(): string;

    /**
     * Xác minh chữ ký webhook từ SeaPay.
     *
     * @param array<string, mixed> $data
     */
    public function verifyWebhook(array $data): bool;
}
