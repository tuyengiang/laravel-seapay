<?php

namespace SeaPay\LaravelSeaPay;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SeaPay\LaravelSeaPay\Exceptions\InvalidAccountException;
use SeaPay\LaravelSeaPay\Exceptions\PaymentException;

class SeaPayClient
{
    private Client $http;
    private string $baseUrl;
    private string $merchantId;
    private string $apiKey;
    private string $secretKey;

    /** @param array<string, mixed> $config */
    public function __construct(private readonly array $config)
    {
        $this->baseUrl = $config['api_url'][$config['env'] ?? 'sandbox'];
        $this->http    = $this->buildHttpClient();
    }

    /**
     * Gán thông tin xác thực cho tài khoản hiện tại.
     *
     * @param array<string, mixed> $accountConfig
     */
    public function withAccount(array $accountConfig, string $accountName): static
    {
        foreach (['merchant_id', 'api_key', 'secret_key'] as $field) {
            if (empty($accountConfig[$field])) {
                throw InvalidAccountException::missingCredentials($accountName, $field);
            }
        }

        $clone             = clone $this;
        $clone->merchantId = $accountConfig['merchant_id'];
        $clone->apiKey     = $accountConfig['api_key'];
        $clone->secretKey  = $accountConfig['secret_key'];

        return $clone;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function post(string $endpoint, array $payload): array
    {
        $payload = $this->signPayload($payload);

        try {
            $response = $this->http->post($endpoint, [
                'json'    => $payload,
                'headers' => $this->buildHeaders(),
            ]);

            return $this->decodeResponse($response);
        } catch (ConnectException $e) {
            throw PaymentException::networkError($e->getMessage());
        } catch (RequestException $e) {
            $body = $e->hasResponse()
                ? (string) $e->getResponse()->getBody()
                : $e->getMessage();

            $this->log('error', 'SeaPay API request failed', ['error' => $body, 'endpoint' => $endpoint]);
            throw PaymentException::apiError($body);
        }
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public function get(string $endpoint, array $query = []): array
    {
        $query = $this->signPayload($query);

        try {
            $response = $this->http->get($endpoint, [
                'query'   => $query,
                'headers' => $this->buildHeaders(),
            ]);

            return $this->decodeResponse($response);
        } catch (ConnectException $e) {
            throw PaymentException::networkError($e->getMessage());
        } catch (RequestException $e) {
            $body = $e->hasResponse()
                ? (string) $e->getResponse()->getBody()
                : $e->getMessage();

            throw PaymentException::apiError($body);
        }
    }

    /**
     * Tạo chữ ký HMAC-SHA256 cho request.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function signPayload(array $payload): array
    {
        $payload['merchant_id'] = $this->merchantId;
        $payload['timestamp']   = time();

        ksort($payload);
        $stringToSign = http_build_query($payload);
        $payload['signature'] = hash_hmac('sha256', $stringToSign, $this->secretKey);

        return $payload;
    }

    /**
     * Xác minh chữ ký webhook từ SeaPay.
     *
     * @param array<string, mixed> $data
     */
    public function verifyWebhookSignature(array $data, string $webhookSecret): bool
    {
        $receivedSignature = $data['signature'] ?? '';
        unset($data['signature']);

        ksort($data);
        $stringToSign      = http_build_query($data);
        $expectedSignature = hash_hmac('sha256', $stringToSign, $webhookSecret);

        return hash_equals($expectedSignature, $receivedSignature);
    }

    /** @return array<string, string> */
    private function buildHeaders(): array
    {
        return [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'X-Api-Key'     => $this->apiKey,
            'X-Merchant-Id' => $this->merchantId,
        ];
    }

    /** @return array<string, mixed> */
    private function decodeResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw PaymentException::apiError('Phản hồi từ SeaPay không hợp lệ (không phải JSON).');
        }

        return $data;
    }

    private function buildHttpClient(): Client
    {
        $stack = HandlerStack::create();

        $retries = (int) ($this->config['http']['retry'] ?? 2);
        if ($retries > 0) {
            $stack->push(Middleware::retry(
                decider: function (int $retries, RequestInterface $req, ?ResponseInterface $res = null, ?\Throwable $e = null) use ($retries): bool {
                    if ($retries >= $retries) {
                        return false;
                    }
                    if ($e instanceof ConnectException) {
                        return true;
                    }
                    return $res && $res->getStatusCode() >= 500;
                },
                delay: fn (int $retries) => (int) ($this->config['http']['retry_delay'] ?? 500) * $retries,
            ));
        }

        return new Client([
            'base_uri'        => rtrim($this->baseUrl, '/') . '/',
            'timeout'         => $this->config['http']['timeout'] ?? 30,
            'connect_timeout' => $this->config['http']['connect_timeout'] ?? 10,
            'handler'         => $stack,
            'http_errors'     => true,
        ]);
    }

    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->config['logging']['enabled'] ?? true) {
            Log::channel($this->config['logging']['channel'] ?? 'stack')
                ->{$level}("[SeaPay] {$message}", $context);
        }
    }
}
