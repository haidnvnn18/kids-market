<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user     = $request->attributes->get('auth_user');
        $supplier = $user->supplier;

        $ordersQuery    = Order::where('supplier_id', $supplier->id);
        $todayQuery     = (clone $ordersQuery)->whereDate('created_at', today());

        $stats = [
            'total_products'  => $supplier->products()->count(),
            'total_orders'    => $ordersQuery->count(),
            'today_orders'    => $todayQuery->count(),
            'total_revenue'   => (float) $ordersQuery->sum('total_amount'),
            'today_revenue'   => (float) $todayQuery->sum('total_amount'),
            'pending_orders'  => (clone $ordersQuery)->where('status', 'pending')->count(),
            'reviews_count'   => $supplier->products()
                ->join('reviews', 'products.id', '=', 'reviews.product_id')
                ->count(),
            'avg_rating'      => round((float) $supplier->products()
                ->join('reviews', 'products.id', '=', 'reviews.product_id')
                ->avg('reviews.rating'), 2),
        ];

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }
}
