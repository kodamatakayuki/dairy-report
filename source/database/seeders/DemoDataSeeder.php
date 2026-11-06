<?php

namespace Database\Seeders;

use App\Models\DailyReport;
use App\Models\Organization;
use App\Models\ReportAction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $board = Organization::query()->updateOrCreate(
            ['name' => '取締役会', 'parent_id' => null],
            ['level' => Organization::LEVEL_BOARD, 'is_active' => true],
        );
        $headquarters = Organization::query()->updateOrCreate(
            ['name' => '本部', 'parent_id' => $board->id],
            ['level' => Organization::LEVEL_HEADQUARTERS, 'is_active' => true],
        );
        $sales = Organization::query()->updateOrCreate(
            ['name' => '営業部', 'parent_id' => $headquarters->id],
            ['level' => Organization::LEVEL_DEPARTMENT, 'is_active' => true],
        );
        $humanResources = Organization::query()->updateOrCreate(
            ['name' => '人事部', 'parent_id' => $headquarters->id],
            ['level' => Organization::LEVEL_DEPARTMENT, 'is_active' => true],
        );
        $salesOne = Organization::query()->updateOrCreate(
            ['name' => '営業1G', 'parent_id' => $sales->id],
            ['level' => Organization::LEVEL_GROUP, 'is_active' => true],
        );
        $salesTwo = Organization::query()->updateOrCreate(
            ['name' => '営業2G', 'parent_id' => $sales->id],
            ['level' => Organization::LEVEL_GROUP, 'is_active' => true],
        );
        $humanResourcesGroup = Organization::query()->updateOrCreate(
            ['name' => '人事G', 'parent_id' => $humanResources->id],
            ['level' => Organization::LEVEL_GROUP, 'is_active' => true],
        );

        $leader = $this->employee('E1025', '鈴木 花子', $salesOne, User::ROLE_GENERAL, true);
        $member = $this->employee('E1024', '佐藤 太郎', $salesOne, User::ROLE_GENERAL, false);
        $colleague = $this->employee('E1026', '高橋 次郎', $salesOne, User::ROLE_GENERAL, false);
        $this->employee('E1088', '田中 一郎', $sales, User::ROLE_ADMIN, false);
        $this->employee('E1030', '小林 明', $salesTwo, User::ROLE_GENERAL, true);
        $this->employee('E1040', '山本 美咲', $humanResourcesGroup, User::ROLE_GENERAL, true);

        $today = today()->toDateString();
        $yesterday = today()->subDay()->toDateString();

        $this->report($member, $today, '週次のタスク整理', '進行中の案件を整理し、明日の優先順位をまとめました。', DailyReport::STATUS_DRAFT);
        $this->report($colleague, $today, '新機能の仕様確認', '画面設計を確認し、入力項目と操作手順を整理しました。', DailyReport::STATUS_SUBMITTED);
        $published = $this->report($member, $yesterday, '顧客ヒアリング', 'お客さまから伺った要望をチーム内で共有しました。', DailyReport::STATUS_PUBLISHED);
        $returned = $this->report($colleague, $yesterday, '定例資料の作成', '定例会向けの資料を作成しています。', DailyReport::STATUS_RETURNED);

        ReportAction::query()->firstOrCreate(
            [
                'daily_report_id' => $published->id,
                'actor_id' => $leader->id,
                'action_type' => ReportAction::TYPE_PUBLISH,
            ],
            ['comment_text' => '内容を確認しました。上位組織へ共有しています。'],
        );
        ReportAction::query()->firstOrCreate(
            [
                'daily_report_id' => $returned->id,
                'actor_id' => $leader->id,
                'action_type' => ReportAction::TYPE_RETURN,
            ],
            ['comment_text' => '確認用の数値を追記して、もう一度提出してください。'],
        );

        $this->command?->info('デモ用社員: 管理者 E1088 / リーダー E1025 / メンバー E1024・E1026');
        $this->command?->warn('デモ用パスワードはすべて password です。実際の運用前に変更してください。');
    }

    private function employee(
        string $loginId,
        string $name,
        Organization $organization,
        string $role,
        bool $isLeader,
    ): User {
        return User::query()->updateOrCreate(
            ['login_id' => $loginId],
            [
                'name' => $name,
                'email' => mb_strtolower($loginId).'@employees.local',
                'password' => Hash::make('password'),
                'system_role' => $role,
                'is_leader' => $isLeader,
                'organization_id' => $organization->id,
                'is_active' => true,
            ],
        );
    }

    private function report(
        User $author,
        string $date,
        string $title,
        string $content,
        string $status,
    ): DailyReport {
        $reportDate = Carbon::parse($date);

        return DailyReport::query()->updateOrCreate(
            ['author_id' => $author->id, 'report_date' => $date, 'title' => $title],
            [
                'organization_id' => $author->organization_id,
                'content' => $content,
                'status' => $status,
                'submitted_at' => $status !== DailyReport::STATUS_DRAFT ? $reportDate->copy()->setTime(17, 0) : null,
                'published_at' => $status === DailyReport::STATUS_PUBLISHED ? $reportDate->copy()->setTime(17, 30) : null,
            ],
        );
    }
}
