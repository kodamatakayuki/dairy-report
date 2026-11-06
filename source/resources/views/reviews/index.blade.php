@extends('layouts.app')

@section('title', '確認待ち')
@section('eyebrow', 'TEAM REVIEW')
@section('heading', 'グループの日報')
@section('subtitle', '提出内容を確認し、コメント・差し戻し・上位組織への共有を行います。')

@section('content')
    <section class="review-intro">
        <span class="review-intro-icon">✳</span>
        <div><strong>ひとつひとつ確認して、チームの成果をつなぎましょう。</strong><p>{{ auth()->user()->organization->name }} に所属する社員から届いた日報です。</p></div>
    </section>
    <section class="panel">
        <form class="filter-bar" method="GET" action="{{ route('reviews.index') }}">
            <label class="search-field"><span>⌕</span><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="タイトルや社員名で検索"></label>
            <span class="pending-label"><span class="status-pulse"></span> {{ $reports->total() }} 件が確認待ち</span>
        </form>
        @if($reports->isNotEmpty())
            @include('reports._table', ['reports' => $reports])
            <div class="pagination-wrap">{{ $reports->links() }}</div>
        @else
            <div class="empty-state"><span class="empty-icon">✓</span><strong>確認待ちの日報はありません</strong><p>メンバーが日報を提出すると、ここに表示されます。</p></div>
        @endif
    </section>
@endsection
