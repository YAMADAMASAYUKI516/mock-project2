<?php

namespace Database\Factories;

use App\Models\Request as AttendanceRequestModel;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class RequestFactory extends Factory
{
    protected $model = AttendanceRequestModel::class;

    public function definition()
    {
        $user       = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        return [
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,

            'start_time'    => '09:00:00',
            'end_time'      => '18:00:00',
            'break1_start'  => '12:00:00',
            'break1_end'    => '13:00:00',
            'break2_start'  => null,
            'break2_end'    => null,

            'requested_date' => Carbon::today()->toDateString(),
            'target_date'    => $attendance->work_date ?? Carbon::today()->toDateString(),

            'status'        => 'pending',
            'note'          => $this->faker->sentence(),

            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }

    public function approved()
    {
        return $this->state(fn() => ['status' => 'approved']);
    }

    public function pending()
    {
        return $this->state(fn() => ['status' => 'pending']);
    }
}
