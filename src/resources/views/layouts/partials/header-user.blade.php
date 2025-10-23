<nav class="header__nav">
    <ul class="header__menu">
        <li class="header__item">
            <a class="header__link" href="{{ route('attendance.index') }}">勤怠</a>
        </li>
        <li class="header__item">
            <a class="header__link" href="{{ route('attendance.list') }}">勤怠一覧</a>
        </li>
        <li class="header__item">
            <a class="header__link" href="{{ route('request.list') }}">申請</a>
        </li>
        <li class="header__item">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="header__button-logout">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>
