<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShopController extends Controller
{
    /**
     * Get shop information
     */
    public function show($id): JsonResponse
    {
        try {
            $shop = Supplier::with('user:id,name,email')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $shop->id,
                    'brand_name' => $shop->brand_name,
                    'country' => $shop->country,
                    'description' => $shop->description,
                    'rating_avg' => $shop->rating_avg,
                    'owner' => [
                        'id' => $shop->user->id,
                        'name' => $shop->user->name,
                        'email' => $shop->user->email,
                    ],
                    'created_at' => $shop->created_at,
                    'updated_at' => $shop->updated_at,
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy shop'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin shop',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products of a shop with pagination and filtering
     */
    public function getProducts(Request $request, $id): JsonResponse
    {
        try {
            // Check if shop exists
            $shop = Supplier::findOrFail($id);

            $query = Product::where('supplier_id', $id)
                ->where('status', 'active');

            // Filter by category
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Filter by search
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Sorting
            $sortBy = $request->query('sort_by', 'created_at');
            $sortOrder = $request->query('sort_order', 'desc');
            
            if (in_array($sortBy, ['name', 'created_at', 'rating_avg'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->query('per_page', 10);
            $products = $query->with(['category', 'variants', 'images', 'reviews'])
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'shop' => [
                        'id' => $shop->id,
                        'brand_name' => $shop->brand_name,
                        'rating_avg' => $shop->rating_avg,
                    ],
                    'products' => $products->items(),
                    'pagination' => [
                        'total' => $products->total(),
                        'per_page' => $products->perPage(),
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy shop'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách sản phẩm của shop',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
