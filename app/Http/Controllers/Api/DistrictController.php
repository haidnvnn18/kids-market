<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\JsonResponse;

class DistrictController extends Controller
{
    /**
     * Lấy danh sách phường/xã theo tỉnh/thành phố
     */
    public function index(int $provinceId): JsonResponse
    {
        try {
            $districts = District::where('province_id', $provinceId)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $districts,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách phường/xã',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
