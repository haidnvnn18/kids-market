<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\ProductVariant;
use App\Models\ShippingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Get user orders list
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $user = $request->attributes->get('auth_user');

            $query = Order::where('user_id', $user->id)
                ->with([
                    'items.productVariant.product.images',
                    'payment',
                    'shipment',
                    'supplier',
                ])
                ->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            if ($request->filled('order_id')) {
                $query->where('id', 'like', '%' . $request->order_id . '%');
            }

            if ($request->filled('keyword')) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('id', 'like', '%' . $keyword . '%')
                        ->orWhereHas('items.productVariant.product', function ($productQuery) use ($keyword) {
                            $productQuery->where('name', 'like', '%' . $keyword . '%');
                        });
                });
            }

            $perPage = (int) $request->query('per_page', 10);
            $perPage = max(1, min($perPage, 50));

            $orders = $query->paginate($perPage)->appends($request->query());

            return response()->json([
                'success' => true,
                'data' => $orders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách đơn hàng',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order details by id
     */
    public function show(\Illuminate\Http\Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->attributes->get('auth_user');

            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->with([
                    'items.productVariant.product.images',
                    'payment',
                    'shipment',
                    'shippingAddress.province',
                    'shippingAddress.district',
                    'shippingProvince',
                    'shippingDistrict',
                    'supplier',
                ])
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng không tồn tại',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $order,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin đơn hàng',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel order by id
     */
    public function cancel(\Illuminate\Http\Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->attributes->get('auth_user');

            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng không tồn tại',
                ], 404);
            }

            if (!in_array($order->status, ['pending', 'paid'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể hủy đơn ở trạng thái Chờ xử lý hoặc Đã thanh toán',
                ], 422);
            }

            $order->update([
                'status' => 'cancelled',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hủy đơn hàng thành công',
                'data' => $order,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi hủy đơn hàng',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new order from items
     */
    public function store(OrderRequest $request): JsonResponse
    {
        try {
            $user = $request->attributes->get('auth_user');
            $shippingAddressId = $request->shipping_address_id;
            $items = $request->items;

            return DB::transaction(function () use ($user, $shippingAddressId, $items) {
                // Get shipping address
                $shippingAddress = ShippingAddress::findOrFail($shippingAddressId);

                // Verify shipping address belongs to user
                if ($shippingAddress->user_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Địa chỉ giao hàng không hợp lệ',
                    ], 403);
                }

                // Collect cart_item_ids to delete later
                $cartItemIdsToDelete = [];

                // Validate items and get total amount
                $totalAmount = 0;
                $supplier_id = null;
                $validatedItems = [];

                foreach ($items as $item) {
                    $variant = ProductVariant::with('product')->findOrFail($item['product_variant_id']);
                    
                    // Check stock
                    if ($variant->stock < $item['quantity']) {
                        throw new \Exception("Sản phẩm {$variant->product->name} ({$variant->sku}) chỉ còn {$variant->stock} cái");
                    }

                    // Validate cart_item_id if provided
                    if (!empty($item['cart_item_id'])) {
                        $cartItem = CartItem::with('cart')->findOrFail($item['cart_item_id']);
                        
                        // Verify cart_item belongs to user
                        if ($cartItem->cart->user_id !== $user->id) {
                            throw new \Exception('Mục giỏ hàng không hợp lệ');
                        }

                        $cartItemIdsToDelete[] = $item['cart_item_id'];
                    }

                    // Set supplier_id from first product
                    if ($supplier_id === null) {
                        $supplier_id = $variant->product->supplier_id;
                    }

                    $totalAmount += $variant->price * $item['quantity'];
                    $validatedItems[] = [
                        'variant' => $variant,
                        'quantity' => $item['quantity'],
                    ];
                }

                // Create order with shipping snapshot
                $order = Order::create([
                    'user_id' => $user->id,
                    'supplier_id' => $supplier_id,
                    'shipping_address_id' => $shippingAddress->id,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    // Shipping snapshot
                    'shipping_full_name' => $shippingAddress->full_name,
                    'shipping_phone' => $shippingAddress->phone,
                    'shipping_address_detail' => $shippingAddress->address_detail,
                    'shipping_province_id' => $shippingAddress->province_id,
                    'shipping_district_id' => $shippingAddress->district_id,
                ]);

                // Create order items and update stock
                foreach ($validatedItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_variant_id' => $item['variant']->id,
                        'price' => $item['variant']->price,
                        'quantity' => $item['quantity'],
                    ]);

                    // Update product variant stock
                    $item['variant']->decrement('stock', $item['quantity']);
                }

                // Delete cart items if any
                if (!empty($cartItemIdsToDelete)) {
                    CartItem::whereIn('id', $cartItemIdsToDelete)->delete();
                }

                // Create payment record
                Payment::create([
                    'order_id' => $order->id,
                    'method' => 'pending',
                    'amount' => $order->total_amount,
                    'status' => 'pending',
                ]);

                // Create shipment record
                Shipment::create([
                    'order_id' => $order->id,
                    'status' => 'pending',
                ]);

                // Load relations for response
                $order->load([
                    'items.productVariant.product',
                    'shippingAddress.province',
                    'shippingAddress.district',
                    'shippingProvince',
                    'shippingDistrict',
                    'payment',
                    'shipment',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Tạo đơn hàng thành công',
                    'data' => $order,
                ], 201);
            });

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm hoặc địa chỉ giao hàng không tồn tại',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo đơn hàng',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
