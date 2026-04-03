<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Seller\ReviewReplyRequest;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $supplier = $request->attributes->get('auth_user')->supplier;

        $stats = Review::whereHas('product', function ($q) use ($supplier) {
            $q->where('supplier_id', $supplier->id);
        })->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star,
            SUM(CASE WHEN seller_reply IS NOT NULL THEN 1 ELSE 0 END) as replied,
            SUM(CASE WHEN seller_reply IS NULL THEN 1 ELSE 0 END) as not_replied,
            AVG(rating) as avg_rating
        ')->first();

        $query = Review::with(['user', 'product'])
            ->whereHas('product', fn ($q) => $q->where('supplier_id', $supplier->id))
            ->latest();

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('reply_status')) {
            if ($request->reply_status === 'replied') {
                $query->whereNotNull('seller_reply');
            } elseif ($request->reply_status === 'not_replied') {
                $query->whereNull('seller_reply');
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        $reviews = $query->paginate(20);

        return response()->json([
            'success' => true,
            'stats'   => [
                'total'      => (int) $stats->total,
                'five_star'  => (int) $stats->five_star,
                'four_star'  => (int) $stats->four_star,
                'three_star' => (int) $stats->three_star,
                'two_star'   => (int) $stats->two_star,
                'one_star'   => (int) $stats->one_star,
                'replied'    => (int) $stats->replied,
                'not_replied'=> (int) $stats->not_replied,
                'avg_rating' => round((float) $stats->avg_rating, 2),
            ],
            'data'    => $reviews->map(fn ($r) => $this->formatReview($r)),
            'meta'    => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'per_page'     => $reviews->perPage(),
                'total'        => $reviews->total(),
            ],
        ]);
    }

    public function reply(ReviewReplyRequest $request, int $id): JsonResponse
    {
        $supplier = $request->attributes->get('auth_user')->supplier;

        $review = Review::whereHas('product', fn ($q) => $q->where('supplier_id', $supplier->id))
            ->find($id);

        if (!$review) {
            return response()->json(['success' => false, 'message' => 'Đánh giá không tồn tại'], 404);
        }

        $review->update([
            'seller_reply' => $request->reply,
            'replied_at'   => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã trả lời đánh giá',
            'data'    => $this->formatReview($review->fresh(['user', 'product'])),
        ]);
    }

    private function formatReview(Review $review): array
    {
        return [
            'id'           => $review->id,
            'rating'       => $review->rating,
            'comment'      => $review->comment,
            'seller_reply' => $review->seller_reply,
            'replied_at'   => $review->replied_at?->toIso8601String(),
            'created_at'   => $review->created_at?->toIso8601String(),
            'user'         => $review->user ? [
                'id'     => $review->user->id,
                'name'   => $review->user->name,
                'avatar' => $review->user->avatar ? asset('storage/' . $review->user->avatar) : null,
            ] : null,
            'product'      => $review->product ? [
                'id'   => $review->product->id,
                'name' => $review->product->name,
            ] : null,
        ];
    }
}
