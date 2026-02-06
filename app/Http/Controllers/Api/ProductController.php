<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Search products by name (autocomplete)
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q', '');

            if (strlen($query) < 1) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ], 200);
            }

            // Search for products matching the query
            $products = Product::where('status', 'active')
                ->where('name', 'like', '%' . $query . '%')
                ->select('id', 'name')
                ->limit(10)
                ->get();

            // Extract only product names
            $suggestions = $products->pluck('name')->toArray();

            return response()->json([
                'success' => true,
                'data' => $suggestions
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tìm kiếm sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed product list with filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::where('status', 'active');

            // Filter by category
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Filter by supplier
            if ($request->has('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }

            // Filter by search
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Pagination
            $perPage = $request->query('per_page', 10);
            $products = $query->with(['supplier', 'category', 'variants', 'images'])
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single product detail
     */
    public function show($id): JsonResponse
    {
        try {
            $product = Product::with(['supplier', 'category', 'variants', 'images'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $product
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tìm thấy',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
