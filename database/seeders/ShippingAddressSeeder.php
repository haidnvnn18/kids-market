<?php

namespace Database\Seeders;

use App\Models\ShippingAddress;
use App\Models\User;
use App\Models\Province;
use App\Models\District;
use Illuminate\Database\Seeder;

class ShippingAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buyer1 = User::where('username', 'nguyenvana')->first();
        $buyer2 = User::where('username', 'tranthib')->first();

        // Get provinces and districts
        $hanoiProvince = Province::where('name', 'Thành phố Hà Nội')->first();
        $hcmProvince = Province::where('name', 'Thành phố Hồ Chí Minh')->first();
        
        // Get first district for each province
        $hanoiDistrict = District::where('province_id', $hanoiProvince->id)->first();
        $hcmDistrict = District::where('province_id', $hcmProvince->id)->first();

        // Shipping address for buyer 1
        ShippingAddress::create([
            'user_id' => $buyer1->id,
            'province_id' => $hanoiProvince->id,
            'district_id' => $hanoiDistrict->id,
            'full_name' => 'Nguyễn Văn A',
            'phone' => '0901234567',
            'address_detail' => 'Số 123, Đường Láng',
            'is_default' => true,
        ]);

        // Additional address for buyer 1
        ShippingAddress::create([
            'user_id' => $buyer1->id,
            'province_id' => $hanoiProvince->id,
            'district_id' => $hanoiDistrict->id,
            'full_name' => 'Nguyễn Văn A',
            'phone' => '0901234567',
            'address_detail' => 'Số 456, Phố Huế',
            'is_default' => false,
        ]);

        // Shipping address for buyer 2
        ShippingAddress::create([
            'user_id' => $buyer2->id,
            'province_id' => $hcmProvince->id,
            'district_id' => $hcmDistrict->id,
            'full_name' => 'Trần Thị B',
            'phone' => '0902345678',
            'address_detail' => 'Số 789, Đường Nguyễn Huệ',
            'is_default' => true,
        ]);

        // Additional address for buyer 2
        ShippingAddress::create([
            'user_id' => $buyer2->id,
            'province_id' => $hcmProvince->id,
            'district_id' => $hcmDistrict->id,
            'full_name' => 'Trần Thị B',
            'phone' => '0902345678',
            'address_detail' => 'Số 321, Đường Lê Lợi',
            'is_default' => false,
        ]);
    }
}
