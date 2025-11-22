<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        return $admin;
    }

    /**
     * 勤怠詳細画面の内容が選択した情報と一致する
     */
    public function test_admin_can_view_selected_attendance_detail()
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-10',
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'break1_start' => '12:00',
            'break1_end'   => '13:00',
            'note'         => 'テスト備考',
        ]);

        $response = $this->get("/admin/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('テスト備考');
    }

    /**
     * 出勤 > 退勤 の場合バリデーションエラー
     */
    public function test_admin_start_after_end_shows_error()
    {
        $this->actingAsAdmin();

        $attendance = Attendance::factory()->create([
            'start_time' => '09:00',
            'end_time'   => '18:00',
        ]);

        $response = $this->put("/admin/attendance/update/{$attendance->id}", [
            'start_time' => '19:00',
            'end_time'   => '18:00',
            'note'       => 'メモ',
        ]);

        $response->assertSessionHasErrors([
            'start_time' => '出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     * 休憩開始が退勤後 → エラー
     */
    public function test_admin_break_start_after_end_shows_error()
    {
        $this->actingAsAdmin();

        $attendance = Attendance::factory()->create([
            'start_time' => '09:00',
            'end_time'   => '18:00',
        ]);

        $response = $this->put("/admin/attendance/update/{$attendance->id}", [
            'start_time'    => '09:00',
            'end_time'      => '18:00',
            'break1_start'  => '19:00',
            'note'          => 'メモ',
        ]);

        $response->assertSessionHasErrors([
            'break1_start' => '休憩時間が不適切な値です'
        ]);
    }

    /**
     * 休憩終了が退勤後 → エラー
     */
    public function test_admin_break_end_after_end_shows_error()
    {
        $this->actingAsAdmin();

        $attendance = Attendance::factory()->create([
            'start_time' => '09:00',
            'end_time'   => '18:00',
        ]);

        $response = $this->put("/admin/attendance/update/{$attendance->id}", [
            'start_time'    => '09:00',
            'end_time'      => '18:00',
            'break1_end'    => '19:00',
            'note'          => 'メモ',
        ]);

        $response->assertSessionHasErrors([
            'break1_end' => '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     * 備考未入力 → エラー
     */
    public function test_admin_note_required_error()
    {
        $this->actingAsAdmin();

        $attendance = Attendance::factory()->create([
            'start_time' => '09:00',
            'end_time'   => '18:00',
        ]);

        $response = $this->put("/admin/attendance/update/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'note'       => '',
        ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください'
        ]);
    }
}
