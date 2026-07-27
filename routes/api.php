<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Api\GooglePlacesController;
use App\Http\Controllers\Api\Driver\AuthController as DriverAuthController;
use App\Http\Controllers\Api\Driver\ManifestController as DriverManifestController;
use App\Http\Controllers\Api\Driver\OrderController as DriverOrderController;
use App\Http\Controllers\Api\Driver\AnalyticsController as DriverAnalyticsController;
use App\Http\Controllers\Api\Driver\ProfileController as DriverProfileController;

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



// Customer search API
Route::get('customers/search', [App\Http\Controllers\Api\CustomerController::class, 'search']);
Route::get('/customers/{customer}', [App\Http\Controllers\Api\CustomerController::class, 'viewcustomers']);

Route::prefix('google/places')->group(function () {
    Route::post('autocomplete', [GooglePlacesController::class, 'autocomplete']);
    Route::get('{placeId}', [GooglePlacesController::class, 'details']);
});

// Driver mobile app API
Route::prefix('driver')->name('driver.')->group(function () {
    Route::post('login', [DriverAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'driver.scope'])->group(function () {
        Route::post('logout', [DriverAuthController::class, 'logout']);
        Route::get('me', [DriverAuthController::class, 'me']);

        Route::get('manifests', [DriverManifestController::class, 'index']);
        Route::get('manifests/{manifest}', [DriverManifestController::class, 'show']);
        Route::post('manifests/{manifest}/start', [DriverManifestController::class, 'start']);
        Route::post('manifests/{manifest}/complete', [DriverManifestController::class, 'complete']);

        Route::get('orders/{order}', [DriverOrderController::class, 'show']);
        Route::post('orders/{order}/status', [DriverOrderController::class, 'updateStatus']);
        Route::post('orders/{order}/cancel', [DriverOrderController::class, 'cancel']);

        Route::get('analytics/overview', [DriverAnalyticsController::class, 'overview']);
        Route::get('analytics/status-breakdown', [DriverAnalyticsController::class, 'statusBreakdown']);
        Route::get('analytics/history', [DriverAnalyticsController::class, 'history']);

        Route::get('profile', [DriverProfileController::class, 'show']);
        Route::put('profile', [DriverProfileController::class, 'update']);
        Route::put('profile/password', [DriverProfileController::class, 'updatePassword']);
    });
});

