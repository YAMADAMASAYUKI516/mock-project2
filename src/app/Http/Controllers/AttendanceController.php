<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest as AttendanceFormRequest;
use App\Models\Attendance;
use App\Models\Request as AttendanceRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = now();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today->toDateString())
            ->first();

        $status = $this->determineStatus($attendance);
        $weekday = ['日', '月', '火', '水', '木', '金', '土'][$today->dayOfWeek];

        return view('attendance.index', [
            'status' => $status,
            'date' => $today->format("Y年m月d日") . "({$weekday})",
            'time' => $today->format('H:i'),
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
            // 休憩1中 → 何もしない
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

    public function end()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if ($attendance && !$attendance->end_time) {
            $attendance->end_time = now();

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

    private function determineStatus($attendance): string
    {
        if (!$attendance) return '勤務外';
        if ($attendance->end_time) return '退勤済';
        if (
            ($attendance->break2_start && !$attendance->break2_end) ||
            ($attendance->break1_start && !$attendance->break1_end)
        ) {
            return '休憩中';
        }
        if ($attendance->start_time) return '出勤中';
        return '勤務外';
    }

public function list(Request $request)
{
    $user = Auth::user();
    $currentMonth = $request->input('month', now()->format('Y-m'));

    $startOfMonth = Carbon::parse($currentMonth . '-01')->startOfMonth();
    $endOfMonth   = $startOfMonth->copy()->endOfMonth();

    $prevMonth = $startOfMonth->copy()->subMonth()->format('Y-m');
    $nextMonth = $startOfMonth->copy()->addMonth()->format('Y-m');

    $datesInMonth = collect(CarbonPeriod::create($startOfMonth, $endOfMonth));

    $attendances = Attendance::where('user_id', $user->id)
        ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
        ->orderBy('work_date')
        ->get()
        ->keyBy(fn($a) => Carbon::parse($a->work_date)->format('Y-m-d'));

        $requests = DB::table('requests')
        ->leftJoin('attendances', 'requests.attendance_id', '=', 'attendances.id')
        ->where(function ($query) use ($user) {
            $query->where('attendances.user_id', $user->id)
                ->orWhereNull('requests.attendance_id');
        })
        ->whereBetween('attendances.work_date', [$startOfMonth, $endOfMonth])
        ->orderBy('attendances.work_date')
        ->select('requests.*', 'attendances.work_date')
        ->get()
        ->keyBy(fn($r) => \Carbon\Carbon::parse($r->work_date)->format('Y-m-d'));

    $weekdayMap = ['日', '月', '火', '水', '木', '金', '土'];

    foreach ($attendances as $attendance) {
        foreach (['work_date', 'start_time', 'end_time', 'break1_start', 'break1_end', 'break2_start', 'break2_end'] as $field) {
            $attendance->$field = $attendance->$field ? Carbon::parse($attendance->$field) : null;
        }

        $attendance->weekday = $weekdayMap[$attendance->work_date->dayOfWeek];

        if ($attendance->total_work_time !== null) {
            $h = floor($attendance->total_work_time);
            $m = round(($attendance->total_work_time - $h) * 60);
            $attendance->total_time_formatted = sprintf('%d:%02d', $h, $m);
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
        'requests' => $requests,
        'datesInMonth' => $datesInMonth,
        'currentMonth' => $currentMonth,
        'currentMonthDisplay' => $startOfMonth->format('Y/m'),
        'currentMonthValue' => $currentMonth,
        'prevMonth' => $prevMonth,
        'nextMonth' => $nextMonth,
    ]);
}

    public function detail($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
            $requestData = AttendanceRequest::where('attendance_id', $id)
                            ->where('status', 'pending')
                            ->first();

        $isEditable = !$requestData || $requestData->status === 'approved';

        foreach (['work_date', 'start_time', 'end_time', 'break1_start', 'break1_end', 'break2_start', 'break2_end'] as $field) {
            $attendance->$field = !empty($attendance->$field) ? Carbon::parse($attendance->$field) : null;
        }

        return view('attendance.detail', compact('attendance', 'requestData', 'isEditable'));
    }

    public function detailByDate($date)
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $date)
            ->with('user')
            ->first();

        if (!$attendance) {
            $attendance = new Attendance([
                'user_id' => $user->id,
                'work_date' => $date,
            ]);
            $attendance->user = $user;
        }

        $requestData = AttendanceRequest::whereHas('attendance', function ($q) use ($user, $date) {
                $q->where('user_id', $user->id)
                ->whereDate('work_date', $date);
            })
            ->latest('updated_at')
            ->first();

        if ($requestData && !$attendance->id) {
            $attendance->id = $requestData->attendance_id;
        }

        $isEditable = !$requestData || $requestData->status === 'approved';

        foreach (['work_date', 'start_time', 'end_time', 'break1_start', 'break1_end', 'break2_start', 'break2_end'] as $field) {
            if (!empty($attendance->$field)) {
                $attendance->$field = Carbon::parse($attendance->$field);
            }
        }

        return view('attendance.detail', compact('attendance', 'isEditable', 'requestData'));
    }

    public function request(AttendanceFormRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $validated = $request->validated();

        AttendanceRequest::updateOrCreate(
            ['attendance_id' => $id],
            array_merge($validated, [
                'user_id' => Auth::id(),
                'status' => 'pending',
                'requested_date' => Carbon::today(),
                'target_date' => $attendance->work_date,
            ])
        );

        return redirect()->route('attendance.detail', $id)->withInput();
    }

    public function requestByDate(AttendanceFormRequest $request, $date)
    {
        $user = Auth::user();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $date],
            []
        );

        $validated = $request->validated();

        AttendanceRequest::updateOrCreate(
            ['attendance_id' => $attendance->id],
            array_merge($validated, [
                'user_id' => $user->id,
                'status' => 'pending',
                'requested_date' => Carbon::today(),
                'target_date' => $attendance->work_date,
            ])
        );

        return redirect()->route('attendance.detail', $attendance->id)->withInput();
    }
}
