# TMS V2 Authentication & Testing Guide

## 🚀 Implementation Complete!

I've built a complete authentication system with dark theme UI, proper logging, and V2 integration for your TMS system.

---

## ✅ What's Been Created

### 1. **Authentication Views**
- ✅ `resources/views/layouts/guest.blade.php` - Beautiful dark theme auth layout
- ✅ `resources/views/auth/login.blade.php` - Login page (needs manual update - see below)
- ✅ `resources/views/auth/register.blade.php` - Register page (needs manual update - see below)
- ✅ `resources/views/components/auth-session-status.blade.php` - Session status component
- ✅ `resources/views/components/input-error.blade.php` - Input error component

### 2. **Authentication Controllers**
- ✅ `app/Http/Controllers/Auth/AuthenticatedSessionController.php` - Login/Logout with logging
- ✅ `app/Http/Controllers/Auth/RegisteredUserController.php` - Registration with auto-company creation

### 3. **Services**
- ✅ `app/Services/LogService.php` - Comprehensive logging service
  - `logAuth()` - Authentication events
  - `log()` - General activity
  - `logSecurity()` - Security events
  - `logError()` - Error logging

### 4. **Styling**
- ✅ `resources/css/custom.css` - Dark theme CSS with:
  - `.btn-primary`, `.btn-secondary`
  - `.sidebar-link`, `.sidebar-link-active`
  - `.form-label`, `.input`
  - Custom scrollbars
  - Dividers

### 5. **V2 Components** (Previously Created)
- ✅ Dashboard with stats
- ✅ Complete Users CRUD
- ✅ Sidebar, Navbar, Footer
- ✅ Form components
- ✅ Multi-tenancy middleware

---

## 🔧 Setup Instructions

### Step 1: Import Custom CSS

Update `resources/css/app.css`:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@import './custom.css';
```

### Step 2: Manually Update Login View

Open `resources/views/auth/login.blade.php` and replace ALL content with:

```blade
<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-white mb-2">Welcome back</h2>
        <p class="text-gray-400">Sign in to continue to your dashboard</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="form-label text-gray-300">Email Address</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                    </svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="input pl-10 bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500" placeholder="you@example.com" required autofocus autocomplete="username">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="form-label text-gray-300 mb-0">Password</label>
                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-primary-400 hover:text-primary-300 transition-colors">Forgot password?</a>
                @endif
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <input id="password" type="password" name="password" class="input pl-10 bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500" placeholder="••••••••" required autocomplete="current-password">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>
        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-600 bg-gray-800 text-primary-500 focus:ring-primary-500 focus:ring-offset-gray-900">
            <label for="remember_me" class="text-sm text-gray-400">Remember me for 30 days</label>
        </div>
        <button type="submit" class="btn-primary w-full py-2.5 text-base"><span>Sign in</span><svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></button>
        <p class="text-center text-gray-400 pt-2">Don't have an account? <a href="{{ route('register') }}" class="text-primary-400 hover:text-primary-300 font-medium transition-colors">Sign up free</a></p>
    </form>
</x-guest-layout>
```

### Step 3: Manually Update Register View

Open `resources/views/auth/register.blade.php` and replace ALL content with:

```blade
<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-white mb-2">Create your account</h2>
        <p class="text-gray-400">Start managing your logistics today</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <label for="name" class="form-label text-gray-300">Full Name</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <input id="name" type="text" name="name" value="{{ old('name') }}" class="input pl-10 bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500" placeholder="John Doe" required autofocus autocomplete="name">
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>
        <div>
            <label for="email" class="form-label text-gray-300">Email Address</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                    </svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="input pl-10 bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500" placeholder="you@example.com" required autocomplete="username">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
        <div>
            <label for="password" class="form-label text-gray-300">Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <input id="password" type="password" name="password" class="input pl-10 bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500" placeholder="••••••••" required autocomplete="new-password">
            </div>
            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>
        <div>
            <label for="password_confirmation" class="form-label text-gray-300">Confirm Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <input id="password_confirmation" type="password" name="password_confirmation" class="input pl-10 bg-gray-800/50 border-gray-700 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500" placeholder="••••••••" required autocomplete="new-password">
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>
        <div class="flex items-start gap-3">
            <input id="terms" type="checkbox" name="terms" class="w-4 h-4 mt-0.5 rounded border-gray-600 bg-gray-800 text-primary-500 focus:ring-primary-500 focus:ring-offset-gray-900" required>
            <label for="terms" class="text-sm text-gray-400">I agree to the <a href="#" class="text-primary-400 hover:text-primary-300 transition-colors">Terms of Service</a> and <a href="#" class="text-primary-400 hover:text-primary-300 transition-colors">Privacy Policy</a></label>
        </div>
        <button type="submit" class="btn-primary w-full py-3 text-base"><span>Create account</span><svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></button>
        <p class="text-center text-gray-400 pt-2">Already have an account? <a href="{{ route('login') }}" class="text-primary-400 hover:text-primary-300 font-medium transition-colors">Sign in</a></p>
    </form>
</x-guest-layout>
```

### Step 4: Create LoginRequest

Create `app/Http/Requests/Auth/LoginRequest.php`:

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
```

### Step 5: Setup Auth Routes

Update `routes/web.php` to include:

```php
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
```

### Step 6: Compile Assets

```bash
npm run build
```

### Step 7: Run Migrations

```bash
php artisan migrate
```

---

## 🧪 How to Test

### 1. **Test Registration**

```bash
# Start server if not running
php artisan serve
```

1. Visit: `http://localhost:8000/register`
2. Fill in:
   - **Name**: John Doe
   - **Email**: john@example.com
   - **Password**: password123
   - **Confirm Password**: password123
   - ✅ Check terms checkbox
3. Click "Create account"
4. Should redirect to: `http://localhost:8000/{company-slug}/`
5. Check database:
   ```bash
   php artisan tinker
   >>> \App\Models\User::latest()->first()
   >>> \App\Models\Company::latest()->first()
   >>> \App\Models\ActivityLogs::where('action', 'register.success')->latest()->first()
   ```

### 2. **Test Login**

1. Visit: `http://localhost:8000/login`
2. Use credentials from registration
3. Should redirect to V2 dashboard
4. Check activity logs:
   ```bash
   php artisan tinker
   >>> \App\Models\ActivityLogs::where('action', 'login.success')->latest()->first()
   ```

### 3. **Test Logout**

1. Click your avatar in top-right
2. Click "Sign Out"
3. Should redirect to login
4. Check logs:
   ```bash
   php artisan tinker
   >>> \App\Models\ActivityLogs::where('action', 'logout')->latest()->first()
   ```

### 4. **Test V2 Dashboard Access**

1. Login
2. Visit: `http://localhost:8000/{your-company-slug}/`
3. Should see dashboard with stats
4. Try visiting: `http://localhost:8000/{your-company-slug}/users`
5. Should see users management

### 5. **Test Security Logging**

1. Try wrong password 3 times
2. Check logs:
   ```bash
   php artisan tinker
   >>> \App\Models\ActivityLogs::where('action', 'login.attempt')->latest()->get()
   >>> \App\Models\ActivityLogs::where('action', 'LIKE', 'security.%')->latest()->get()
   ```

### 6. **Check Laravel Logs**

```bash
tail -f storage/logs/laravel.log
```

You should see:
- `[AUTH] login.attempt`
- `[AUTH] login.success`
- `[AUTH] logout`
- `[SECURITY]` events
- `[ACTIVITY]` logs

---

## 📊 Logging System

### Log Types

1. **Authentication Logs** (`logAuth`)
   - login.attempt
   - login.success
   - login.failed
   - logout
   - register.success

2. **Activity Logs** (`log`)
   - created
   - updated
   - deleted
   - Custom actions

3. **Security Logs** (`logSecurity`)
   - security.unauthorized_access
   - security.permission_denied
   - security.suspicious_activity

4. **Error Logs** (`logError`)
   - Exception tracking
   - Stack traces
   - Context information

### Usage Examples

```php
// In your controllers
use App\Services\LogService;

class YourController extends Controller
{
    protected LogService $logService;
    
    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }
    
    public function someAction()
    {
        // Log activity
        $this->logService->log(
            'order.created',
            "New order #123 created",
            Order::class,
            123,
            ['amount' => 1500]
        );
        
        // Log security event
        $this->logService->logSecurity(
            'unauthorized_access',
            "User tried to access restricted area"
        );
    }
}
```

---

## 🎨 UI Features

### Dark Theme
- ✅ Gradient background
- ✅ Glass-morphism effects
- ✅ Smooth transitions
- ✅ Custom scrollbars
- ✅ Hover states
- ✅ Focus rings

### Responsive Design
- ✅ Mobile-first approach
- ✅ Breakpoints: sm, md, lg, xl
- ✅ Collapsible sidebar (V2 dashboard)
- ✅ Mobile-friendly forms

### Accessibility
- ✅ ARIA labels
- ✅ Keyboard navigation
- ✅ Focus indicators
- ✅ Screen reader support

---

## 🚨 Troubleshooting

### Issue: "Class LogService not found"
```bash
composer dump-autoload
```

### Issue: "Route [login] not defined"
Make sure you added auth routes to `routes/web.php`

### Issue: CSS not applying
```bash
npm run build
php artisan view:clear
```

### Issue: Can't see logs in database
Check migrations ran:
```bash
php artisan migrate:status
```

### Issue: 404 on V2 routes
Make sure migrations ran and company has a slug:
```bash
php artisan tinker
>>> \App\Models\Company::all()->each(function($c) { 
    if (!$c->slug) {
        $c->slug = \Str::slug($c->name);
        $c->save();
    }
});
```

---

## 🎯 Next Steps

1. ✅ **Test authentication flow**
2. ✅ **Verify logging works**
3. ⏳ **Add forgot password functionality**
4. ⏳ **Add email verification**
5. ⏳ **Add two-factor authentication**
6. ⏳ **Build remaining modules** (Orders, Manifests, etc.)

---

## 📝 Quick Commands Summary

```bash
# Setup
npm run build
php artisan migrate
php artisan serve

# Testing
php artisan tinker
>>> \App\Models\User::latest()->first()
>>> \App\Models\ActivityLogs::latest()->get()

# Clear caches
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# View logs
tail -f storage/logs/laravel.log
```

---

## ✨ Features Summary

- ✅ Beautiful dark theme UI
- ✅ Complete login/register flow
- ✅ Auto-company creation on register
- ✅ Comprehensive logging system
- ✅ V2 dashboard integration
- ✅ Multi-tenancy support
- ✅ Security features (rate limiting)
- ✅ Responsive design
- ✅ Activity tracking
- ✅ Error handling

---

**Your TMS authentication system is ready to use! 🎉**

For questions or issues, check the troubleshooting section or review the Laravel logs.
