<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Get user's cart information
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $user = $request->attributes->get('auth_user');

            $cart = Cart::where('user_id', $user->id)
                ->with(['items.productVariant.product.images'])
                ->first();

            if (!$cart) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'cart' => null,
                        'items' => [],
                        'total_items' => 0,
                        'total_amount' => 0,
                    ],
                ], 200);
            }

            // Calculate totals
            $totalItems = $cart->items->sum('quantity');
            $totalAmount = $cart->items->sum(function ($item) {
                return $item->quantity * $item->productVariant->price;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'cart_id' => $cart->id,
                    'items' => $cart->items,
                    'total_items' => $totalItems,
                    'total_amount' => $totalAmount,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin giỏ hàng',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save buyer cart items
     */
    public function store(CartRequest $request): JsonResponse
    {
        try {
            $user = $request->attributes->get('auth_user');
            $items = $request->items;

            $cart = DB::transaction(function () use ($user, $items) {
                $cart = Cart::firstOrCreate([
                    'user_id' => $user->id,
                ]);

                // Reset cart items
                CartItem::where('cart_id', $cart->id)->delete();

                foreach ($items as $item) {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_variant_id' => $item['product_variant_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }

                return $cart->load(['items.productVariant']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Lưu giỏ hàng thành công',
                'data' => $cart,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu giỏ hàng',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
