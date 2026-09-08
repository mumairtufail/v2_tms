<?php

use App\Models\Company;
use App\Http\Controllers\Portal\AuthenticatedSessionController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\OrderController;
use App\Http\Controllers\Portal\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Portal Routes
|--------------------------------------------------------------------------
|
| Company-scoped customer portal: /{company}/portal/*
|
*/

Route::prefix('{company}/portal')->name('portal.')->middleware(['portal.company'])->group(function () {

    Route::middleware(['portal.guest'])->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);
    });

    Route::middleware(['auth:customer', 'portal.scope'])->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
        Route::get('orders/{order}/activity-logs', [OrderController::class, 'activityLogs'])->name('orders.activity-logs');
        Route::patch('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('notifications', [\App\Http\Controllers\Portal\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/read-all', [\App\Http\Controllers\Portal\NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('notifications/{notification}/read', [\App\Http\Controllers\Portal\NotificationController::class, 'markRead'])->name('notifications.read');
        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::patch('settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
        Route::patch('settings/preferences', [SettingsController::class, 'updateSettings'])->name('settings.preferences.update');
        Route::get('profile', fn (Company $company) => redirect()->route('portal.settings', ['company' => $company->slug]))->name('profile');
    });
});
