<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VcardTemplateController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/login/verify', [AuthController::class, 'showOtp'])->name('otp.show');
    Route::post('/login/verify', [AuthController::class, 'verifyOtp'])->name('otp.verify');
    Route::post('/login/verify/resend', [AuthController::class, 'resendOtp'])->name('otp.resend');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
    Route::put('/account/password', [AccountController::class, 'changePassword'])->name('account.password');

    Route::middleware('role:superadmin')->group(function (): void {
        Route::resource('admin', AdminController::class)->parameters(['admin' => 'user']);
        Route::patch('/admin/{user}/status', [AdminController::class, 'toggleStatus'])->name('admin.status');
        Route::get('/users/{user}/role', [UserController::class, 'editRole'])->name('users.role.edit');
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role.update');
        Route::patch('/users/{user}/verification', [UserController::class, 'toggleVerification'])->name('users.verification');
        Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.status');
        Route::resource('users', UserController::class);
        Route::patch('/plans/{plan}/status', [PlanController::class, 'toggleStatus'])->name('plans.status');
        Route::patch('/plans/{plan}/default', [PlanController::class, 'setDefault'])->name('plans.default');
        Route::resource('plans', PlanController::class);
        Route::patch('/taxes/{tax}/status', [TaxController::class, 'toggleStatus'])->name('taxes.status');
        Route::resource('taxes', TaxController::class)->except(['show']);
        Route::patch('/vcard-templates/{vcard_template}/status', [VcardTemplateController::class, 'toggleStatus'])->name('vcard-templates.status');
        Route::resource('vcard-templates', VcardTemplateController::class)->except(['show']);
    });

    Route::get('/organizations', [SectionController::class, 'organizations'])->name('organizations.index');
    Route::get('/vcards', [SectionController::class, 'vcards'])->name('vcards.index');
});
