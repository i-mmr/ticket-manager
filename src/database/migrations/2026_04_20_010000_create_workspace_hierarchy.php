<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        if (! Schema::hasColumn('tickets', 'project_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->foreignId('project_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        $workspaceId = DB::table('workspaces')->insertGetId([
            'name' => 'Default Workspace',
            'description' => '初期ワークスペース',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $teamId = DB::table('teams')->insertGetId([
            'workspace_id' => $workspaceId,
            'name' => 'Support Team',
            'description' => 'チケット対応チーム',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $projectIds = [];
        foreach ([
            ['Ticket Operations', '問い合わせ対応プロジェクト'],
            ['Customer Portal', '顧客ポータル改善プロジェクト'],
            ['Billing Support', '請求関連サポートプロジェクト'],
            ['Internal Tools', '社内運用ツール改善プロジェクト'],
        ] as [$name, $description]) {
            $projectIds[] = DB::table('projects')->insertGetId([
                'workspace_id' => $workspaceId,
                'name' => $name,
                'description' => $description,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('users')->whereNull('team_id')->update(['team_id' => $teamId]);
        DB::table('tickets')->whereNull('project_id')->update(['project_id' => $projectIds[0]]);

        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->timestamps();
        });

        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_done')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('related_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['ticket_id', 'related_ticket_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('related_tickets');
        Schema::dropIfExists('ticket_subtasks');
        Schema::dropIfExists('ticket_activities');
        Schema::dropIfExists('ticket_attachments');
        Schema::dropIfExists('ticket_comments');

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
        });

        Schema::dropIfExists('projects');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('workspaces');
    }
};
