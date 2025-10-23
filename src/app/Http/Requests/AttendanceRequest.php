<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in'    => ['required', 'date_format:H:i'],
            'clock_out'   => ['required', 'date_format:H:i', 'after:clock_in'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end'   => ['nullable', 'date_format:H:i', 'after:break_start'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in.required'       => '出勤時刻を入力してください。',
            'clock_in.date_format'    => '出勤時刻は「時:分」の形式で入力してください。',
            'clock_out.required'      => '退勤時刻を入力してください。',
            'clock_out.date_format'   => '退勤時刻は「時:分」の形式で入力してください。',
            'clock_out.after'         => '退勤時刻は出勤時刻より後の時間を指定してください。',
            'break_start.date_format' => '休憩開始時刻は「時:分」の形式で入力してください。',
            'break_end.date_format'   => '休憩終了時刻は「時:分」の形式で入力してください。',
            'break_end.after'         => '休憩終了時刻は休憩開始時刻より後の時間を指定してください。',
        ];
    }
}
