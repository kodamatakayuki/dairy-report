@extends('layouts.app')

@section('title', '日報一覧')
@section('eyebrow', 'DAILY REPORTS')
@section('heading', '日報')
@section('subtitle', '自分の日報と、組織内で共有された記録を確認できます。')
@section('heading-action')
    <a class="button button-primary" href="{{ route('reports.create') }}"><span>＋</span> 日報を書く</a>
@endsection

@section('content')
    <section class="panel">
        <form class="filter-bar" method="GET" action="{{ route('reports.index') }}">
            <label class="search-field">
                <span>⌕</span>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="タイトルや社員名で検索">
            </label>
            <select class="select-control" name="status" aria-label="状態で絞り込む">
                <option value="">すべての状態</option>
                @foreach(['DRAFT' => '下書き', 'SUBMITTED' => '提出済み', 'RETURNED' => '差し戻し', 'PUBLISHED' => '公開済み'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="button button-secondary" type="submit">絞り込む</button>
        </form>

        @if($reports->isNotEmpty())
            @include('reports._table', ['reports' => $reports])
            <div class="pagination-wrap">{{ $reports->links() }}</div>
        @else
            <div class="empty-state"><span class="empty-icon">▤</span><strong>該当する日報はありません</strong><p>キーワードや状態を変えてもう一度検索してください。</p><a class="text-link" href="{{ route('reports.create') }}">新しい日報を書く →</a></div>
        @endif
    </section>
@endsection
