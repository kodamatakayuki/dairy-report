@extends('layouts.app')

@section('title', 'ホーム')
@section('eyebrow', 'YOUR DAILY WORKSPACE')
@section('heading', 'こんにちは、'.auth()->user()->name.'さん')
@section('subtitle', '今日の仕事を振り返り、チームの動きを確認しましょう。')
@section('heading-action')
    <a class="button button-primary" href="{{ route('reports.create') }}"><span>＋</span> 日報を書く</a>
@endsection

@section('content')
    <section class="welcome-banner">
        <div class="welcome-copy">
            <span class="welcome-tag">TODAY’S FOCUS</span>
            <h2>今日の一歩を、<br>チームの前進に。</h2>
            <p>{{ now()->isoFormat('YYYY年 M月 D日（dddd）') }} の出来事を記録しましょう。</p>
            <a href="{{ route('reports.create') }}" class="banner-link">日報を作成する <span>→</span></a>
        </div>
        <div class="banner-art" aria-hidden="true">
            <div class="art-orbit orbit-outer"></div>
            <div class="art-orbit orbit-middle"></div>
            <div class="art-orbit orbit-inner"></div>
            <div class="art-sun">✳</div>
            <div class="art-note"><span>TODAY</span><strong>一歩ずつ。</strong><i>✓</i></div>
            <div class="art-sparkle sparkle-one">✦</div>
            <div class="art-sparkle sparkle-two">·</div>
        </div>
    </section>

    <section class="summary-grid">
        <article class="summary-card">
            <div class="summary-top"><span>MY REPORTS</span><span class="summary-icon icon-lilac">▤</span></div>
            <strong class="summary-number">{{ $employee->dailyReports()->count() }}</strong>
            <div class="summary-bottom"><span>これまでの日報</span><a href="{{ route('reports.index') }}">一覧を見る →</a></div>
        </article>
        @if($employee->isLeader())
            <article class="summary-card summary-card-accent">
                <div class="summary-top"><span>NEEDS REVIEW</span><span class="summary-icon icon-peach">✓</span></div>
                <strong class="summary-number">{{ $pendingReports }}<small>件</small></strong>
                <div class="summary-bottom"><span>チームの確認待ち</span><a href="{{ route('reviews.index') }}">確認する →</a></div>
            </article>
        @else
            <article class="summary-card">
                <div class="summary-top"><span>MY TEAM</span><span class="summary-icon icon-mint">⌘</span></div>
                <strong class="summary-text">{{ $employee->organization->name }}</strong>
                <div class="summary-bottom"><span>現在の所属組織</span><span class="muted">{{ $employee->isLeader() ? 'リーダー' : 'メンバー' }}</span></div>
            </article>
        @endif
        <article class="summary-card">
            <div class="summary-top"><span>ROLE</span><span class="summary-icon icon-blue">♙</span></div>
            <strong class="summary-text">{{ $employee->isAdmin() ? '管理者' : ($employee->isLeader() ? 'グループリーダー' : 'メンバー') }}</strong>
            <div class="summary-bottom"><span>{{ $employee->login_id }}</span><span class="role-indicator"><i></i> 利用中</span></div>
        </article>
    </section>

    <div class="dashboard-columns">
        <section class="panel">
            <div class="section-heading">
                <div><p class="eyebrow">RECENT ACTIVITY</p><h2>最近の日報</h2></div>
                <a class="text-link" href="{{ route('reports.index') }}">すべて見る <span>→</span></a>
            </div>
            @if($recentReports->isNotEmpty())
                @include('reports._table', ['reports' => $recentReports])
            @else
                <div class="empty-state"><span class="empty-icon">▤</span><strong>日報はまだありません</strong><p>今日の出来事を、はじめの一件に。</p><a class="button button-secondary" href="{{ route('reports.create') }}">日報を書く</a></div>
            @endif
        </section>

        <section class="panel published-panel">
            <div class="section-heading">
                <div><p class="eyebrow">SHARED WITH YOU</p><h2>組織の日報</h2></div>
            </div>
            @forelse($publishedReports as $publishedReport)
                <a class="shared-item" href="{{ route('reports.show', $publishedReport) }}">
                    <span class="shared-mark">↗</span>
                    <span class="shared-copy"><strong>{{ $publishedReport->title }}</strong><small>{{ $publishedReport->organization->name }} ・ {{ $publishedReport->report_date->format('Y/m/d') }}</small></span>
                    <span class="shared-arrow">→</span>
                </a>
            @empty
                <div class="empty-state empty-state-small"><span class="empty-icon">✳</span><strong>公開された日報はありません</strong><p>リーダーが共有した日報がここに表示されます。</p></div>
            @endforelse
            <div class="share-note"><span>✦</span> 公開された日報は、上位組織のメンバーと共有されます。</div>
        </section>
    </div>
@endsection
