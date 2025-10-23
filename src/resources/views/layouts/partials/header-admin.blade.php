<nav class="header__nav">
    <ul class="header__menu">
        <li class="header__item">
            <a class="header__link" href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
        </li>
        <li class="header__item">
            <a class="header__link" href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
        </li>
        <li class="header__item">
            <a class="header__link" href="{{ route('admin.request.list') }}">申請一覧</a>
        </li>
        <li class="header__item">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="header__button-logout">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>
