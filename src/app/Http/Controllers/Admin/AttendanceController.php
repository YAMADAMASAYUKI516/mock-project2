<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function list()
    {
        return view('admin.attendance.list');
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
