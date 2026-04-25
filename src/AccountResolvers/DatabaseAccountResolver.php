<?php

namespace SeaPay\LaravelSeaPay\AccountResolvers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use SeaPay\LaravelSeaPay\Contracts\AccountResolverInterface;

/**
 * Lấy tài khoản từ bảng seapay_accounts trong database.
 * Hỗ trợ cache để tránh query DB liên tục.
 */
class DatabaseAccountResolver implements AccountResolverInterface
{
    private string $table;
    private int $cacheTtl;
    private string $cachePrefix;

    /** @param array<string, mixed> $config */
    public function __construct(private readonly array $config)
    {
        $this->table       = $config['account_resolver']['table']        ?? 'seapay_accounts';
        $this->cacheTtl    = $config['account_resolver']['cache_ttl']    ?? 300;
        $this->cachePrefix = $config['account_resolver']['cache_prefix'] ?? 'seapay_account_';
    }

    public function resolve(string $accountName): ?array
    {
        if ($this->cacheTtl > 0) {
            return Cache::remember(
                $this->cachePrefix . $accountName,
                $this->cacheTtl,
                fn () => $this->fetchFromDb($accountName),
            );
        }

        return $this->fetchFromDb($accountName);
    }

    public function all(): array
    {
        if ($this->cacheTtl > 0) {
            return Cache::remember(
                $this->cachePrefix . 'all',
                $this->cacheTtl,
                fn () => $this->fetchAllFromDb(),
            );
        }

        return $this->fetchAllFromDb();
    }

    public function has(string $accountName): bool
    {
        return $this->resolve($accountName) !== null;
    }

    /**
     * Xóa cache của một tài khoản (dùng khi cập nhật credentials).
     */
    public function clearCache(string $accountName): void
    {
        Cache::forget($this->cachePrefix . $accountName);
        Cache::forget($this->cachePrefix . 'all');
    }

    /**
     * Xóa toàn bộ cache tài khoản.
     */
    public function clearAllCache(): void
    {
        Cache::forget($this->cachePrefix . 'all');
    }

    private function fetchFromDb(string $accountName): ?array
    {
        $row = DB::table($this->table)
            ->where('name', $accountName)
            ->where('is_active', true)
            ->first();

        if (!$row) {
            return null;
        }

        return $this->rowToArray($row);
    }

    /** @return array<string, array<string, mixed>> */
    private function fetchAllFromDb(): array
    {
        $rows = DB::table($this->table)
            ->where('is_active', true)
            ->get();

        $accounts = [];
        foreach ($rows as $row) {
            $accounts[$row->name] = $this->rowToArray($row);
        }

        return $accounts;
    }

    /** @return array<string, mixed> */
    private function rowToArray(object $row): array
    {
        return [
            'merchant_id' => $row->merchant_id,
            'api_key'     => $row->api_key,
            'secret_key'  => $row->secret_key,
            'description' => $row->description ?? null,
        ];
    }
}
