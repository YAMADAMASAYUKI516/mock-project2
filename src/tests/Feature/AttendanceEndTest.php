<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceEndTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 退勤ボタンが正しく機能する
     * 勤務中 → 退勤済
     */
    public function test_user_can_end_work()
    {
        Carbon::setTestNow('2025-11-20 18:00:00');

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
        $response->assertSee('退勤');

        $this->post('/attendance/end');

        $response = $this->get('/attendance/index');
        $response->assertSee('退勤済');
    }

    /**
     * 退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_end_time_is_recorded_in_list_page()
    {
        Carbon::setTestNow('2025-11-20 09:00:00');
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/start');

        Carbon::setTestNow('2025-11-20 18:00:00');
        $this->post('/attendance/end');

        $response = $this->get('/attendance/list');

        $response->assertSee('18:00');
    }
}
