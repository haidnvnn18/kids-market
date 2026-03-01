<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        // Get orders for this supplier
        $orders = Order::where('supplier_id', $supplier->id);
        $today_orders = (clone $orders)->whereDate('created_at', today());

        // Get basic statistics
        $stats = [
            'total_products' => $supplier->products()->count(),
            'total_orders' => $orders->count(),
            'today_orders' => $today_orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'today_revenue' => $today_orders->sum('total_amount'),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'reviews_count' => $supplier->products()
                ->join('reviews', 'products.id', '=', 'reviews.product_id')
                ->count(),
            'avg_rating' => $supplier->products()
                ->join('reviews', 'products.id', '=', 'reviews.product_id')
                ->avg('reviews.rating'),
        ];

        return view('seller.dashboard', compact('stats', 'supplier'));
    }
}
