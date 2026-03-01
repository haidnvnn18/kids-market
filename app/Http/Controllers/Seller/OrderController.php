<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        if (!$supplier) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $query = Order::where('supplier_id', $supplier->id)
            ->with(['user', 'items.productVariant.product', 'payment', 'shipment'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by order ID or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        // Get order statistics
        $stats = [
            'total' => Order::where('supplier_id', $supplier->id)->count(),
            'pending' => Order::where('supplier_id', $supplier->id)->where('status', 'pending')->count(),
            'paid' => Order::where('supplier_id', $supplier->id)->where('status', 'paid')->count(),
            'shipping' => Order::where('supplier_id', $supplier->id)->where('status', 'shipping')->count(),
            'completed' => Order::where('supplier_id', $supplier->id)->where('status', 'completed')->count(),
            'cancelled' => Order::where('supplier_id', $supplier->id)->where('status', 'cancelled')->count(),
        ];

        return view('seller.orders.index', compact('orders', 'stats'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        if (!$supplier) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $order = Order::where('supplier_id', $supplier->id)
            ->with(['user', 'items.productVariant.product.images', 'payment', 'shipment'])
            ->findOrFail($id);

        return view('seller.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        if (!$supplier) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $order = Order::where('supplier_id', $supplier->id)->findOrFail($id);

        $request->validate([
            'status' => ['required', 'in:pending,paid,shipping,completed,cancelled'],
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Validate status transitions
        $allowedTransitions = [
            'pending' => ['paid', 'cancelled'],
            'paid' => ['shipping', 'cancelled'],
            'shipping' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$oldStatus])) {
            return back()->withErrors([
                'status' => "Không thể chuyển trạng thái từ '{$oldStatus}' sang '{$newStatus}'."
            ]);
        }

        $order->update(['status' => $newStatus]);

        return redirect()->route('seller.orders.show', $order->id)
            ->with('success', 'Cập nhật trạng thái đơn hàng thành công!');
    }
}
