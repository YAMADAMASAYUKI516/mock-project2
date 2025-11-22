<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function detail_screen_shows_logged_in_users_name()
    {
        $user = User::factory()->create(['name' => '佐藤太郎']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('佐藤太郎');
    }

    /** @test */
    public function detail_screen_shows_selected_date()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-10',
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);

        $response->assertSee('2025年');
        $response->assertSee('11月10日');
    }

    /** @test */
    public function detail_screen_shows_start_and_end_time_correctly()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'     => $user->id,
            'start_time'  => '2025-11-10 09:00:00',
            'end_time'    => '2025-11-10 18:30:00',
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);

        $response->assertSee('09:00');
        $response->assertSee('18:30');
    }

    /** @test */
    public function detail_screen_shows_break_times_correctly()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'        => $user->id,
            'break1_start'   => '2025-11-10 12:00:00',
            'break1_end'     => '2025-11-10 13:00:00',
            'break2_start'   => '2025-11-10 15:30:00',
            'break2_end'     => '2025-11-10 15:45:00',
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);

        // 休憩1
        $response->assertSee('12:00');
        $response->assertSee('13:00');

        // 休憩2
        $response->assertSee('15:30');
        $response->assertSee('15:45');
    }
}
