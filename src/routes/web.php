<?php

use App\Http\Controllers\Auth\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketShowController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 公開トップページ。未ログインユーザー向けの導線を表示する。
Route::get('/', fn () => Inertia::render('Landing'))->name('landing');

// 未ログインユーザー向けのログイン・仮登録・本登録フロー。
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:register')
        ->name('register.store');
    Route::get('/register/pending', [RegisterController::class, 'pending'])->name('register.pending');
    Route::get('/register/complete/{pendingRegistration}/{hash}', [RegisterController::class, 'complete'])
        ->middleware('signed')
        ->name('register.complete');
    Route::post('/register/complete/{pendingRegistration}/{hash}', [RegisterController::class, 'finalize'])
        ->middleware(['signed', 'throttle:register-complete'])
        ->name('register.finalize');
});

// ログイン済みユーザー向けのチケット管理とアカウント操作。
Route::middleware('auth')->group(function () {
    // メール確認済みユーザーだけが利用できるダッシュボードとチケット操作。
    Route::middleware('verified')->group(function () {
        Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
        Route::get('/home', [HomeController::class, 'index'])->name('home');
        Route::get('/workspaces', [WorkspaceController::class, 'index'])->name('workspaces.index');
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
        Route::get('/tickets/{ticket}', TicketShowController::class)->name('tickets.show');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
        Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
        Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');
    });

    // メール確認前でも実行できるパスワード変更・退会・ログアウト操作。
    Route::patch('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
    Route::delete('/account', [AccountController::class, 'destroy'])->name('account.destroy');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
