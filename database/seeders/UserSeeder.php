<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Nguyễn Văn A',
            'username' => 'nguyenvana',
            'email' => 'vana@gmail.com',
            'phone' => '0901234567',
            'password' => bcrypt('password123'),
            'dob' => '1990-01-15',
            'role' => 'buyer',
            'login_provider' => 'local',
            'avatar' => null,
        ]);

        User::create([
            'name' => 'Trần Thị B',
            'username' => 'tranthib',
            'email' => 'thib@gmail.com',
            'phone' => '0902345678',
            'password' => bcrypt('password123'),
            'dob' => '1992-03-20',
            'role' => 'buyer',
            'login_provider' => 'local',
            'avatar' => null,
        ]);

        User::create([
            'name' => 'Phạm Văn C - Shop',
            'username' => 'phamvanc_shop',
            'email' => 'phamvanc@shop.com',
            'phone' => '0903456789',
            'password' => bcrypt('password123'),
            'dob' => '1988-05-10',
            'role' => 'seller',
            'login_provider' => 'local',
            'avatar' => null,
        ]);

        User::create([
            'name' => 'Lê Thị D - Shop',
            'username' => 'lethid_shop',
            'email' => 'lethid@shop.com',
            'phone' => '0904567890',
            'password' => bcrypt('password123'),
            'dob' => '1995-07-25',
            'role' => 'seller',
            'login_provider' => 'local',
            'avatar' => null,
        ]);

        User::create([
            'name' => 'Quản Trị Viên',
            'username' => 'admin',
            'email' => 'admin@kidsmarket.com',
            'phone' => '0900000000',
            'password' => bcrypt('admin123'),
            'dob' => '1985-01-01',
            'role' => 'admin',
            'login_provider' => 'local',
            'avatar' => null,
        ]);
    }
}
