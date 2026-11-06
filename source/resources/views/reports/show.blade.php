@extends('layouts.app')

@section('title', '日報の詳細')
@section('eyebrow', 'REPORT DETAIL')
@section('heading', $report->title)
@section('subtitle', $report->author->name.' · '.$report->organization->name)
@section('heading-action')
    @can('update', $report)
        <a class="button button-secondary" href="{{ route('reports.edit', $report) }}">日報を編集</a>
    @endcan
@endsection

@section('content')
    <div class="detail-layout">
        <section class="panel report-detail-panel">
            <div class="detail-meta">
                @include('reports._status', ['status' => $report->status])
                <span class="detail-date">{{ $report->report_date->isoFormat('YYYY年 M月 D日（ddd）') }}</span>
                @if($report->published_at)
                    <span class="published-note">↗ {{ $report->published_at->isoFormat('M月D日 HH:mm') }} に公開</span>
                @endif
            </div>
            <div class="report-author">
                <span class="avatar avatar-soft">{{ mb_substr($report->author->name, 0, 1) }}</span>
                <span><strong>{{ $report->author->name }}</strong><small>{{ $report->author->login_id }} · {{ $report->organization->name }}</small></span>
            </div>
            <div class="report-body">{{ $report->content }}</div>
        </section>

        @can('review', $report)
            <section class="panel review-panel">
                <div class="section-heading"><div><p class="eyebrow">LEADER REVIEW</p><h2>内容を確認する</h2></div><span class="review-stamp">✳</span></div>
                <form method="POST" action="{{ route('reviews.actions.store', $report) }}">
                    @csrf
                    <label class="field-group" for="comment_text"><span class="field-label">コメント <span class="required-mark">差し戻し時は必須</span></span>
                        <textarea id="comment_text" class="form-control review-textarea" name="comment_text" rows="4" maxlength="4000" placeholder="確認した内容や、修正してほしい点を入力してください。">{{ old('comment_text') }}</textarea>
                    </label>
                    <div class="review-actions">
                        <button class="button button-ghost" type="submit" name="action" value="comment">コメントを保存</button>
                        <button class="button button-return" type="submit" name="action" value="return" onclick="return confirm('コメントを添えてこの日報を差し戻しますか？')">差し戻す</button>
                        <button class="button button-primary" type="submit" name="action" value="publish" onclick="return confirm('この日報を上位組織に公開しますか？')">上位へ公開 <span>↗</span></button>
                    </div>
                </form>
            </section>
        @endcan

        <section class="panel history-panel">
            <div class="section-heading"><div><p class="eyebrow">ACTIVITY</p><h2>コメント・更新履歴</h2></div><span class="history-count">{{ $report->actions->count() }}</span></div>
            @forelse($report->actions as $action)
                <article class="history-item">
                    <span class="history-avatar">{{ mb_substr($action->actor->name, 0, 1) }}</span>
                    <div class="history-content">
                        <div class="history-heading"><strong>{{ $action->actor->name }}</strong><span>@switch($action->action_type)@case('RETURN') 差し戻し @break @case('PUBLISH') 上位組織に公開 @break @default コメント @endswitch</span><time>{{ $action->created_at->isoFormat('M月D日 HH:mm') }}</time></div>
                        @if($action->comment_text)<p>{{ $action->comment_text }}</p>@endif
                    </div>
                </article>
            @empty
                <p class="history-empty">コメントや操作履歴はまだありません。</p>
            @endforelse
        </section>
    </div>
@endsection
