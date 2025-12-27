<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->is_super_admin) {
            return redirect()->route('admin.dashboard');
        }
        
        $company = app('current.company') ?? Auth::user()->company;
        if ($company) {
            return redirect()->route('v2.dashboard', ['company' => $company->slug]);
        }
    }
    return view('welcome');
});

Route::get('/dashboard', function () {
    if (Auth::user()->is_super_admin) {
        return redirect()->route('admin.dashboard');
    }
    
    $company = app('current.company') ?? Auth::user()->company;
    if ($company) {
        return redirect()->route('v2.dashboard', ['company' => $company->slug]);
    }
    
    return redirect('/');
})->middleware(['auth', 'verified'])->name('dashboard');

// Global Admin Routes (Super Admin Only)
Route::middleware(['auth', 'verified', \App\Http\Middleware\IsSuperAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'index'])->name('dashboard');
    Route::resource('companies', App\Http\Controllers\CompanyController::class);
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::get('/logs', [App\Http\Controllers\HomeController::class, 'activity_logs'])->name('logs');
});

Route::get('/home', function () {
    return redirect('/');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
