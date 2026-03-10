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
            abort(403, __('seller.common.forbidden'));
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
            abort(403, __('seller.common.forbidden'));
        }

        $order = Order::where('supplier_id', $supplier->id)
            ->with([
                'user', 
                'items.productVariant.product.images', 
                'payment', 
                'shipment',
                'shippingProvince',
                'shippingDistrict'
            ])
            ->findOrFail($id);

        return view('seller.orders.show', compact('order'));
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        if (!$supplier) {
            abort(403, __('seller.common.forbidden'));
        }

        $order = Order::where('supplier_id', $supplier->id)
            ->with('payment')
            ->findOrFail($id);

        if (!$order->payment) {
            return back()->withErrors([
                'payment_status' => __('seller.orders.no_payment_info')
            ]);
        }

        $request->validate([
            'payment_status' => ['required', 'in:pending,success,failed'],
        ]);

        $oldStatus = $order->payment->status;
        $newStatus = $request->payment_status;

        // Validate status transitions
        $allowedTransitions = [
            'pending' => ['success', 'failed'],
            'failed' => ['success'],
            'success' => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$oldStatus])) {
            return back()->withErrors([
                'payment_status' => __('seller.orders.invalid_payment_transition', ['from' => $oldStatus, 'to' => $newStatus])
            ]);
        }

        // Update payment status
        $order->payment->update([
            'status' => $newStatus,
            'paid_at' => $newStatus === 'success' ? now() : null,
        ]);

        // Update order status based on payment status
        if ($newStatus === 'success' && $order->status === 'pending') {
            $order->update(['status' => 'paid']);
        }

        return redirect()->route('seller.orders.show', $order->id)
            ->with('success', __('seller.orders.payment_status_updated'));
    }

    public function updateShipmentStatus(Request $request, $id)
    {
        $user = Auth::user();
        $supplier = $user->supplier;

        if (!$supplier) {
            abort(403, __('seller.common.forbidden'));
        }

        $order = Order::where('supplier_id', $supplier->id)
            ->with('shipment')
            ->findOrFail($id);

        if (!$order->shipment) {
            return back()->withErrors([
                'shipment_status' => __('seller.orders.no_shipment_info')
            ]);
        }

        $request->validate([
            'shipment_status' => ['required', 'in:pending,processing,shipped,delivered,cancelled'],
            'tracking_code' => ['nullable', 'string', 'max:255'],
        ]);

        $oldStatus = $order->shipment->status;
        $newStatus = $request->shipment_status;

        // Validate status transitions
        $allowedTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
            'delivered' => [],
            'cancelled' => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$oldStatus])) {
            return back()->withErrors([
                'shipment_status' => __('seller.orders.invalid_shipment_transition', ['from' => $oldStatus, 'to' => $newStatus])
            ]);
        }

        // Update shipment status
        $updateData = ['status' => $newStatus];
        
        if ($request->filled('tracking_code')) {
            $updateData['tracking_code'] = $request->tracking_code;
        }

        $order->shipment->update($updateData);

        // Update order status based on shipment status
        if ($newStatus === 'processing' || $newStatus === 'shipped') {
            if ($order->status === 'paid' || $order->status === 'pending') {
                $order->update(['status' => 'shipping']);
            }
        } elseif ($newStatus === 'delivered') {
            $order->update(['status' => 'completed']);
        } elseif ($newStatus === 'cancelled') {
            $order->update(['status' => 'cancelled']);
        }

        return redirect()->route('seller.orders.show', $order->id)
            ->with('success', __('seller.orders.shipment_status_updated'));
    }
}
