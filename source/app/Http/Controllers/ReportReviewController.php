<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\ReportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportReviewController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $reports = $request->user()->organization->dailyReports()
            ->where('status', DailyReport::STATUS_SUBMITTED)
            ->with(['author:id,name,login_id'])
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhereHas('author', fn (Builder $authorQuery) => $authorQuery->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('reviews.index', compact('reports', 'filters'));
    }

    public function store(Request $request, DailyReport $report): RedirectResponse
    {
        $this->authorize('review', $report);

        $action = $request->string('action')->toString();
        $requiresComment = in_array($action, ['comment', 'return'], true);

        $data = $request->validate([
            'action' => ['required', Rule::in(['comment', 'return', 'publish'])],
            'comment_text' => [$requiresComment ? 'required' : 'nullable', 'string', 'max:4000'],
        ], [], [
            'comment_text' => 'コメント',
        ]);

        DB::transaction(function () use ($data, $request, $report): void {
            $report = DailyReport::query()->lockForUpdate()->findOrFail($report->id);
            $this->authorize('review', $report);

            $actionType = match ($data['action']) {
                'return' => ReportAction::TYPE_RETURN,
                'publish' => ReportAction::TYPE_PUBLISH,
                default => ReportAction::TYPE_COMMENT,
            };

            $report->actions()->create([
                'actor_id' => $request->user()->id,
                'action_type' => $actionType,
                'comment_text' => $data['comment_text'] ?? null,
            ]);

            if ($actionType === ReportAction::TYPE_RETURN) {
                $report->update(['status' => DailyReport::STATUS_RETURNED, 'published_at' => null]);
            } elseif ($actionType === ReportAction::TYPE_PUBLISH) {
                $report->update(['status' => DailyReport::STATUS_PUBLISHED, 'published_at' => now()]);
            }
        });

        $message = match ($data['action']) {
            'return' => 'コメントを添えて日報を差し戻しました。',
            'publish' => '日報を上位組織に公開しました。',
            default => 'コメントを保存しました。',
        };

        return redirect()->route('reports.show', $report)->with('success', $message);
    }
}
