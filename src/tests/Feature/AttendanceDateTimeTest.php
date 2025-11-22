<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_datetime_is_displayed_correctly()
    {
        $fixedNow = Carbon::create(2025, 11, 20, 10, 30);
        Carbon::setTestNow($fixedNow);

        $user = User::factory()->create();
        $this->actingAs($user);

        $weekday = ['日', '月', '火', '水', '木', '金', '土'][$fixedNow->dayOfWeek];

        $expectedDate = $fixedNow->format("Y年m月d日") . "({$weekday})";
        $expectedTime = $fixedNow->format('H:i');

        $response = $this->get('/attendance/index');

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }
}
