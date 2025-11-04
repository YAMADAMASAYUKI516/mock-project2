@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h2 class="attendance-detail__title">勤怠詳細</h2>

    <form action="{{ route('attendance.request', $attendance->id) }}" method="POST">
        @csrf
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
                <td>{{ optional(\Carbon\Carbon::parse($attendance->work_date))->format('Y年') }}</td>
                <td></td>
                <td>{{ optional(\Carbon\Carbon::parse($attendance->work_date))->format('n月j日') }}</td>
            </tr>

            {{-- 出勤・退勤 --}}
            <tr>
                <th>出勤・退勤</th>
                <td>
                    @if ($isEditable)
                        <input type="text" name="start_time"
                            value="{{ old('start_time', $requestData->start_time ?? $attendance->start_time?->format('H:i')) }}"
                            pattern="^([01]\d|2[0-3]):[0-5]\d$"
                            inputmode="numeric">
                        @error('start_time')
                            <div class="attendance-detail__error">{{ $message }}</div>
                        @enderror
                    @else
                        <span>{{ $attendance->start_time?->format('H:i') ?? '' }}</span>
                    @endif
                </td>
                <td>〜</td>
                <td>
                    @if ($isEditable)
                        <input type="text" name="end_time"
                            value="{{ old('end_time', $requestData->end_time ?? $attendance->end_time?->format('H:i')) }}"
                            pattern="^([01]\d|2[0-3]):[0-5]\d$"
                            inputmode="numeric">
                        @error('end_time')
                            <div class="attendance-detail__error">{{ $message }}</div>
                        @enderror
                    @else
                        <span>{{ $attendance->end_time?->format('H:i') ?? '' }}</span>
                    @endif
                </td>
            </tr>

            {{-- 休憩1 --}}
            @if ($isEditable || $attendance->break1_start || $attendance->break1_end)
            <tr>
                <th>休憩</th>
                <td>
                    @if ($isEditable)
                        <input type="text" name="break1_start"
                            value="{{ old('break1_start', $requestData->break1_start ?? $attendance->break1_start?->format('H:i')) }}"
                            pattern="^([01]\d|2[0-3]):[0-5]\d$"
                            inputmode="numeric">
                        @error('break1_start')
                            <div class="attendance-detail__error">{{ $message }}</div>
                        @enderror
                    @else
                        <span>{{ $attendance->break1_start?->format('H:i') ?? '' }}</span>
                    @endif
                </td>
                <td>〜</td>
                <td>
                    @if ($isEditable)
                        <input type="text" name="break1_end"
                            value="{{ old('break1_end', $requestData->break1_end ?? $attendance->break1_end?->format('H:i')) }}"
                            pattern="^([01]\d|2[0-3]):[0-5]\d$"
                            inputmode="numeric">
                        @error('break1_end')
                            <div class="attendance-detail__error">{{ $message }}</div>
                        @enderror
                    @else
                        <span>{{ $attendance->break1_end?->format('H:i') ?? '' }}</span>
                    @endif
                </td>
            </tr>
            @endif

            {{-- 休憩2 --}}
            @if ($isEditable || $attendance->break2_start || $attendance->break2_end)
            <tr>
                <th>休憩2</th>
                <td>
                    @if ($isEditable)
                        <input type="text" name="break2_start"
                            value="{{ old('break2_start', $requestData->break2_start ?? $attendance->break2_start?->format('H:i')) }}"
                            pattern="^([01]\d|2[0-3]):[0-5]\d$"
                            inputmode="numeric">
                        @error('break2_start')
                            <div class="attendance-detail__error">{{ $message }}</div>
                        @enderror
                    @else
                        <span>{{ $attendance->break2_start?->format('H:i') ?? '' }}</span>
                    @endif
                </td>
                <td>〜</td>
                <td>
                    @if ($isEditable)
                        <input type="text" name="break2_end"
                            value="{{ old('break2_end', $requestData->break2_end ?? $attendance->break2_end?->format('H:i')) }}"
                            pattern="^([01]\d|2[0-3]):[0-5]\d$"
                            inputmode="numeric">
                        @error('break2_end')
                            <div class="attendance-detail__error">{{ $message }}</div>
                        @enderror
                    @else
                        <span>{{ $attendance->break2_end?->format('H:i') ?? '' }}</span>
                    @endif
                </td>
            </tr>
            @endif

            {{-- 備考 --}}
            <tr>
                <th>備考</th>
                <td colspan="3" class="attendance-detail__span">
                    <div class="attendance-detail__span-grid">
                        @if ($isEditable)
                            <textarea
                                class="attendance-detail__textarea-span"
                                name="note">{{ old('note', $requestData->note ?? $attendance->note) }}</textarea>
                        @else
                            <div class="attendance-detail__text-note-full">
                                {{ $attendance->note ?? '' }}
                            </div>
                        @endif
                    </div>
                    @error('note')
                        <div class="attendance-detail__error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>

        @if ($isEditable)
            <div class="attendance-detail__submit-wrapper">
                <button type="submit" class="attendance-detail__submit-button">修正</button>
            </div>
        @else
            <p class="attendance-detail__notice">*承認待ちのため修正はできません。</p>
        @endif
    </form>
</div>
@endsection
