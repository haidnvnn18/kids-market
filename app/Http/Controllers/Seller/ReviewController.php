<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $supplier = $request->user()->supplier;

        // Get statistics
        $stats = Review::whereHas('product', function ($query) use ($supplier) {
            $query->where('supplier_id', $supplier->id);
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

        // Build query
        $query = Review::with(['user', 'product'])
            ->whereHas('product', function ($q) use ($supplier) {
                $q->where('supplier_id', $supplier->id);
            })
            ->orderBy('created_at', 'desc');

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by reply status
        if ($request->filled('reply_status')) {
            if ($request->reply_status === 'replied') {
                $query->whereNotNull('seller_reply');
            } elseif ($request->reply_status === 'not_replied') {
                $query->whereNull('seller_reply');
            }
        }

        // Search by customer name or comment
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        $reviews = $query->paginate(20);

        // Get products for filter dropdown
        $products = Product::where('supplier_id', $supplier->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('seller.reviews.index', compact('reviews', 'stats', 'products'));
    }

    public function reply(Request $request, $id)
    {
        $supplier = $request->user()->supplier;

        $review = Review::whereHas('product', function ($query) use ($supplier) {
            $query->where('supplier_id', $supplier->id);
        })->findOrFail($id);

        $request->validate([
            'seller_reply' => 'required|string|max:1000',
        ]);

        $review->update([
            'seller_reply' => $request->seller_reply,
            'replied_at' => now(),
        ]);

        return redirect()->route('seller.reviews.index')
            ->with('success', 'Đã trả lời đánh giá thành công');
    }
}
