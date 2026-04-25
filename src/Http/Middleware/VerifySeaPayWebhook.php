<?php

namespace SeaPay\LaravelSeaPay\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use SeaPay\LaravelSeaPay\Contracts\SeaPayInterface;
use SeaPay\LaravelSeaPay\Exceptions\PaymentException;
use Symfony\Component\HttpFoundation\Response;

class VerifySeaPayWebhook
{
    public function __construct(private readonly SeaPayInterface $seapay) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!$this->seapay->verifyWebhook($request->all())) {
                return response()->json(['message' => 'Chữ ký không hợp lệ.'], 401);
            }
        } catch (PaymentException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return $next($request);
    }
}
