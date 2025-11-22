<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Request as AttendanceRequestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminRequestFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        return $admin;
    }

    /** @test 承認待ちの申請一覧が表示される */
    public function pending_requests_are_displayed()
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();

        // Attendance は start_time NOT NULL 対策のため必須
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        AttendanceRequestModel::factory()->pending()->create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'note'          => '修正1',
            'target_date'   => $attendance->work_date,
        ]);

        $response = $this->get('/admin/request/list?tab=pending');

        $response->assertStatus(200);
        $response->assertSee('修正1');
    }

    /** @test 承認済み申請一覧が表示される */
    public function approved_requests_are_displayed()
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        AttendanceRequestModel::factory()->approved()->create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'note'          => '承認済み申請',
            'target_date'   => $attendance->work_date,
        ]);

        $response = $this->get('/admin/request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
    }

    /** @test 修正申請の詳細内容が正しく表示される */
    public function request_detail_is_displayed_correctly()
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'    => $user->id,
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $request = AttendanceRequestModel::factory()->pending()->create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'start_time'    => '10:00:00',
            'end_time'      => '19:00:00',
            'note'          => 'テスト申請',
            'target_date'   => $attendance->work_date,
        ]);

        $response = $this->get("/admin/request/approve/{$request->id}");

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('テスト申請');
    }

    /** @test 修正申請の承認処理が正しく行われる */
    public function request_approval_updates_attendance()
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'    => $user->id,
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
        ]);

        $request = AttendanceRequestModel::factory()->pending()->create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'start_time'    => '10:00:00',
            'end_time'      => '19:00:00',
            'note'          => '修正依頼',
            'target_date'   => $attendance->work_date,
        ]);

        $this->post("/admin/request/approve/{$request->id}");

        $attendance->refresh();
        $request->refresh();

        $this->assertEquals('10:00:00', $attendance->start_time->format('H:i:s'));
        $this->assertEquals('19:00:00', $attendance->end_time->format('H:i:s'));

        $this->assertEquals('approved', $request->status);
    }
}
