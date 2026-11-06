<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\Organization;
use App\Models\ReportAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyReportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_can_save_a_draft_and_submit_it_to_their_leader(): void
    {
        $organization = Organization::factory()->create();
        $member = User::factory()->for($organization)->create();

        $this->actingAs($member)
            ->post(route('reports.store'), [
                'report_date' => '2026-10-30',
                'title' => '今日の打ち合わせ',
                'content' => '資料を作成しました。',
                'intent' => 'draft',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('daily_reports', [
            'author_id' => $member->id,
            'organization_id' => $organization->id,
            'title' => '今日の打ち合わせ',
            'status' => DailyReport::STATUS_DRAFT,
            'submitted_at' => null,
        ]);
    }

    public function test_a_leader_can_return_a_report_and_its_author_can_resubmit_it(): void
    {
        $organization = Organization::factory()->create();
        $leader = User::factory()->for($organization)->create(['is_leader' => true]);
        $member = User::factory()->for($organization)->create();
        $report = $this->reportFor($member);

        $this->actingAs($leader)
            ->post(route('reviews.actions.store', $report), [
                'action' => 'return',
                'comment_text' => '確認用の数値を追記してください。',
            ])
            ->assertRedirect(route('reports.show', $report));

        $this->assertDatabaseHas('daily_reports', [
            'id' => $report->id,
            'status' => DailyReport::STATUS_RETURNED,
        ]);
        $this->assertDatabaseHas('report_actions', [
            'daily_report_id' => $report->id,
            'actor_id' => $leader->id,
            'action_type' => ReportAction::TYPE_RETURN,
            'comment_text' => '確認用の数値を追記してください。',
        ]);

        $this->actingAs($member)
            ->put(route('reports.update', $report), [
                'report_date' => '2026-10-30',
                'title' => '今日の打ち合わせ（修正済み）',
                'content' => '確認用の数値を追加しました。',
                'intent' => 'submit',
            ])
            ->assertRedirect(route('reports.show', $report));

        $this->assertDatabaseHas('daily_reports', [
            'id' => $report->id,
            'title' => '今日の打ち合わせ（修正済み）',
            'status' => DailyReport::STATUS_SUBMITTED,
        ]);
        $this->assertNotNull($report->fresh()->submitted_at);
    }

    public function test_a_leader_can_publish_a_report_to_the_organizations_above_its_group(): void
    {
        $board = Organization::factory()->create(['level' => Organization::LEVEL_BOARD]);
        $headquarters = Organization::factory()->create([
            'level' => Organization::LEVEL_HEADQUARTERS,
            'parent_id' => $board->id,
        ]);
        $department = Organization::factory()->create([
            'level' => Organization::LEVEL_DEPARTMENT,
            'parent_id' => $headquarters->id,
        ]);
        $group = Organization::factory()->create([
            'level' => Organization::LEVEL_GROUP,
            'parent_id' => $department->id,
        ]);
        $leader = User::factory()->for($group)->create(['is_leader' => true]);
        $member = User::factory()->for($group)->create();
        $departmentMember = User::factory()->for($department)->create();
        $otherDepartment = Organization::factory()->create([
            'level' => Organization::LEVEL_DEPARTMENT,
            'parent_id' => $headquarters->id,
        ]);
        $otherMember = User::factory()->for($otherDepartment)->create();
        $report = $this->reportFor($member);

        $this->actingAs($leader)
            ->post(route('reviews.actions.store', $report), ['action' => 'publish'])
            ->assertRedirect(route('reports.show', $report));

        $this->assertDatabaseHas('daily_reports', [
            'id' => $report->id,
            'status' => DailyReport::STATUS_PUBLISHED,
        ]);
        $this->assertNotNull($report->fresh()->published_at);
        $this->assertDatabaseHas('report_actions', [
            'daily_report_id' => $report->id,
            'actor_id' => $leader->id,
            'action_type' => ReportAction::TYPE_PUBLISH,
        ]);

        $this->actingAs($departmentMember)
            ->get(route('reports.show', $report))
            ->assertOk()
            ->assertSee('今日の打ち合わせ');

        $this->actingAs($otherMember)
            ->get(route('reports.show', $report))
            ->assertNotFound();
    }

    public function test_a_leader_cannot_return_a_report_without_a_comment(): void
    {
        $organization = Organization::factory()->create();
        $leader = User::factory()->for($organization)->create(['is_leader' => true]);
        $member = User::factory()->for($organization)->create();
        $report = $this->reportFor($member);

        $this->actingAs($leader)
            ->from(route('reports.show', $report))
            ->post(route('reviews.actions.store', $report), ['action' => 'return'])
            ->assertRedirect(route('reports.show', $report))
            ->assertSessionHasErrors(['comment_text' => 'コメントを入力してください。']);

        $this->assertDatabaseHas('daily_reports', [
            'id' => $report->id,
            'status' => DailyReport::STATUS_SUBMITTED,
        ]);
        $this->assertDatabaseCount('report_actions', 0);
    }

    public function test_employees_cannot_view_another_groups_unpublished_report(): void
    {
        $reportingGroup = Organization::factory()->create();
        $otherGroup = Organization::factory()->create();
        $author = User::factory()->for($reportingGroup)->create();
        $otherEmployee = User::factory()->for($otherGroup)->create();
        $report = $this->reportFor($author);

        $this->actingAs($otherEmployee)
            ->get(route('reports.show', $report))
            ->assertNotFound();
    }

    public function test_daily_report_content_is_escaped_before_it_is_displayed(): void
    {
        $organization = Organization::factory()->create();
        $employee = User::factory()->for($organization)->create();
        $dangerousContent = '<script>alert("daily report")</script>';
        $report = $this->reportFor($employee, content: $dangerousContent);

        $this->actingAs($employee)
            ->get(route('reports.show', $report))
            ->assertOk()
            ->assertSee($dangerousContent, true)
            ->assertDontSee('<script>', false);
    }

    private function reportFor(
        User $author,
        string $content = '今日の業務内容を記録しました。',
    ): DailyReport {
        return DailyReport::query()->create([
            'author_id' => $author->id,
            'organization_id' => $author->organization_id,
            'report_date' => '2026-10-30',
            'title' => '今日の打ち合わせ',
            'content' => $content,
            'status' => DailyReport::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }
}
