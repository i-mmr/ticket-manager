<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AccountDeletionFeedback;
use App\Support\EmailHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * ログイン済みユーザーのパスワード変更と退会処理を担当する。
 *
 * `/account/password` と `/account` から使う。
 * 退会時は退会理由を保存してからログアウトし、ユーザーを削除する。
 */
class AccountController extends Controller
{
    public function updatePassword(Request $request)
    {
        $attributes = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.current_password' => '現在のパスワードが正しくありません。',
        ]);

        $user = $request->user();
        $user->forceFill([
            'password' => Hash::make($attributes['password']),
        ])->save();

        return back()->with('status', 'password-updated');
    }

    public function destroy(Request $request)
    {
        $user = $request->user();

        $attributes = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        AccountDeletionFeedback::query()->create([
            'user_email' => $user->email,
            'user_email_hash' => EmailHasher::make($user->email),
            'user_name' => $user->name,
            'reason' => $attributes['reason'],
            'comment' => $attributes['comment'] ?? null,
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('landing')->with('status', 'account-deleted');
    }
}
