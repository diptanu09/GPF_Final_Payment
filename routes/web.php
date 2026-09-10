<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorityController;
use App\Http\Controllers\CalculationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\InwardCaseController;
use App\Http\Controllers\NomineeController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Public / Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// User Registration
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Password Recovery
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetToken'])->name('password.email');
Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Username Recovery
Route::get('/forgot-username', [AuthController::class, 'showForgotUsername'])->name('username.request');
Route::post('/forgot-username', [AuthController::class, 'recoverUsername'])->name('username.recover');

// Authenticated Application Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Inward Management
    Route::prefix('inward')->name('inward.')->group(function () {
        Route::get('/', [InwardCaseController::class, 'index'])->name('index');
        Route::get('/create', [InwardCaseController::class, 'create'])->name('create');
        Route::post('/', [InwardCaseController::class, 'store'])->name('store');
        Route::get('/lookup', [InwardCaseController::class, 'lookup'])->name('lookup');
        Route::get('/{id}', [InwardCaseController::class, 'show'])->name('show');
        Route::post('/{id}/assign', [InwardCaseController::class, 'assignStaff'])->name('assign');
    });

    // Calculation Engine & Ledgers
    Route::prefix('calculation')->name('calculation.')->group(function () {
        Route::get('/{caseId}', [CalculationController::class, 'show'])->name('show');
        Route::post('/{caseId}', [CalculationController::class, 'store'])->name('store');
    });

    // Nominees / Shareholders
    Route::prefix('nominees')->name('nominees.')->group(function () {
        Route::get('/{caseId}', [NomineeController::class, 'index'])->name('index');
        Route::post('/{caseId}', [NomineeController::class, 'sync'])->name('sync');
    });

    // Tiered Approvals (AAO / Sr. AO)
    Route::prefix('approval')->name('approval.')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::post('/{caseId}/check', [ApprovalController::class, 'check'])->name('check');
        Route::post('/{caseId}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{caseId}/revert', [ApprovalController::class, 'revert'])->name('revert');
    });

    // Payment Authorities & DSC Signing
    Route::prefix('authority')->name('authority.')->group(function () {
        Route::get('/', [AuthorityController::class, 'index'])->name('index');
        Route::post('/generate/{caseId}', [AuthorityController::class, 'generate'])->name('generate');
        Route::get('/{id}', [AuthorityController::class, 'show'])->name('show');
        Route::post('/{id}/sign', [AuthorityController::class, 'sign'])->name('sign');
        Route::get('/{id}/print', [AuthorityController::class, 'print'])->name('print');
        Route::get('/{id}/print-dlis', [AuthorityController::class, 'printDlis'])->name('print-dlis');
    });

    // Outward & HRMS Dispatch
    Route::prefix('dispatch')->name('dispatch.')->group(function () {
        Route::get('/', [DispatchController::class, 'index'])->name('index');
        Route::post('/{id}/hrms', [DispatchController::class, 'uploadHrms'])->name('hrms');
        Route::post('/{id}/dispatch', [DispatchController::class, 'dispatch'])->name('dispatch');
    });

    // MIS Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/settled', [ReportController::class, 'settledCases'])->name('settled');
        Route::get('/pending', [ReportController::class, 'pendingCases'])->name('pending');
    });
});
