<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthToken
{
    /**
     * Handle an incoming request.
     */
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

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return response()->json([
                'success' => false,
                'message' => 'Token đã hết hạn'
            ], 401);
        }

        // Get user
        $userId = $payload['sub'] ?? null;
        $user = $userId ? User::find($userId) : null;

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không tồn tại'
            ], 401);
        }

        // Attach user to request
        $request->attributes->set('auth_user', $user);

        return $next($request);
    }

    private function decodeJwt(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header64, $payload64, $signature64] = $parts;

        $signatureInput = $header64 . '.' . $payload64;
        $secret = config('app.key');

        $signature = $this->base64UrlEncode(hash_hmac('sha256', $signatureInput, $secret, true));

        if (!hash_equals($signature, $signature64)) {
            return null;
        }

        $payloadJson = $this->base64UrlDecode($payload64);
        $payload = json_decode($payloadJson, true);

        return is_array($payload) ? $payload : null;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }
}
