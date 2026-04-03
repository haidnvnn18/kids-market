<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthTokenSeller
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu hoặc sai định dạng token'
            ], 401);
        }

        $token = trim(str_replace('Bearer', '', $authHeader));

        $payload = $this->decodeJwt($token);

        if (!$payload) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ'
            ], 401);
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return response()->json([
                'success' => false,
                'message' => 'Token đã hết hạn'
            ], 401);
        }

        $userId = $payload['sub'] ?? null;
        $user = $userId ? User::find($userId) : null;

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không tồn tại'
            ], 401);
        }

        if ($user->role !== 'seller') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập tính năng này'
            ], 403);
        }

        $request->attributes->set('auth_user', $user);

        return $next($request);
    }

    private function decodeJwt(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $secret = config('app.key');
        $signatureInput = $headerEncoded . '.' . $payloadEncoded;
        $expectedSignature = rtrim(strtr(base64_encode(hash_hmac('sha256', $signatureInput, $secret, true)), '+/', '-_'), '=');

        if (!hash_equals($expectedSignature, $signatureEncoded)) {
            return null;
        }

        $payloadJson = base64_decode(strtr($payloadEncoded, '-_', '+/'));
        $payload = json_decode($payloadJson, true);

        return is_array($payload) ? $payload : null;
    }
}
