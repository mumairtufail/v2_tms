<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CompanyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('orders', [OrderController::class, 'getOrders'])->name('orders.list');
Route::get('column-preferences', [OrderController::class, 'getColumnPreferences']);
Route::post('column-preferences', [OrderController::class, 'saveColumnPreferences']);

// Customer search API
Route::get('customers/search', [App\Http\Controllers\Api\CustomerController::class, 'search']);

// Manifest routes moved to web.php for proper authentication

Route::get('/customers/{customer}', [App\Http\Controllers\Api\CustomerController::class, 'viewcustomers']);

// Customer search for order creation
// Route::post('/customers/search', [App\Http\Controllers\CustomerController::class, 'searchCustomers']);


   // Dashboard API Routes for real-time updates
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/stats-api', [CompanyController::class, 'getStatsApi'])->name('stats.api');
        Route::get('/chart-data-api', [CompanyController::class, 'getChartDataApi'])->name('chart.api');
        Route::get('/order-status-api', [CompanyController::class, 'getOrderStatusApi'])->name('order-status.api');
        Route::get('/equipment-status-api', [CompanyController::class, 'getEquipmentStatusApi'])->name('equipment.api');
        Route::get('/refresh', [CompanyController::class, 'refreshDashboard'])->name('refresh');
    });
