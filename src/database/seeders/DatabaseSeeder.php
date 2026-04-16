<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
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
        User::query()->updateOrCreate([
            'email' => 'test@example.com',
        ], [
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

        foreach ($tickets as $ticket) {
            Ticket::query()->firstOrCreate(
                ['title' => $ticket['title']],
                $ticket,
            );
        }
    }
}
