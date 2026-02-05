<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Áo Thun Tay Ngắn Trơn Màu
        $product1 = Product::where('name', 'Áo Thun Tay Ngắn Trơn Màu')->first();
        ProductVariant::create([
            'product_id' => $product1->id,
            'sku' => 'AOTHUN-001-RED-S',
            'color' => 'Đỏ',
            'size' => 'S',
            'price' => 35000,
            'stock' => 50,
        ]);
        ProductVariant::create([
            'product_id' => $product1->id,
            'sku' => 'AOTHUN-001-RED-M',
            'color' => 'Đỏ',
            'size' => 'M',
            'price' => 35000,
            'stock' => 45,
        ]);
        ProductVariant::create([
            'product_id' => $product1->id,
            'sku' => 'AOTHUN-001-BLUE-S',
            'color' => 'Xanh',
            'size' => 'S',
            'price' => 35000,
            'stock' => 60,
        ]);
        ProductVariant::create([
            'product_id' => $product1->id,
            'sku' => 'AOTHUN-001-BLUE-M',
            'color' => 'Xanh',
            'size' => 'M',
            'price' => 35000,
            'stock' => 55,
        ]);

        // Áo Thun In Hình Hoạt Hình
        $product2 = Product::where('name', 'Áo Thun In Hình Hoạt Hình')->first();
        ProductVariant::create([
            'product_id' => $product2->id,
            'sku' => 'AOTHUN-002-MICKEY-S',
            'color' => 'Trắng',
            'size' => 'S',
            'price' => 50000,
            'stock' => 30,
        ]);
        ProductVariant::create([
            'product_id' => $product2->id,
            'sku' => 'AOTHUN-002-MICKEY-M',
            'color' => 'Trắng',
            'size' => 'M',
            'price' => 50000,
            'stock' => 35,
        ]);

        // Quần Shorts Kaki
        $product3 = Product::where('name', 'Quần Shorts Kaki Trẻ Em')->first();
        ProductVariant::create([
            'product_id' => $product3->id,
            'sku' => 'SHORTS-001-BEIGE-S',
            'color' => 'Be',
            'size' => 'S',
            'price' => 65000,
            'stock' => 25,
        ]);
        ProductVariant::create([
            'product_id' => $product3->id,
            'sku' => 'SHORTS-001-BEIGE-M',
            'color' => 'Be',
            'size' => 'M',
            'price' => 65000,
            'stock' => 20,
        ]);
        ProductVariant::create([
            'product_id' => $product3->id,
            'sku' => 'SHORTS-001-BLACK-S',
            'color' => 'Đen',
            'size' => 'S',
            'price' => 65000,
            'stock' => 28,
        ]);

        // Ô Tô Điều Khiển
        $product4 = Product::where('name', 'Ô Tô Điều Khiển Từ Xa')->first();
        ProductVariant::create([
            'product_id' => $product4->id,
            'sku' => 'CAR-001-RED',
            'color' => 'Đỏ',
            'size' => null,
            'price' => 180000,
            'stock' => 15,
        ]);
        ProductVariant::create([
            'product_id' => $product4->id,
            'sku' => 'CAR-001-BLUE',
            'color' => 'Xanh',
            'size' => null,
            'price' => 180000,
            'stock' => 12,
        ]);

        // Xe Tải Kéo
        $product5 = Product::where('name', 'Xe Tải Kéo Trẻ Em')->first();
        ProductVariant::create([
            'product_id' => $product5->id,
            'sku' => 'TRUCK-001-YELLOW',
            'color' => 'Vàng',
            'size' => null,
            'price' => 80000,
            'stock' => 40,
        ]);

        // Búp Bê Barbie
        $product6 = Product::where('name', 'Búp Bê Barbie Xinh Đẹp')->first();
        ProductVariant::create([
            'product_id' => $product6->id,
            'sku' => 'BARBIE-001',
            'color' => null,
            'size' => null,
            'price' => 250000,
            'stock' => 10,
        ]);

        // Lego
        $product7 = Product::where('name', 'Bộ Xếp Hình Lego 100+ Mảnh')->first();
        ProductVariant::create([
            'product_id' => $product7->id,
            'sku' => 'LEGO-001-COLOR',
            'color' => null,
            'size' => null,
            'price' => 350000,
            'stock' => 8,
        ]);

        // Giày Thể Thao
        $product8 = Product::where('name', 'Giày Thể Thao Sneaker Trẻ Em')->first();
        ProductVariant::create([
            'product_id' => $product8->id,
            'sku' => 'SHOE-001-BLACK-30',
            'color' => 'Đen',
            'size' => '30',
            'price' => 250000,
            'stock' => 18,
        ]);
        ProductVariant::create([
            'product_id' => $product8->id,
            'sku' => 'SHOE-001-BLACK-31',
            'color' => 'Đen',
            'size' => '31',
            'price' => 250000,
            'stock' => 20,
        ]);
        ProductVariant::create([
            'product_id' => $product8->id,
            'sku' => 'SHOE-001-WHITE-30',
            'color' => 'Trắng',
            'size' => '30',
            'price' => 250000,
            'stock' => 15,
        ]);

        // Mũ Lưỡi Trai
        $product9 = Product::where('name', 'Mũ Lưỡi Trai Trẻ Em')->first();
        ProductVariant::create([
            'product_id' => $product9->id,
            'sku' => 'HAT-001-BLUE',
            'color' => 'Xanh',
            'size' => null,
            'price' => 50000,
            'stock' => 100,
        ]);
        ProductVariant::create([
            'product_id' => $product9->id,
            'sku' => 'HAT-001-PINK',
            'color' => 'Hồng',
            'size' => null,
            'price' => 50000,
            'stock' => 90,
        ]);
    }
}
