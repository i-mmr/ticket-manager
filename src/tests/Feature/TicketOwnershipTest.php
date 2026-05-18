<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_creation_sets_creator_and_accepts_assignee_and_category(): void
    {
        $user = User::factory()->create();
        $assignee = User::factory()->create();
        $workspace = Workspace::query()->create(['name' => 'Workspace']);
        $project = Project::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Helpdesk',
        ]);

        $response = $this->actingAs($user)->post(route('tickets.store'), [
            'project_id' => $project->id,
            'assignee_id' => $assignee->id,
            'title' => '登録テスト',
            'category' => '問い合わせ',
            'description' => '説明',
            'status' => 'open',
            'priority' => 'high',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'project_id' => $project->id,
            'assignee_id' => $assignee->id,
            'created_by' => $user->id,
            'title' => '登録テスト',
            'category' => '問い合わせ',
        ]);
    }
}
