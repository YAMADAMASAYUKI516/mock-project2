<?php

namespace App\Http\Controllers;

use App\Models\Request as AttendanceRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function list(HttpRequest $httpRequest)
    {
        $user = Auth::user();
        $status = $httpRequest->input('status', 'pending');

        $requests = AttendanceRequest::with('user')
            ->where('user_id', $user->id)
            ->where('status', $status)
            ->orderByDesc('requested_date')
            ->get();

        return view('request.list', compact('requests', 'status'));
    }
}
