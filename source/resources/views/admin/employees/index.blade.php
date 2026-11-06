@extends('layouts.app')

@section('title', '社員マスタ')
@section('eyebrow', 'ADMINISTRATION · PEOPLE')
@section('heading', '社員マスタ')
@section('subtitle', '社員ID・所属・システム権限・リーダー設定を管理します。')
@section('heading-action')
    <a class="button button-primary" href="{{ route('admin.employees.create') }}"><span>＋</span> 社員を追加</a>
@endsection

@section('content')
    <div class="master-summary"><span class="summary-icon icon-lilac">♙</span><span><strong>{{ $employees->total() }}<small>名</small></strong><small>登録社員数</small></span><span class="master-note">社員を停止しても、過去の日報とコメントは保持されます。</span></div>
    <section class="panel">
        <div class="table-wrap">
            <table class="data-table master-table">
                <thead><tr><th>社員</th><th>社員ID</th><th>所属組織</th><th>権限</th><th>リーダー</th><th>状態</th><th></th></tr></thead>
                <tbody>
                @foreach($employees as $employee)
                    <tr>
                        <td><div class="person-cell"><span class="avatar avatar-soft">{{ mb_substr($employee->name, 0, 1) }}</span><strong>{{ $employee->name }}</strong></div></td>
                        <td class="date-cell">{{ $employee->login_id }}</td>
                        <td>{{ $employee->organization->name }}</td>
                        <td><span class="role-pill {{ $employee->isAdmin() ? 'role-admin' : '' }}">{{ $employee->isAdmin() ? '管理者' : '一般' }}</span></td>
                        <td>{{ $employee->isLeader() ? 'リーダー' : '—' }}</td>
                        <td><span class="role-indicator {{ $employee->is_active ? '' : 'indicator-offline' }}"><i></i> {{ $employee->is_active ? '利用中' : '利用停止' }}</span></td>
                        <td class="action-cell"><a class="text-link" href="{{ route('admin.employees.edit', $employee) }}">編集</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())<div class="pagination-wrap">{{ $employees->links() }}</div>@endif
    </section>
@endsection
