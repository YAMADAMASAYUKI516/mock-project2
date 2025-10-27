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
        $this->call([
            AdminSeeder::class,
            UserSeeder::class,
        ]);

        $users = \App\Models\User::all();

        foreach ($users as $user) {
            for ($i = 1; $i < 91; $i++) {
                $date = \Carbon\Carbon::today()->subDays($i);
                if ($date->isWeekend()) continue;

                \App\Models\Attendance::factory()->create([
                    'user_id' => $user->id,
                    'work_date' => $date->format('Y-m-d'),
                ]);
            }
        }
    }
}
