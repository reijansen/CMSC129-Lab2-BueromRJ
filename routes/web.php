<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'supabase.guest'])->group(function () {
    Route::view('/', 'welcome')->name('landing');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendForgotPassword'])->name('password.email');
});

Route::middleware(['web', 'supabase.auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::resource('categories', CategoryController::class);
    Route::get('/budgets/trash', [BudgetController::class, 'trash'])->name('budgets.trash');
    Route::patch('/budgets/{id}/restore', [BudgetController::class, 'restore'])->name('budgets.restore');
    Route::delete('/budgets/{id}/force-delete', [BudgetController::class, 'forceDelete'])->name('budgets.force-delete');
    Route::resource('budgets', BudgetController::class);
    Route::get('/transactions/trash', [TransactionController::class, 'trash'])->name('transactions.trash');
    Route::patch('/transactions/{id}/restore', [TransactionController::class, 'restore'])->name('transactions.restore');
    Route::delete('/transactions/{id}/force-delete', [TransactionController::class, 'forceDelete'])->name('transactions.force-delete');
    Route::resource('transactions', TransactionController::class);
});
