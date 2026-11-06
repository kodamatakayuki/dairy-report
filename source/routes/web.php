<?php

use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportReviewController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::middleware(['auth', 'employee.active'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::resource('reports', DailyReportController::class)
        ->except(['destroy'])
        ->parameters(['reports' => 'report']);

    Route::middleware('leader')->prefix('reviews')->name('reviews.')->group(function (): void {
        Route::get('/', [ReportReviewController::class, 'index'])->name('index');
        Route::post('/{report}/actions', [ReportReviewController::class, 'store'])->name('actions.store');
    });

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::resource('employees', EmployeeController::class)->except(['show']);
        Route::resource('organizations', OrganizationController::class)->except(['show']);
    });
});
