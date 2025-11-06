<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;

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
            'currentDate' => $date,
            'prevDate' => $date->copy()->subDay(),
            'nextDate' => $date->copy()->addDay(),
            'attendances' => $attendances,
        ]);
    }

    public function detail($id)
    {
        return view('admin.attendance.detail', compact('id'));
    }

    public function staff($id)
    {
        return view('admin.attendance.staff', compact('id'));
    }
}
