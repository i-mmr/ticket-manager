<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * ワークスペース画面の入口を担当する。
 *
 * `/workspaces` から使う。
 * ヘッダーやサイドメニューに必要な共通情報は Inertia の共有 props から受け取る。
 */
class WorkspaceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Workspaces/Index');
    }
}
