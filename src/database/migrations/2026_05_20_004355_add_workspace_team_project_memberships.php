<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['team_id', 'user_id']);
        });

        Schema::create('project_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['project_id', 'team_id']);
        });

        $now = now();

        DB::table('users')
            ->whereNotNull('team_id')
            ->orderBy('id')
            ->select(['id', 'team_id'])
            ->chunk(100, function ($users) use ($now) {
                foreach ($users as $user) {
                    DB::table('team_user')->updateOrInsert([
                        'team_id' => $user->team_id,
                        'user_id' => $user->id,
                    ], [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });

        DB::table('projects')
            ->join('teams', 'projects.workspace_id', '=', 'teams.workspace_id')
            ->orderBy('projects.id')
            ->select(['projects.id as project_id', 'teams.id as team_id'])
            ->chunk(100, function ($rows) use ($now) {
                foreach ($rows as $row) {
                    DB::table('project_team')->updateOrInsert([
                        'project_id' => $row->project_id,
                        'team_id' => $row->team_id,
                    ], [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });

        $firstUserId = DB::table('users')->orderBy('id')->value('id');

        if ($firstUserId) {
            DB::table('workspaces')
                ->whereNull('owner_id')
                ->update(['owner_id' => $firstUserId]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_team');
        Schema::dropIfExists('team_user');

        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });
    }
};
