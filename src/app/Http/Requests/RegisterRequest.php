<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:20'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                  => '氏名を入力してください。',
            'name.max'                       => '氏名は20文字以内で入力してください。',
            'email.required'                 => 'メールアドレスを入力してください。',
            'email.email'                    => '有効なメールアドレス形式で入力してください。',
            'email.unique'                   => 'このメールアドレスはすでに登録されています。',
            'password.required'              => 'パスワードを入力してください。',
            'password.min'                   => 'パスワードは8文字以上で入力してください。',
            'password.confirmed'             => '確認用パスワードが一致しません。',
            'password_confirmation.required' => '確認用パスワードを入力してください',
            'password_confirmation.min'      => '確認用パスワードは8文字以上で入力してください',
        ];
    }
}
