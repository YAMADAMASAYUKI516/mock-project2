@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request/list.css') }}">
@endsection

@section('content')
<div class="request-list">
    <h2 class="request-list__title">申請一覧</h2>

    <div class="request-list__tabs">
        <a href="{{ route('request.list', ['status' => 'pending']) }}" class="request-list__tab {{ $status === 'pending' ? 'active' : '' }}">承認待ち</a>
        <a href="{{ route('request.list', ['status' => 'approved']) }}" class="request-list__tab {{ $status === 'approved' ? 'active' : '' }}">承認済み</a>
    </div>

    <table class="request-list__table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $request)
                <tr>
                    <td>{{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                    <td>{{ $request->user->name }}</td>
                    <td class="date">{{ \Carbon\Carbon::parse($request->target_date)->format('Y/m/d') }}</td>
                    <td>{{ $request->note }}</td>
                    <td class="date">{{ \Carbon\Carbon::parse($request->requested_date)->format('Y/m/d') }}</td>
                    <td><a href="{{ route('attendance.detail', $request->attendance_id) }}">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
