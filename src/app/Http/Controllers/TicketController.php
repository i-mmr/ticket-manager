<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Tickets/Create', [
            'projects' => $this->projects(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $ticket = Ticket::query()->create($this->validated($request));

        $ticket->activities()->create([
            'user_id' => $request->user()?->id,
            'action' => 'created',
            'description' => 'チケットを登録しました。',
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'ticket-created');
    }

    public function edit(Ticket $ticket): Response
    {
        return Inertia::render('Tickets/Edit', [
            'projects' => $this->projects(),
            'ticket' => [
                'id' => $ticket->id,
                'project_id' => $ticket->project_id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
            ],
        ]);
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $ticket->update($this->validated($request));

        $ticket->activities()->create([
            'user_id' => $request->user()?->id,
            'action' => 'updated',
            'description' => 'チケットを更新しました。',
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'ticket-updated');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $ticket->delete();

        return redirect()
            ->route('dashboard')
            ->with('status', 'ticket-deleted');
    }

    /**
     * @return array<int, array{id: int, name: string, workspace: string|null}>
     */
    private function projects(): array
    {
        return Project::query()
            ->with('workspace')
            ->orderBy('name')
            ->get()
            ->map(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'workspace' => $project->workspace?->name,
            ])
            ->all();
    }

    /**
     * @return array{project_id: int|null, title: string, description: string|null, status: string, priority: string}
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['open', 'in_progress', 'done'])],
            'priority' => ['required', Rule::in(['high', 'medium', 'low'])],
        ]);
    }
}
