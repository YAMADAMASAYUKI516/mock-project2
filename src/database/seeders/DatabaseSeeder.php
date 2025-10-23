<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $users = User::factory(10)->create();

        foreach ($users as $user) {
            for ($i = 0; $i < 90; $i++) {
                $date = Carbon::today()->subDays($i);

                if ($date->isWeekend()) continue;

                Attendance::factory()->create([
                    'user_id' => $user->id,
                    'work_date' => $date->format('Y-m-d'),
                ]);
            }
        }
    }
}
