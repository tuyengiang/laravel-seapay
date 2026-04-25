# Laravel SeaPay

[![Latest Version on Packagist](https://img.shields.io/packagist/v/seapay/laravel-seapay.svg?style=flat-square)](https://packagist.org/packages/seapay/laravel-seapay)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg?style=flat-square)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-10.x%20%7C%2011.x%20%7C%2012.x-orange.svg?style=flat-square)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](LICENSE)

Package Laravel tích hợp cổng thanh toán **SEAPAY**, hỗ trợ:

- Thanh toán qua **nhiều tài khoản** SeaPay đã liên kết (multi-account)
- Tạo payment link / QR code
- Truy vấn trạng thái giao dịch
- Hoàn tiền (refund)
- Xác minh webhook tự động (HMAC-SHA256)
- Lưu lịch sử giao dịch vào database
- Events/Listeners cho mỗi trạng thái thanh toán

---

## Mục lục

- [Yêu cầu](#yêu-cầu)
- [Cài đặt](#cài-đặt)
- [Cấu hình](#cấu-hình)
  - [Một tài khoản](#một-tài-khoản)
  - [Nhiều tài khoản](#nhiều-tài-khoản)
- [Account Resolver — nguồn tài khoản động](#account-resolver--nguồn-tài-khoản-động)
  - [Driver: database](#driver-database)
  - [Driver: chain](#driver-chain)
  - [Driver: custom](#driver-custom)
- [Sử dụng](#sử-dụng)
  - [Tạo thanh toán](#tạo-thanh-toán)
  - [Chọn tài khoản cụ thể](#chọn-tài-khoản-cụ-thể)
  - [Truy vấn giao dịch](#truy-vấn-giao-dịch)
  - [Hoàn tiền](#hoàn-tiền)
  - [Danh sách tài khoản](#danh-sách-tài-khoản)
- [Webhook](#webhook)
  - [Cấu hình webhook](#cấu-hình-webhook)
  - [Lắng nghe sự kiện](#lắng-nghe-sự-kiện)
- [Dependency Injection](#dependency-injection)
- [Xử lý lỗi](#xử-lý-lỗi)
- [Cấu hình nâng cao](#cấu-hình-nâng-cao)
- [Đóng góp](#đóng-góp)
- [License](#license)

---

## Yêu cầu

| Yêu cầu | Phiên bản |
|---|---|
| PHP | >= 8.1 |
| Laravel | 10.x / 11.x / 12.x |
| Guzzle HTTP | >= 7.5 |

---

## Cài đặt

```bash
composer require seapay/laravel-seapay
```

Package tự đăng ký qua **Laravel Package Auto-Discovery**.

> **Nếu tắt Auto-Discovery**, thêm thủ công vào `config/app.php`:
> ```php
> 'providers' => [
>     SeaPay\LaravelSeaPay\SeaPayServiceProvider::class,
> ],
> 'aliases' => [
>     'SeaPay' => SeaPay\LaravelSeaPay\Facades\SeaPay::class,
> ],
> ```

Publish config và migration:

```bash
php artisan vendor:publish --tag=seapay-config
php artisan vendor:publish --tag=seapay-migrations
php artisan migrate
```

---

## Cấu hình

### Một tài khoản

Thêm vào file `.env`:

```env
SEAPAY_ENV=sandbox          # sandbox hoặc production
SEAPAY_ACCOUNT=main

SEAPAY_MERCHANT_ID=your_merchant_id
SEAPAY_API_KEY=your_api_key
SEAPAY_SECRET_KEY=your_secret_key

SEAPAY_WEBHOOK_SECRET=your_webhook_secret
SEAPAY_WEBHOOK_PATH=seapay/webhook
```

### Nhiều tài khoản

Mở `config/seapay.php` và thêm các tài khoản vào mảng `accounts`:

```php
'default' => env('SEAPAY_ACCOUNT', 'main'),

'accounts' => [
    'main' => [
        'merchant_id' => env('SEAPAY_MERCHANT_ID'),
        'api_key'     => env('SEAPAY_API_KEY'),
        'secret_key'  => env('SEAPAY_SECRET_KEY'),
        'description' => 'Cửa hàng chính',
    ],
    'store_hanoi' => [
        'merchant_id' => env('SEAPAY_HANOI_MERCHANT_ID'),
        'api_key'     => env('SEAPAY_HANOI_API_KEY'),
        'secret_key'  => env('SEAPAY_HANOI_SECRET_KEY'),
        'description' => 'Chi nhánh Hà Nội',
    ],
    'store_hcm' => [
        'merchant_id' => env('SEAPAY_HCM_MERCHANT_ID'),
        'api_key'     => env('SEAPAY_HCM_API_KEY'),
        'secret_key'  => env('SEAPAY_HCM_SECRET_KEY'),
        'description' => 'Chi nhánh TP.HCM',
    ],
],
```

Và `.env`:

```env
# Tài khoản chính
SEAPAY_MERCHANT_ID=merchant_main
SEAPAY_API_KEY=apikey_main
SEAPAY_SECRET_KEY=secret_main

# Chi nhánh Hà Nội
SEAPAY_HANOI_MERCHANT_ID=merchant_hanoi
SEAPAY_HANOI_API_KEY=apikey_hanoi
SEAPAY_HANOI_SECRET_KEY=secret_hanoi

# Chi nhánh TP.HCM
SEAPAY_HCM_MERCHANT_ID=merchant_hcm
SEAPAY_HCM_API_KEY=apikey_hcm
SEAPAY_HCM_SECRET_KEY=secret_hcm
```

---

## Account Resolver — nguồn tài khoản động

Mặc định package đọc tài khoản từ `config/seapay.php`. Bạn có thể chuyển sang lấy từ **database**, kết hợp cả hai, hoặc tự viết resolver riêng — không cần sửa code nghiệp vụ.

Chọn driver trong `.env`:

```env
SEAPAY_RESOLVER=config     # Mặc định — lấy từ config/seapay.php
SEAPAY_RESOLVER=database   # Lấy từ bảng seapay_accounts trong DB
SEAPAY_RESOLVER=chain      # Thử DB trước, fallback về config
SEAPAY_RESOLVER=custom     # Dùng class tự viết
```

---

### Driver: database

Lưu tài khoản vào bảng `seapay_accounts` (tạo bằng migration đã publish):

```bash
php artisan vendor:publish --tag=seapay-migrations
php artisan migrate
```

Thêm tài khoản vào DB:

```php
use SeaPay\LaravelSeaPay\Models\SeaPayAccount;

SeaPayAccount::create([
    'name'        => 'store_hanoi',
    'merchant_id' => 'MERCHANT_HN_001',
    'api_key'     => 'ak_hanoi_xxxxx',
    'secret_key'  => 'sk_hanoi_xxxxx',
    'description' => 'Chi nhánh Hà Nội',
    'is_active'   => true,
]);
```

Bật driver:

```env
SEAPAY_RESOLVER=database
```

Tài khoản được **cache 5 phút** mặc định. Khi cập nhật credentials, xóa cache:

```php
use SeaPay\LaravelSeaPay\AccountResolvers\DatabaseAccountResolver;

app(DatabaseAccountResolver::class)->clearCache('store_hanoi');
```

Tuỳ chỉnh cache trong `config/seapay.php`:

```php
'account_resolver' => [
    'driver'       => 'database',
    'table'        => 'seapay_accounts', // Tên bảng
    'cache_ttl'    => 300,               // Giây, 0 = tắt cache
    'cache_prefix' => 'seapay_account_',
],
```

---

### Driver: chain

Thử tìm trong **DB trước**, nếu không có thì **fallback về config**. Phù hợp khi một số tài khoản cố định trong config, một số lấy động từ DB.

```env
SEAPAY_RESOLVER=chain
```

---

### Driver: custom

Tự viết resolver khi cần lấy tài khoản từ nguồn bất kỳ (API, Redis, file JSON, ...):

```php
// app/Services/MySeaPayResolver.php
namespace App\Services;

use SeaPay\LaravelSeaPay\Contracts\AccountResolverInterface;

class MySeaPayResolver implements AccountResolverInterface
{
    public function resolve(string $accountName): ?array
    {
        // Ví dụ: lấy từ Redis
        $data = cache("seapay:{$accountName}");
        return $data ? json_decode($data, true) : null;
    }

    public function all(): array
    {
        // Trả về toàn bộ tài khoản
        return [];
    }

    public function has(string $accountName): bool
    {
        return $this->resolve($accountName) !== null;
    }
}
```

Khai báo trong `config/seapay.php`:

```php
'account_resolver' => [
    'driver' => 'custom',
    'class'  => \App\Services\MySeaPayResolver::class,
],
```

> Mọi resolver đều phải trả về array gồm `merchant_id`, `api_key`, `secret_key` (và `description` tùy chọn).

---

## Sử dụng

### Tạo thanh toán

```php
use SeaPay\LaravelSeaPay\Facades\SeaPay;
use SeaPay\LaravelSeaPay\DTO\PaymentRequest;

// Cách 1: Khởi tạo trực tiếp
$request = new PaymentRequest(
    orderId:       'ORDER-2024-001',
    amount:        250000,
    description:   'Thanh toán đơn hàng #ORDER-2024-001',
    returnUrl:     route('payment.return'),
    cancelUrl:     route('payment.cancel'),
    currency:      'VND',
    customerName:  'Nguyễn Văn A',
    customerEmail: 'khachhang@email.com',
    customerPhone: '0901234567',
);

$response = SeaPay::createPayment($request);

if ($response->success) {
    // Chuyển khách tới trang thanh toán
    return redirect($response->paymentUrl);
    
    // Hoặc lấy QR code nếu có
    // $qrCode = $response->qrCode;
    // $deeplink = $response->deeplink;
}
```

```php
// Cách 2: Dùng fromArray (tiện cho request từ controller)
$response = SeaPay::createPayment(PaymentRequest::fromArray([
    'order_id'       => 'ORDER-2024-001',
    'amount'         => 250000,
    'description'    => 'Thanh toán đơn hàng',
    'return_url'     => route('payment.return'),
    'cancel_url'     => route('payment.cancel'),
    'customer_name'  => 'Nguyễn Văn A',
    'customer_email' => 'khachhang@email.com',
    'customer_phone' => '0901234567',
    'metadata'       => ['user_id' => 42, 'source' => 'web'],
    'expired_at'     => now()->addMinutes(15)->toIso8601String(),
]));
```

**Thuộc tính của `PaymentResponse`:**

| Thuộc tính | Kiểu | Mô tả |
|---|---|---|
| `success` | `bool` | Tạo yêu cầu thành công hay không |
| `transactionId` | `string` | Mã giao dịch từ SeaPay |
| `orderId` | `string` | Mã đơn hàng gốc |
| `status` | `string` | `pending`, `success`, `failed`, ... |
| `amount` | `float` | Số tiền |
| `paymentUrl` | `?string` | Link thanh toán để redirect |
| `qrCode` | `?string` | Dữ liệu QR code |
| `deeplink` | `?string` | Deeplink cho ứng dụng di động |
| `message` | `?string` | Thông báo từ SeaPay |
| `account` | `?string` | Tài khoản đã dùng |
| `raw` | `array` | Response gốc từ API |

---

### Chọn tài khoản cụ thể

```php
// Dùng tài khoản 'store_hanoi' thay vì mặc định
$response = SeaPay::account('store_hanoi')->createPayment($request);

// Dùng tài khoản 'store_hcm'
$response = SeaPay::account('store_hcm')->createPayment($request);

// Lấy tên tài khoản đang dùng
$currentAccount = SeaPay::getCurrentAccount(); // 'main'
```

---

### Truy vấn giao dịch

```php
$query = SeaPay::queryTransaction('TXN_ID_12345');

if ($query->isPaid()) {
    echo "Đã thanh toán lúc: {$query->paidAt}";
}

if ($query->isPending()) {
    echo "Đang chờ thanh toán...";
}

// Kiểm tra thông qua tài khoản cụ thể
$query = SeaPay::account('store_hanoi')->queryTransaction('TXN_ID_12345');
```

**Thuộc tính của `TransactionQueryResponse`:**

| Thuộc tính | Kiểu | Mô tả |
|---|---|---|
| `success` | `bool` | Truy vấn thành công |
| `transactionId` | `string` | Mã giao dịch |
| `orderId` | `string` | Mã đơn hàng |
| `status` | `string` | Trạng thái giao dịch |
| `amount` | `float` | Số tiền |
| `paymentMethod` | `?string` | Phương thức thanh toán |
| `paidAt` | `?string` | Thời điểm thanh toán |

---

### Hoàn tiền

```php
use SeaPay\LaravelSeaPay\DTO\RefundRequest;

$refund = SeaPay::refund(new RefundRequest(
    transactionId: 'TXN_ID_12345',
    amount:        100000,        // Có thể hoàn một phần
    reason:        'Khách hàng đổi ý',
    refundOrderId: 'REFUND-001',  // Tuỳ chọn
));

if ($refund->success) {
    echo "Hoàn tiền thành công! Mã hoàn tiền: {$refund->refundId}";
}

// Hoàn tiền qua tài khoản cụ thể
$refund = SeaPay::account('store_hcm')->refund(new RefundRequest(
    transactionId: 'TXN_ID_12345',
    amount:        100000,
));
```

---

### Danh sách tài khoản

```php
$accounts = SeaPay::getAccounts();

// Kết quả:
// [
//   'main'         => ['merchant_id' => '...', 'description' => 'Cửa hàng chính'],
//   'store_hanoi'  => ['merchant_id' => '...', 'description' => 'Chi nhánh Hà Nội'],
//   'store_hcm'    => ['merchant_id' => '...', 'description' => 'Chi nhánh TP.HCM'],
// ]

foreach ($accounts as $name => $info) {
    echo "{$name}: {$info['description']} (merchant: {$info['merchant_id']})\n";
}
```

---

## Trang thanh toán tích hợp

Package cung cấp sẵn trang thanh toán với:
- **Mã QR** tự động render từ dữ liệu SeaPay API
- **Countdown** đếm ngược theo `expired_at`, chuyển đỏ khi < 60 giây
- **Thông tin chuyển khoản** (ngân hàng, số tài khoản, nội dung) với nút sao chép
- **Tự động polling** kiểm tra trạng thái mỗi 5 giây
- **Overlay** thành công / thất bại / hết hạn với redirect tự động

### Cách dùng

```php
// Trong controller của bạn
use SeaPay\LaravelSeaPay\Facades\SeaPay;
use SeaPay\LaravelSeaPay\DTO\PaymentRequest;

public function pay(Request $request)
{
    $paymentRequest = new PaymentRequest(
        orderId:      'ORDER-001',
        amount:       250000,
        description:  'Thanh toán đơn hàng',
        returnUrl:    route('payment.success'),  // redirect sau khi trả thành công
        cancelUrl:    route('payment.cancel'),
        expiredAt:    now()->addMinutes(15)->toIso8601String(),
        customerName: 'Nguyễn Văn A',
    );

    $response = SeaPay::createPayment($paymentRequest);

    // Chuyển tới trang thanh toán tích hợp
    return SeaPay::paymentPage($response, $paymentRequest);
}
```

Trang thanh toán sẽ tự động:
1. Hiển thị QR code để quét
2. Đếm ngược thời gian còn lại
3. Kiểm tra trạng thái mỗi 5 giây
4. Khi thanh toán xong → redirect tới `returnUrl`
5. Khi hết hạn → hiện overlay hết hạn

### Tuỳ chỉnh giao diện

Publish view để tuỳ chỉnh HTML/CSS:

```bash
php artisan vendor:publish --tag=seapay-views
```

File view sẽ được copy vào `resources/views/vendor/seapay/payment.blade.php`.

---

## Webhook

### Cấu hình webhook

Package tự tạo route:

```
POST https://yourdomain.com/seapay/webhook
```

Đăng ký URL này trong dashboard SEAPAY.

**Loại trừ khỏi CSRF** (bắt buộc):

```php
// app/Http/Middleware/VerifyCsrfToken.php  (Laravel 10)
protected $except = [
    'seapay/webhook',
];

// Laravel 11+: trong bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'seapay/webhook',
    ]);
})
```

Thay đổi đường dẫn webhook trong `.env`:

```env
SEAPAY_WEBHOOK_PATH=my-custom-webhook-path
SEAPAY_WEBHOOK_SECRET=your_webhook_secret
```

---

### Lắng nghe sự kiện

Package phát ra 3 events sau mỗi callback:

| Event | Khi nào |
|---|---|
| `PaymentSucceeded` | Thanh toán thành công |
| `PaymentFailed` | Thanh toán thất bại / huỷ |
| `RefundSucceeded` | Hoàn tiền thành công |

**Đăng ký listener:**

```php
// app/Providers/EventServiceProvider.php
use SeaPay\LaravelSeaPay\Events\PaymentSucceeded;
use SeaPay\LaravelSeaPay\Events\PaymentFailed;
use SeaPay\LaravelSeaPay\Events\RefundSucceeded;

protected $listen = [
    PaymentSucceeded::class => [
        \App\Listeners\HandleSeaPaySuccess::class,
    ],
    PaymentFailed::class => [
        \App\Listeners\HandleSeaPayFailed::class,
    ],
    RefundSucceeded::class => [
        \App\Listeners\HandleSeaPayRefund::class,
    ],
];
```

**Ví dụ Listener:**

```php
// app/Listeners/HandleSeaPaySuccess.php
namespace App\Listeners;

use SeaPay\LaravelSeaPay\Events\PaymentSucceeded;

class HandleSeaPaySuccess
{
    public function handle(PaymentSucceeded $event): void
    {
        $response = $event->response;   // PaymentResponse
        $account  = $event->account;    // 'main', 'store_hanoi', ...
        $rawData  = $event->webhookData; // Dữ liệu thô từ webhook

        // Cập nhật đơn hàng
        \App\Models\Order::where('code', $response->orderId)
            ->update([
                'status'         => 'paid',
                'transaction_id' => $response->transactionId,
                'paid_at'        => now(),
            ]);
    }
}
```

---

## Dependency Injection

Bạn có thể inject `SeaPayInterface` vào bất kỳ class nào:

```php
use SeaPay\LaravelSeaPay\Contracts\SeaPayInterface;
use SeaPay\LaravelSeaPay\DTO\PaymentRequest;

class PaymentService
{
    public function __construct(
        private readonly SeaPayInterface $seapay
    ) {}

    public function createCheckout(Order $order, string $account = 'main'): string
    {
        $response = $this->seapay
            ->account($account)
            ->createPayment(new PaymentRequest(
                orderId:      $order->code,
                amount:       $order->total,
                description:  "Thanh toán đơn #{$order->code}",
                returnUrl:    route('checkout.return', $order->id),
                cancelUrl:    route('checkout.cancel', $order->id),
                customerEmail: $order->customer->email,
            ));

        if (!$response->success) {
            throw new \RuntimeException($response->message ?? 'Không thể tạo liên kết thanh toán.');
        }

        return $response->paymentUrl;
    }
}
```

---

## Xử lý lỗi

```php
use SeaPay\LaravelSeaPay\Exceptions\SeaPayException;
use SeaPay\LaravelSeaPay\Exceptions\InvalidAccountException;
use SeaPay\LaravelSeaPay\Exceptions\PaymentException;

try {
    $response = SeaPay::account('store_x')->createPayment($request);

} catch (InvalidAccountException $e) {
    // Tài khoản không tồn tại hoặc thiếu credentials
    Log::error($e->getMessage(), $e->getContext());

} catch (PaymentException $e) {
    // Lỗi API (4xx/5xx) hoặc lỗi mạng
    Log::error("[{$e->getErrorCode()}] {$e->getMessage()}");
    // Các mã lỗi: NETWORK_ERROR | API_ERROR | INVALID_SIGNATURE

} catch (SeaPayException $e) {
    // Mọi lỗi khác của package
    Log::error($e->getMessage());
}
```

---

## Cấu hình nâng cao

Toàn bộ tùy chọn trong `config/seapay.php`:

```php
return [
    'default' => env('SEAPAY_ACCOUNT', 'main'),
    'env'     => env('SEAPAY_ENV', 'sandbox'), // 'sandbox' | 'production'

    // Endpoint API (tự động chọn theo 'env')
    'api_url' => [
        'sandbox'    => 'https://sandbox-api.seapay.vn/v1',
        'production' => 'https://api.seapay.vn/v1',
    ],

    // Cấu hình HTTP client
    'http' => [
        'timeout'         => 30,   // Giây chờ tối đa cho 1 request
        'connect_timeout' => 10,   // Giây chờ kết nối
        'retry'           => 2,    // Số lần retry khi lỗi mạng / 5xx
        'retry_delay'     => 500,  // Milliseconds giữa các lần retry
    ],

    // Webhook
    'webhook' => [
        'secret'      => env('SEAPAY_WEBHOOK_SECRET'),
        'path'        => env('SEAPAY_WEBHOOK_PATH', 'seapay/webhook'),
        'middlewares' => [], // Middleware tùy chọn cho route webhook
    ],

    // Logging
    'logging' => [
        'enabled' => true,
        'channel' => 'stack', // Log channel trong config/logging.php
    ],

    // Lưu DB
    'database' => [
        'enabled'    => true,
        'table_name' => 'seapay_transactions',
    ],

    'currency' => 'VND',
];
```

---

## Đóng góp

1. Fork repo
2. Tạo branch: `git checkout -b feature/ten-tinh-nang`
3. Commit: `git commit -m 'feat: thêm tính năng X'`
4. Push: `git push origin feature/ten-tinh-nang`
5. Tạo Pull Request

---

## License

[MIT](LICENSE)
