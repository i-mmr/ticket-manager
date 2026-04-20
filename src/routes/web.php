<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\AccountController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketShowController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Landing'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/register/pending', [RegisterController::class, 'pending'])->name('register.pending');
    Route::get('/register/complete/{pendingRegistration}/{hash}', [RegisterController::class, 'complete'])
        ->middleware('signed')
        ->name('register.complete');
    Route::post('/register/complete/{pendingRegistration}', [RegisterController::class, 'finalize'])
        ->name('register.finalize');
});

Route::middleware('auth')->group(function () {
    Route::middleware('verified')->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/tickets/{ticket}', TicketShowController::class)->name('tickets.show');
    });

    Route::patch('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
    Route::delete('/account', [AccountController::class, 'destroy'])->name('account.destroy');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
