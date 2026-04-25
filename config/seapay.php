<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tài khoản mặc định
    |--------------------------------------------------------------------------
    | Tên tài khoản SeaPay sẽ được sử dụng mặc định khi không chỉ định.
    */
    'default' => env('SEAPAY_ACCOUNT', 'main'),

    /*
    |--------------------------------------------------------------------------
    | Môi trường
    |--------------------------------------------------------------------------
    | 'sandbox'    : Môi trường kiểm thử
    | 'production' : Môi trường thật
    */
    'env' => env('SEAPAY_ENV', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | URL API
    |--------------------------------------------------------------------------
    */
    'api_url' => [
        'sandbox'    => 'https://sandbox-api.seapay.vn/v1',
        'production' => 'https://api.seapay.vn/v1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cấu hình HTTP Client
    |--------------------------------------------------------------------------
    */
    'http' => [
        'timeout'         => env('SEAPAY_TIMEOUT', 30),
        'connect_timeout' => env('SEAPAY_CONNECT_TIMEOUT', 10),
        'retry'           => env('SEAPAY_RETRY', 2),
        'retry_delay'     => env('SEAPAY_RETRY_DELAY', 500), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Danh sách tài khoản đã liên kết
    |--------------------------------------------------------------------------
    | Mỗi tài khoản gồm:
    |   - merchant_id  : Mã merchant
    |   - api_key      : API key
    |   - secret_key   : Secret key để ký request
    |   - description  : Mô tả tài khoản (tuỳ chọn)
    */
    'accounts' => [
        'main' => [
            'merchant_id' => env('SEAPAY_MERCHANT_ID'),
            'api_key'     => env('SEAPAY_API_KEY'),
            'secret_key'  => env('SEAPAY_SECRET_KEY'),
            'description' => 'Tài khoản chính',
        ],

        // Ví dụ tài khoản thứ hai
        // 'store_2' => [
        //     'merchant_id' => env('SEAPAY_STORE2_MERCHANT_ID'),
        //     'api_key'     => env('SEAPAY_STORE2_API_KEY'),
        //     'secret_key'  => env('SEAPAY_STORE2_SECRET_KEY'),
        //     'description' => 'Cửa hàng 2',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cấu hình Webhook
    |--------------------------------------------------------------------------
    */
    'webhook' => [
        'secret'      => env('SEAPAY_WEBHOOK_SECRET'),
        'path'        => env('SEAPAY_WEBHOOK_PATH', 'seapay/webhook'),
        'middlewares' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Lưu lịch sử giao dịch vào database
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('SEAPAY_LOG_ENABLED', true),
        'channel' => env('SEAPAY_LOG_CHANNEL', 'stack'),
    ],

    'database' => [
        'enabled'    => env('SEAPAY_DB_ENABLED', true),
        'table_name' => 'seapay_transactions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Đơn vị tiền tệ mặc định
    |--------------------------------------------------------------------------
    */
    'currency' => env('SEAPAY_CURRENCY', 'VND'),
];
