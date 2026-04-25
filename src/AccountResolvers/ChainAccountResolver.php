<?php

namespace SeaPay\LaravelSeaPay\AccountResolvers;

use SeaPay\LaravelSeaPay\Contracts\AccountResolverInterface;

/**
 * Thử lần lượt từng resolver cho đến khi tìm thấy tài khoản.
 * Mặc định: DB trước, config sau (fallback).
 */
class ChainAccountResolver implements AccountResolverInterface
{
    /** @param AccountResolverInterface[] $resolvers */
    public function __construct(private readonly array $resolvers) {}

    public function resolve(string $accountName): ?array
    {
        foreach ($this->resolvers as $resolver) {
            $account = $resolver->resolve($accountName);
            if ($account !== null) {
                return $account;
            }
        }

        return null;
    }

    public function all(): array
    {
        $accounts = [];

        // Merge từ tất cả resolver, resolver đầu tiên có độ ưu tiên cao hơn
        foreach (array_reverse($this->resolvers) as $resolver) {
            $accounts = array_merge($accounts, $resolver->all());
        }

        return $accounts;
    }

    public function has(string $accountName): bool
    {
        return $this->resolve($accountName) !== null;
    }
}
