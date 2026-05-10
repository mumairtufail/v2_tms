<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Api\GooglePlacesController;

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

