<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supplier1 = Supplier::first();
        $supplier2 = Supplier::skip(1)->first();

        // Áo Thun Trẻ Em
        $tShirtCat = Category::where('slug', 'ao-thun-tre-em')->first();
        Product::create([
            'supplier_id' => $supplier1->id,
            'category_id' => $tShirtCat->id,
            'name' => 'Áo Thun Tay Ngắn Trơn Màu',
            'description' => 'Áo thun tay ngắn chất liệu cotton 100%, thoáng mát, phù hợp với thời tiết Việt Nam. Có nhiều màu sắc bắt mắt cho bé.',
            'wholesale_price' => 25000,
            'min_quantity' => 10,
            'status' => 'active',
        ]);

        Product::create([
            'supplier_id' => $supplier1->id,
            'category_id' => $tShirtCat->id,
            'name' => 'Áo Thun In Hình Hoạt Hình',
            'description' => 'Áo thun in hình nhân vật hoạt hình yêu thích của bé. Chất lượng in sắc nét, không bay màu sau giặt.',
            'wholesale_price' => 35000,
            'min_quantity' => 5,
            'status' => 'active',
        ]);

        // Quần Shorts
        $shortsCat = Category::where('slug', 'quan-shorts-tre-em')->first();
        Product::create([
            'supplier_id' => $supplier1->id,
            'category_id' => $shortsCat->id,
            'name' => 'Quần Shorts Kaki Trẻ Em',
            'description' => 'Quần shorts kaki chất lượng cao, co giãn tốt, có túi hộp tiện lợi. Độ bền cao, không xù lông.',
            'wholesale_price' => 45000,
            'min_quantity' => 8,
            'status' => 'active',
        ]);

        // Xe Đồ Chơi
        $carCat = Category::where('slug', 'xe-do-choi')->first();
        Product::create([
            'supplier_id' => $supplier2->id,
            'category_id' => $carCat->id,
            'name' => 'Ô Tô Điều Khiển Từ Xa',
            'description' => 'Xe ô tô điều khiển từ xa chạy bằng pin, tốc độ nhanh, khả năng cơ động cao. An toàn cho bé từ 4 tuổi trở lên.',
            'wholesale_price' => 120000,
            'min_quantity' => 3,
            'status' => 'active',
        ]);

        Product::create([
            'supplier_id' => $supplier2->id,
            'category_id' => $carCat->id,
            'name' => 'Xe Tải Kéo Trẻ Em',
            'description' => 'Xe tải kéo chất liệu nhựa an toàn, có bánh xe xoay trơn, phù hợp cho bé từ 1 tuổi.',
            'wholesale_price' => 55000,
            'min_quantity' => 5,
            'status' => 'active',
        ]);

        // Búp Bê
        $dollCat = Category::where('slug', 'bup-be')->first();
        Product::create([
            'supplier_id' => $supplier2->id,
            'category_id' => $dollCat->id,
            'name' => 'Búp Bê Barbie Xinh Đẹp',
            'description' => 'Búp bê Barbie kinh điển với trang phục xinh đẹp, tóc dài vàng. Phụ kiện đi kèm đủ đầy.',
            'wholesale_price' => 180000,
            'min_quantity' => 2,
            'status' => 'active',
        ]);

        // Lego
        $legoCat = Category::where('slug', 'xep-hinh-lego')->first();
        Product::create([
            'supplier_id' => $supplier2->id,
            'category_id' => $legoCat->id,
            'name' => 'Bộ Xếp Hình Lego 100+ Mảnh',
            'description' => 'Bộ xếp hình Lego với 100+ mảnh, giúp bé phát triển sự sáng tạo và tư duy không gian.',
            'wholesale_price' => 250000,
            'min_quantity' => 1,
            'status' => 'active',
        ]);

        // Giày Thể Thao
        $sportShoesCat = Category::where('slug', 'giay-the-thao')->first();
        Product::create([
            'supplier_id' => $supplier1->id,
            'category_id' => $sportShoesCat->id,
            'name' => 'Giày Thể Thao Sneaker Trẻ Em',
            'description' => 'Giày sneaker nhẹ, thoáng khí, có đệm êm chân. Thiết kế hiện đại phù hợp với bé trai và bé gái.',
            'wholesale_price' => 180000,
            'min_quantity' => 4,
            'status' => 'active',
        ]);

        // Mũ Nón
        $hatCat = Category::where('slug', 'mu-non-tre-em')->first();
        Product::create([
            'supplier_id' => $supplier1->id,
            'category_id' => $hatCat->id,
            'name' => 'Mũ Lưỡi Trai Trẻ Em',
            'description' => 'Mũ lưỡi trai vải cotton mềm mại, có nắp có thể điều chỉnh kích thước, bảo vệ da bé khỏi nắng.',
            'wholesale_price' => 35000,
            'min_quantity' => 10,
            'status' => 'active',
        ]);
    }
}
