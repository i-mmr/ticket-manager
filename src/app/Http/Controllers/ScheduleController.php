<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * スケジュール画面を担当する。
 *
 * `/schedule` から使う。
 * ヘッダーやサイドメニューに必要な共通情報は Inertia の共有 props から受け取る。
 */
class ScheduleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Schedule/Index');
    }
}
