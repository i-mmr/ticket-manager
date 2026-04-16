<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TicketShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_a_ticket_detail_page(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::query()->create([
            'title' => 'ログイン画面の確認',
            'description' => 'ログイン後にチケット詳細へ遷移できるようにする',
            'status' => 'open',
            'priority' => 'high',
        ]);

        $response = $this->actingAs($user)->get(route('tickets.show', $ticket));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Tickets/Show', false)
            ->where('ticket.id', $ticket->id)
            ->where('ticket.title', 'ログイン画面の確認')
            ->where('ticket.description', 'ログイン後にチケット詳細へ遷移できるようにする')
        );
    }

    public function test_guest_is_redirected_when_accessing_ticket_detail_page(): void
    {
        $ticket = Ticket::query()->create([
            'title' => '未ログインでは見えない',
            'description' => null,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $response = $this->get(route('tickets.show', $ticket));

        $response->assertRedirect(route('login'));
    }
}
