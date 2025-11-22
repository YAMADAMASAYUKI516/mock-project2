<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        $start = Carbon::createFromTime(rand(8, 9), $this->faker->randomElement([0, 15, 30, 45]));
        $end = (clone $start)->copy()->addHours(8)->addMinutes(rand(0, 30));

        $hasBreak1 = $this->faker->boolean(80);
        $hasBreak2 = $hasBreak1 ? $this->faker->boolean(20) : false;

        $break1Start = $break1End = $break2Start = $break2End = null;
        $totalBreakMinutes = 0;

        if ($hasBreak1) {
            $break1Start = (clone $start)->copy()->addHours(3);
            $break1End = (clone $break1Start)->copy()->addMinutes(60);
            $totalBreakMinutes += $break1End->diffInMinutes($break1Start);
        }

        if ($hasBreak2) {
            $break2Start = $break1End->copy()->addHours(2);
            $break2End = (clone $break2Start)->copy()->addMinutes(30);
            $totalBreakMinutes += $break2End->diffInMinutes($break2Start);
        }

        $totalWorkMinutes = $end->diffInMinutes($start) - $totalBreakMinutes;
        $totalWorkTime = round($totalWorkMinutes / 60, 2);

        $notes = [
            null,
            '電車遅延のため',
            '体調不良のため',
            '客先対応のため',
            '保育園送迎のため',
            '会議が延長',
        ];

        return [
            'user_id' => User::factory(),
            'work_date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),

            'break1_start' => $break1Start?->format('H:i:s'),
            'break1_end' => $break1End?->format('H:i:s'),
            'break2_start' => $break2Start?->format('H:i:s'),
            'break2_end' => $break2End?->format('H:i:s'),

            'total_work_time' => $totalWorkTime,
            'note' => $this->faker->randomElement($notes),
        ];
    }

    /**
     * ▼ テスト用の初期状態
     */
    public function testState(): static
    {
        return $this->state(function () {
            return [
                'work_date'      => '2025-11-20',
                'start_time'     => null,
                'end_time'       => null,
                'break1_start'   => null,
                'break1_end'     => null,
                'break2_start'   => null,
                'break2_end'     => null,
                'total_work_time' => null,
                'note'           => null,
            ];
        });
    }

    /**
     * ▼ テストで "出勤中" を生成する
     */
    public function working(): static
    {
        return $this->state(function () {
            return [
                'start_time' => '10:00:00',
                'end_time' => null,
            ];
        });
    }

    /**
     * ▼ テストで "休憩中（1回目）" を生成する
     */
    public function break1(): static
    {
        return $this->state(function () {
            return [
                'start_time'    => '10:00:00',
                'break1_start'  => '11:00:00',
                'break1_end'    => null,
            ];
        });
    }

    /**
     * ▼ テストで "休憩中（2回目）" を生成する
     */
    public function break2(): static
    {
        return $this->state(function () {
            return [
                'start_time'    => '10:00:00',
                'break1_start'  => '11:00:00',
                'break1_end'    => '11:30:00',
                'break2_start'  => '14:00:00',
                'break2_end'    => null,
            ];
        });
    }

    /**
     * ▼ テストで "退勤済" を生成する
     */
    public function finished(): static
    {
        return $this->state(function () {
            return [
                'start_time' => '10:00:00',
                'end_time'   => '18:00:00',
            ];
        });
    }
}
