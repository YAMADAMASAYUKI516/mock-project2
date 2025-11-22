<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_time'   => ['nullable', 'date_format:H:i'],
            'end_time'     => ['nullable', 'date_format:H:i'],
            'break1_start' => ['nullable', 'date_format:H:i'],
            'break1_end'   => ['nullable', 'date_format:H:i'],
            'break2_start' => ['nullable', 'date_format:H:i'],
            'break2_end'   => ['nullable', 'date_format:H:i'],
            'note'         => ['required', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $start = $this->input('start_time');
            $end   = $this->input('end_time');

            $startC = $start ? Carbon::createFromFormat('H:i', $start) : null;
            $endC   = $end   ? Carbon::createFromFormat('H:i', $end)   : null;

            if ($startC && $endC && $startC->greaterThanOrEqualTo($endC)) {
                $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            foreach ([1, 2] as $i) {
                $bStart = $this->input("break{$i}_start");
                $bEnd   = $this->input("break{$i}_end");

                $bStartC = $bStart ? Carbon::createFromFormat('H:i', $bStart) : null;
                $bEndC   = $bEnd   ? Carbon::createFromFormat('H:i', $bEnd)   : null;

                if ($bStartC && $endC && $bStartC->greaterThan($endC)) {
                    $validator->errors()->add("break{$i}_start", '休憩時間が不適切な値です');
                }

                if ($bEndC && $endC && $bEndC->greaterThan($endC)) {
                    $validator->errors()->add("break{$i}_end", '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'note.required' => '備考を記入してください',
        ];
    }
}
