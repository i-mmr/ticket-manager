<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Notifications\CompleteRegistrationNotification;
use App\Support\EmailHasher;
use App\Support\RegistrationTokenHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 未ログインユーザーの仮登録、本登録完了、登録待ち画面を担当する。
 *
 * `/register`、`/register/pending`、`/register/complete/{pendingRegistration}/{hash}` から使う。
 * 登録完了メールの送信、期限付き署名 URL とトークン検証、ユーザー作成までを扱う。
 */
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
            return to_route('register.pending')->with([
                'status' => 'registration-link-sent',
                'registration_email' => $email,
            ]);
        }

        $token = Str::random(64);

        $pendingRegistration = PendingRegistration::query()->updateOrCreate([
            'email_hash' => $emailHash,
        ], [
            'email' => $email,
            'token_hash' => RegistrationTokenHasher::make($token),
            'expires_at' => now()->addMinutes(60),
        ]);

        Notification::route('mail', $pendingRegistration->email)
            ->notify(new CompleteRegistrationNotification($pendingRegistration, $token));

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

    public function complete(Request $request, PendingRegistration $pendingRegistration, string $hash): Response
    {
        $token = (string) $request->query('token', '');

        abort_unless(hash_equals($pendingRegistration->email_hash, $hash), 403);
        abort_if($pendingRegistration->expires_at->isPast(), 403);
        abort_unless(hash_equals($pendingRegistration->token_hash, RegistrationTokenHasher::make($token)), 403);

        return Inertia::render('Auth/RegisterComplete', [
            'pendingRegistration' => [
                'id' => $pendingRegistration->id,
                'email' => $pendingRegistration->email,
                'token' => $token,
            ],
            'finalizeUrl' => URL::temporarySignedRoute(
                'register.finalize',
                $pendingRegistration->expires_at,
                [
                    'pendingRegistration' => $pendingRegistration,
                    'hash' => $pendingRegistration->email_hash,
                    'token' => $token,
                ],
            ),
        ]);
    }

    public function finalize(Request $request, PendingRegistration $pendingRegistration, string $hash)
    {
        abort_unless(hash_equals($pendingRegistration->email_hash, $hash), 403);
        abort_if($pendingRegistration->expires_at->isPast(), 403);

        $attributes = $request->validate([
            'token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        abort_unless(hash_equals((string) $request->query('token', ''), $attributes['token']), 403);
        abort_unless(hash_equals($pendingRegistration->token_hash, RegistrationTokenHasher::make($attributes['token'])), 403);

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
