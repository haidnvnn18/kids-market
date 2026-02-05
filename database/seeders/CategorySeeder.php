<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Main categories
        $clothing = Category::create([
            'parent_id' => null,
            'name' => 'Quần Áo Trẻ Em',
            'slug' => Str::slug('Quần Áo Trẻ Em'),
        ]);

        $toys = Category::create([
            'parent_id' => null,
            'name' => 'Đồ Chơi',
            'slug' => Str::slug('Đồ Chơi'),
        ]);

        $shoes = Category::create([
            'parent_id' => null,
            'name' => 'Giày Dép',
            'slug' => Str::slug('Giày Dép'),
        ]);

        $accessories = Category::create([
            'parent_id' => null,
            'name' => 'Phụ Kiện Trẻ Em',
            'slug' => Str::slug('Phụ Kiện Trẻ Em'),
        ]);

        // Subcategories for Clothing
        Category::create([
            'parent_id' => $clothing->id,
            'name' => 'Áo Thun Trẻ Em',
            'slug' => Str::slug('Áo Thun Trẻ Em'),
        ]);

        Category::create([
            'parent_id' => $clothing->id,
            'name' => 'Quần Shorts Trẻ Em',
            'slug' => Str::slug('Quần Shorts Trẻ Em'),
        ]);

        Category::create([
            'parent_id' => $clothing->id,
            'name' => 'Váy Trẻ Em',
            'slug' => Str::slug('Váy Trẻ Em'),
        ]);

        // Subcategories for Toys
        Category::create([
            'parent_id' => $toys->id,
            'name' => 'Xe Đồ Chơi',
            'slug' => Str::slug('Xe Đồ Chơi'),
        ]);

        Category::create([
            'parent_id' => $toys->id,
            'name' => 'Búp Bê',
            'slug' => Str::slug('Búp Bê'),
        ]);

        Category::create([
            'parent_id' => $toys->id,
            'name' => 'Xếp Hình Lego',
            'slug' => Str::slug('Xếp Hình Lego'),
        ]);

        // Subcategories for Shoes
        Category::create([
            'parent_id' => $shoes->id,
            'name' => 'Giày Thể Thao',
            'slug' => Str::slug('Giày Thể Thao'),
        ]);

        Category::create([
            'parent_id' => $shoes->id,
            'name' => 'Sandal Trẻ Em',
            'slug' => Str::slug('Sandal Trẻ Em'),
        ]);

        // Subcategories for Accessories
        Category::create([
            'parent_id' => $accessories->id,
            'name' => 'Mũ Nón Trẻ Em',
            'slug' => Str::slug('Mũ Nón Trẻ Em'),
        ]);

        Category::create([
            'parent_id' => $accessories->id,
            'name' => 'Tất Vớ Trẻ Em',
            'slug' => Str::slug('Tất Vớ Trẻ Em'),
        ]);
    }
}
