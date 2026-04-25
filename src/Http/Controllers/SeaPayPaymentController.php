<?php

namespace SeaPay\LaravelSeaPay\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use SeaPay\LaravelSeaPay\Contracts\SeaPayInterface;
use SeaPay\LaravelSeaPay\DTO\PaymentSession;

class SeaPayPaymentController extends Controller
{
    public function __construct(private readonly SeaPayInterface $seapay) {}

    public function show(string $token): mixed
    {
        $session = $this->getSession($token);

        if (!$session) {
            abort(404, 'Phiên thanh toán không tồn tại hoặc đã hết hạn.');
        }

        return view('seapay::payment', compact('session'));
    }

    public function checkStatus(string $token): JsonResponse
    {
        $session = $this->getSession($token);

        if (!$session) {
            return response()->json(['status' => 'expired', 'is_expired' => true]);
        }

        // Trạng thái cuối — không cần query API thêm
        if (in_array($session->status, ['success', 'paid', 'completed', 'failed', 'cancelled', 'expired'])) {
            return $this->statusResponse($session);
        }

        // Query SeaPay API để lấy trạng thái mới nhất
        try {
            $query = $this->seapay->account($session->account)->queryTransaction($session->transactionId);

            if ($query->status !== $session->status) {
                $data           = $session->toArray();
                $data['status'] = $query->status;
                Cache::put($this->cacheKey($token), $data, 3600);
                $session->status = $query->status;
            }
        } catch (\Throwable) {
            // Giữ trạng thái cũ nếu API lỗi
        }

        return $this->statusResponse($session);
    }

    private function statusResponse(PaymentSession $session): JsonResponse
    {
        $isPaid   = in_array($session->status, ['success', 'paid', 'completed']);
        $isFailed = in_array($session->status, ['failed', 'cancelled', 'expired']);

        return response()->json([
            'status'     => $session->status,
            'is_paid'    => $isPaid,
            'is_failed'  => $isFailed,
            'return_url' => $session->returnUrl,
            'cancel_url' => $session->cancelUrl,
        ]);
    }

    private function getSession(string $token): ?PaymentSession
    {
        $data = Cache::get($this->cacheKey($token));
        return $data ? PaymentSession::fromArray($data) : null;
    }

    private function cacheKey(string $token): string
    {
        return 'seapay_session_' . $token;
    }
}
