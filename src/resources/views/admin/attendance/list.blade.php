@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@section('content')
    <div class="admin-attendance-list">
        <h2 class="admin-attendance-list__title">
            {{ $currentDate->format('Y年n月j日') }}の勤怠
        </h2>

        <form method="GET" action="{{ route('admin.attendance.list') }}" class="admin-attendance-list__date-form" id="adminDateForm">
            <a href="{{ route('admin.attendance.list', ['date' => $prevDate->format('Y-m-d')]) }}" class="admin-attendance-list__arrow-link">
                <img src="{{ asset('images/arrow.png') }}" alt="前日" class="admin-attendance-list__arrow-icon">
                <span class="admin-attendance-list__arrow-text">前日</span>
            </a>

            <div class="admin-attendance-list__date-picker" onclick="flatpickrInstance.open()">
                <img src="{{ asset('images/calender.png') }}" alt="カレンダー" class="admin-attendance-list__calendar-icon">
                <span class="admin-attendance-list__date-label">{{ $currentDate->format('Y/m/d') }}</span>

                <input
                    id="adminDatePicker"
                    type="text"
                    class="admin-attendance-list__date-input"
                    value="{{ $currentDate->format('Y-m-d') }}"
                    readonly
                >

                <input type="hidden" name="date" id="adminDateHidden">
            </div>

            <a href="{{ route('admin.attendance.list', ['date' => $nextDate->format('Y-m-d')]) }}" class="admin-attendance-list__arrow-link">
                <span class="admin-attendance-list__arrow-text">翌日</span>
                <img src="{{ asset('images/arrow.png') }}" alt="翌日" class="admin-attendance-list__arrow-icon admin-attendance-list__arrow-icon--next">
            </a>
        </form>

        <table class="admin-attendance-list__table">
            <thead>
                <tr>
                    <th>氏名</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->user->name }}</td>
                        <td>{{ $attendance->start_time ? $attendance->start_time->format('H:i') : '-' }}</td>
                        <td>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '-' }}</td>
                        <td>{{ $attendance->break_time_formatted ?? '-' }}</td>
                        <td>{{ $attendance->total_time_formatted ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">この日の勤怠記録はありません</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
    <script>
        let flatpickrInstance;

        document.addEventListener('DOMContentLoaded', function () {
            flatpickrInstance = flatpickr('#adminDatePicker', {
                altInput: false,
                dateFormat: 'Y-m-d',
                locale: 'ja',
                onChange: function (selectedDates, dateStr) {
                    document.getElementById('adminDateHidden').value = dateStr;
                    document.getElementById('adminDateForm').submit();
                }
            });
        });
    </script>
@endsection
