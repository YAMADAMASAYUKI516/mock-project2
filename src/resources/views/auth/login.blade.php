@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
<div class="login">
    <h1 class="login__title">ログイン</h1>

    <form action="{{ route('login') }}" method="POST">
        @csrf

        <label class="login__label" for="email">メールアドレス</label>
        <input class="login__input" type="email" id="email" name="email" value="{{ old('email') }}">
        @error('email')
            <div class="login__error">{{ $message }}</div>
        @enderror

        <label class="login__label" for="password">パスワード</label>
        <input class="login__input" type="password" id="password" name="password">
        @error('password')
            <div class="login__error">{{ $message }}</div>
        @enderror

        <button class="login__button" type="submit">ログインする</button>

        <a class="login__register-link" href="{{ route('register') }}">会員登録はこちら</a>
    </form>
</div>
@endsection
