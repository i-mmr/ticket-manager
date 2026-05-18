<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_assigned_and_created_tickets_for_current_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $workspace = Workspace::query()->create(['name' => 'Workspace']);
        $project = Project::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Helpdesk',
        ]);

        Ticket::query()->create([
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'created_by' => $otherUser->id,
            'title' => '担当チケット',
            'category' => '問い合わせ',
            'status' => 'open',
            'priority' => 'high',
        ]);
        Ticket::query()->create([
            'project_id' => $project->id,
            'assignee_id' => $otherUser->id,
            'created_by' => $user->id,
            'title' => '登録チケット',
            'category' => '不具合',
            'status' => 'in_progress',
            'priority' => 'medium',
        ]);
        Ticket::query()->create([
            'project_id' => $project->id,
            'assignee_id' => $otherUser->id,
            'created_by' => $otherUser->id,
            'title' => '関係ないチケット',
            'category' => 'その他',
            'status' => 'done',
            'priority' => 'low',
        ]);

        $response = $this->actingAs($user)->get(route('home.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home', false)
            ->has('assignedTickets', 1)
            ->where('assignedTickets.0.project_name', 'Helpdesk')
            ->where('assignedTickets.0.title', '担当チケット')
            ->where('assignedTickets.0.category', '問い合わせ')
            ->has('createdTickets', 1)
            ->where('createdTickets.0.title', '登録チケット')
            ->where('createdTickets.0.category', '不具合')
        );
    }

    public function test_placeholder_pages_are_available(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('projects.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Projects/Index', false));
        $this->actingAs($user)->get(route('schedule.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Schedule/Index', false));
    }
}
