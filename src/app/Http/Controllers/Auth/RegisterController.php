<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Notifications\CompleteRegistrationNotification;
use App\Support\EmailHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);
        $email = EmailHasher::normalize($attributes['email']);
        $emailHash = EmailHasher::make($email);

        if (User::query()->where('email_hash', $emailHash)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'このメールアドレスはすでに登録されています。',
            ]);
        }

        $pendingRegistration = PendingRegistration::query()->updateOrCreate([
            'email_hash' => $emailHash,
        ], [
            'email' => $email,
            'token' => Str::random(64),
            'expires_at' => now()->addMinutes(60),
        ]);

        Notification::route('mail', $pendingRegistration->email)
            ->notify(new CompleteRegistrationNotification($pendingRegistration));

        return to_route('register.pending')->with([
            'status' => 'registration-link-sent',
            'registration_email' => $pendingRegistration->email,
        ]);
    }

    public function pending(Request $request): Response
    {
        return Inertia::render('Auth/RegisterPending', [
            'status' => session('status'),
            'email' => session('registration_email'),
        ]);
    }

    public function complete(PendingRegistration $pendingRegistration, string $hash): Response
    {
        abort_unless(hash_equals($pendingRegistration->email_hash, $hash), 403);
        abort_if($pendingRegistration->expires_at->isPast(), 403);

        return Inertia::render('Auth/RegisterComplete', [
            'pendingRegistration' => [
                'id' => $pendingRegistration->id,
                'email' => $pendingRegistration->email,
                'token' => $pendingRegistration->token,
            ],
        ]);
    }

    public function finalize(Request $request, PendingRegistration $pendingRegistration)
    {
        $attributes = $request->validate([
            'token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        abort_unless(hash_equals($pendingRegistration->token, $attributes['token']), 403);
        abort_if($pendingRegistration->expires_at->isPast(), 403);

        $user = User::query()->create([
            'name' => $attributes['name'],
            'email' => $pendingRegistration->email,
            'email_hash' => $pendingRegistration->email_hash,
            'email_verified_at' => now(),
            'password' => Hash::make($attributes['password']),
        ]);

        $pendingRegistration->delete();

        Auth::login($user);

        return to_route('dashboard');
    }
}
