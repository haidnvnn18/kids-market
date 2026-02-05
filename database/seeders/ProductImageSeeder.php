<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            // Tạo ảnh chính
            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => 'https://via.placeholder.com/400x400?text=' . urlencode($product->name) . '+1',
                'is_main' => true,
            ]);

            // Tạo ảnh phụ
            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => 'https://via.placeholder.com/400x400?text=' . urlencode($product->name) . '+2',
                'is_main' => false,
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => 'https://via.placeholder.com/400x400?text=' . urlencode($product->name) . '+3',
                'is_main' => false,
            ]);
        }
    }
}
