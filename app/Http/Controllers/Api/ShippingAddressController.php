<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShippingAddressRequest;
use App\Models\ShippingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShippingAddressController extends Controller
{
    /**
     * Lấy danh sách địa chỉ nhận hàng của người mua.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->attributes->get('auth_user');

            $addresses = ShippingAddress::where('user_id', $user->id)
                ->with(['province', 'district'])
                ->orderByDesc('is_default')
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $addresses,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách địa chỉ',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Thêm địa chỉ nhận hàng mới.
     */
    public function store(ShippingAddressRequest $request): JsonResponse
    {
        try {
            $user = $request->attributes->get('auth_user');

            $address = DB::transaction(function () use ($user, $request) {
                $isDefault = $request->boolean('is_default', false);

                // Nếu đặt làm mặc định, bỏ mặc định của các địa chỉ cũ
                if ($isDefault) {
                    ShippingAddress::where('user_id', $user->id)
                        ->where('is_default', true)
                        ->update(['is_default' => false]);
                }

                // Nếu là địa chỉ đầu tiên, tự động đặt làm mặc định
                $hasAddress = ShippingAddress::where('user_id', $user->id)->exists();
                if (!$hasAddress) {
                    $isDefault = true;
                }

                return ShippingAddress::create([
                    'user_id'        => $user->id,
                    'province_id'    => $request->province_id,
                    'district_id'    => $request->district_id,
                    'full_name'      => $request->full_name,
                    'phone'          => $request->phone,
                    'address_detail' => $request->address_detail,
                    'is_default'     => $isDefault,
                ]);
            });

            $address->load(['province', 'district']);

            return response()->json([
                'success' => true,
                'message' => 'Thêm địa chỉ thành công',
                'data'    => $address,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thêm địa chỉ',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cập nhật địa chỉ nhận hàng.
     */
    public function update(ShippingAddressRequest $request, int $id): JsonResponse
    {
        try {
            $user = $request->attributes->get('auth_user');

            $address = ShippingAddress::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Địa chỉ không tồn tại',
                ], 404);
            }

            $address = DB::transaction(function () use ($user, $address, $request) {
                $isDefault = $request->boolean('is_default', $address->is_default);

                // Nếu đặt làm mặc định, bỏ mặc định của các địa chỉ cũ
                if ($isDefault && !$address->is_default) {
                    ShippingAddress::where('user_id', $user->id)
                        ->where('is_default', true)
                        ->update(['is_default' => false]);
                }

                $address->update([
                    'province_id'    => $request->province_id,
                    'district_id'    => $request->district_id,
                    'full_name'      => $request->full_name,
                    'phone'          => $request->phone,
                    'address_detail' => $request->address_detail,
                    'is_default'     => $isDefault,
                ]);

                return $address;
            });

            $address->load(['province', 'district']);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật địa chỉ thành công',
                'data'    => $address,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật địa chỉ',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Đặt địa chỉ làm mặc định.
     */
    public function setDefault(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->attributes->get('auth_user');

            $address = ShippingAddress::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Địa chỉ không tồn tại',
                ], 404);
            }

            DB::transaction(function () use ($user, $address) {
                ShippingAddress::where('user_id', $user->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);

                $address->update(['is_default' => true]);
            });

            $address->load(['province', 'district']);

            return response()->json([
                'success' => true,
                'message' => 'Đã đặt làm địa chỉ mặc định',
                'data'    => $address,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật địa chỉ mặc định',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa địa chỉ nhận hàng.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->attributes->get('auth_user');

            $address = ShippingAddress::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Địa chỉ không tồn tại',
                ], 404);
            }

            $wasDefault = $address->is_default;
            $address->delete();

            // Nếu xóa địa chỉ mặc định, tự động đặt địa chỉ mới nhất làm mặc định
            if ($wasDefault) {
                $next = ShippingAddress::where('user_id', $user->id)
                    ->orderByDesc('created_at')
                    ->first();

                if ($next) {
                    $next->update(['is_default' => true]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Xóa địa chỉ thành công',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa địa chỉ',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
