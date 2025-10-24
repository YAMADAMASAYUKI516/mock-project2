<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => '宮阪 奈緒美',   'email' => 'naomi.m@coachtech.com'],
            ['name' => '齋藤 慶',     'email' => 'kei.s@coachtech.com'],
            ['name' => '井原 有沙',   'email' => 'arisa.i@coachtech.com'],
            ['name' => '熊谷 恵',     'email' => 'megumi.k@coachtech.com'],
            ['name' => '上野 哲郎',   'email' => 'tetsuro.u@coachtech.com'],
            ['name' => '五味 彩子',   'email' => 'saiko.g@coachtech.com'],
            ['name' => '井上 ゆかり', 'email' => 'yukari.i@coachtech.com'],
            ['name' => '清水 祥吾',   'email' => 'syogo.s@coachtech.com'],
            ['name' => '高橋 勉',     'email' => 'tsutomu.t@coachtech.com'],
            ['name' => '櫻井 新一郎', 'email' => 'sinichiro.s@coachtech.com'],
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }
    }
}
