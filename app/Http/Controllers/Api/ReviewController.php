<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Get all reviews for a product
     */
    public function index($productId): JsonResponse
    {
        try {
            $product = Product::findOrFail($productId);

            $reviews = Review::where('product_id', $productId)
                ->with('user:id,name,email')
                ->orderBy('created_at', 'desc')
                ->get();

            $averageRating = $reviews->avg('rating');
            $totalReviews = $reviews->count();

            $ratingDistribution = [
                5 => $reviews->where('rating', 5)->count(),
                4 => $reviews->where('rating', 4)->count(),
                3 => $reviews->where('rating', 3)->count(),
                2 => $reviews->where('rating', 2)->count(),
                1 => $reviews->where('rating', 1)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                    ],
                    'reviews' => $reviews,
                    'statistics' => [
                        'average_rating' => round($averageRating, 1),
                        'total_reviews' => $totalReviews,
                        'rating_distribution' => $ratingDistribution,
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách đánh giá',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new review for a product
     */
    public function store(Request $request, $productId): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get authenticated user from middleware
            $user = $request->attributes->get('auth_user');

            // Check if product exists
            $product = Product::findOrFail($productId);

            // Check if user has already reviewed this product
            $existingReview = Review::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã đánh giá sản phẩm này rồi'
                ], 409);
            }

            // Create review
            $review = Review::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            // Load user relationship
            $review->load('user:id,name,email');

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá sản phẩm thành công',
                'data' => $review
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo đánh giá',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
