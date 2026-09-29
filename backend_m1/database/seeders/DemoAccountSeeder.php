<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAccountSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['admin@dt07.test','admin','Quản trị viên DT07'],
            ['host@dt07.test','host','Chủ homestay thử nghiệm'],
            ['guest@dt07.test','guest','Khách thử nghiệm'],
            ['seller@dt07.test','seller','Đối tác sản phẩm thử nghiệm'],
        ];
        foreach ($rows as [$email,$role,$name]) {
            User::updateOrCreate(['email'=>$email],[
                'password_hash'=>Hash::make('Dt07@2026!'),
                'role'=>$role,'full_name'=>$name,'status'=>'active'
            ]);
        }
    }
}
