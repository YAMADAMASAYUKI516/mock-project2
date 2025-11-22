<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤務外 → ステータス「勤務外」
     */
    public function test_status_is_outside_when_no_attendance_record()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance/index');

        $response->assertSee('勤務外');
    }

    /**
     * 出勤中 → ステータス「出勤中」
     */
    public function test_status_is_working_when_started_and_not_ended()
    {
        $user = User::factory()->create();

        Attendance::factory()
            ->testState()
            ->working()
            ->create([
                'user_id'   => $user->id,
                'work_date' => Carbon::today()->toDateString(),
            ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/index');

        $response->assertSee('出勤中');
    }

    /**
     * 休憩中 → ステータス「休憩中」
     */
    public function test_status_is_on_break_when_break_started_and_not_ended()
    {
        Carbon::setTestNow('2025-11-21 12:00:00');

        $user = User::factory()->create();

        Attendance::factory()
            ->testState()
            ->break1()
            ->create([
                'user_id'   => $user->id,
                'work_date' => Carbon::today()->toDateString(),
                'start_time' => '09:00:00',
            ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/index');

        $response->assertSee('休憩中');
    }

    /**
     * 退勤済 → ステータス「退勤済」
     */
    public function test_status_is_finished_when_end_time_exists()
    {
        $user = User::factory()->create();

        Attendance::factory()
            ->testState()
            ->finished()
            ->create([
                'user_id'   => $user->id,
                'work_date' => Carbon::today()->toDateString(),
                'start_time'=> '09:00:00',
            ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/index');

        $response->assertSee('退勤済');
    }
}
