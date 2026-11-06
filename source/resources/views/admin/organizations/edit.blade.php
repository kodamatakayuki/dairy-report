@extends('layouts.app')

@section('title', '組織を編集')
@section('eyebrow', 'ORGANIZATION · EDIT')
@section('heading', $organization->name)
@section('subtitle', '組織の階層と上位組織を変更できます。')

@section('content')
    @include('admin.organizations._form')
    @if($organization->children_count === 0 && $organization->users_count === 0 && $organization->daily_reports_count === 0)
        <section class="danger-zone"><div><strong>この組織を削除する</strong><p>社員・日報・下位組織がない場合のみ削除できます。</p></div><form method="POST" action="{{ route('admin.organizations.destroy', $organization) }}" onsubmit="return confirm('この組織を削除しますか？')">@csrf @method('DELETE')<button class="button button-return" type="submit">組織を削除</button></form></section>
    @else
        <section class="muted-info">社員・日報・下位組織が登録されているため、この組織は削除できません。</section>
    @endif
@endsection
