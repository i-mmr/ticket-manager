<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * チケットの一覧、作成、登録、編集、更新、削除を担当する。
 *
 * `/tickets`、`/tickets/create`、`/tickets/{ticket}/edit`、`POST /tickets`、
 * `PATCH /tickets/{ticket}`、`DELETE /tickets/{ticket}` から使う。
 * 入力検証、プロジェクト・ユーザー選択肢の取得、作成・更新時の活動履歴記録も扱う。
 */
class TicketController extends Controller
{
    public function index(): Response
    {
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
                'category' => $ticket->category,
                'description' => $ticket->description,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'created_at' => $ticket->created_at?->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Tickets/Index', [
            'status' => session('status'),
            'tickets' => $tickets,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Tickets/Create', [
            'projects' => $this->projects(),
            'users' => $this->users(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $ticket = Ticket::query()->create([
            ...$this->validated($request),
            'created_by' => $request->user()?->id,
        ]);

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
            'users' => $this->users(),
            'ticket' => [
                'id' => $ticket->id,
                'project_id' => $ticket->project_id,
                'assignee_id' => $ticket->assignee_id,
                'title' => $ticket->title,
                'category' => $ticket->category,
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
            ->route('tickets.index')
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
     * @return array<int, array{id: int, name: string, email: string}>
     */
    private function users(): array
    {
        return User::query()
            ->orderBy('id')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->all();
    }

    /**
     * @return array{project_id: int|null, assignee_id: int|null, title: string, category: string|null, description: string|null, status: string, priority: string}
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'assignee_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['open', 'in_progress', 'done'])],
            'priority' => ['required', Rule::in(['high', 'medium', 'low'])],
        ]);
    }
}
