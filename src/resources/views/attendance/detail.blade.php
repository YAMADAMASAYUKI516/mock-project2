@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h2 class="attendance-detail__title">勤怠詳細</h2>

    <table class="attendance-detail__table">
        <tr>
            <th>名前</th>
            <td>{{ $attendance->user->last_name ?? '' }}　{{ $attendance->user->first_name ?? '' }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ optional($attendance->work_date)->format('Y年') ?? '' }}</td>
            <td>{{ optional($attendance->work_date)->format('n月j日') ?? '' }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>{{ optional($attendance->start_time)->format('H:i') ?? '' }}</td>
            <td>〜</td>
            <td>{{ optional($attendance->end_time)->format('H:i') ?? '' }}</td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>{{ optional($attendance->break1_start)->format('H:i') ?? '' }}</td>
            <td>〜</td>
            <td>{{ optional($attendance->break1_end)->format('H:i') ?? '' }}</td>
        </tr>
        <tr>
            <th>休憩2</th>
            <td>{{ optional($attendance->break2_start)->format('H:i') ?? '' }}</td>
            <td>〜</td>
            <td>{{ optional($attendance->break2_end)->format('H:i') ?? '' }}</td>
        </tr>
        <tr>
            <th>備考</th>
            <td colspan="3">{{ $attendance->note ?? '' }}</td>
        </tr>
    </table>

    @if ($attendance->status !== 'pending')
    <form action="{{ route('attendance.request', $attendance->id) }}" method="POST">
        @csrf
        <button type="submit" class="attendance-detail__submit-button">修正</button>
    </form>
    @else
    <p class="attendance-detail__notice">* 承認待ちのため修正はできません。</p>
    @endif
</div>
@endsection
