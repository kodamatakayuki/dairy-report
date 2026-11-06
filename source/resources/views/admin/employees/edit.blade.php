@extends('layouts.app')

@section('title', '社員を編集')
@section('eyebrow', 'PEOPLE · EDIT MEMBER')
@section('heading', $employee->name)
@section('subtitle', $employee->login_id.' の所属・権限・アカウント設定を変更します。')

@section('content')
    @include('admin.employees._form')
    @if($employee->is_active)
        <section class="danger-zone"><div><strong>この社員の利用を停止する</strong><p>過去の日報を保持したまま、ログインできなくなります。</p></div><form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" onsubmit="return confirm('この社員を利用停止にしますか？')">@csrf @method('DELETE')<button class="button button-return" type="submit">利用停止</button></form></section>
    @endif
@endsection
