@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
<div class="attendance">
    {{-- 勤務状況バッジ --}}
    <div class="attendance__status">
        @if ($status === '勤務外')
            <span class="attendance__status-label">勤務外</span>
        @elseif ($status === '出勤中')
            <span class="attendance__status-label">出勤中</span>
        @elseif ($status === '休憩中')
            <span class="attendance__status-label">休憩中</span>
        @elseif ($status === '退勤済み')
            <span class="attendance__status-label">退勤済み</span>
        @endif
    </div>

    {{-- 日付と時刻 --}}
    <div class="attendance__date">{{ $date }}</div>
    <div class="attendance__time">{{ $time }}</div>

    {{-- 勤怠ボタンエリア --}}
    <div class="attendance__buttons">
        @if ($status === '勤務外')
            <form action="{{ route('attendance.start') }}" method="POST">
                @csrf
                <button type="submit" class="attendance__button attendance__button--start">出勤</button>
            </form>

        @elseif ($status === '出勤中')
            <form action="{{ route('attendance.end') }}" method="POST">
                @csrf
                <button type="submit" class="attendance__button attendance__button--end">退勤</button>
            </form>
            <form action="{{ route('attendance.break.start') }}" method="POST">
                @csrf
                <button type="submit" class="attendance__button attendance__button--break-start">休憩入</button>
            </form>

        @elseif ($status === '休憩中')
            <form action="{{ route('attendance.break.end') }}" method="POST">
                @csrf
                <button type="submit" class="attendance__button attendance__button--break-end">休憩戻</button>
            </form>

        @elseif ($status === '退勤済み')
            <div class="attendance__message">お疲れ様でした。</div>
        @endif
    </div>
</div>
@endsection
