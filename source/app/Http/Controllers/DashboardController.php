<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $employee = auth()->user();

        $recentReports = $employee->dailyReports()
            ->with('organization')
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $pendingReports = $employee->isLeader()
            ? $employee->organization->dailyReports()
                ->where('status', DailyReport::STATUS_SUBMITTED)
                ->count()
            : 0;

        $publishedReports = $employee->organization
            ->dailyReports()
            ->where('status', DailyReport::STATUS_PUBLISHED)
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('dashboard', compact('employee', 'recentReports', 'pendingReports', 'publishedReports'));
    }
}
