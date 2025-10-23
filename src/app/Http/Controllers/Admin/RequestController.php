<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function list()
    {
        return view('admin.request.list');
    }

    public function approve($id)
    {
        // 今後承認処理を追加
        return redirect()->route('admin.request.list');
    }
}
