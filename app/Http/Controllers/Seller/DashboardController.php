<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        // Get basic statistics
        $stats = [
            'total_products' => $supplier->products()->count(),
            'total_orders' => $supplier->products()
                ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                ->join('order_items', 'product_variants.id', '=', 'order_items.product_variant_id')
                ->distinct('order_items.order_id')
                ->count('order_items.order_id'),
            'total_revenue' => $supplier->products()
                ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                ->join('order_items', 'product_variants.id', '=', 'order_items.product_variant_id')
                ->selectRaw('SUM(order_items.price * order_items.quantity) as total')
                ->value('total') ?? 0,
            'pending_orders' => $supplier->products()
                ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                ->join('order_items', 'product_variants.id', '=', 'order_items.product_variant_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', 'pending')
                ->distinct('order_items.order_id')
                ->count('order_items.order_id'),
        ];

        return view('seller.dashboard', compact('stats', 'supplier'));
    }
}
