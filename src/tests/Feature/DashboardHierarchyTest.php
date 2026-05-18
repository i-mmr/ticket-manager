<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_workspace_team_and_project_tree(): void
    {
        $workspace = Workspace::query()->create([
            'name' => 'Customer Success',
        ]);
        $team = Team::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Tier 1 Support',
        ]);
        $project = Project::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Helpdesk',
        ]);
        $user = User::factory()->create([
            'team_id' => $team->id,
            'name' => 'Support User',
        ]);

        Ticket::query()->create([
            'project_id' => $project->id,
            'title' => 'ログインできない',
            'status' => 'open',
            'priority' => 'high',
        ]);

        $response = $this->actingAs($user)->get(route('tickets.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Tickets/Index', false)
            ->where('workspaces.0.name', 'Customer Success')
            ->where('workspaces.0.teams.0.name', 'Tier 1 Support')
            ->where('workspaces.0.teams.0.users.0.name', 'Support User')
            ->where('workspaces.0.projects.0.name', 'Helpdesk')
            ->where('workspaces.0.projects.0.tickets_count', 1)
            ->has('tickets')
        );
    }
}
