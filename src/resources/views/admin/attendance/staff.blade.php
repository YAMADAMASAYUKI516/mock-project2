@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/staff.css') }}">
@endsection

@section('content')
<div class="admin-attendance-staff">
    <h2 class="admin-attendance-staff__title">{{ $user->name }}さんの勤怠</h2>

    <form method="GET"
          action="{{ route('admin.attendance.staff', $user->id) }}"
          class="admin-attendance-staff__month-form"
          id="monthForm">

        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}"
           class="admin-attendance-staff__arrow-link">
            <img src="{{ asset('images/arrow.png') }}" alt="前月" class="admin-attendance-staff__arrow-icon">
            <span class="admin-attendance-staff__arrow-text">前月</span>
        </a>

        <div class="admin-attendance-staff__month-picker" onclick="flatpickrInstance.open()">
            <img src="{{ asset('images/calender.png') }}" alt="カレンダー" class="admin-attendance-staff__calendar-icon">
            <span class="admin-attendance-staff__month-label">{{ $currentMonthDisplay }}</span>

            <input id="monthPicker"
                   type="text"
                   class="admin-attendance-staff__month-input"
                   value="{{ \Carbon\Carbon::createFromFormat('Y-m', $currentMonthValue)->format('Y/m') }}"
                   readonly>
        </div>

        <input type="hidden" name="month" id="monthHidden">

        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}"
           class="admin-attendance-staff__arrow-link">
            <span class="admin-attendance-staff__arrow-text">翌月</span>
            <img src="{{ asset('images/arrow.png') }}"
                 alt="翌月"
                 class="admin-attendance-staff__arrow-icon admin-attendance-staff__arrow-icon--next">
        </a>
    </form>

    <table class="admin-attendance-staff__table">
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
                    $dateKey = $date->format('Y-m-d');
                    $attendance = $attendances[$dateKey] ?? null;
                    $request = $requests[$dateKey] ?? null;
                    $weekday = ['日','月','火','水','木','金','土'][$date->dayOfWeek];

                    $startTime = $attendance?->start_time ?? $request?->start_time;
                    $endTime   = $attendance?->end_time   ?? $request?->end_time;

                    $breakMinutes = 0;

                    if (($attendance?->break1_start && $attendance?->break1_end) ||
                        ($request?->break1_start && $request?->break1_end)) {
                        $start1 = $attendance?->break1_start ?? $request?->break1_start;
                        $end1   = $attendance?->break1_end   ?? $request?->break1_end;
                        $breakMinutes += \Carbon\Carbon::parse($start1)->diffInMinutes(\Carbon\Carbon::parse($end1));
                    }

                    if (($attendance?->break2_start && $attendance?->break2_end) ||
                        ($request?->break2_start && $request?->break2_end)) {
                        $start2 = $attendance?->break2_start ?? $request?->break2_start;
                        $end2   = $attendance?->break2_end   ?? $request?->break2_end;
                        $breakMinutes += \Carbon\Carbon::parse($start2)->diffInMinutes(\Carbon\Carbon::parse($end2));
                    }

                    $breakTime = $breakMinutes > 0
                        ? sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60)
                        : '';

                    if ($startTime && $endTime) {
                        $workMinutes = \Carbon\Carbon::parse($startTime)
                                        ->diffInMinutes(\Carbon\Carbon::parse($endTime)) - $breakMinutes;
                        $totalTime = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
                    } else {
                        $totalTime = '';
                    }
                @endphp

                <tr>
                    <td>{{ $date->format('m/d') }}({{ $weekday }})</td>
                    <td>{{ $startTime ? \Carbon\Carbon::parse($startTime)->format('H:i') : '' }}</td>
                    <td>{{ $endTime   ? \Carbon\Carbon::parse($endTime)->format('H:i')   : '' }}</td>
                    <td>{{ $breakTime }}</td>
                    <td>{{ $totalTime }}</td>
                    <td>
                        @if ($attendance && $attendance->id)
                            <a href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a>
                        @else
                            <a href="{{ route('admin.attendance.detail_by_date', ['user_id' => $user->id, 'date' => $dateKey]) }}">
                                詳細
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="admin-attendance-staff__export">
        <a href="{{ route('admin.attendance.export', ['id' => $user->id, 'month' => $currentMonth]) }}"
           class="admin-attendance-staff__export-button">
            CSV出力
        </a>
    </div>
</div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

    <script>
        let flatpickrInstance;

        document.addEventListener("DOMContentLoaded", function () {
            flatpickrInstance = flatpickr("#monthPicker", {
                locale: "ja",
                altInput: false,
                plugins: [
                    new monthSelectPlugin({
                        shorthand: true,
                        dateFormat: "Y/m",
                        theme: "light"
                    })
                ],
                onChange: function(selectedDates, dateStr) {
                    const formatted = dateStr.replace("/", "-");
                    document.getElementById('monthHidden').value = formatted;
                    document.getElementById('monthForm').submit();
                }
            });
        });
    </script>
@endsection
