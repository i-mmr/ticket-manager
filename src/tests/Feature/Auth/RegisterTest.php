<?php

namespace Tests\Feature\Auth;

use App\Models\PendingRegistration;
use App\Models\User;
use App\Notifications\CompleteRegistrationNotification;
use App\Support\EmailHasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_a_registration_link_by_email(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'email' => 'new-user@example.com',
        ]);

        $emailHash = EmailHasher::make('new-user@example.com');
        $pendingRegistration = PendingRegistration::query()->where('email_hash', $emailHash)->first();

        $this->assertNotNull($pendingRegistration);
        $this->assertSame('new-user@example.com', $pendingRegistration->email);
        $this->assertDatabaseMissing('users', [
            'email_hash' => $emailHash,
        ]);
        Notification::assertSentOnDemand(CompleteRegistrationNotification::class);
        $response->assertRedirect(route('register.pending'));
    }

    public function test_user_can_complete_registration_from_the_email_link(): void
    {
        $pendingRegistration = PendingRegistration::query()->create([
            'email' => 'new-user@example.com',
            'email_hash' => EmailHasher::make('new-user@example.com'),
            'token' => str_repeat('a', 64),
            'expires_at' => now()->addHour(),
        ]);

        $completeUrl = (new CompleteRegistrationNotification($pendingRegistration))->registrationUrl();

        $this->get($completeUrl)->assertOk();

        $response = $this->post("/register/complete/{$pendingRegistration->id}", [
            'token' => str_repeat('a', 64),
            'name' => 'Test User',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::query()->where('email_hash', EmailHasher::make('new-user@example.com'))->first();

        $this->assertNotNull($user);
        $this->assertSame('new-user@example.com', $user->email);
        $this->assertSame('Test User', $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('pending_registrations', [
            'id' => $pendingRegistration->id,
        ]);
        $response->assertRedirect(route('dashboard'));
    }
}
