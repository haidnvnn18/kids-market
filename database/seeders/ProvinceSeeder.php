<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            'An Giang',
            'Bắc Ninh',
            'Cà Mau',
            'Cao Bằng',
            'Đắk Lắk',
            'Điện Biên',
            'Đồng Nai',
            'Đồng Tháp',
            'Gia Lai',
            'Hà Tĩnh',
            'Hưng Yên',
            'Khánh Hoà',
            'Lai Châu',
            'Lâm Đồng',
            'Lạng Sơn',
            'Lào Cai',
            'Nghệ An',
            'Ninh Bình',
            'Phú Thọ',
            'Quảng Ngãi',
            'Quảng Ninh',
            'Quảng Trị',
            'Sơn La',
            'Tây Ninh',
            'Thái Nguyên',
            'Thanh Hóa',
            'Thành phố Cần Thơ',
            'Thành phố Đà Nẵng',
            'Thành phố Hà Nội',
            'Thành phố Hải Phòng',
            'Thành phố Hồ Chí Minh',
            'Thành phố Huế',
            'Tuyên Quang',
            'Vĩnh Long',
        ];

        foreach ($provinces as $provinceName) {
            Province::firstOrCreate(['name' => $provinceName]);
        }
    }
}
