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

    // Orders
    Route::get('orders', [\App\Http\Controllers\V2\OrderController::class, 'index'])->name('orders.index');
    Route::post('orders', [\App\Http\Controllers\V2\OrderController::class, 'store'])->name('orders.store');
    Route::get('orders/{order}/edit', [\App\Http\Controllers\V2\OrderController::class, 'edit'])->name('orders.edit');
    Route::patch('orders/{order}', [\App\Http\Controllers\V2\OrderController::class, 'update'])->name('orders.update');
    Route::delete('orders/{order}', [\App\Http\Controllers\V2\OrderController::class, 'destroy'])->name('orders.destroy');
    
    // Order specific actions
    Route::post('orders/{order}/sync-quickbooks', [\App\Http\Controllers\V2\OrderController::class, 'syncToQuickBooks'])->name('orders.sync-quickbooks');
    Route::get('orders/search-customers', [\App\Http\Controllers\V2\OrderController::class, 'searchCustomers'])->name('orders.search-customers');



    // Customers
    Route::resource('customers', \App\Http\Controllers\V2\CustomerController::class);

    // Carriers
    Route::resource('carriers', \App\Http\Controllers\V2\CarrierController::class);



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

    // Manifests
    Route::get('manifests', [\App\Http\Controllers\V2\ManifestController::class, 'index'])->name('manifests.index');
    Route::post('manifests', [\App\Http\Controllers\V2\ManifestController::class, 'store'])->name('manifests.store');
    Route::get('manifests/{manifest}/edit', [\App\Http\Controllers\V2\ManifestController::class, 'edit'])->name('manifests.edit');
    Route::patch('manifests/{manifest}', [\App\Http\Controllers\V2\ManifestController::class, 'update'])->name('manifests.update');
    Route::delete('manifests/{manifest}', [\App\Http\Controllers\V2\ManifestController::class, 'destroy'])->name('manifests.destroy');
    Route::post('manifests/quick-create', [\App\Http\Controllers\V2\ManifestController::class, 'quickCreate'])->name('manifests.quick-create');
    
    // Manifest Relations
    Route::post('manifests/{manifest}/drivers', [\App\Http\Controllers\V2\ManifestController::class, 'assignDriver'])->name('manifests.drivers.store');
    Route::delete('manifests/{manifest}/drivers/{driver}', [\App\Http\Controllers\V2\ManifestController::class, 'removeDriver'])->name('manifests.drivers.destroy');
    
    Route::post('manifests/{manifest}/carriers', [\App\Http\Controllers\V2\ManifestController::class, 'assignCarrier'])->name('manifests.carriers.store');
    Route::delete('manifests/{manifest}/carriers/{carrier}', [\App\Http\Controllers\V2\ManifestController::class, 'removeCarrier'])->name('manifests.carriers.destroy');
    
    Route::post('manifests/{manifest}/equipment', [\App\Http\Controllers\V2\ManifestController::class, 'assignEquipment'])->name('manifests.equipment.store');
    Route::delete('manifests/{manifest}/equipment/{equipment}', [\App\Http\Controllers\V2\ManifestController::class, 'removeEquipment'])->name('manifests.equipment.destroy');
    
    Route::post('manifests/{manifest}/stops', [\App\Http\Controllers\V2\ManifestController::class, 'addStop'])->name('manifests.stops.store');
    Route::delete('manifests/{manifest}/stops/{stop}', [\App\Http\Controllers\V2\ManifestController::class, 'removeStop'])->name('manifests.stops.destroy');
    
    // Manifest Modal API Endpoints
    Route::get('manifests/{manifest}/drivers/available', [\App\Http\Controllers\V2\ManifestController::class, 'availableDrivers'])->name('manifests.drivers.available');
    Route::post('manifests/{manifest}/drivers/sync', [\App\Http\Controllers\V2\ManifestController::class, 'syncDrivers'])->name('manifests.drivers.sync');
    
    Route::get('manifests/{manifest}/equipment/available', [\App\Http\Controllers\V2\ManifestController::class, 'availableEquipment'])->name('manifests.equipment.available');
    Route::post('manifests/{manifest}/equipment/sync', [\App\Http\Controllers\V2\ManifestController::class, 'syncEquipment'])->name('manifests.equipment.sync');
    
    Route::get('manifests/{manifest}/carriers/available', [\App\Http\Controllers\V2\ManifestController::class, 'availableCarriers'])->name('manifests.carriers.available');
    Route::post('manifests/{manifest}/carriers/sync', [\App\Http\Controllers\V2\ManifestController::class, 'syncCarriers'])->name('manifests.carriers.sync');
    Route::post('manifests/{manifest}/cost-estimates', [\App\Http\Controllers\V2\ManifestController::class, 'addCostEstimates'])->name('manifests.cost-estimates.store');

    // Profile
    Route::get('profile', [\App\Http\Controllers\V2\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [\App\Http\Controllers\V2\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [\App\Http\Controllers\V2\ProfileController::class, 'destroy'])->name('profile.destroy');



    // Bulk Actions
    Route::post('orders/bulk-delete', [\App\Http\Controllers\V2\OrderController::class, 'bulkDestroy'])->name('orders.bulk-destroy');
    Route::post('manifests/bulk-delete', [\App\Http\Controllers\V2\ManifestController::class, 'bulkDestroy'])->name('manifests.bulk-destroy');

});
