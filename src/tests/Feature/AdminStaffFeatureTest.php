<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminStaffFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        return $admin;
    }

    /**
     * スタッフ一覧に全ユーザーの「氏名」「メールアドレス」が表示される
     */
    public function test_admin_can_see_all_users_info()
    {
        $this->actingAsAdmin();

        $users = User::factory()->count(3)->create();

        $response = $this->get('/admin/staff/list');

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    /**
     * 選択したユーザーの勤怠一覧が正しく表示される
     */
    public function test_admin_can_see_user_attendance_list()
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => '2025-11-10',
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'break1_start' => '12:00',
            'break1_end'   => '13:00',
        ]);

        $response = $this->get("/admin/attendance/staff/{$user->id}");

        $response->assertSee('11/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }

    /**
     * 前月ボタンで前月の勤怠一覧が表示される
     */
    public function test_admin_prev_month_button_loads_previous_month()
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => '2025-10-05',
            'start_time' => '09:00',
            'end_time'   => '18:00',
        ]);

        $response = $this->get("/admin/attendance/staff/{$user->id}?month=2025-11");

        $response = $this->get("/admin/attendance/staff/{$user->id}?month=2025-10");

        $response->assertSee('10/05');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 翌月ボタンで翌月の勤怠一覧が表示される
     */
    public function test_admin_next_month_button_loads_next_month()
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => '2025-12-01',
            'start_time' => '09:00',
            'end_time'   => '18:00',
        ]);

        $response = $this->get("/admin/attendance/staff/{$user->id}?month=2025-11");

        $response = $this->get("/admin/attendance/staff/{$user->id}?month=2025-12");

        $response->assertSee('12/01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 「詳細」ボタンで勤怠詳細画面に遷移する
     */
    public function test_admin_can_go_to_attendance_detail()
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => '2025-11-10',
        ]);

        $response = $this->get("/admin/attendance/staff/{$user->id}?month=2025-11");

        $response->assertSee("/admin/attendance/detail/{$attendance->id}");
    }
}
