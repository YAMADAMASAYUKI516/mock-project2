<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason'      => ['required', 'string', 'max:255'],
            'target_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required'      => '申請理由を入力してください。',
            'reason.max'           => '申請理由は255文字以内で入力してください。',
            'target_date.required' => '対象日を入力してください。',
            'target_date.date'     => '対象日は日付形式で入力してください。',
        ];
    }
}
