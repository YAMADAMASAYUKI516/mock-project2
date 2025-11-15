@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/list.css') }}">
@endsection

@section('content')
<div class="admin-staff-list">
    <h2 class="admin-staff-list__title">スタッフ一覧</h2>

    <table class="admin-staff-list__table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($staffs as $staff)
                <tr>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email }}</td>
                    <td><a href="{{ route('admin.attendance.staff', $staff->id) }}">詳細</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">スタッフが登録されていません</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
