@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
@include('attendance.detail', [
    'attendance'  => $attendance,
    'requestData' => $requestData ?? null,
    'isEditable'  => true,  {{-- 管理者は常に編集可能 --}}
])
@endsection
