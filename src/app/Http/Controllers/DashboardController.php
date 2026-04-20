<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Workspace;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $user = auth()->user();

        $tickets = Ticket::query()
            ->with('project.workspace')
            ->latest()
            ->get()
            ->map(fn (Ticket $ticket) => [
                'id' => $ticket->id,
                'project' => $ticket->project ? [
                    'id' => $ticket->project->id,
                    'name' => $ticket->project->name,
                    'workspace' => $ticket->project->workspace?->name,
                ] : null,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'created_at' => $ticket->created_at?->format('Y-m-d H:i'),
            ]);

        $workspaces = Workspace::query()
            ->with([
                'teams.users:id,team_id,name,email',
                'projects' => fn ($query) => $query->withCount('tickets')->orderBy('name'),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Workspace $workspace) => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'teams' => $workspace->teams
                    ->sortBy('name')
                    ->values()
                    ->map(fn ($team) => [
                        'id' => $team->id,
                        'name' => $team->name,
                        'users' => $team->users
                            ->sortBy('name')
                            ->values()
                            ->map(fn ($user) => [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                            ]),
                    ]),
                'projects' => $workspace->projects->map(fn ($project) => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'status' => $project->status,
                    'tickets_count' => $project->tickets_count,
                ]),
            ]);

        return Inertia::render('Dashboard', [
            'currentUser' => [
                'name' => $user?->name,
                'email' => $user?->email,
            ],
            'status' => session('status'),
            'workspaces' => $workspaces,
            'tickets' => $tickets,
        ]);
    }
}
