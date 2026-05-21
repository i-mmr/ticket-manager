<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Inertia\Inertia;
use Inertia\Response;

/**
 * ログイン後のホーム画面を担当する。
 *
 * `/home` から使う。
 * 自分が担当・作成したチケットを取得し、ホーム画面へ渡す。
 */
class HomeController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        return Inertia::render('Home', [
            'status' => session('status'),
            'assignedTickets' => $this->ticketsFor('assignee_id', $user?->id),
            'createdTickets' => $this->ticketsFor('created_by', $user?->id),
        ]);
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
}
