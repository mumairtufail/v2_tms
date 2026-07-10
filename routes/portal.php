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
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::patch('settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
        Route::patch('settings/preferences', [SettingsController::class, 'updateSettings'])->name('settings.preferences.update');
        Route::get('profile', fn (Company $company) => redirect()->route('portal.settings', ['company' => $company->slug]))->name('profile');
    });
});
