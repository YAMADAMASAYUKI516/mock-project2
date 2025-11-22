<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\Request as AttendanceRequestModel;
use App\Models\User;
use App\Http\Requests\Admin\AttendanceRequest as AdminAttendanceRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function list(Request $request)
    {
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : Carbon::today();

        $attendances = Attendance::with('user')
            ->whereDate('work_date', $date->toDateString())
            ->get();

        return view('admin.attendance.list', [
            'currentDate'  => $date,
            'prevDate'     => $date->copy()->subDay(),
            'nextDate'     => $date->copy()->addDay(),
            'attendances'  => $attendances,
        ]);
    }

    public function detail($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);

        $requestData = AttendanceRequestModel::where('attendance_id', $id)
            ->where('status', 'pending')
            ->first();

        $isEditable = !$requestData || $requestData->status === 'approved';

        foreach ([
            'work_date', 'start_time', 'end_time',
            'break1_start', 'break1_end',
            'break2_start', 'break2_end'
        ] as $field) {
            $attendance->$field = !empty($attendance->$field)
                ? Carbon::parse($attendance->$field)
                : null;
        }

        return view('admin.attendance.detail', compact('attendance', 'requestData', 'isEditable'));
    }

    public function detailByDate($user_id, $date)
    {
        $attendance = Attendance::where('user_id', $user_id)
            ->whereDate('work_date', $date)
            ->with('user')
            ->first();

        if (!$attendance) {
            $user = User::findOrFail($user_id);

            $attendance = new Attendance([
                'user_id'   => $user->id,
                'work_date' => $date,
            ]);

            $attendance->user = $user;
        }

        $requestData = AttendanceRequestModel::whereHas('attendance', function ($q) use ($user_id, $date) {
                $q->where('user_id', $user_id)
                  ->whereDate('work_date', $date);
            })
            ->latest('updated_at')
            ->first();

        if ($requestData && !$attendance->id) {
            $attendance->id = $requestData->attendance_id;
        }

        $isEditable = !$requestData || $requestData->status === 'approved';

        foreach ([
            'work_date', 'start_time', 'end_time',
            'break1_start', 'break1_end',
            'break2_start', 'break2_end'
        ] as $field) {
            if (!empty($attendance->$field)) {
                $attendance->$field = Carbon::parse($attendance->$field);
            }
        }

        return view('admin.attendance.detail', compact('attendance', 'isEditable', 'requestData'));
    }

    public function update(AdminAttendanceRequest $request, $id)
    {
        $validated = $request->validated();

        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            'start_time'   => $validated['start_time']   ?? null,
            'end_time'     => $validated['end_time']     ?? null,
            'break1_start' => $validated['break1_start'] ?? null,
            'break1_end'   => $validated['break1_end']   ?? null,
            'break2_start' => $validated['break2_start'] ?? null,
            'break2_end'   => $validated['break2_end']   ?? null,
            'note'         => $validated['note']         ?? null,
        ]);

        $pendingRequest = AttendanceRequestModel::where('attendance_id', $id)
            ->where('status', 'pending')
            ->first();

        if ($pendingRequest) {
            $pendingRequest->update([
                'status'     => 'approved',
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('admin.attendance.detail', $attendance->id);
    }

    public function staff($id, Request $request)
    {
        $user          = User::findOrFail($id);
        $currentMonth  = $request->input('month', Carbon::now()->format('Y-m'));
        $startOfMonth  = Carbon::parse($currentMonth . '-01')->startOfMonth();
        $endOfMonth    = Carbon::parse($currentMonth . '-01')->endOfMonth();
        $prevMonth     = $startOfMonth->copy()->subMonth()->format('Y-m');
        $nextMonth     = $startOfMonth->copy()->addMonth()->format('Y-m');
        $datesInMonth  = collect(CarbonPeriod::create($startOfMonth, $endOfMonth));

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->orderBy('work_date')
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->work_date)->format('Y-m-d'));

        $requests = DB::table('requests')
            ->leftJoin('attendances', 'requests.attendance_id', '=', 'attendances.id')
            ->where(function ($query) use ($user) {
                $query->where('attendances.user_id', $user->id)
                      ->orWhere('requests.user_id', $user->id);
            })
            ->whereBetween(
                DB::raw('COALESCE(attendances.work_date, requests.target_date)'),
                [$startOfMonth, $endOfMonth]
            )
            ->select(
                'requests.*',
                DB::raw('COALESCE(attendances.work_date, requests.target_date) as work_date')
            )
            ->orderBy('work_date')
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->work_date)->format('Y-m-d'));

        foreach ($requests as $dateKey => $requestRow) {
            if (!isset($attendances[$dateKey])) {
                $attendance                = new \stdClass();
                $attendance->id            = null;
                $attendance->start_time    = $requestRow->start_time ? Carbon::parse($requestRow->start_time) : null;
                $attendance->end_time      = $requestRow->end_time   ? Carbon::parse($requestRow->end_time)   : null;
                $attendance->break1_start  = $requestRow->break1_start ? Carbon::parse($requestRow->break1_start) : null;
                $attendance->break1_end    = $requestRow->break1_end   ? Carbon::parse($requestRow->break1_end)   : null;
                $attendance->break2_start  = $requestRow->break2_start ? Carbon::parse($requestRow->break2_start) : null;
                $attendance->break2_end    = $requestRow->break2_end   ? Carbon::parse($requestRow->break2_end)   : null;

                $breakMinutes = 0;

                if ($attendance->break1_start && $attendance->break1_end) {
                    $breakMinutes += $attendance->break1_start->diffInMinutes($attendance->break1_end);
                }

                if ($attendance->break2_start && $attendance->break2_end) {
                    $breakMinutes += $attendance->break2_start->diffInMinutes($attendance->break2_end);
                }

                $attendance->break_time_formatted = sprintf(
                    '%d:%02d',
                    intdiv($breakMinutes, 60),
                    $breakMinutes % 60
                );

                if ($attendance->start_time && $attendance->end_time) {
                    $totalMinutes = $attendance->start_time->diffInMinutes($attendance->end_time) - $breakMinutes;

                    $attendance->total_time_formatted = sprintf(
                        '%d:%02d',
                        intdiv($totalMinutes, 60),
                        $totalMinutes % 60
                    );
                } else {
                    $attendance->total_time_formatted = '-';
                }

                $attendances[$dateKey] = $attendance;
            }
        }

        return view('admin.attendance.staff', [
            'user'               => $user,
            'attendances'        => $attendances,
            'requests'           => $requests,
            'datesInMonth'       => $datesInMonth,
            'currentMonth'       => $currentMonth,
            'currentMonthValue'  => $currentMonth,
            'currentMonthDisplay'=> $startOfMonth->format('Y/m'),
            'prevMonth'          => $prevMonth,
            'nextMonth'          => $nextMonth,
        ]);
    }

    public function exportCsv($id, Request $request)
    {
        $user          = User::findOrFail($id);
        $currentMonth  = $request->input('month', Carbon::now()->format('Y-m'));
        $startOfMonth  = Carbon::parse($currentMonth . '-01')->startOfMonth();
        $endOfMonth    = Carbon::parse($currentMonth . '-01')->endOfMonth();
        $datesInMonth  = collect(CarbonPeriod::create($startOfMonth, $endOfMonth));

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->orderBy('work_date')
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->work_date)->format('Y-m-d'));

        $requests = DB::table('requests')
            ->leftJoin('attendances', 'requests.attendance_id', '=', 'attendances.id')
            ->where(function ($query) use ($user) {
                $query->where('attendances.user_id', $user->id)
                      ->orWhere('requests.user_id', $user->id);
            })
            ->whereBetween(
                DB::raw('COALESCE(attendances.work_date, requests.target_date)'),
                [$startOfMonth, $endOfMonth]
            )
            ->select(
                'requests.*',
                DB::raw('COALESCE(attendances.work_date, requests.target_date) as work_date')
            )
            ->orderBy('work_date')
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->work_date)->format('Y-m-d'));

        $csvData   = [];
        $csvData[] = ['日付', '出勤', '退勤', '休憩時間', '合計勤務時間'];

        foreach ($datesInMonth as $date) {
            $dateKey    = $date->format('Y-m-d');
            $attendance = $attendances[$dateKey] ?? null;
            $request    = $requests[$dateKey] ?? null;

            $startTime  = $attendance?->start_time ?? $request?->start_time;
            $endTime    = $attendance?->end_time   ?? $request?->end_time;

            $breakMinutes = 0;

            if (
                ($attendance?->break1_start && $attendance?->break1_end) ||
                ($request?->break1_start && $request?->break1_end)
            ) {
                $start1 = $attendance?->break1_start ?? $request?->break1_start;
                $end1   = $attendance?->break1_end   ?? $request?->break1_end;

                $breakMinutes += Carbon::parse($start1)->diffInMinutes(Carbon::parse($end1));
            }

            if (
                ($attendance?->break2_start && $attendance?->break2_end) ||
                ($request?->break2_start && $request?->break2_end)
            ) {
                $start2 = $attendance?->break2_start ?? $request?->break2_start;
                $end2   = $attendance?->break2_end   ?? $request?->break2_end;

                $breakMinutes += Carbon::parse($start2)->diffInMinutes(Carbon::parse($end2));
            }

            $breakTime = $breakMinutes > 0
                ? sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60)
                : '';

            $totalTime = '';

            if ($startTime && $endTime) {
                $totalMinutes = Carbon::parse($startTime)->diffInMinutes(Carbon::parse($endTime)) - $breakMinutes;

                $totalTime = sprintf(
                    '%d:%02d',
                    intdiv($totalMinutes, 60),
                    $totalMinutes % 60
                );
            }

            $csvData[] = [
                $date->format('Y/m/d'),
                $startTime ? Carbon::parse($startTime)->format('H:i') : '',
                $endTime   ? Carbon::parse($endTime)->format('H:i')   : '',
                $breakTime,
                $totalTime,
            ];
        }

        $filename = $user->name . '_attendance_' . $currentMonth . '.csv';

        $response = Response::stream(function () use ($csvData) {
            $file = fopen('php://output', 'w');

            echo chr(0xEF) . chr(0xBB) . chr(0xBF);

            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);

        return $response;
    }
}
