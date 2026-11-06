@php($editing = isset($report))
<form class="panel form-panel" method="POST" action="{{ $editing ? route('reports.update', $report) : route('reports.store') }}">
    @csrf
    @if($editing)
        @method('PUT')
    @endif
    <div class="form-grid">
        <div class="field-group">
            <label for="report_date">日付 <span class="required-mark">必須</span></label>
            <input id="report_date" class="form-control" type="date" name="report_date" value="{{ old('report_date', $editing ? $report->report_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
        </div>
        <div class="field-group">
            <label for="title">タイトル <span class="required-mark">必須</span></label>
            <input id="title" class="form-control" type="text" name="title" value="{{ old('title', $report->title ?? '') }}" maxlength="200" placeholder="例：新機能の仕様を確認" required>
        </div>
        <div class="field-group field-full">
            <div class="field-label-row"><label for="content">日報内容 <span class="required-mark">必須</span></label><span class="field-hint">今日の業務や気づきを自由に記入してください。</span></div>
            <textarea id="content" class="form-control report-textarea" name="content" rows="12" maxlength="20000" placeholder="今日取り組んだこと、進捗、気づいたことなど…" required>{{ old('content', $report->content ?? '') }}</textarea>
        </div>
    </div>

    @if($editing && $report->status === \App\Models\DailyReport::STATUS_RETURNED)
        <div class="return-notice"><span>↩</span><div><strong>リーダーから差し戻されています</strong><p>下記のコメントを確認して修正し、もう一度提出してください。</p>        @foreach($report->actions as $action)<small>{{ $action->comment_text }}</small>@endforeach</div></div>
    @endif

    <div class="form-footer">
        <a href="{{ route('reports.index') }}" class="button button-ghost">キャンセル</a>
        <div class="form-actions">
            <button class="button button-secondary" type="submit" name="intent" value="draft">下書き保存</button>
            <button class="button button-primary" type="submit" name="intent" value="submit"><span>✓</span> {{ $editing && $report->status === \App\Models\DailyReport::STATUS_RETURNED ? '再提出する' : '提出する' }}</button>
        </div>
    </div>
</form>
