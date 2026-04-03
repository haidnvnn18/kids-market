<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Seller\SellerRegisterRequest;
use App\Http\Requests\Api\Seller\SellerLoginRequest;
use App\Models\User;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(SellerRegisterRequest $request): JsonResponse
    {
        try {
            $user = DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'           => $request->name,
                    'username'       => $request->username ? strtolower($request->username) : null,
                    'email'          => strtolower($request->email),
                    'phone'          => $request->phone,
                    'password'       => Hash::make($request->password),
                    'role'           => 'seller',
                    'login_provider' => 'local',
                ]);

                Supplier::create([
                    'user_id'     => $user->id,
                    'brand_name'  => $request->brand_name,
                    'country'     => $request->country ?? 'CN',
                    'description' => $request->description,
                ]);

                return $user;
            });

            $accessToken = $this->generateAccessToken($user);

            return response()->json([
                'success'     => true,
                'message'     => 'Đăng ký tài khoản seller thành công',
                'accessToken' => $accessToken,
                'data'        => $this->formatUser($user),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng ký tài khoản thất bại',
            ], 500);
        }
    }

    public function login(SellerLoginRequest $request): JsonResponse
    {
        try {
            $user = User::where('username', $request->username_or_email)
                        ->orWhere('email', $request->username_or_email)
                        ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sai tài khoản hoặc mật khẩu',
                ], 401);
            }

            if ($user->role !== 'seller') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản này không phải tài khoản seller',
                ], 403);
            }

            $accessToken = $this->generateAccessToken($user);

            return response()->json([
                'success'     => true,
                'message'     => 'Đăng nhập thành công',
                'accessToken' => $accessToken,
                'data'        => $this->formatUser($user),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng nhập thất bại',
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        // JWT is stateless — client should discard the token
        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công',
        ], 200);
    }

    private function formatUser(User $user): array
    {
        $supplier = $user->supplier;

        return [
            'id'       => $user->id,
            'name'     => $user->name,
            'username' => $user->username,
            'email'    => $user->email,
            'phone'    => $user->phone,
            'role'     => $user->role,
            'shop'     => $supplier ? [
                'id'          => $supplier->id,
                'brand_name'  => $supplier->brand_name,
                'country'     => $supplier->country,
                'description' => $supplier->description,
                'rating_avg'  => $supplier->rating_avg,
                'avatar'      => $supplier->avatar ? asset('storage/' . $supplier->avatar) : null,
                'banner'      => $supplier->banner ? asset('storage/' . $supplier->banner) : null,
            ] : null,
        ];
    }

    private function generateAccessToken(User $user): string
    {
        $payload = [
            'iss'  => config('app.url'),
            'sub'  => $user->id,
            'iat'  => time(),
            'exp'  => time() + (24 * 60 * 60),
            'user' => [
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $user->role,
            ],
        ];

        return $this->encodeJwt($payload);
    }

    private function encodeJwt(array $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $headerEncoded  = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $payloadEncoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

        $signatureInput   = $headerEncoded . '.' . $payloadEncoded;
        $secret           = config('app.key');
        $signature        = hash_hmac('sha256', $signatureInput, $secret, true);
        $signatureEncoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return $signatureInput . '.' . $signatureEncoded;
    }
}
