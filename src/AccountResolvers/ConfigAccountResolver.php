<?php

namespace SeaPay\LaravelSeaPay\AccountResolvers;

use SeaPay\LaravelSeaPay\Contracts\AccountResolverInterface;

/**
 * Lấy tài khoản từ config/seapay.php (mặc định).
 */
class ConfigAccountResolver implements AccountResolverInterface
{
    /** @param array<string, mixed> $config */
    public function __construct(private readonly array $config) {}

    public function resolve(string $accountName): ?array
    {
        return $this->config['accounts'][$accountName] ?? null;
    }

    public function all(): array
    {
        return $this->config['accounts'] ?? [];
    }

    public function has(string $accountName): bool
    {
        return isset($this->config['accounts'][$accountName]);
    }
}
