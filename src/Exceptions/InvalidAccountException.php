<?php

namespace SeaPay\LaravelSeaPay\Exceptions;

class InvalidAccountException extends SeaPayException
{
    public static function notFound(string $account): static
    {
        return new static(
            message:   "Tài khoản SeaPay '{$account}' không tồn tại trong cấu hình.",
            errorCode: 'ACCOUNT_NOT_FOUND',
            context:   ['account' => $account],
        );
    }

    public static function missingCredentials(string $account, string $field): static
    {
        return new static(
            message:   "Tài khoản SeaPay '{$account}' thiếu thông tin '{$field}'.",
            errorCode: 'MISSING_CREDENTIALS',
            context:   ['account' => $account, 'field' => $field],
        );
    }
}
