<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\EmailHasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete('/account', [
            'reason' => '使い方が合わなかった',
            'comment' => '必要な導線を試したかったです。',
        ]);

        $response->assertRedirect(route('landing'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
        $this->assertDatabaseHas('account_deletion_feedback', [
            'user_email_hash' => EmailHasher::make($user->email),
            'reason' => '使い方が合わなかった',
        ]);
    }

    public function test_inertia_delete_account_redirects_with_see_other(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->withHeader('X-Inertia', 'true')
            ->delete('/account', [
                'reason' => '使い方が合わなかった',
                'comment' => null,
            ]);

        $response->assertStatus(303);
        $response->assertRedirect(route('landing'));
    }

    public function test_guest_cannot_delete_an_account(): void
    {
        $user = User::factory()->create();

        $response = $this->delete('/account');

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    public function test_authenticated_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
        ]);

        $response = $this->actingAs($user)->patch('/account/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }
}
