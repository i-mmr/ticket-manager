<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $workspaceId = DB::table('workspaces')
            ->where('name', 'Default Workspace')
            ->value('id');

        if (! $workspaceId) {
            $workspaceId = DB::table('workspaces')->insertGetId([
                'name' => 'Default Workspace',
                'description' => '初期ワークスペース',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $projects = [
            ['Ticket Operations', '問い合わせ対応プロジェクト'],
            ['Customer Portal', '顧客ポータル改善プロジェクト'],
            ['Billing Support', '請求関連サポートプロジェクト'],
            ['Internal Tools', '社内運用ツール改善プロジェクト'],
        ];

        $projectIds = [];
        foreach ($projects as [$name, $description]) {
            $projectIds[] = DB::table('projects')->updateOrInsert([
                'workspace_id' => $workspaceId,
                'name' => $name,
            ], [
                'description' => $description,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $projectIds = DB::table('projects')
            ->where('workspace_id', $workspaceId)
            ->whereIn('name', array_column($projects, 0))
            ->orderBy('id')
            ->pluck('id')
            ->values()
            ->all();

        if ($projectIds === []) {
            return;
        }

        $statuses = ['open', 'in_progress', 'done'];
        $priorities = ['high', 'medium', 'low'];

        $ticketsToCreate = max(0, 200 - DB::table('tickets')->count());

        for ($number = 1; $number <= $ticketsToCreate; $number++) {
            $projectId = $projectIds[($number - 1) % count($projectIds)];
            $title = sprintf('サンプルチケット %03d', $number);

            DB::table('tickets')->updateOrInsert([
                'title' => $title,
            ], [
                'project_id' => $projectId,
                'description' => sprintf('プロジェクトごとの表示確認に使うサンプルチケットです。管理番号: %03d', $number),
                'status' => $statuses[$number % count($statuses)],
                'priority' => $priorities[$number % count($priorities)],
                'created_at' => now()->subMinutes(200 - $number),
                'updated_at' => now()->subMinutes(200 - $number),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('tickets')
            ->where('title', 'like', 'サンプルチケット %')
            ->delete();
    }
};
