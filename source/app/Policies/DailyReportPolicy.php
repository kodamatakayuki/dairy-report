<?php

namespace App\Policies;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DailyReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, DailyReport $dailyReport): bool|Response
    {
        if (! $user->is_active) {
            return Response::denyAsNotFound();
        }

        if ($user->isAdmin() || $dailyReport->author_id === $user->id) {
            return true;
        }

        if (
            $user->isLeader()
            && $dailyReport->organization_id === $user->organization_id
            && in_array($dailyReport->status, [
                DailyReport::STATUS_SUBMITTED,
                DailyReport::STATUS_RETURNED,
                DailyReport::STATUS_PUBLISHED,
            ], true)
        ) {
            return true;
        }

        if (
            $dailyReport->status === DailyReport::STATUS_PUBLISHED
            && $user->organization !== null
            && $user->organization->includes($dailyReport->organization)
        ) {
            return true;
        }

        return Response::denyAsNotFound();
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->organization_id !== null;
    }

    public function update(User $user, DailyReport $dailyReport): bool
    {
        return $user->is_active
            && $dailyReport->author_id === $user->id
            && in_array($dailyReport->status, [DailyReport::STATUS_DRAFT, DailyReport::STATUS_RETURNED], true);
    }

    public function review(User $user, DailyReport $dailyReport): bool
    {
        return $user->is_active
            && $user->isLeader()
            && $dailyReport->organization_id === $user->organization_id
            && $dailyReport->status === DailyReport::STATUS_SUBMITTED
            && $dailyReport->author_id !== $user->id;
    }
}
