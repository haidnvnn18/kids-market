<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Supplier;
use App\Models\ProductVariant;
use App\Models\ShippingAddress;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buyer1 = User::where('username', 'nguyenvana')->first();
        $buyer2 = User::where('username', 'tranthib')->first();
        $supplier1 = Supplier::first();
        $supplier2 = Supplier::skip(1)->first();

        // Get shipping addresses
        $shipping1 = ShippingAddress::where('user_id', $buyer1->id)->where('is_default', true)->first();
        $shipping2 = ShippingAddress::where('user_id', $buyer2->id)->where('is_default', true)->first();

        // Order 1: Buyer 1 -> Supplier 1
        $order1 = Order::create([
            'user_id' => $buyer1->id,
            'supplier_id' => $supplier1->id,
            'shipping_address_id' => $shipping1->id,
            'shipping_full_name' => $shipping1->full_name,
            'shipping_phone' => $shipping1->phone,
            'shipping_address_detail' => $shipping1->address_detail,
            'shipping_province_id' => $shipping1->province_id,
            'shipping_district_id' => $shipping1->district_id,
            'total_amount' => 140000,
            'status' => 'completed',
        ]);

        // OrderItems for Order 1
        $variant1 = ProductVariant::where('sku', 'AOTHUN-001-RED-S')->first();
        $variant2 = ProductVariant::where('sku', 'HAT-001-BLUE')->first();

        OrderItem::create([
            'order_id' => $order1->id,
            'product_variant_id' => $variant1->id,
            'price' => 35000,
            'quantity' => 2,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_variant_id' => $variant2->id,
            'price' => 50000,
            'quantity' => 1,
        ]);

        // Payment for Order 1
        Payment::create([
            'order_id' => $order1->id,
            'method' => 'transfer',
            'amount' => 140000,
            'status' => 'success',
            'paid_at' => now()->subDays(5),
        ]);

        // Shipment for Order 1
        Shipment::create([
            'order_id' => $order1->id,
            'tracking_code' => 'VN123456789ABC',
            'status' => 'delivered',
        ]);

        // Order 2: Buyer 2 -> Supplier 2
        $order2 = Order::create([
            'user_id' => $buyer2->id,
            'supplier_id' => $supplier2->id,
            'shipping_address_id' => $shipping2->id,
            'shipping_full_name' => $shipping2->full_name,
            'shipping_phone' => $shipping2->phone,
            'shipping_address_detail' => $shipping2->address_detail,
            'shipping_province_id' => $shipping2->province_id,
            'shipping_district_id' => $shipping2->district_id,
            'total_amount' => 530000,
            'status' => 'shipping',
        ]);

        // OrderItems for Order 2
        $variant3 = ProductVariant::where('sku', 'CAR-001-RED')->first();
        $variant4 = ProductVariant::where('sku', 'LEGO-001-COLOR')->first();

        OrderItem::create([
            'order_id' => $order2->id,
            'product_variant_id' => $variant3->id,
            'price' => 180000,
            'quantity' => 1,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_variant_id' => $variant4->id,
            'price' => 350000,
            'quantity' => 1,
        ]);

        // Payment for Order 2
        Payment::create([
            'order_id' => $order2->id,
            'method' => 'cod',
            'amount' => 530000,
            'status' => 'pending',
            'paid_at' => null,
        ]);

        // Shipment for Order 2
        Shipment::create([
            'order_id' => $order2->id,
            'tracking_code' => 'VN987654321XYZ',
            'status' => 'shipping',
        ]);

        // Order 3: Buyer 1 -> Supplier 2
        $order3 = Order::create([
            'user_id' => $buyer1->id,
            'supplier_id' => $supplier2->id,
            'shipping_address_id' => $shipping1->id,
            'shipping_full_name' => $shipping1->full_name,
            'shipping_phone' => $shipping1->phone,
            'shipping_address_detail' => $shipping1->address_detail,
            'shipping_province_id' => $shipping1->province_id,
            'shipping_district_id' => $shipping1->district_id,
            'total_amount' => 360000,
            'status' => 'paid',
        ]);

        // OrderItems for Order 3
        $variant5 = ProductVariant::where('sku', 'SHOE-001-BLACK-30')->first();
        $variant6 = ProductVariant::where('sku', 'BARBIE-001')->first();

        OrderItem::create([
            'order_id' => $order3->id,
            'product_variant_id' => $variant5->id,
            'price' => 250000,
            'quantity' => 1,
        ]);

        OrderItem::create([
            'order_id' => $order3->id,
            'product_variant_id' => $variant6->id,
            'price' => 250000,
            'quantity' => 1,
        ]);

        // Payment for Order 3
        Payment::create([
            'order_id' => $order3->id,
            'method' => 'transfer',
            'amount' => 360000,
            'status' => 'success',
            'paid_at' => now()->subDay(),
        ]);

        // Shipment for Order 3 (chưa giao)
        Shipment::create([
            'order_id' => $order3->id,
            'tracking_code' => null,
            'status' => 'pending',
        ]);

        // Order 4: Buyer 2 -> Supplier 1
        $order4 = Order::create([
            'user_id' => $buyer2->id,
            'supplier_id' => $supplier1->id,
            'shipping_address_id' => $shipping2->id,
            'shipping_full_name' => $shipping2->full_name,
            'shipping_phone' => $shipping2->phone,
            'shipping_address_detail' => $shipping2->address_detail,
            'shipping_province_id' => $shipping2->province_id,
            'shipping_district_id' => $shipping2->district_id,
            'total_amount' => 195000,
            'status' => 'pending',
        ]);

        // OrderItems for Order 4
        $variant7 = ProductVariant::where('sku', 'SHOE-001-WHITE-30')->first();
        $variant8 = ProductVariant::where('sku', 'SHORTS-001-BEIGE-S')->first();

        OrderItem::create([
            'order_id' => $order4->id,
            'product_variant_id' => $variant7->id,
            'price' => 250000,
            'quantity' => 1,
        ]);

        OrderItem::create([
            'order_id' => $order4->id,
            'product_variant_id' => $variant8->id,
            'price' => 65000,
            'quantity' => 2,
        ]);

        // Payment for Order 4
        Payment::create([
            'order_id' => $order4->id,
            'method' => 'cod',
            'amount' => 195000,
            'status' => 'pending',
            'paid_at' => null,
        ]);

        // Shipment for Order 4
        Shipment::create([
            'order_id' => $order4->id,
            'tracking_code' => null,
            'status' => 'pending',
        ]);
    }
}
