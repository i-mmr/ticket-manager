<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Inertia\Inertia;
use Inertia\Response;

class TicketShowController extends Controller
{
    public function __invoke(Ticket $ticket): Response
    {
        $ticket->load([
            'project.workspace',
            'comments.user',
            'attachments',
            'activities',
            'subtasks',
            'relatedTickets',
        ]);

        return Inertia::render('Tickets/Show', [
            'ticket' => [
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
                'comments' => $ticket->comments->map(fn ($comment) => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'user' => $comment->user?->name,
                    'created_at' => $comment->created_at?->format('Y-m-d H:i'),
                ]),
                'attachments' => $ticket->attachments->map(fn ($attachment) => [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'mime_type' => $attachment->mime_type,
                    'size_bytes' => $attachment->size_bytes,
                ]),
                'activities' => $ticket->activities->map(fn ($activity) => [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'description' => $activity->description,
                    'created_at' => $activity->created_at?->format('Y-m-d H:i'),
                ]),
                'subtasks' => $ticket->subtasks->sortBy('sort_order')->values()->map(fn ($subtask) => [
                    'id' => $subtask->id,
                    'title' => $subtask->title,
                    'is_done' => $subtask->is_done,
                ]),
                'related_tickets' => $ticket->relatedTickets->map(fn ($relatedTicket) => [
                    'id' => $relatedTicket->id,
                    'title' => $relatedTicket->title,
                ]),
                'created_at' => $ticket->created_at?->format('Y-m-d H:i'),
                'updated_at' => $ticket->updated_at?->format('Y-m-d H:i'),
            ],
        ]);
    }
}
