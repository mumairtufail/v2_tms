<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CompanyScope;
use App\Http\Controllers\V2\DashboardController;

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
    Route::resource('users', \App\Http\Controllers\V2\CompanyUsersController::class);
    Route::post('users/{user}/toggle-status', [\App\Http\Controllers\V2\CompanyUsersController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // Roles & Permissions
    Route::resource('roles', \App\Http\Controllers\V2\RoleController::class);
    Route::put('roles/{role}/permissions', [\App\Http\Controllers\V2\RoleController::class, 'updatePermissions'])->name('roles.update-permissions');

    // Orders (placeholder)
    // Route::resource('orders', OrderController::class);

    // Manifests (placeholder)
    // Route::resource('manifests', ManifestController::class);

    // Customers
    Route::resource('customers', \App\Http\Controllers\V2\CustomerController::class);

    // Carriers
    Route::resource('carriers', \App\Http\Controllers\V2\CarrierController::class);

    // Drivers (placeholder)
    // Route::resource('drivers', DriverController::class);

    // Equipment
    Route::resource('equipment', \App\Http\Controllers\V2\EquipmentController::class);

    // Plugins
    Route::get('plugins', [\App\Http\Controllers\V2\PluginController::class, 'index'])->name('plugins.index');
    Route::post('plugins/toggle', [\App\Http\Controllers\V2\PluginController::class, 'toggle'])->name('plugins.toggle');
    Route::post('plugins/{slug}/install', [\App\Http\Controllers\V2\PluginController::class, 'install'])->name('plugins.install');
    Route::delete('plugins/{slug}/uninstall', [\App\Http\Controllers\V2\PluginController::class, 'uninstall'])->name('plugins.uninstall');
    Route::get('plugins/{slug}/settings', [\App\Http\Controllers\V2\PluginController::class, 'settings'])->name('plugins.settings');
    Route::post('plugins/{slug}/settings', [\App\Http\Controllers\V2\PluginController::class, 'updateSettings'])->name('plugins.settings.update');
    
    // OAuth
    Route::get('plugins/quickbooks/connect', [\App\Http\Controllers\V2\PluginController::class, 'connectQuickBooks'])->name('plugins.quickbooks.connect');
    Route::get('plugins/quickbooks/callback', [\App\Http\Controllers\V2\PluginController::class, 'callbackQuickBooks'])->name('plugins.quickbooks.callback');

    // Profile
    Route::get('profile', [\App\Http\Controllers\V2\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [\App\Http\Controllers\V2\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [\App\Http\Controllers\V2\ProfileController::class, 'destroy'])->name('profile.destroy');

    // Settings (placeholder)
    // Route::get('settings', [SettingsController::class, 'index'])->name('settings');
});
