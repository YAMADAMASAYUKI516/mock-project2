<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Request as AttendanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceDetailUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function createAttendance(User $user)
    {
        return Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => '2025-11-20',
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'break1_start' => '12:00',
            'break1_end'   => '13:00'
        ]);
    }

    /**
     * 出勤時間 > 退勤時間 → 「出勤時間が不適切な値です」
     */
    public function test_start_time_after_end_time_causes_error()
    {
        $user = User::factory()->create();
        $attendance = $this->createAttendance($user);

        $this->actingAs($user);

        $response = $this->post("/attendance/request/{$attendance->id}", [
            'start_time' => '19:00',
            'end_time'   => '18:00',
            'note'       => 'テスト'
        ]);

        $response->assertSessionHasErrors(['start_time' => '出勤時間が不適切な値です']);
    }

    /**
     * 休憩開始 > 退勤時間 → 「休憩時間が不適切な値です」
     */
    public function test_break_start_after_end_time_causes_error()
    {
        $user = User::factory()->create();
        $attendance = $this->createAttendance($user);

        $this->actingAs($user);

        $response = $this->post("/attendance/request/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'break1_start' => '19:00',
            'note'       => 'メモ'
        ]);

        $response->assertSessionHasErrors([
            'break1_start' => '休憩時間が不適切な値です'
        ]);
    }

    /**
     * 休憩終了 > 退勤時間 → 「休憩時間もしくは退勤時間が不適切な値です」
     */
    public function test_break_end_after_end_time_causes_error()
    {
        $user = User::factory()->create();
        $attendance = $this->createAttendance($user);

        $this->actingAs($user);

        $response = $this->post("/attendance/request/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'break1_end' => '20:00',
            'note'       => 'メモ'
        ]);

        $response->assertSessionHasErrors([
            'break1_end' => '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     * 備考未入力 → 「備考を記入してください」
     */
    public function test_note_required_error()
    {
        $user = User::factory()->create();
        $attendance = $this->createAttendance($user);

        $this->actingAs($user);

        $response = $this->post("/attendance/request/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'note'       => ''
        ]);

        $response->assertSessionHasErrors(['note' => '備考を記入してください']);
    }

    /**
     * 修正申請が保存される
     */
    public function test_request_is_saved()
    {
        $user = User::factory()->create();
        $attendance = $this->createAttendance($user);

        $this->actingAs($user);

        $this->post("/attendance/request/{$attendance->id}", [
            'start_time' => '09:30',
            'end_time'   => '18:00',
            'note'       => '修正申請'
        ]);

        $this->assertDatabaseHas('requests', [
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'status'        => 'pending',
            'note'          => '修正申請'
        ]);
    }

    /**
     * 申請一覧に承認待ちが表示される
     */
    public function test_pending_request_shown_in_request_list()
    {
        $user = User::factory()->create();
        $attendance = $this->createAttendance($user);

        $request = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'status'        => 'pending',
            'note'          => '修正申請',

            'start_time'    => $attendance->start_time,
            'end_time'      => $attendance->end_time,
            'break1_start'  => $attendance->break1_start,
            'break1_end'    => $attendance->break1_end,
            'break2_start'  => null,
            'break2_end'    => null,

            'requested_date' => '2025-11-20',
            'target_date'    => '2025-11-20',
        ]);

        $this->actingAs($user);

        $response = $this->get('/request/list');

        $response->assertSee('修正申請');
    }

    /**
     * 管理者が承認すると「承認済み」に表示される
     */
    public function test_admin_approval_moves_item_to_approved_list()
    {
        $admin = \App\Models\Admin::factory()->create();
        $user  = User::factory()->create();
        $attendance = $this->createAttendance($user);

        $request = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'status'        => 'pending',
            'note'          => '修正申請',

            'start_time'    => $attendance->start_time,
            'end_time'      => $attendance->end_time,
            'break1_start'  => $attendance->break1_start,
            'break1_end'    => $attendance->break1_end,
            'break2_start'  => null,
            'break2_end'    => null,

            'requested_date' => '2025-11-20',
            'target_date'    => '2025-11-20',
        ]);

        $this->actingAs($admin, 'admin');

        $this->post("/admin/request/approve/{$request->id}", [
            'status' => 'approved'
        ]);

        $this->assertDatabaseHas('requests', [
            'id'     => $request->id,
            'status' => 'approved'
        ]);
    }

    /**
     * 申請一覧の「詳細」押下 → 勤怠詳細へ遷移
     */
    public function test_request_detail_link_goes_to_attendance_detail()
    {
        $user = User::factory()->create();
        $attendance = $this->createAttendance($user);

        $request = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'status'        => 'pending',
            'note'          => 'テスト',

            'start_time'    => $attendance->start_time,
            'end_time'      => $attendance->end_time,
            'break1_start'  => $attendance->break1_start,
            'break1_end'    => $attendance->break1_end,
            'break2_start'  => null,
            'break2_end'    => null,

            'requested_date' => '2025-11-20',
            'target_date'    => '2025-11-20',
        ]);

        $this->actingAs($user);

        $response = $this->get('/request/list');

        $response->assertSee("/attendance/detail/{$attendance->id}");
    }
}
