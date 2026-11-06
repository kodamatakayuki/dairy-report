<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>日付</th><th>タイトル</th><th>作成者</th><th>状態</th><th></th></tr></thead>
        <tbody>
        @foreach($reports as $report)
            <tr>
                <td class="date-cell">{{ $report->report_date->format('Y/m/d') }}</td>
                <td><a class="table-title" href="{{ route('reports.show', $report) }}">{{ $report->title }}</a></td>
                <td class="person-cell">{{ $report->author->name }}</td>
                <td>@include('reports._status', ['status' => $report->status])</td>
                <td class="action-cell"><a class="row-arrow" href="{{ route('reports.show', $report) }}" aria-label="{{ $report->title }}を見る">↗</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
