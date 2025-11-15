@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
@include('attendance.detail', [
    'attendance' => $attendance,
    'requestData' => $requestData,
    'isEditable' => $isEditable
])
@endsection
