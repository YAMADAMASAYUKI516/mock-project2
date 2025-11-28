@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
@php use Carbon\Carbon; @endphp

<div class="attendance-detail">
    <h2 class="attendance-detail__title">勤怠詳細</h2>

    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')

        @if ($attendance->id)
            @method('PUT')
        @endif

        <table class="attendance-detail__table">

            {{-- 名前 --}}
            <tr>
                <th>名前</th>
                <td colspan="3" class="attendance-detail__span">
                    <div class="attendance-detail__span-grid">
                        <div class="attendance-detail__span-col1">
                            {{ $attendance->user->name ?? '未設定' }}
                        </div>
                    </div>
                </td>
            </tr>

            {{-- 日付 --}}
            <tr>
                <th>日付</th>
                <td>{{ optional($attendance->work_date)->format('Y年') }}</td>
                <td></td>
                <td>{{ optional($attendance->work_date)->format('n月j日') }}</td>
            </tr>

            {{-- 出勤・退勤 --}}
            <tr>
                <th>出勤・退勤</th>

                {{-- 出勤 --}}
                <td>
                    <input type="text" name="start_time"
                           value="{{ old('start_time',
                               optional($attendance->start_time)->format('H:i')
                           ) }}"
                           pattern="^([01]\d|2[0-3]):[0-5]\d$"
                           inputmode="numeric">
                    @error('start_time')
                        <div class="attendance-detail__error">{{ $message }}</div>
                    @enderror
                </td>

                <td>〜</td>

                {{-- 退勤 --}}
                <td>
                    <input type="text" name="end_time"
                           value="{{ old('end_time',
                               optional($attendance->end_time)->format('H:i')
                           ) }}"
                           pattern="^([01]\d|2[0-3]):[0-5]\d$"
                           inputmode="numeric">
                    @error('end_time')
                        <div class="attendance-detail__error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>

            {{-- 休憩1 --}}
            <tr>
                <th>休憩1</th>

                <td>
                    <input type="text" name="break1_start"
                           value="{{ old('break1_start',
                               optional($attendance->break1_start)->format('H:i')
                           ) }}"
                           pattern="^([01]\d|2[0-3]):[0-5]\d$"
                           inputmode="numeric">
                    @error('break1_start')
                        <div class="attendance-detail__error">{{ $message }}</div>
                    @enderror
                </td>

                <td>〜</td>

                <td>
                    <input type="text" name="break1_end"
                           value="{{ old('break1_end',
                               optional($attendance->break1_end)->format('H:i')
                           ) }}"
                           pattern="^([01]\d|2[0-3]):[0-5]\d$"
                           inputmode="numeric">
                    @error('break1_end')
                        <div class="attendance-detail__error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>

            {{-- 休憩2 --}}
            <tr>
                <th>休憩2</th>

                <td>
                    <input type="text" name="break2_start"
                           value="{{ old('break2_start',
                               optional($attendance->break2_start)->format('H:i')
                           ) }}"
                           pattern="^([01]\d|2[0-3]):[0-5]\d$"
                           inputmode="numeric">
                    @error('break2_start')
                        <div class="attendance-detail__error">{{ $message }}</div>
                    @enderror
                </td>

                <td>〜</td>

                <td>
                    <input type="text" name="break2_end"
                           value="{{ old('break2_end',
                               optional($attendance->break2_end)->format('H:i')
                           ) }}"
                           pattern="^([01]\d|2[0-3]):[0-5]\d$"
                           inputmode="numeric">
                    @error('break2_end')
                        <div class="attendance-detail__error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>

            {{-- 備考 --}}
            <tr>
                <th>備考</th>
                <td colspan="3" class="attendance-detail__span">
                    <div class="attendance-detail__span-grid">
                        <textarea class="attendance-detail__textarea-span" name="note">
{{ old('note', $attendance->note) }}
                        </textarea>
                    </div>
                    @error('note')
                        <div class="attendance-detail__error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>

        </table>

        {{-- 修正ボタン --}}
        <div class="attendance-detail__submit-wrapper">
            <button type="submit" class="attendance-detail__submit-button">
                修正
            </button>
        </div>

    </form>
</div>

@endsection
