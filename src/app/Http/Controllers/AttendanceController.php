<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        $status = '勤務外';

        if ($attendance) {
            if ($attendance->end_time) {
                $status = '退勤済み';
            } elseif ($attendance->start_time) {
                // 勤怠レコードがあり、まだ退勤していない場合、休憩中かをチェック
                $latestBreak = BreakTime::where('attendance_id', $attendance->id)
                    ->latest()
                    ->first();

                if ($latestBreak && is_null($latestBreak->break_end_time)) {
                    $status = '休憩中';
                } else {
                    $status = '出勤中';
                }
            }
        }

        return view('attendance.index', [
            'status' => $status,
            'date' => $today->isoFormat('Y年M月D日(dd)'),
            'time' => now()->format('H:i'),
        ]);
    }

    public function store(Request $request)
    {
        // 今後バリデーションや登録処理を追加
        return redirect()->route('attendance.index');
    }

    public function list()
    {
        return view('attendance.list');
    }

    public function detail($id)
    {
        return view('attendance.detail', compact('id'));
    }
}
