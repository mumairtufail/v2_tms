<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CompanyScope;
use App\Http\Controllers\V2\DashboardController;
use App\Http\Controllers\V2\UserController;

/*
|--------------------------------------------------------------------------
| V2 Routes
|--------------------------------------------------------------------------
|
| V2 routes with company-based multi-tenancy
| Pattern: /{company}/module/action
|
*/

Route::middleware(['auth', CompanyScope::class])->prefix('{company}')->name('v2.')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Users Management
    Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Orders (placeholder)
    // Route::resource('orders', OrderController::class);

    // Manifests (placeholder)
    // Route::resource('manifests', ManifestController::class);

    // Customers (placeholder)
    // Route::resource('customers', CustomerController::class);

    // Carriers (placeholder)
    // Route::resource('carriers', CarrierController::class);

    // Drivers (placeholder)
    // Route::resource('drivers', DriverController::class);

    // Equipment (placeholder)
    // Route::resource('equipment', EquipmentController::class);

    // Settings (placeholder)
    // Route::get('settings', [SettingsController::class, 'index'])->name('settings');
});
