<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use App\Support\EmailHasher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $workspace = Workspace::query()->firstOrCreate([
            'name' => 'Default Workspace',
        ], [
            'description' => '初期ワークスペース',
        ]);

        $team = Team::query()->firstOrCreate([
            'workspace_id' => $workspace->id,
            'name' => 'Support Team',
        ], [
            'description' => 'チケット対応チーム',
        ]);

        $projects = collect([
            ['name' => 'Ticket Operations', 'description' => '問い合わせ対応プロジェクト'],
            ['name' => 'Customer Portal', 'description' => '顧客ポータル改善プロジェクト'],
            ['name' => 'Billing Support', 'description' => '請求関連サポートプロジェクト'],
            ['name' => 'Internal Tools', 'description' => '社内運用ツール改善プロジェクト'],
        ])->map(fn (array $project) => Project::query()->firstOrCreate([
            'workspace_id' => $workspace->id,
            'name' => $project['name'],
        ], [
            'description' => $project['description'],
            'status' => 'active',
        ]))->values();

        $email = 'test@example.com';

        User::query()->updateOrCreate([
            'email_hash' => EmailHasher::make($email),
        ], [
            'team_id' => $team->id,
            'email' => $email,
            'name' => 'Test User',
            'password' => 'password',
        ]);

        $tickets = [
            [
                'title' => 'ログイン後にチケット一覧を表示する',
                'description' => 'ダッシュボードを一覧画面として使い、ログイン後の初期画面にする。',
                'status' => 'open',
                'priority' => 'high',
            ],
            [
                'title' => 'Redis セッション設定の確認',
                'description' => 'セッション・キャッシュ・キューの役割分担を見直す。',
                'status' => 'in_progress',
                'priority' => 'medium',
            ],
            [
                'title' => 'テストデータ投入手順を整備する',
                'description' => '初期ユーザーとサンプルチケットを Seeder で投入できるようにする。',
                'status' => 'done',
                'priority' => 'low',
            ],
        ];

        foreach ($tickets as $index => $ticket) {
            Ticket::query()->updateOrCreate(
                ['title' => $ticket['title']],
                ['project_id' => $projects[$index % $projects->count()]->id, ...$ticket],
            );
        }

        $statuses = ['open', 'in_progress', 'done'];
        $priorities = ['high', 'medium', 'low'];

        $ticketsToCreate = max(0, 200 - Ticket::query()->count());

        for ($number = 1; $number <= $ticketsToCreate; $number++) {
            Ticket::query()->updateOrCreate([
                'title' => sprintf('サンプルチケット %03d', $number),
            ], [
                'project_id' => $projects[($number - 1) % $projects->count()]->id,
                'description' => sprintf('プロジェクトごとの表示確認に使うサンプルチケットです。管理番号: %03d', $number),
                'status' => $statuses[$number % count($statuses)],
                'priority' => $priorities[$number % count($priorities)],
            ]);
        }
    }
}
