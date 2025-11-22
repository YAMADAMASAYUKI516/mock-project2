<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Request as AttendanceRequest;
use Carbon\Carbon;

class RequestController extends Controller
{
    public function list(Request $request)
    {
        $status = $request->input('status', 'pending');

        $requests = \App\Models\Request::with('user')
            ->where('status', $status)
            ->orderBy('requested_date', 'desc')
            ->get();

        return view('admin.request.list', compact('requests', 'status'));
    }

    public function approve($id, Request $request)
    {
        if ($request->isMethod('get')) {
            $requestData = AttendanceRequest::with(['attendance.user'])->findOrFail($id);
            $attendance  = $requestData->attendance;

            foreach ([
                'work_date', 'start_time', 'end_time',
                'break1_start', 'break1_end',
                'break2_start', 'break2_end'
            ] as $field) {
                if (!empty($requestData->$field)) {
                    $requestData->$field = Carbon::parse($requestData->$field);
                }
            }

            return view('admin.request.approve', compact('requestData', 'attendance'));
        }

        if ($request->isMethod('post')) {
            $requestData = AttendanceRequest::findOrFail($id);
            $attendance  = Attendance::findOrFail($requestData->attendance_id);

            $attendance->start_time   = $requestData->start_time;
            $attendance->end_time     = $requestData->end_time;
            $attendance->break1_start = $requestData->break1_start;
            $attendance->break1_end   = $requestData->break1_end;
            $attendance->break2_start = $requestData->break2_start;
            $attendance->break2_end   = $requestData->break2_end;
            $attendance->note         = $requestData->note;
            $attendance->save();

            $requestData->status = 'approved';
            $requestData->save();

            return redirect()
                ->route('admin.request.list', ['status' => 'approved']);
        }

        abort(405);
    }
}
