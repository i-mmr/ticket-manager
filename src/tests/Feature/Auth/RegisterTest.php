<?php

namespace Tests\Feature\Auth;

use App\Models\PendingRegistration;
use App\Models\User;
use App\Notifications\CompleteRegistrationNotification;
use App\Support\EmailHasher;
use App\Support\RegistrationTokenHasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
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
        $this->assertArrayNotHasKey('token', $pendingRegistration->getAttributes());
        $this->assertMatchesRegularExpression('/\A[0-9a-f]{64}\z/', $pendingRegistration->token_hash);
        $this->assertDatabaseMissing('users', [
            'email_hash' => $emailHash,
        ]);
        Notification::assertSentOnDemand(CompleteRegistrationNotification::class);
        $response->assertRedirect(route('register.pending'));
    }

    public function test_user_can_complete_registration_from_the_email_link(): void
    {
        $token = str_repeat('a', 64);

        $pendingRegistration = PendingRegistration::query()->create([
            'email' => 'new-user@example.com',
            'email_hash' => EmailHasher::make('new-user@example.com'),
            'token_hash' => RegistrationTokenHasher::make($token),
            'expires_at' => now()->addHour(),
        ]);

        $completeUrl = (new CompleteRegistrationNotification($pendingRegistration, $token))->registrationUrl();

        $this->get($completeUrl)->assertOk();

        $finalizeUrl = URL::temporarySignedRoute(
            'register.finalize',
            $pendingRegistration->expires_at,
            [
                'pendingRegistration' => $pendingRegistration,
                'hash' => $pendingRegistration->email_hash,
                'token' => $token,
            ],
        );

        $response = $this->post($finalizeUrl, [
            'token' => $token,
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

    public function test_existing_user_email_gets_the_same_pending_response_without_a_registration_link(): void
    {
        Notification::fake();

        User::factory()->create([
            'email' => 'existing@example.com',
            'email_hash' => EmailHasher::make('existing@example.com'),
        ]);

        $response = $this->post('/register', [
            'email' => 'existing@example.com',
        ]);

        $this->assertDatabaseMissing('pending_registrations', [
            'email_hash' => EmailHasher::make('existing@example.com'),
        ]);
        Notification::assertNothingSent();
        $response->assertRedirect(route('register.pending'));
    }

    public function test_registration_cannot_be_finalized_without_a_signed_url(): void
    {
        $token = str_repeat('a', 64);

        $pendingRegistration = PendingRegistration::query()->create([
            'email' => 'new-user@example.com',
            'email_hash' => EmailHasher::make('new-user@example.com'),
            'token_hash' => RegistrationTokenHasher::make($token),
            'expires_at' => now()->addHour(),
        ]);

        $this->post("/register/complete/{$pendingRegistration->id}/{$pendingRegistration->email_hash}", [
            'token' => $token,
            'name' => 'Test User',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertForbidden();
    }
}
