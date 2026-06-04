<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
        
        $company = Auth::user()->company;
        if ($company) {
            return redirect()->route('v2.dashboard', ['company' => $company->slug]);
        }
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    if (Auth::user()->is_super_admin) {
        return redirect()->route('admin.dashboard');
    }
    
    $company = Auth::user()->company;
    if ($company) {
        return redirect()->route('v2.dashboard', ['company' => $company->slug]);
    }
    
    return redirect('/');
})->middleware(['auth', 'verified'])->name('dashboard');

// Global Admin Routes (Super Admin Only)
Route::middleware(['auth', 'verified', \App\Http\Middleware\IsSuperAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'index'])->name('dashboard');
    Route::get('/companies/generate-shortcode', [App\Http\Controllers\CompanyController::class, 'generateShortcode'])->name('companies.generate-shortcode');
    Route::resource('companies', App\Http\Controllers\CompanyController::class);
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::post('/users/{user}/impersonate', [App\Http\Controllers\Admin\UserController::class, 'impersonate'])->name('users.impersonate');
    Route::get('/logs', [App\Http\Controllers\HomeController::class, 'activity_logs'])->name('logs');
    Route::get('/settings', [App\Http\Controllers\Admin\SystemSettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/branding', [App\Http\Controllers\Admin\SystemSettingsController::class, 'branding'])->name('settings.branding');
    Route::put('/settings/branding', [App\Http\Controllers\Admin\SystemSettingsController::class, 'updateBranding'])->name('settings.branding.update');
    // Super admin profile — lives under /admin/profile
    Route::get('/profile', function () {
        return view('v2.admin.profile.index');
    })->name('profile');

    // 2FA setup confirm/cancel (controller-based, avoids Livewire state loss)
    Route::post('/2fa/confirm', [\App\Http\Controllers\Admin\TwoFactorSetupController::class, 'confirm'])->name('2fa.confirm');
    Route::post('/2fa/cancel', [\App\Http\Controllers\Admin\TwoFactorSetupController::class, 'cancel'])->name('2fa.cancel');
});

Route::get('/home', function () {
    return redirect('/');
});

// Stop impersonating — accessible by any authenticated user (the impersonated user)
Route::middleware('auth')->post('/admin/impersonate/stop', [App\Http\Controllers\Admin\UserController::class, 'stopImpersonating'])->name('admin.impersonate.stop');

// 2FA challenge — no auth middleware; user is logged out before redirected here
Route::get('/two-factor-challenge', \App\Livewire\Auth\TwoFactorChallenge::class)->name('two-factor.challenge');

require __DIR__.'/auth.php';

// Storage Fallback for environments where symlinks are disabled (e.g. Shared Hosting)
Route::get('storage/{path}', function ($path) {
    $fullPath = storage_path("app/public/" . $path);
    
    if (!file_exists($fullPath)) {
        abort(404);
    }

    return response()->file($fullPath);
})->where('path', '.*');
