<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>coachtech 勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <p class="header__logo">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
            </p>

            @if (Request::is('login') || Request::is('register')  || Request::is('email/verify') || Request::is('admin/login'))
                {{-- ログイン・登録画面ではロゴのみ --}}
            @else
                {{-- ユーザータイプでナビゲーションを切り替え --}}
                @if (Auth::guard('admin')->check())
                    @include('layouts.partials.header-admin')
                @elseif (Auth::check())
                    @include('layouts.partials.header-user')
                @endif
            @endif
        </div>
    </header>

    <main>
        <div class="common__heading">
            @yield('title')
        </div>
        @yield('content')
    </main>
    @yield('js')
</body>

</html>