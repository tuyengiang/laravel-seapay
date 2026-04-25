<?php

namespace SeaPay\LaravelSeaPay\Contracts;

interface AccountResolverInterface
{
    /**
     * Lấy thông tin credentials của một tài khoản theo tên.
     *
     * Trả về array gồm: merchant_id, api_key, secret_key, description (tuỳ chọn).
     * Trả về null nếu không tìm thấy tài khoản.
     *
     * @return array{merchant_id: string, api_key: string, secret_key: string, description?: string}|null
     */
    public function resolve(string $accountName): ?array;

    /**
     * Lấy toàn bộ danh sách tài khoản.
     *
     * @return array<string, array{merchant_id: string, api_key: string, secret_key: string, description?: string}>
     */
    public function all(): array;

    /**
     * Kiểm tra tài khoản có tồn tại không.
     */
    public function has(string $accountName): bool;
}
