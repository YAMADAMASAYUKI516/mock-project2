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
            'start_time'    => ['nullable', 'date_format:H:i'],
            'end_time'      => ['nullable', 'date_format:H:i'],
            'break1_start'  => ['nullable', 'date_format:H:i'],
            'break1_end'    => ['nullable', 'date_format:H:i'],
            'break2_start'  => ['nullable', 'date_format:H:i'],
            'break2_end'    => ['nullable', 'date_format:H:i'],
            'note'          => ['required', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $start = $this->input('start_time');
            $end   = $this->input('end_time');

            /**
             * ▼ ① 出勤 > 退勤 → エラー（テスト仕様）
             * 「出勤時間が不適切な値です」
             */
            if ($start && $end && $start >= $end) {
                $validator->errors()->add('start_time', '出勤時間が不適切な値です');
            }

            /**
             * ▼ ② 休憩時間の整合性チェック
             *
             * 【テスト仕様 / 追加仕様に統一】
             *
             * 休憩開始 < 出勤            → 「休憩時間が不適切な値です」
             * 休憩開始 > 退勤            → 「休憩時間が不適切な値です」
             *
             * 休憩終了 < 出勤            → 「休憩時間もしくは退勤時間が不適切な値です」
             * 休憩終了 > 退勤            → 「休憩時間もしくは退勤時間が不適切な値です」
             *
             */
            if ($start && $end) {

                foreach ([1, 2] as $i) {

                    $breakStart = $this->input("break{$i}_start");
                    $breakEnd   = $this->input("break{$i}_end");

                    // ▼ 休憩開始
                    if ($breakStart) {

                        if ($breakStart < $start || $breakStart > $end) {
                            $validator->errors()->add(
                                "break{$i}_start",
                                '休憩時間が不適切な値です'
                            );
                        }
                    }

                    // ▼ 休憩終了
                    if ($breakEnd) {

                        if ($breakEnd < $start || $breakEnd > $end) {
                            $validator->errors()->add(
                                "break{$i}_end",
                                '休憩時間もしくは退勤時間が不適切な値です'
                            );
                        }
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'start_time.date_format'   => '出勤時間は「時:分」の形式で入力してください',
            'end_time.date_format'     => '退勤時間は「時:分」の形式で入力してください',

            'break1_start.date_format' => '休憩1開始時間は「時:分」の形式で入力してください',
            'break1_end.date_format'   => '休憩1終了時間は「時:分」の形式で入力してください',

            'break2_start.date_format' => '休憩2開始時間は「時:分」の形式で入力してください',
            'break2_end.date_format'   => '休憩2終了時間は「時:分」の形式で入力してください',

            'note.required'            => '備考を記入してください',
        ];
    }
}
