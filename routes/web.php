<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VcardTemplateController;
use App\Http\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/admin/{user}/verify-email/{hash}', [AdminController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('admin.email.verify');
Route::get('/organizations/{organization}/verify-owner-email/{hash}', [OrganizationController::class, 'verifyOwnerEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('organizations.owner.verify');
Route::get('/organizations/{organization}/verify-member-email/{member}/{hash}', [OrganizationController::class, 'verifyMemberEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('organizations.member.verify');

Route::middleware('guest:superadmin,web')->group(function (): void {
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

Route::middleware(['auth:superadmin,web', EnsureEmailIsVerified::class])->group(function (): void {
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
        Route::get('/vcard-templates/{vcard_template}/preview', [VcardTemplateController::class, 'preview'])->name('vcard-templates.preview');
        Route::patch('/organizations/{organization}/status', [OrganizationController::class, 'toggleStatus'])->name('organizations.status');
        Route::post('/organizations/{organization}/verification/resend', [OrganizationController::class, 'resendVerification'])->name('organizations.verification.resend');
        Route::patch('/organizations/{organization}/owner/password', [OrganizationController::class, 'updateOwnerPassword'])->name('organizations.owner.password');
        Route::resource('organizations', OrganizationController::class)->except(['show']);
    });

    Route::get('/organizations/my-organization', [OrganizationController::class, 'mine'])->name('organizations.mine');
    Route::patch('/organizations/{organization}/logo', [OrganizationController::class, 'updateLogo'])->name('organizations.logo');
    Route::patch('/organizations/{organization}/template', [OrganizationController::class, 'updateTemplate'])->name('organizations.template');
    Route::get('/organizations/{organization}/users/create', [OrganizationController::class, 'createUser'])->name('organizations.users.create');
    Route::post('/organizations/{organization}/users', [OrganizationController::class, 'storeUser'])->name('organizations.users.store');
    Route::get('/organizations/{organization}/users/{member}/edit', [OrganizationController::class, 'editUser'])->name('organizations.users.edit');
    Route::patch('/organizations/{organization}/users/{member}', [OrganizationController::class, 'updateUser'])->name('organizations.users.update');
    Route::patch('/organizations/{organization}/users/{member}/status', [OrganizationController::class, 'toggleUserStatus'])->name('organizations.users.status');
    Route::delete('/organizations/{organization}/users/{member}', [OrganizationController::class, 'destroyUser'])->name('organizations.users.destroy');
    Route::get('/organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
    Route::get('/vcards', [SectionController::class, 'vcards'])->name('vcards.index');
});
