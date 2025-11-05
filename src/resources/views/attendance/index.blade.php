@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
<div class="attendance">

    <div class="attendance__status">
        <span class="attendance__status-label">{{ $status }}</span>
    </div>

    <div class="attendance__date">{{ $date }}</div>
    <div class="attendance__time" id="clock">{{ $time }}</div>

    <div class="attendance__buttons">
        @switch($status)
            @case('勤務外')
                <form method="POST" action="{{ route('attendance.start') }}">@csrf
                    <button type="submit" class="attendance__button attendance__button--start">出勤</button>
                </form>
                @break

            @case('出勤中')
                <form method="POST" action="{{ route('attendance.end') }}">@csrf
                    <button type="submit" class="attendance__button attendance__button--end">退勤</button>
                </form>
                <form method="POST" action="{{ route('attendance.break.start') }}">@csrf
                    <button type="submit" class="attendance__button attendance__button--break-start">休憩入</button>
                </form>
                @break

            @case('休憩中')
                <form method="POST" action="{{ route('attendance.break.end') }}">@csrf
                    <button type="submit" class="attendance__button attendance__button--break-end">休憩戻</button>
                </form>
                @break

            @case('退勤済')
                <div class="attendance__message">お疲れ様でした。</div>
                @break
        @endswitch
    </div>
</div>
@endsection

@section('js')
<script>
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('clock').textContent = `${hours}:${minutes}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateClock();
        setInterval(updateClock, 1000);
    });
</script>
@endsection
