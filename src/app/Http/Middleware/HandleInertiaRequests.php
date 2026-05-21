<?php

namespace App\Http\Middleware;

use App\Support\WorkspaceNavigation;
use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Inertia の全画面共通 props を定義する。
 *
 * ヘッダーとサイドメニューで使うログインユーザー情報とワークスペース階層を共有する。
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'currentUser' => fn () => $user ? [
                'name' => $user->name,
                'email' => $user->email,
            ] : null,
            'workspaces' => fn () => $user ? WorkspaceNavigation::forUser($user) : [],
        ];
    }
}
