<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * プロジェクト画面を担当する。
 *
 * `/projects` から使う。
 * ヘッダーやサイドメニューに必要な共通情報は Inertia の共有 props から受け取る。
 */
class ProjectController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Projects/Index');
    }
}
