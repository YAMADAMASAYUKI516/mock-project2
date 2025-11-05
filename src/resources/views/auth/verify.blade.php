@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify.css') }}">
@endsection

@section('content')
<div class="verify">
    <div class="verify__inner">
        <p class="verify__message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        @if (session('resent'))
            <p class="verify__resent">新しい認証メールを送信しました。</p>
        @endif

        <a href="http://localhost:8025" class="verify__button" target="_blank" rel="noopener noreferrer">
            認証はこちらから
        </a>

        <form class="verify__resend-form" method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit" class="verify__resend-link">認証メールを再送する</button>
        </form>
    </div>
</div>
@endsection
