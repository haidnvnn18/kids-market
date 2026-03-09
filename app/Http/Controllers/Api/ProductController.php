<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductLike;
use App\Models\ProductView;
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
                ->withCount('views')
                ->withCount('likes')
                ->withSum('completedOrderItems', 'quantity')
                ->paginate($perPage);

            // Rename sum field for clarity
            $products->getCollection()->transform(function ($product) {
                $product->sold_count = (int) ($product->completed_order_items_sum_quantity ?? 0);
                unset($product->completed_order_items_sum_quantity);
                return $product;
            });

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
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->attributes->get('auth_user');

            $product = Product::with(['supplier', 'category', 'variants', 'images'])
                ->withCount('views')
                ->withCount('likes')
                ->withSum('completedOrderItems', 'quantity')
                ->findOrFail($id);

            $product->sold_count = (int) ($product->completed_order_items_sum_quantity ?? 0);
            unset($product->completed_order_items_sum_quantity);

            $product->is_liked = $user
                ? ProductLike::where('user_id', $user->id)->where('product_id', $id)->exists()
                : false;

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

    /**
     * Toggle like/unlike a product for the authenticated user
     */
    public function toggleLike(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->attributes->get('auth_user');

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập để thích sản phẩm'
                ], 401);
            }

            $product = Product::findOrFail($id);

            $like = ProductLike::where('user_id', $user->id)
                ->where('product_id', $id)
                ->first();

            if ($like) {
                $like->delete();
                $liked = false;
                $message = 'Đã bỏ thích sản phẩm';
            } else {
                ProductLike::create([
                    'user_id' => $user->id,
                    'product_id' => $id,
                ]);
                $liked = true;
                $message = 'Đã thích sản phẩm';
            }

            $likesCount = ProductLike::where('product_id', $id)->count();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'is_liked' => $liked,
                    'likes_count' => $likesCount,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thực hiện thao tác thích sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recommended products for homepage based on user's viewed products
     */
    public function getHomePageRecommendations(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $user = $request->attributes->get('auth_user');

            if (!$user) {
                // If user is not authenticated, return popular products by view count
                $products = Product::where('status', 'active')
                    ->with(['supplier', 'category', 'variants', 'images'])
                    ->withCount('views')
                    ->orderBy('views_count', 'desc')
                    ->paginate($perPage);

                return response()->json([
                    'success' => true,
                    'data' => $products,
                    'recommendation_type' => 'popular'
                ], 200);
            }

            // Get user's recently viewed products (last 20 views)
            $viewedProductIds = ProductView::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->pluck('product_id')
                ->toArray();

            // If user has viewed products, get related products
            if (!empty($viewedProductIds)) {
                // Get categories and suppliers from viewed products
                $viewedProducts = Product::whereIn('id', $viewedProductIds)->get();
                $categoryIds = $viewedProducts->pluck('category_id')->unique()->toArray();
                $supplierIds = $viewedProducts->pluck('supplier_id')->unique()->toArray();

                // Get related products: same category or supplier, excluding already viewed
                $products = Product::where('status', 'active')
                    ->whereNotIn('id', $viewedProductIds)
                    ->where(function ($query) use ($categoryIds, $supplierIds) {
                        $query->whereIn('category_id', $categoryIds)
                              ->orWhereIn('supplier_id', $supplierIds);
                    })
                    ->with(['supplier', 'category', 'variants', 'images'])
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);

                return response()->json([
                    'success' => true,
                    'data' => $products,
                    'recommendation_type' => 'related'
                ], 200);
            }

            // If user has no view history, return newest active products
            $products = Product::where('status', 'active')
                ->with(['supplier', 'category', 'variants', 'images'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products,
                'recommendation_type' => 'newest'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách sản phẩm đề xuất',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track product view for logged-in users
     */
    public function trackView(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->attributes->get('auth_user');

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập để ghi nhận hoạt động xem sản phẩm'
                ], 401);
            }

            $product = Product::findOrFail($id);

            // Create or update the view record
            ProductView::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'product_id' => $id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Đã ghi nhận hoạt động xem sản phẩm'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi ghi nhận hoạt động xem sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
