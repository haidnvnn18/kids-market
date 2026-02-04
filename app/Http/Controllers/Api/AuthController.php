<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user account
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Create new user
            $user = User::create([
                'name' => $request->fullname,
                'username' => $request->username,
                'password' => bcrypt($request->password),
                'phone' => $request->phone,
                'dob' => $request->dob,
                'role' => 'buyer',
                'login_provider' => 'local',
            ]);

            // Create cart for new user
            $user->cart()->create();

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký tài khoản thành công',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng ký tài khoản thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user with username and password
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Find user by username or email
            $user = User::where('username', $request->username)
                        ->orWhere('email', $request->username)
                        ->first();

            // Check if user exists and password is correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sai tài khoản hoặc mật khẩu'
                ], 401);
            }

            // Generate access token
            $accessToken = $this->generateAccessToken($user);

            return response()->json([
                'success' => true,
                'userId' => (string)$user->id,
                'accessToken' => $accessToken,
                'message' => 'Đăng nhập thành công'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng nhập thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a simple JWT-like access token
     * In production, consider using tymon/jwt-auth package
     */
    private function generateAccessToken(User $user): string
    {
        // Payload
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60), // 24 hours
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ];

        // Encode JWT
        return $this->encodeJwt($payload);
    }

    /**
     * Simple JWT encoding (Base64URL)
     * In production, use a proper JWT library
     */
    private function encodeJwt(array $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        
        $header_encoded = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $payload_encoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        
        $signature_input = $header_encoded . '.' . $payload_encoded;
        $secret = config('app.key');
        
        $signature = hash_hmac('sha256', $signature_input, $secret, true);
        $signature_encoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        
        return $signature_input . '.' . $signature_encoded;
    }
}
