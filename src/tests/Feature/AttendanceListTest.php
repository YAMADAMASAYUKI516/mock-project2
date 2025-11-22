<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 自分の勤怠情報が全て表示されている
     */
    public function test_attendance_list_shows_all_my_records()
    {
        Carbon::setTestNow('2025-11-20');

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-01',
            'start_time' => '09:00:00',
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-10',
            'start_time' => '09:30:00',
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-15',
            'start_time' => '10:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('11/01');
        $response->assertSee('11/10');
        $response->assertSee('11/15');
    }

    /**
     * 現在の月が表示される
     */
    public function test_current_month_is_displayed()
    {
        Carbon::setTestNow('2025-11-20');

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertSee('2025/11');
    }

    /**
     * 前月ボタンで前月に遷移する
     */
    public function test_prev_month_button_shows_previous_month()
    {
        Carbon::setTestNow('2025-11-20');

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance/list?month=2025-10');

        $response->assertSee('2025/10');
    }

    /**
     * 翌月ボタンで翌月に遷移する
     */
    public function test_next_month_button_shows_next_month()
    {
        Carbon::setTestNow('2025-11-20');

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance/list?month=2025-12');

        $response->assertSee('2025/12');
    }

    /**
     * 「詳細」ボタンでその日の勤怠詳細に遷移
     */
    public function test_detail_button_navigates_to_detail()
    {
        Carbon::setTestNow('2025-11-20');

        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => '2025-11-10',
            'start_time'=> '09:00:00'
        ]);

        $response = $this->get('/attendance/list');
        $response->assertSee('/attendance/detail/' . $attendance->id);

        $response = $this->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }
}
