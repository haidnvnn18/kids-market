<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy seller users
        $seller1 = User::where('username', 'phamvanc_shop')->first();
        $seller2 = User::where('username', 'lethid_shop')->first();

        Supplier::create([
            'user_id' => $seller1->id,
            'brand_name' => 'Phạm Văn C Shop',
            'country' => 'VN',
            'description' => 'Chuyên cung cấp quần áo trẻ em chất lượng cao, giá tốt nhất thị trường',
            'rating_avg' => 4.85,
        ]);

        Supplier::create([
            'user_id' => $seller2->id,
            'brand_name' => 'Lê Thị D Shop',
            'country' => 'CN',
            'description' => 'Nhập khẩu trực tiếp đồ chơi và phụ kiện trẻ em từ Trung Quốc, giá sỉ rẻ',
            'rating_avg' => 4.72,
        ]);
    }
}
