<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1回目の休憩開始 → 「休憩中」
     */
    public function test_first_break_start()
    {
        Carbon::setTestNow('2025-11-20 10:00:00');

        $user = User::factory()->create();

        $attendance = Attendance::factory()
            ->testState()
            ->working()
            ->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $this->post('/attendance/break/start');

        $response = $this->get('/attendance/index');
        $response->assertSee('休憩中');
    }

    /**
     * 1回目の休憩終了 → 「出勤中」
     */
    public function test_first_break_end()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()
            ->testState()
            ->break1()
            ->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Carbon::setTestNow('2025-11-20 10:15:00');

        $this->post('/attendance/break/end');

        $response = $this->get('/attendance/index');
        $response->assertSee('出勤中');
    }

    /**
     * 2回目の休憩開始 → 「休憩中」
     */
    public function test_second_break_start()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()
            ->testState()
            ->state([
                'start_time' => '10:00:00',
                'break1_start' => '10:30:00',
                'break1_end'   => '10:45:00',
            ])
            ->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Carbon::setTestNow('2025-11-20 11:00:00');

        $this->post('/attendance/break/start');

        $response = $this->get('/attendance/index');
        $response->assertSee('休憩中');
    }

    /**
     * 2回目の休憩終了 → 「出勤中」
     */
    public function test_second_break_end()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()
            ->testState()
            ->break2()
            ->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Carbon::setTestNow('2025-11-20 11:15:00');

        $this->post('/attendance/break/end');

        $response = $this->get('/attendance/index');
        $response->assertSee('出勤中');
    }

    /**
     * 3回目の休憩はできない → 「出勤中」のまま
     */
    public function test_third_break_is_not_allowed()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()
            ->testState()
            ->state([
                'start_time'   => '10:00:00',
                'break1_start' => '10:30:00',
                'break1_end'   => '10:45:00',
                'break2_start' => '11:00:00',
                'break2_end'   => '11:15:00',
            ])
            ->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Carbon::setTestNow('2025-11-20 12:00:00');

        $this->post('/attendance/break/start');

        $response = $this->get('/attendance/index');
        $response->assertSee('出勤中');
    }
}
