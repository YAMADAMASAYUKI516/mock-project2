<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        $status = $this->determineStatus($attendance);

        return view('attendance.index', [
            'status' => $status,
            'date' => now()->format('Y年m月d日'),
            'time' => now()->format('H:i'),
        ]);
    }

    public function start()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['start_time' => now()]
        );

        return redirect()->route('attendance.index');
    }

    public function end()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if ($attendance && !$attendance->end_time) {
            $attendance->end_time = now();

            // 勤務時間計算
            $workDuration = Carbon::parse($attendance->start_time)->diffInMinutes(now());
            $breakDuration = 0;

            if ($attendance->break1_start && $attendance->break1_end) {
                $breakDuration += Carbon::parse($attendance->break1_start)->diffInMinutes($attendance->break1_end);
            }
            if ($attendance->break2_start && $attendance->break2_end) {
                $breakDuration += Carbon::parse($attendance->break2_start)->diffInMinutes($attendance->break2_end);
            }

            $netMinutes = max(0, $workDuration - $breakDuration);
            $attendance->total_work_time = round($netMinutes / 60, 2);

            $attendance->save();
        }

        return redirect()->route('attendance.index');
    }

    public function breakStart()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if (!$attendance) return redirect()->route('attendance.index');

        if (!$attendance->break1_start) {
            $attendance->break1_start = now();
        } elseif ($attendance->break1_start && !$attendance->break1_end) {
            // 休憩1中 → 無視
        } elseif (!$attendance->break2_start) {
            $attendance->break2_start = now();
        }

        $attendance->save();
        return redirect()->route('attendance.index');
    }

    public function breakEnd()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if (!$attendance) return redirect()->route('attendance.index');

        if ($attendance->break2_start && !$attendance->break2_end) {
            $attendance->break2_end = now();
        } elseif ($attendance->break1_start && !$attendance->break1_end) {
            $attendance->break1_end = now();
        }

        $attendance->save();
        return redirect()->route('attendance.index');
    }

    private function determineStatus($attendance): string
    {
        if (!$attendance) return '勤務外';
        if ($attendance->end_time) return '退勤済';
        if (($attendance->break2_start && !$attendance->break2_end) || ($attendance->break1_start && !$attendance->break1_end)) {
            return '休憩中';
        }
        if ($attendance->start_time) return '出勤中';
        return '勤務外';
    }

    public function list(Request $request)
    {
        $user = Auth::user();

        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));

        $startOfMonth = Carbon::parse($currentMonth . '-01')->startOfMonth();
        $endOfMonth   = Carbon::parse($currentMonth . '-01')->endOfMonth();

        $prevMonth = $startOfMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $startOfMonth->copy()->addMonth()->format('Y-m');

        $datesInMonth = collect(CarbonPeriod::create($startOfMonth, $endOfMonth));

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->orderBy('work_date')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->format('Y-m-d');
            });

        $weekdayMap = ['日', '月', '火', '水', '木', '金', '土'];

        foreach ($attendances as $attendance) {
            $attendance->work_date = Carbon::parse($attendance->work_date);
            $attendance->start_time = $attendance->start_time ? Carbon::parse($attendance->start_time) : null;
            $attendance->end_time = $attendance->end_time ? Carbon::parse($attendance->end_time) : null;
            $attendance->break1_start = $attendance->break1_start ? Carbon::parse($attendance->break1_start) : null;
            $attendance->break1_end = $attendance->break1_end ? Carbon::parse($attendance->break1_end) : null;
            $attendance->break2_start = $attendance->break2_start ? Carbon::parse($attendance->break2_start) : null;
            $attendance->break2_end = $attendance->break2_end ? Carbon::parse($attendance->break2_end) : null;

            $attendance->weekday = $weekdayMap[$attendance->work_date->dayOfWeek];

            if ($attendance->total_work_time !== null) {
                $hours = floor($attendance->total_work_time);
                $minutes = round(($attendance->total_work_time - $hours) * 60);
                $attendance->total_time_formatted = sprintf('%d:%02d', $hours, $minutes);
            } else {
                $attendance->total_time_formatted = '-';
            }

            $breakMinutes = 0;
            if ($attendance->break1_start && $attendance->break1_end) {
                $breakMinutes += $attendance->break1_start->diffInMinutes($attendance->break1_end);
            }
            if ($attendance->break2_start && $attendance->break2_end) {
                $breakMinutes += $attendance->break2_start->diffInMinutes($attendance->break2_end);
            }
            $attendance->break_time_formatted = sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
        }

        return view('attendance.list', [
            'attendances' => $attendances,
            'datesInMonth' => $datesInMonth,
            'currentMonth' => Carbon::parse($currentMonth . '-01')->format('Y年m月'),
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function detail($id)
    {
        $attendance = Attendance::findOrFail($id);
        return view('attendance.detail', compact('attendance'));
    }

    public function detailByDate(Request $request)
    {
        $date = $request->query('date');

        if (!$date) {
            abort(404);
        }

        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('work_date', $date)
            ->first();

        return view('attendance.detail', compact('attendance', 'date'));
    }
}
