<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('09:00', '10:00');

        $end = (clone $start)->modify('+8 hours');

        $breakStart = (clone $start)->modify('+3 hours');
        $breakEnd = (clone $breakStart)->modify('+1 hour');

        $notes = [
            null,
            '電車遅延のため10分遅れ',
            '体調不良のため在宅勤務',
            '客先対応のため直行',
            '保育園送迎のため時差出勤',
            '会議が延長しました',
        ];

        return [
            'user_id' => User::factory(),
            'work_date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'break_start' => $breakStart->format('H:i:s'),
            'break_end' => $breakEnd->format('H:i:s'),
            'total_work_time' => 8.00,
            'note' => $this->faker->randomElement($notes),
        ];
    }
}
