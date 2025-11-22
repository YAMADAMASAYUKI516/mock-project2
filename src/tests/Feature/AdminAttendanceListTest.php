<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者でログイン
     */
    private function actingAsAdmin()
    {
        $admin = \App\Models\Admin::factory()->create();

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * 全ユーザーの勤怠が表示される
     */
    public function test_admin_sees_all_users_attendance_for_the_day()
    {
        $this->actingAsAdmin();

        $date = '2025-11-20';

        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            Attendance::factory()->create([
                'user_id'    => $user->id,
                'work_date'  => $date,
                'start_time' => '09:00',
                'end_time'   => '17:00',
            ]);
        }

        $response = $this->get('/admin/attendance/list?date=' . $date);

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee('09:00');
            $response->assertSee('17:00');
        }
    }

    /**
     * 遷移時に当日が表示される
     */
    public function test_admin_attendance_list_shows_today_date()
    {
        $this->actingAsAdmin();

        $today = now()->format('Y-m-d');

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response->assertSee(now()->format('Y/m/d'));
    }

    /**
     * 前日ボタン
     */
    public function test_prev_day_button_loads_previous_date_records()
    {
        $this->actingAsAdmin();

        $today = '2025-11-20';
        $prevDay = '2025-11-19';

        Attendance::factory()->create([
            'user_id'    => User::factory()->create()->id,
            'work_date'  => $prevDay,
            'start_time' => '10:00',
            'end_time'   => '18:00',
        ]);

        $response = $this->get('/admin/attendance/list?date=' . $prevDay);

        $response->assertStatus(200);

        $response->assertSee('2025/11/19');

        $response->assertSee('10:00');
        $response->assertSee('18:00');
    }


    /**
     * 翌日ボタン
     */
    public function test_next_day_button_loads_next_date_records()
    {
        $this->actingAsAdmin();

        $nextDay = '2025-11-21';

        Attendance::factory()->create([
            'user_id'    => User::factory()->create()->id,
            'work_date'  => $nextDay,
            'start_time' => '08:30',
            'end_time'   => '17:45',
        ]);

        $response = $this->get('/admin/attendance/list?date=' . $nextDay);

        $response->assertStatus(200);

        $response->assertSee('2025/11/21');

        $response->assertSee('08:30');
        $response->assertSee('17:45');
    }
}
