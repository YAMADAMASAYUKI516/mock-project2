@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h2 class="attendance-list__title">勤怠一覧</h2>

    <div class="attendance-list__month-control">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="attendance-list__nav">&larr; 前月</a>
        <span class="attendance-list__current-month">
            <i class="fa-regular fa-calendar-days"></i> {{ $currentMonth }}
        </span>
        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="attendance-list__nav">翌月 &rarr;</a>
    </div>

    <table class="attendance-list__table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datesInMonth as $date)
                @php
                    $attendance = $attendances[$date->format('Y-m-d')] ?? null;
                    $weekday = ['日','月','火','水','木','金','土'][$date->dayOfWeek];
                @endphp
                <tr>
                    <td>{{ $date->format('m/d') }}({{ $weekday }})</td>

                    @if ($attendance)
                        <td>{{ $attendance->start_time ? $attendance->start_time->format('H:i') : '-' }}</td>
                        <td>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '-' }}</td>
                        <td>{{ $attendance->break_time_formatted }}</td>
                        <td>{{ $attendance->total_time_formatted }}</td>
                        <td><a href="{{ route('attendance.detail', $attendance->id) }}">詳細</a></td>
                    @else
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        @if ($attendance)
                            <td><a href="{{ route('attendance.detail', $attendance->id) }}">詳細</a></td>
                        @else
                            <td><a href="{{ route('attendance.detail_by_date', ['date' => $date->format('Y-m-d')]) }}">詳細</a></td>
                        @endif
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
