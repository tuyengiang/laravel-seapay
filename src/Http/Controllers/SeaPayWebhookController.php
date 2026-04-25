<?php

namespace SeaPay\LaravelSeaPay\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SeaPay\LaravelSeaPay\Contracts\SeaPayInterface;
use SeaPay\LaravelSeaPay\DTO\PaymentResponse;
use SeaPay\LaravelSeaPay\Events\PaymentFailed;
use SeaPay\LaravelSeaPay\Events\PaymentSucceeded;
use SeaPay\LaravelSeaPay\Exceptions\PaymentException;

class SeaPayWebhookController extends Controller
{
    public function __construct(private readonly SeaPayInterface $seapay) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->all();

        // Xác minh chữ ký
        try {
            if (!$this->seapay->verifyWebhook($data)) {
                return response()->json(['message' => 'Chữ ký không hợp lệ.'], 401);
            }
        } catch (PaymentException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        // Xây dựng PaymentResponse từ dữ liệu webhook
        $response = PaymentResponse::fromApiResponse($data);
        $account  = $data['merchant_id'] ?? 'unknown';

        if ($response->isPaid()) {
            event(new PaymentSucceeded($response, $account, $data));
        } else {
            event(new PaymentFailed($response, $account, $data));
        }

        return response()->json(['message' => 'OK'], 200);
    }
}
