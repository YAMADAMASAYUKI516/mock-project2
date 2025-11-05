@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="register">
    <h1 class="register__title">会員登録</h1>

    <form action="{{ route('register') }}" method="POST">
        @csrf

        <label class="register__label" for="name">名前</label>
        <input class="register__input" type="text" id="name" name="name" value="{{ old('name') }}">
        @error('name')
            <div class="register__error">{{ $message }}</div>
        @enderror

        <label class="register__label" for="email">メールアドレス</label>
        <input class="register__input" type="email" id="email" name="email" value="{{ old('email') }}">
        @error('email')
            <div class="register__error">{{ $message }}</div>
        @enderror

        <label class="register__label" for="password">パスワード</label>
        <input class="register__input" type="password" id="password" name="password">
        @error('password')
            <div class="register__error">{{ $message }}</div>
        @enderror

        <label class="register__label" for="password_confirmation">パスワード確認</label>
        <input class="register__input" type="password" id="password_confirmation" name="password_confirmation">
        @error('password_confirmation')
            <div class="register__error">{{ $message }}</div>
        @enderror

        <button class="register__button" type="submit">登録する</button>

        <a class="register__login-link" href="{{ route('login') }}">ログインはこちら</a>
    </form>
</div>
@endsection
