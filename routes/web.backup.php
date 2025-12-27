<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\CompanyUserController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolesController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\StopController;
use App\Http\Controllers\ManifestEquipmentController;
use App\Http\Controllers\ManifestDriverController;
use App\Http\Controllers\ManifestCarrierController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('auth')->group(function () {

        // Plugin Routes
    Route::get('/plugins', [App\Http\Controllers\PluginController::class, 'index'])->name('plugins.index');
    Route::post('/plugins/{slug}/activate', [App\Http\Controllers\PluginController::class, 'activate'])->name('plugins.activate');
    Route::post('/plugins/{slug}/deactivate', [App\Http\Controllers\PluginController::class, 'deactivate'])->name('plugins.deactivate');
    Route::delete('/plugins/{slug}/uninstall', [App\Http\Controllers\PluginController::class, 'uninstall'])->name('plugins.uninstall');

    // Plugin Configuration Routes
    Route::get('/plugins/{slug}/settings', [App\Http\Controllers\PluginController::class, 'settings'])->name('plugins.settings');
    Route::post('/plugins/{slug}/settings', [App\Http\Controllers\PluginController::class, 'storeSettings'])->name('plugins.settings.store');
    Route::put('/plugins/{slug}/settings/{id}', [App\Http\Controllers\PluginController::class, 'updateSettings'])->name('plugins.settings.update');
    Route::delete('/plugins/{slug}/settings/{id}', [App\Http\Controllers\PluginController::class, 'destroySettings'])->name('plugins.settings.destroy');

    // QuickBooks OAuth Routes
    Route::get('/plugins/quickbooks/connect', [App\Http\Controllers\PluginController::class, 'connectQuickBooks'])->name('plugins.quickbooks.connect');
    Route::get('/plugins/quickbooks/callback', [App\Http\Controllers\PluginController::class, 'callbackQuickBooks'])->name('plugins.quickbooks.callback');

    
    // Route::middleware(['auth', 'permission:users,create'])->group(function () {
        Route::get('admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('company/dashboard', [DashboardController::class, 'index'])->name('company.dashboard');

    // Route::controller(AdminController::class)->group(function () {
    //     Route::get('admin/dashboard', 'index')->name('admin.dashboard');
    // });

    // Route::controller(DashboardController::class)->group(function () {
    //     Route::get('company/dashboard', 'index')->name('company.dashboard');
    // });

    Route::middleware('is_company')->group(function () {

        Route::get('/manifest', [ManifestController::class, 'index'])->name('manifest.index');
        Route::get('/manifest/create', [ManifestController::class, 'create'])->name('manifest.create');
        Route::post('/manifest/store', [ManifestController::class, 'store'])->name('manifest.store');

        Route::get('/manifest/{id}', [ManifestController::class, 'ViewEdit'])->name('manifest.edit');
        Route::put('/manifest/{id}', [ManifestController::class, 'update'])->name('manifest.update');
        Route::delete('/manifest/{id}', [ManifestController::class, 'destroy'])->name('manifest.destroy');

        // New equipment assignment routes
        Route::get('/manifest/{id}/equipment', [ManifestEquipmentController::class, 'getEquipment'])->name('manifest.equipment');
        Route::post('/manifest/{id}/equipment', [ManifestEquipmentController::class, 'assignEquipment'])->name('manifest.assign.equipment');

        // New driver assignment routes
        Route::get('/manifest/{id}/drivers', [ManifestDriverController::class, 'getDrivers'])->name('manifest.drivers');
        Route::post('/manifest/{id}/drivers', [ManifestDriverController::class, 'assignDrivers'])->name('manifest.assign.drivers');

        // Routes for manifest carriers
        Route::get('/manifest/{manifestId}/carriers', [ManifestCarrierController::class, 'getCarriers'])
            ->name('manifest.carriers.get');
        Route::post('/manifest/{manifestId}/carriers', [ManifestCarrierController::class, 'assignCarriers'])
            ->name('manifest.carriers.assign');

        // Add route for storing tasks (assigning task to a manifest)
        Route::post('/manifest/{id}/tasks', [\App\Http\Controllers\ManifestTaskController::class, 'store'])
            ->name('manifest.tasks.store');

        // Add routes for updating and deleting tasks
        Route::put('/manifest/{id}/tasks/{taskId}', [App\Http\Controllers\ManifestTaskController::class, 'update'])
            ->name('manifest.tasks.update');
        Route::delete('/manifest/tasks/{taskId}', [App\Http\Controllers\ManifestTaskController::class, 'destroy'])
            ->name('manifest.tasks.destroy');

        // Manifest document upload route
        Route::post('/manifest/{id}/document', [ManifestController::class, 'uploadDocument'])->name('manifest.document');
        // Route::controller(CompanyUserController::class)->group(function () {

        //     Route::get('/users/create', 'Viewcreate')->name('users.create');
        // });
        Route::controller(CompanyUserController::class)->group(function () {
            Route::get('/users', 'index')->name('users.index');
            Route::get('/users/create', 'Viewcreate')->name('users.create');
            Route::post('/users', 'store')->name('users.store');
            Route::get('/users/{id}/edit', 'edit')->name('users.edit');
            Route::put('/users/{id}', 'update')->name('users.update');
            Route::delete('/users/{id}', 'destroy')->name('users.destroy');
        });
        Route::controller(RolesController::class)->group(function () {
            Route::get('/roles', [RolesController::class, 'view'])->name('roles.index');
            Route::post('/roles', [RolesController::class, 'store'])->name('roles.store');
            Route::put('/roles/{id}', [RolesController::class, 'update'])->name('roles.update');
            Route::delete('/roles/{id}', [RolesController::class, 'destroy'])->name('roles.destroy');
            Route::get('/roles/{id}/permissions', [RolesController::class, 'getPermissions']);
            Route::post('/roles/{id}/permissions', [RolesController::class, 'updatePermissions']);
        });
        Route::controller(CustomerController::class)->group(function () {
            Route::get('/customers', 'index')->name('customers.index');
            Route::get('/customers/create', 'create')->name('customers.create');
            Route::post('/customers', 'store')->name('customers.store');
            Route::get('/customers/{id}/edit', 'edit')->name('customers.edit');
            Route::put('/customers/{id}', 'update')->name('customers.update');
            Route::delete('/customers/{id}', 'destroy')->name('customers.destroy');
        });
        Route::post('api/customers/search', [App\Http\Controllers\CustomerController::class, 'searchCustomers']);


        Route::controller(EquipmentController::class)->group(function () {
            Route::get('/equipment', 'index')->name('equipment.index');
            Route::get('/equipment/create', 'create')->name('equipment.create');
            Route::post('/equipment/store', 'store')->name('equipment.store');
            Route::get('/equipment/{id}/edit', 'edit')->name('equipment.edit');
            Route::put('/equipment/{id}', 'update')->name('equipment.update');
            Route::delete('/equipment/{id}', 'destroy')->name('equipment.destroy');
        });

        Route::POST('/cost-estimates/create', [App\Http\Controllers\ManifestController::class, 'storeCostEstimates'])->name('cost-estimates.store');
        Route::put('/cost-estimates/{manifest_id}', [App\Http\Controllers\CostEstimateController::class, 'update'])->name('cost-estimates.update');

        Route::controller(App\Http\Controllers\CarrierController::class)->group(function () {
            Route::get('/carriers', 'index')->name('carriers.index');
            Route::get('/carriers/create', 'create')->name('carriers.create');
            Route::post('/carriers', 'store')->name('carriers.store');
            Route::get('/carriers/{id}/edit', 'edit')->name('carriers.edit');
            Route::put('/carriers/{id}', 'update')->name('carriers.update');
            Route::delete('/carriers/{id}', 'destroy')->name('carriers.destroy');
        });

        // Stop routes
        Route::post('/stops/create', [StopController::class, 'createStop'])->name('stops.createStop');
        Route::put('/stops/{id}', [StopController::class, 'update'])->name('stops.update');
        Route::delete('/stops/{id}', [StopController::class, 'destroy'])->name('stops.destroy');
    });

    Route::middleware('is_admin')->group(function () {
        Route::controller(CompanyUserController::class)->group(function () {
            Route::get('/company', 'index')->name('company.index');
            Route::get('/company/create', 'Viewcreate')->name('company.create');
            Route::post('/company', 'store')->name('company.store');
            Route::get('/company/{id}/edit', 'edit')->name('company.edit');
            Route::put('/company/{id}', 'update')->name('company.update');
            Route::delete('/company/{id}', 'destroy')->name('company.destroy');
        });
    });

    // Remove or comment out existing orders routes
    // Route::get('/view-orders', [OrderController::class, 'index'])->name('orders.index');


    // Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
    Route::get('/activity/logs', [App\Http\Controllers\HomeController::class, 'activity_logs'])->name('logs');
    Route::get('/activity/logs/{id}', [App\Http\Controllers\HomeController::class, 'viewLogs'])->name('logs.view');

    Route::POST('/stops/create', [App\Http\Controllers\StopController::class, 'createStop'])->name('stops.createStop');

    Route::POST('/cost-estimates/create', [App\Http\Controllers\ManifestController::class, 'storeCostEstimates'])->name('cost-estimates.store');
    Route::put('/cost-estimates/{manifest_id}', [App\Http\Controllers\CostEstimateController::class, 'update'])->name('cost-estimates.update');

 Route::controller(OrderController::class)->prefix('orders')->as('orders.')->group(function () {
        
        // GET /dashboard/orders
        // Shows the list of all orders.
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        // GET /dashboard/orders/create
        // Creates a new draft order and redirects to the edit page.
        Route::get('/create', 'create')->name('create');

        // GET /dashboard/orders/{order}/edit
        // Shows the main edit page with the Point-to-Point and Single Shipper tabs.
        Route::get('/{order}/edit', 'edit')->name('edit');

        // PUT /dashboard/orders/{order}
        // This is the SINGLE endpoint for updating an order. It handles submissions
        // from BOTH the Point-to-Point and Single Shipper forms.
        Route::put('/{order}', 'update')->name('update');
        
        // DELETE /dashboard/orders/{order}
        // Deletes an order.
        Route::delete('/{order}', 'destroy')->name('destroy');
        
        // You would also have these routes for a full CRUD setup
        // POST   /dashboard/orders         - (store) - Not needed with our create->edit flow
        // GET    /dashboard/orders/{order} - (show) - If you have a separate "view" page
        Route::post('/{order}/save-single-shipper', 'saveSingleShipper')->name('saveSingleShipper');
        Route::post('/{order}/save-single-consignee', 'saveSingleConsignee')->name('saveSingleConsignee');
        
        // Customer and Manifest assignment
        Route::post('/{order}/assign-customer', 'assignCustomer')->name('assignCustomer');
        Route::post('/{order}/assign-manifest', 'assignManifest')->name('assignManifest');
        Route::post('/{order}/save-quote', 'saveQuote')->name('saveQuote');
        Route::post('/{order}/sync-quickbooks', 'syncToQuickBooks')->name('syncQuickBooks');

    
});
        // OrderStop routes
        Route::post('/orders/{order}/stops', [App\Http\Controllers\OrderStopController::class, 'store'])->name('orders.stops.store');
        Route::put('/orders/{order}/stops/{stop}', [App\Http\Controllers\OrderStopController::class, 'update'])->name('orders.stops.update');
        Route::delete('/orders/{order}/stops/{stop}', [App\Http\Controllers\OrderStopController::class, 'destroy'])->name('orders.stops.destroy');
        Route::post('/orders/{order}/stops/{stop}/shippers', [App\Http\Controllers\OrderStopController::class, 'addShipper'])->name('orders.stops.addShipper');
        Route::post('/orders/{order}/stops/{stop}/consignees', [App\Http\Controllers\OrderStopController::class, 'addConsignee'])->name('orders.stops.addConsignee');
        Route::post('/orders/{order}/stops/{stop}/commodities', [App\Http\Controllers\OrderStopController::class, 'addCommodity'])->name('orders.stops.addCommodity');
        Route::post('/orders/{order}/stops/{stop}/sync-accessorials', [App\Http\Controllers\OrderStopController::class, 'syncAccessorials'])->name('orders.stops.syncAccessorials');
        
        // Geocoding routes
        Route::post('/geocode/search', [OrderController::class, 'geocodeAddress'])->name('geocode.search');
        Route::post('/geocode/reverse', [OrderController::class, 'reverseGeocode'])->name('geocode.reverse');
Route::get('/orders/create/{order?}', [OrderController::class, 'createOrEdit'])->name('orders.form');
// Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');


    // Order Routes
    // Additional order routes
    Route::prefix('orders')->name('orders.')->group(function () {
        // Update column preferences
        Route::post('/update-columns', [OrderController::class, 'updateColumns'])->name('update-columns');

        // Update order status
        Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->name('update-status');

        // Download document
        Route::get('/documents/{document}/download', [OrderController::class, 'downloadDocument'])->name('download-document');

        // API route for fetching customer data
        Route::get('/api/customers/{customer}', [OrderController::class, 'getCustomer'])->name('get-customer');
    });
    
    Route::prefix('companies')->name('companies.')->group(function () {
        Route::get('/', [CompanyController::class, 'index'])->name('index');
        Route::get('/create', [CompanyController::class, 'create'])->name('create');
        Route::post('/', [CompanyController::class, 'store'])->name('store');
        Route::get('/{company}', [CompanyController::class, 'show'])->name('show');
        Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
        Route::put('/{company}', [CompanyController::class, 'update'])->name('update');
        Route::delete('/{id}', [CompanyController::class, 'destroy'])->name('destroy');
    });

    // Profile routes
    Route::controller(App\Http\Controllers\ProfileController::class)->group(function () {
        Route::get('/profile', 'show')->name('profile.show');
        Route::get('/profile/edit', 'edit')->name('profile.edit');
        Route::put('/profile', 'update')->name('profile.update');
        Route::post('/profile/avatar', 'updateAvatar')->name('profile.avatar');
    });

    // Manifest API routes (moved from api.php for proper authentication)
    Route::prefix('api')->group(function () {
        Route::get('/manifests', [App\Http\Controllers\Api\ManifestController::class, 'index']);
        Route::get('/manifests/search', [App\Http\Controllers\Api\ManifestController::class, 'search']);
                Route::post('/manifests/quick-create', [App\Http\Controllers\ManifestController::class, 'quickCreate'])->name('manifests.quick-create');

    });
});


Auth::routes();

// Redirect to login if the route is not found
Route::fallback(function () {
    return redirect()->route('login');
});

Route::view('/home', 'welcome');
