<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DailyReportController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->user();
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([
                DailyReport::STATUS_DRAFT,
                DailyReport::STATUS_SUBMITTED,
                DailyReport::STATUS_RETURNED,
                DailyReport::STATUS_PUBLISHED,
            ])],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = DailyReport::query()
            ->with(['author:id,name,login_id', 'organization:id,name'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhereHas('author', fn (Builder $authorQuery) => $authorQuery->where('name', 'like', '%'.$search.'%'));
                });
            });

        if (! $employee->isAdmin()) {
            $organizationIds = $employee->organization->subtreeIds();

            $query->where(function (Builder $query) use ($employee, $organizationIds): void {
                $query->whereBelongsTo($employee, 'author')
                    ->orWhere(function (Builder $publishedQuery) use ($organizationIds): void {
                        $publishedQuery->where('status', DailyReport::STATUS_PUBLISHED)
                            ->whereIn('organization_id', $organizationIds);
                    });
            });
        }

        $reports = $query
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('reports.index', compact('reports', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create', DailyReport::class);

        return view('reports.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', DailyReport::class);

        $data = $this->validatedData($request);
        $submitted = $data['intent'] === 'submit';
        $employee = $request->user();

        $report = DB::transaction(fn (): DailyReport => $employee->dailyReports()->create([
            'organization_id' => $employee->organization_id,
            'report_date' => $data['report_date'],
            'title' => $data['title'],
            'content' => $data['content'],
            'status' => $submitted ? DailyReport::STATUS_SUBMITTED : DailyReport::STATUS_DRAFT,
            'submitted_at' => $submitted ? now() : null,
        ]));

        return redirect()
            ->route('reports.show', $report)
            ->with('success', $submitted ? '日報を提出しました。' : '下書きを保存しました。');
    }

    public function show(DailyReport $report): View
    {
        $this->authorize('view', $report);

        $report->load(['author', 'organization.parent.parent', 'actions.actor']);

        return view('reports.show', compact('report'));
    }

    public function edit(DailyReport $report): View
    {
        $this->authorize('update', $report);
        $report->load(['actions' => fn (Builder $query) => $query->where('action_type', 'RETURN')]);

        return view('reports.edit', compact('report'));
    }

    public function update(Request $request, DailyReport $report): RedirectResponse
    {
        $this->authorize('update', $report);

        $data = $this->validatedData($request);
        $submitted = $data['intent'] === 'submit';
        $employee = $request->user();

        DB::transaction(function () use ($data, $employee, $report, $submitted): void {
            $report->update([
                'organization_id' => $employee->organization_id,
                'report_date' => $data['report_date'],
                'title' => $data['title'],
                'content' => $data['content'],
                'status' => $submitted ? DailyReport::STATUS_SUBMITTED : DailyReport::STATUS_DRAFT,
                'submitted_at' => $submitted ? now() : $report->submitted_at,
                'published_at' => null,
            ]);
        });

        return redirect()
            ->route('reports.show', $report)
            ->with('success', $submitted ? '日報を再提出しました。' : '下書きを保存しました。');
    }

    /**
     * @return array{report_date: string, title: string, content: string, intent: string}
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'report_date' => ['required', 'date_format:Y-m-d'],
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string', 'max:20000'],
            'intent' => ['required', Rule::in(['draft', 'submit'])],
        ], [], [
            'report_date' => '日付',
            'title' => 'タイトル',
            'content' => '日報内容',
            'intent' => '保存方法',
        ]);
    }
}
