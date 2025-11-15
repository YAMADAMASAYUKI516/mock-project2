@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
@php use Carbon\Carbon; @endphp

<div class="attendance-detail">
    <h2 class="attendance-detail__title">勤怠詳細</h2>

    <table class="attendance-detail__table">

        {{-- 名前 --}}
        <tr>
            <th>名前</th>
            <td colspan="3" class="attendance-detail__span">
                <div class="attendance-detail__span-grid">
                    <div class="attendance-detail__span-col1">{{ $attendance->user->name }}</div>
                </div>
            </td>
        </tr>

        {{-- 日付 --}}
        <tr>
            <th>日付</th>
            <td>{{ Carbon::parse($attendance->work_date)->format('Y年') }}</td>
            <td></td>
            <td>{{ Carbon::parse($attendance->work_date)->format('n月j日') }}</td>
        </tr>

        {{-- 出勤・退勤 --}}
        <tr>
            <th>出勤・退勤</th>
            <td>
                <span>{{ optional($requestData->start_time ? Carbon::parse($requestData->start_time) : $attendance->start_time)->format('H:i') }}</span>
            </td>
            <td>〜</td>
            <td>
                <span>{{ optional($requestData->end_time ? Carbon::parse($requestData->end_time) : $attendance->end_time)->format('H:i') }}</span>
            </td>
        </tr>

        {{-- 休憩1 --}}
        @php
            $showBreak1 =
                $requestData?->break1_start ||
                $requestData?->break1_end ||
                $attendance?->break1_start ||
                $attendance?->break1_end;
        @endphp
        @if ($showBreak1)
        <tr>
            <th>休憩</th>
            <td>
                <span>{{ optional($requestData->break1_start ? Carbon::parse($requestData->break1_start) : $attendance->break1_start)->format('H:i') }}</span>
            </td>
            <td>〜</td>
            <td>
                <span>{{ optional($requestData->break1_end ? Carbon::parse($requestData->break1_end) : $attendance->break1_end)->format('H:i') }}</span>
            </td>
        </tr>
        @endif

        {{-- 休憩2 --}}
        @php
            $showBreak2 =
                $requestData?->break2_start ||
                $requestData?->break2_end ||
                $attendance?->break2_start ||
                $attendance?->break2_end;
        @endphp
        @if ($showBreak2)
        <tr>
            <th>休憩2</th>
            <td>
                <span>{{ optional($requestData->break2_start ? Carbon::parse($requestData->break2_start) : $attendance->break2_start)->format('H:i') }}</span>
            </td>
            <td>〜</td>
            <td>
                <span>{{ optional($requestData->break2_end ? Carbon::parse($requestData->break2_end) : $attendance->break2_end)->format('H:i') }}</span>
            </td>
        </tr>
        @endif

        {{-- 備考 --}}
        <tr>
            <th>備考</th>
            <td colspan="3" class="attendance-detail__span">
                <div class="attendance-detail__span-grid">
                    <div class="attendance-detail__text-note-full">
                        {{ $requestData->note ?? $attendance->note ?? '' }}
                    </div>
                </div>
            </td>
        </tr>

    </table>

    {{-- 承認ボタン --}}
    @if ($requestData->status === 'pending')
        <form action="{{ route('admin.request.approve', $requestData->id) }}" method="POST" class="attendance-detail__submit-wrapper">
            @csrf
            <button type="submit" class="attendance-detail__submit-button">承認</button>
        </form>
    @else
        <p class="attendance-detail__notice">承認済み</p>
    @endif

</div>
@endsection
