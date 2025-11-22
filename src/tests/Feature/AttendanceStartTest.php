<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceStartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤ボタンが正しく機能し、勤務外 → 出勤中に変化する
     */
    public function test_user_can_start_work()
    {
        Carbon::setTestNow('2025-11-20 09:00:00');

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance/index');
        $response->assertSee('出勤');

        $this->post('/attendance/start');

        $response = $this->get('/attendance/index');
        $response->assertSee('出勤中');
    }

    /**
     * 出勤は1日1回のみ（退勤済ユーザーは出勤ボタンが非表示）
     */
    public function test_user_cannot_start_work_if_already_completed()
    {
        Carbon::setTestNow('2025-11-20 09:00:00');

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

        $response->assertDontSee('出勤');
    }

    /**
     * 出勤時刻が勤怠一覧画面で確認できる
     */
    public function test_start_time_is_recorded_in_list_page()
    {
        Carbon::setTestNow('2025-11-20 09:00:00');

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/start');

        $response = $this->get('/attendance/list');

        $response->assertSee('09:00');
    }
}
