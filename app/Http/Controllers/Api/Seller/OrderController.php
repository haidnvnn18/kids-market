<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Seller\UpdatePaymentStatusRequest;
use App\Http\Requests\Api\Seller\UpdateShipmentStatusRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $supplier = $request->attributes->get('auth_user')->supplier;

        $query = Order::where('supplier_id', $supplier->id)
            ->with(['user', 'items.productVariant.product', 'payment', 'shipment'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

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

        $orders = $query->paginate(15);

        $stats = [
            'total'     => Order::where('supplier_id', $supplier->id)->count(),
            'pending'   => Order::where('supplier_id', $supplier->id)->where('status', 'pending')->count(),
            'paid'      => Order::where('supplier_id', $supplier->id)->where('status', 'paid')->count(),
            'shipping'  => Order::where('supplier_id', $supplier->id)->where('status', 'shipping')->count(),
            'completed' => Order::where('supplier_id', $supplier->id)->where('status', 'completed')->count(),
            'cancelled' => Order::where('supplier_id', $supplier->id)->where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'success' => true,
            'data'    => $orders->map(fn ($o) => $this->formatOrderSummary($o)),
            'stats'   => $stats,
            'meta'    => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $supplier = $request->attributes->get('auth_user')->supplier;

        $order = Order::where('supplier_id', $supplier->id)
            ->with([
                'user',
                'items.productVariant.product.images',
                'payment',
                'shipment',
                'shippingProvince',
                'shippingDistrict',
            ])
            ->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Đơn hàng không tồn tại'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatOrderDetail($order),
        ]);
    }

    public function updatePaymentStatus(UpdatePaymentStatusRequest $request, int $id): JsonResponse
    {
        $supplier = $request->attributes->get('auth_user')->supplier;

        $order = Order::where('supplier_id', $supplier->id)->with('payment')->find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Đơn hàng không tồn tại'], 404);
        }

        if (!$order->payment) {
            return response()->json(['success' => false, 'message' => 'Đơn hàng chưa có thông tin thanh toán'], 422);
        }

        $oldStatus = $order->payment->status;
        $newStatus = $request->payment_status;

        $allowedTransitions = [
            'pending' => ['success', 'failed'],
            'failed'  => ['success'],
            'success' => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$oldStatus] ?? [])) {
            return response()->json([
                'success' => false,
                'message' => "Không thể chuyển trạng thái thanh toán từ '{$oldStatus}' sang '{$newStatus}'",
            ], 422);
        }

        $order->payment->update([
            'status'  => $newStatus,
            'paid_at' => $newStatus === 'success' ? now() : null,
        ]);

        if ($newStatus === 'success' && $order->status === 'pending') {
            $order->update(['status' => 'paid']);
        }

        return response()->json([
            'success'         => true,
            'message'         => 'Cập nhật trạng thái thanh toán thành công',
            'payment_status'  => $newStatus,
            'order_status'    => $order->fresh()->status,
        ]);
    }

    public function updateShipmentStatus(UpdateShipmentStatusRequest $request, int $id): JsonResponse
    {
        $supplier = $request->attributes->get('auth_user')->supplier;

        $order = Order::where('supplier_id', $supplier->id)->with('shipment')->find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Đơn hàng không tồn tại'], 404);
        }

        if (!$order->shipment) {
            return response()->json(['success' => false, 'message' => 'Đơn hàng chưa có thông tin vận chuyển'], 422);
        }

        $oldStatus = $order->shipment->status ?? 'pending';
        $newStatus = $request->shipment_status;

        $allowedTransitions = [
            'pending'    => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped'    => ['delivered', 'cancelled'],
            'delivered'  => [],
            'cancelled'  => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$oldStatus] ?? [])) {
            return response()->json([
                'success' => false,
                'message' => "Không thể chuyển trạng thái vận chuyển từ '{$oldStatus}' sang '{$newStatus}'",
            ], 422);
        }

        $updateData = ['status' => $newStatus];
        if ($request->filled('tracking_code')) {
            $updateData['tracking_code'] = $request->tracking_code;
        }
        $order->shipment->update($updateData);

        if (in_array($newStatus, ['processing', 'shipped'])) {
            if (in_array($order->status, ['paid', 'pending'])) {
                $order->update(['status' => 'shipping']);
            }
        } elseif ($newStatus === 'delivered') {
            $order->update(['status' => 'completed']);
        } elseif ($newStatus === 'cancelled') {
            $order->update(['status' => 'cancelled']);
        }

        return response()->json([
            'success'          => true,
            'message'          => 'Cập nhật trạng thái vận chuyển thành công',
            'shipment_status'  => $newStatus,
            'order_status'     => $order->fresh()->status,
        ]);
    }

    private function formatOrderSummary(Order $order): array
    {
        return [
            'id'           => $order->id,
            'status'       => $order->status,
            'total_amount' => (float) $order->total_amount,
            'items_count'  => $order->items->count(),
            'customer'     => $order->user ? [
                'id'    => $order->user->id,
                'name'  => $order->user->name,
                'email' => $order->user->email,
            ] : null,
            'payment'      => $order->payment ? [
                'method' => $order->payment->method,
                'status' => $order->payment->status,
                'paid_at'=> $order->payment->paid_at?->toIso8601String(),
            ] : null,
            'shipment'     => $order->shipment ? [
                'status'        => $order->shipment->status,
                'tracking_code' => $order->shipment->tracking_code,
            ] : null,
            'created_at'   => $order->created_at?->toIso8601String(),
        ];
    }

    private function formatOrderDetail(Order $order): array
    {
        return array_merge($this->formatOrderSummary($order), [
            'shipping_info' => [
                'full_name'      => $order->shipping_full_name,
                'phone'          => $order->shipping_phone,
                'address_detail' => $order->shipping_address_detail,
                'province'       => $order->shippingProvince?->name,
                'district'       => $order->shippingDistrict?->name,
            ],
            'items' => $order->items->map(fn ($item) => [
                'id'              => $item->id,
                'price'           => (float) $item->price,
                'quantity'        => $item->quantity,
                'is_wholesale'    => (bool) ($item->is_wholesale ?? false),
                'subtotal'        => (float) $item->price * $item->quantity,
                'variant'         => $item->productVariant ? [
                    'id'    => $item->productVariant->id,
                    'sku'   => $item->productVariant->sku,
                    'color' => $item->productVariant->color,
                    'size'  => $item->productVariant->size,
                ] : null,
                'product'         => $item->productVariant?->product ? [
                    'id'        => $item->productVariant->product->id,
                    'name'      => $item->productVariant->product->name,
                    'main_image'=> $item->productVariant->product->images
                        ->where('is_main', true)->first()?->image_url
                        ? asset('storage/' . $item->productVariant->product->images->where('is_main', true)->first()->image_url)
                        : null,
                ] : null,
            ])->values(),
        ]);
    }
}
