<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'note.max' => '備考は255文字以内で入力してください。',
        ];
    }
}
