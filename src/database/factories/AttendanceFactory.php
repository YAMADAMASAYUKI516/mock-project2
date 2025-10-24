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

        $break1Start = (clone $start)->modify('+3 hours');
        $break1End = (clone $break1Start)->modify('+1 hour');

        $break2Start = (clone $break1End)->modify('+2 hours');
        $break2End = (clone $break2Start)->modify('+30 minutes');

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

            'break1_start' => $break1Start->format('H:i:s'),
            'break1_end' => $break1End->format('H:i:s'),
            'break2_start' => $break2Start->format('H:i:s'),
            'break2_end' => $break2End->format('H:i:s'),

            'total_work_time' => 8.00,
            'note' => $this->faker->randomElement($notes),
        ];
    }
}
