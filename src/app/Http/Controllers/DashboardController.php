<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return to_route('home.dashboard');
    }

    public function home(): Response
    {
        $user = auth()->user();

        return Inertia::render('Home', [
            'currentUser' => [
                'name' => $user?->name,
                'email' => $user?->email,
            ],
            'status' => session('status'),
            'workspaces' => $this->workspaces(),
            'assignedTickets' => $this->ticketsFor('assignee_id', $user?->id),
            'createdTickets' => $this->ticketsFor('created_by', $user?->id),
        ]);
    }

    public function projects(): Response
    {
        return Inertia::render('Projects/Index', [
            'currentUser' => $this->currentUser(),
            'workspaces' => $this->workspaces(),
        ]);
    }

    public function schedule(): Response
    {
        return Inertia::render('Schedule/Index', [
            'currentUser' => $this->currentUser(),
            'workspaces' => $this->workspaces(),
        ]);
    }

    /**
     * @return array<int, array{id: int, name: string, teams: mixed, projects: mixed}>
     */
    private function workspaces(): array
    {
        return Workspace::query()
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
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, project_name: string|null, title: string, category: string|null, status: string}>
     */
    private function ticketsFor(string $column, ?int $userId): array
    {
        if (! $userId) {
            return [];
        }

        return Ticket::query()
            ->with('project')
            ->where($column, $userId)
            ->latest()
            ->get()
            ->map(fn (Ticket $ticket) => [
                'id' => $ticket->id,
                'project_name' => $ticket->project?->name,
                'title' => $ticket->title,
                'category' => $ticket->category,
                'status' => $ticket->status,
            ])
            ->all();
    }

    /**
     * @return array{name: string|null, email: string|null}
     */
    private function currentUser(): array
    {
        $user = auth()->user();

        return [
            'name' => $user?->name,
            'email' => $user?->email,
        ];
    }
}
