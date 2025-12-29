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
    
    // Dashboard - Everyone with view permission can access
    Route::middleware(['permission:dashboard,view'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    });

    // Users Management - Order matters! Specific routes before parameterized ones
    Route::middleware(['permission:users,create'])->group(function () {
        Route::get('users/create', [\App\Http\Controllers\V2\CompanyUsersController::class, 'create'])->name('users.create');
        Route::post('users', [\App\Http\Controllers\V2\CompanyUsersController::class, 'store'])->name('users.store');
    });
    Route::middleware(['permission:users,view'])->group(function () {
        Route::get('users', [\App\Http\Controllers\V2\CompanyUsersController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [\App\Http\Controllers\V2\CompanyUsersController::class, 'show'])->name('users.show');
    });
    Route::middleware(['permission:users,update'])->group(function () {
        Route::get('users/{user}/edit', [\App\Http\Controllers\V2\CompanyUsersController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [\App\Http\Controllers\V2\CompanyUsersController::class, 'update'])->name('users.update');
        Route::patch('users/{user}', [\App\Http\Controllers\V2\CompanyUsersController::class, 'update']);
        Route::post('users/{user}/toggle-status', [\App\Http\Controllers\V2\CompanyUsersController::class, 'toggleStatus'])->name('users.toggle-status');
    });
    Route::middleware(['permission:users,delete'])->group(function () {
        Route::delete('users/{user}', [\App\Http\Controllers\V2\CompanyUsersController::class, 'destroy'])->name('users.destroy');
    });
    
    // Roles & Permissions - Specific routes before parameterized
    Route::middleware(['permission:roles,create'])->group(function () {
        Route::get('roles/create', [\App\Http\Controllers\V2\RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [\App\Http\Controllers\V2\RoleController::class, 'store'])->name('roles.store');
    });
    Route::middleware(['permission:roles,view'])->group(function () {
        Route::get('roles', [\App\Http\Controllers\V2\RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}', [\App\Http\Controllers\V2\RoleController::class, 'show'])->name('roles.show');
    });
    Route::middleware(['permission:roles,update'])->group(function () {
        Route::get('roles/{role}/edit', [\App\Http\Controllers\V2\RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [\App\Http\Controllers\V2\RoleController::class, 'update'])->name('roles.update');
        Route::patch('roles/{role}', [\App\Http\Controllers\V2\RoleController::class, 'update']);
        Route::put('roles/{role}/permissions', [\App\Http\Controllers\V2\RoleController::class, 'updatePermissions'])->name('roles.update-permissions');
    });
    Route::middleware(['permission:roles,delete'])->group(function () {
        Route::delete('roles/{role}', [\App\Http\Controllers\V2\RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Orders
    Route::middleware(['permission:orders,view'])->group(function () {
        Route::get('orders', [\App\Http\Controllers\V2\OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}/edit', [\App\Http\Controllers\V2\OrderController::class, 'edit'])->name('orders.edit');
        Route::get('orders/search-customers', [\App\Http\Controllers\V2\OrderController::class, 'searchCustomers'])->name('orders.search-customers');
    });
    Route::middleware(['permission:orders,create'])->group(function () {
        Route::post('orders', [\App\Http\Controllers\V2\OrderController::class, 'store'])->name('orders.store');
    });
    Route::middleware(['permission:orders,update'])->group(function () {
        Route::patch('orders/{order}', [\App\Http\Controllers\V2\OrderController::class, 'update'])->name('orders.update');
        Route::post('orders/{order}/sync-quickbooks', [\App\Http\Controllers\V2\OrderController::class, 'syncToQuickBooks'])->name('orders.sync-quickbooks');
    });
    Route::middleware(['permission:orders,delete'])->group(function () {
        Route::delete('orders/{order}', [\App\Http\Controllers\V2\OrderController::class, 'destroy'])->name('orders.destroy');
        Route::post('orders/bulk-delete', [\App\Http\Controllers\V2\OrderController::class, 'bulkDestroy'])->name('orders.bulk-destroy');
    });

    // Customers - Specific routes before parameterized
    Route::middleware(['permission:customers,create'])->group(function () {
        Route::get('customers/create', [\App\Http\Controllers\V2\CustomerController::class, 'create'])->name('customers.create');
        Route::post('customers', [\App\Http\Controllers\V2\CustomerController::class, 'store'])->name('customers.store');
    });
    Route::middleware(['permission:customers,view'])->group(function () {
        Route::get('customers', [\App\Http\Controllers\V2\CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{customer}', [\App\Http\Controllers\V2\CustomerController::class, 'show'])->name('customers.show');
    });
    Route::middleware(['permission:customers,update'])->group(function () {
        Route::get('customers/{customer}/edit', [\App\Http\Controllers\V2\CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('customers/{customer}', [\App\Http\Controllers\V2\CustomerController::class, 'update'])->name('customers.update');
        Route::patch('customers/{customer}', [\App\Http\Controllers\V2\CustomerController::class, 'update']);
        Route::post('customers/{customer}/sync-quickbooks', [\App\Http\Controllers\V2\CustomerController::class, 'syncToQuickBooks'])->name('customers.sync-quickbooks');
    });
    Route::middleware(['permission:customers,delete'])->group(function () {
        Route::delete('customers/{customer}', [\App\Http\Controllers\V2\CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // Carriers - Specific routes before parameterized
    Route::middleware(['permission:carriers,create'])->group(function () {
        Route::get('carriers/create', [\App\Http\Controllers\V2\CarrierController::class, 'create'])->name('carriers.create');
        Route::post('carriers', [\App\Http\Controllers\V2\CarrierController::class, 'store'])->name('carriers.store');
    });
    Route::middleware(['permission:carriers,view'])->group(function () {
        Route::get('carriers', [\App\Http\Controllers\V2\CarrierController::class, 'index'])->name('carriers.index');
        Route::get('carriers/{carrier}', [\App\Http\Controllers\V2\CarrierController::class, 'show'])->name('carriers.show');
    });
    Route::middleware(['permission:carriers,update'])->group(function () {
        Route::get('carriers/{carrier}/edit', [\App\Http\Controllers\V2\CarrierController::class, 'edit'])->name('carriers.edit');
        Route::put('carriers/{carrier}', [\App\Http\Controllers\V2\CarrierController::class, 'update'])->name('carriers.update');
        Route::patch('carriers/{carrier}', [\App\Http\Controllers\V2\CarrierController::class, 'update']);
    });
    Route::middleware(['permission:carriers,delete'])->group(function () {
        Route::delete('carriers/{carrier}', [\App\Http\Controllers\V2\CarrierController::class, 'destroy'])->name('carriers.destroy');
    });

    // Equipment - Specific routes before parameterized
    Route::middleware(['permission:equipment,create'])->group(function () {
        Route::get('equipment/create', [\App\Http\Controllers\V2\EquipmentController::class, 'create'])->name('equipment.create');
        Route::post('equipment', [\App\Http\Controllers\V2\EquipmentController::class, 'store'])->name('equipment.store');
    });
    Route::middleware(['permission:equipment,view'])->group(function () {
        Route::get('equipment', [\App\Http\Controllers\V2\EquipmentController::class, 'index'])->name('equipment.index');
        Route::get('equipment/{equipment}', [\App\Http\Controllers\V2\EquipmentController::class, 'show'])->name('equipment.show');
    });
    Route::middleware(['permission:equipment,update'])->group(function () {
        Route::get('equipment/{equipment}/edit', [\App\Http\Controllers\V2\EquipmentController::class, 'edit'])->name('equipment.edit');
        Route::put('equipment/{equipment}', [\App\Http\Controllers\V2\EquipmentController::class, 'update'])->name('equipment.update');
        Route::patch('equipment/{equipment}', [\App\Http\Controllers\V2\EquipmentController::class, 'update']);
    });
    Route::middleware(['permission:equipment,delete'])->group(function () {
        Route::delete('equipment/{equipment}', [\App\Http\Controllers\V2\EquipmentController::class, 'destroy'])->name('equipment.destroy');
    });

    // Plugins - Require settings permission (typically admin only)
    Route::middleware(['permission:settings,view'])->group(function () {
        Route::get('plugins', [\App\Http\Controllers\V2\PluginController::class, 'index'])->name('plugins.index');
        Route::get('plugins/{slug}/settings', [\App\Http\Controllers\V2\PluginController::class, 'settings'])->name('plugins.settings');
    });
    Route::middleware(['permission:settings,update'])->group(function () {
        Route::post('plugins/toggle', [\App\Http\Controllers\V2\PluginController::class, 'toggle'])->name('plugins.toggle');
        Route::post('plugins/{slug}/install', [\App\Http\Controllers\V2\PluginController::class, 'install'])->name('plugins.install');
        Route::delete('plugins/{slug}/uninstall', [\App\Http\Controllers\V2\PluginController::class, 'uninstall'])->name('plugins.uninstall');
        Route::post('plugins/{slug}/settings', [\App\Http\Controllers\V2\PluginController::class, 'updateSettings'])->name('plugins.settings.update');
        Route::get('plugins/quickbooks/connect', [\App\Http\Controllers\V2\PluginController::class, 'connectQuickBooks'])->name('plugins.quickbooks.connect');
        Route::get('plugins/quickbooks/callback', [\App\Http\Controllers\V2\PluginController::class, 'callbackQuickBooks'])->name('plugins.quickbooks.callback');
    });

    // Manifests
    Route::middleware(['permission:manifests,view'])->group(function () {
        Route::get('manifests', [\App\Http\Controllers\V2\ManifestController::class, 'index'])->name('manifests.index');
        Route::get('manifests/{manifest}/edit', [\App\Http\Controllers\V2\ManifestController::class, 'edit'])->name('manifests.edit');
        Route::get('manifests/{manifest}/drivers/available', [\App\Http\Controllers\V2\ManifestController::class, 'availableDrivers'])->name('manifests.drivers.available');
        Route::get('manifests/{manifest}/equipment/available', [\App\Http\Controllers\V2\ManifestController::class, 'availableEquipment'])->name('manifests.equipment.available');
        Route::get('manifests/{manifest}/carriers/available', [\App\Http\Controllers\V2\ManifestController::class, 'availableCarriers'])->name('manifests.carriers.available');
    });
    Route::middleware(['permission:manifests,create'])->group(function () {
        Route::post('manifests', [\App\Http\Controllers\V2\ManifestController::class, 'store'])->name('manifests.store');
        Route::post('manifests/quick-create', [\App\Http\Controllers\V2\ManifestController::class, 'quickCreate'])->name('manifests.quick-create');
    });
    Route::middleware(['permission:manifests,update'])->group(function () {
        Route::patch('manifests/{manifest}', [\App\Http\Controllers\V2\ManifestController::class, 'update'])->name('manifests.update');
        Route::post('manifests/{manifest}/drivers', [\App\Http\Controllers\V2\ManifestController::class, 'assignDriver'])->name('manifests.drivers.store');
        Route::delete('manifests/{manifest}/drivers/{driver}', [\App\Http\Controllers\V2\ManifestController::class, 'removeDriver'])->name('manifests.drivers.destroy');
        Route::post('manifests/{manifest}/carriers', [\App\Http\Controllers\V2\ManifestController::class, 'assignCarrier'])->name('manifests.carriers.store');
        Route::delete('manifests/{manifest}/carriers/{carrier}', [\App\Http\Controllers\V2\ManifestController::class, 'removeCarrier'])->name('manifests.carriers.destroy');
        Route::post('manifests/{manifest}/equipment', [\App\Http\Controllers\V2\ManifestController::class, 'assignEquipment'])->name('manifests.equipment.store');
        Route::delete('manifests/{manifest}/equipment/{equipment}', [\App\Http\Controllers\V2\ManifestController::class, 'removeEquipment'])->name('manifests.equipment.destroy');
        Route::post('manifests/{manifest}/stops', [\App\Http\Controllers\V2\ManifestController::class, 'addStop'])->name('manifests.stops.store');
        Route::delete('manifests/{manifest}/stops/{stop}', [\App\Http\Controllers\V2\ManifestController::class, 'removeStop'])->name('manifests.stops.destroy');
        Route::post('manifests/{manifest}/drivers/sync', [\App\Http\Controllers\V2\ManifestController::class, 'syncDrivers'])->name('manifests.drivers.sync');
        Route::post('manifests/{manifest}/equipment/sync', [\App\Http\Controllers\V2\ManifestController::class, 'syncEquipment'])->name('manifests.equipment.sync');
        Route::post('manifests/{manifest}/carriers/sync', [\App\Http\Controllers\V2\ManifestController::class, 'syncCarriers'])->name('manifests.carriers.sync');
        Route::post('manifests/{manifest}/cost-estimates', [\App\Http\Controllers\V2\ManifestController::class, 'addCostEstimates'])->name('manifests.cost-estimates.store');
    });
    Route::middleware(['permission:manifests,delete'])->group(function () {
        Route::delete('manifests/{manifest}', [\App\Http\Controllers\V2\ManifestController::class, 'destroy'])->name('manifests.destroy');
        Route::post('manifests/bulk-delete', [\App\Http\Controllers\V2\ManifestController::class, 'bulkDestroy'])->name('manifests.bulk-destroy');
    });

    // Profile - All authenticated users can manage their own profile
    Route::get('profile', [\App\Http\Controllers\V2\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [\App\Http\Controllers\V2\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [\App\Http\Controllers\V2\ProfileController::class, 'destroy'])->name('profile.destroy');

});

