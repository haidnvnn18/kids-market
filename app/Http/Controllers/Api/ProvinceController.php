<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\JsonResponse;

class ProvinceController extends Controller
{
    /**
     * Lấy danh sách tất cả tỉnh/thành phố
     */
    public function index(): JsonResponse
    {
        try {
            $provinces = Province::with('districts')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $provinces,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách tỉnh/thành phố',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy thông tin chi tiết tỉnh/thành phố kèm theo danh sách phường/xã
     */
    public function show(int $id): JsonResponse
    {
        try {
            $province = Province::with('districts')
                ->find($id);

            if (!$province) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tỉnh/Thành phố không tồn tại',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => $province,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin tỉnh/thành phố',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
