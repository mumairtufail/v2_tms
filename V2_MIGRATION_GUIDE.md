# TMS V2 Migration Guide

## Overview
This document outlines the V2 architecture for the Transportation Management System, featuring a modern tech stack with Tailwind CSS, Alpine.js, and improved multi-tenancy.

## What Changed?

### 1. Technology Stack
- ❌ Bootstrap 5 → ✅ **Tailwind CSS 3**
- ❌ jQuery → ✅ **Alpine.js 3**
- ❌ Font Awesome → ✅ **Heroicons (inline SVG)**
- ❌ DataTables → ✅ **Native Tailwind tables with Livewire/Alpine (future)**
- ❌ Manual modals → ✅ **ShadeUI patterns**

### 2. Multi-Tenancy Approach
**Old (V1):**
```php
// Filter by company_id in every query
Order::where('company_id', auth()->user()->company_id)->get()
```

**New (V2):**
```php
// Company in URL + automatic scoping
URL: /{company}/orders
Model: use BelongsToCompany trait (automatic filtering)
```

### 3. Folder Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── V2/              ← NEW: All V2 controllers
│   │   └── [V1 controllers] ← OLD: Legacy (unchanged)
│   ├── Middleware/
│   │   └── CompanyScope.php  ← NEW
│   └── Requests/
│       └── V2/              ← NEW: V2 form requests
├── Models/
│   └── Traits/              ← NEW: BelongsToCompany, HasActivityLog
└── Services/                ← NEW: Business logic

resources/
├── views/
│   ├── v2/                  ← NEW: All V2 views
│   │   ├── layouts/
│   │   ├── components/
│   │   ├── orders/
│   │   └── ...
│   └── [legacy views]       ← OLD: V1 views
└── css/
    └── app.css              ← Tailwind imports

routes/
├── web.php                  ← V1 routes (backed up)
├── web.backup.php           ← Backup created
└── v2.php                   ← NEW: V2 routes
```

## Quick Start

### Step 1: Setup Tailwind CSS
```bash
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

### Step 2: Configure Tailwind
See `tailwind.config.js` example in ai_instructions.md

### Step 3: Update app.css
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Dynamic color variables */
:root {
    --color-primary-600: 79 70 229; /* Indigo */
    --color-primary-700: 67 56 202;
}
```

### Step 4: Create V2 Route File
```php
// routes/v2.php
Route::middleware(['auth', 'company.scope'])->group(function () {
    Route::prefix('{company:slug}')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('v2.dashboard');
        
        Route::resource('orders', OrderController::class)
            ->names('v2.orders.*');
    });
});
```

### Step 5: Register V2 Routes
```php
// bootstrap/app.php or app/Providers/RouteServiceProvider.php
Route::middleware('web')
    ->group(base_path('routes/v2.php'));
```

### Step 6: Create Company Scope Middleware
```php
// app/Http/Middleware/CompanyScope.php
namespace App\Http\Middleware;

use Closure;

class CompanyScope
{
    public function handle($request, Closure $next)
    {
        $company = $request->route('company');
        
        // Verify access
        if (!$company || $company->id !== auth()->user()->company_id) {
            abort(403);
        }
        
        // Set global scope
        app()->instance('current.company', $company);
        
        return $next($request);
    }
}
```

### Step 7: Add Trait to Models
```php
// app/Models/Order.php
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\HasActivityLog;

class Order extends Model
{
    use BelongsToCompany, HasActivityLog;
    
    // ... rest of model
}
```

### Step 8: Create Your First V2 Controller
```php
// app/Http/Controllers/V2/OrderController.php
namespace App\Http\Controllers\V2;

use App\Models\Company;
use App\Models\Order;
use App\Services\OrderService;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Company $company)
    {
        $orders = Order::with(['customer', 'manifest'])
            ->latest()
            ->paginate(25);
        
        return view('v2.orders.index', compact('orders', 'company'));
    }
}
```

### Step 9: Create V2 Views
Follow the exact patterns in ai_instructions.md:
- `resources/views/v2/orders/index.blade.php`
- `resources/views/v2/orders/show.blade.php`
- `resources/views/v2/orders/form.blade.php` (for both create & edit)

## URL Patterns

### V1 (Legacy):
```
/dashboard
/orders
/orders/1
/orders/create
```

### V2 (New):
```
/acme-logistics/dashboard
/acme-logistics/orders
/acme-logistics/orders/1
/acme-logistics/orders/create
```

## Component Library

All components are documented in `ai_instructions.md` with full code examples:
- `<x-app-layout>`
- `<x-page-header>`
- `<x-table-container>`
- `<x-form-section>`
- `<x-text-input>`
- `<x-select-input>`
- `<x-textarea-input>`
- `<x-toggle-input>`
- Toast notifications
- Confirm modals

## Key Principles

1. **ALL new development goes in V2 folders**
2. **Company slug MUST be in every V2 URL**
3. **Use Tailwind CSS classes (not Bootstrap)**
4. **Include dark mode classes (dark:) for every color**
5. **Use BelongsToCompany trait for automatic scoping**
6. **Business logic goes in Services (not Controllers)**
7. **Use single form.blade.php for create and edit**
8. **Include breadcrumbs on every page**
9. **Add search functionality to index pages**
10. **Use dynamic color variables (primary, secondary, accent)**

## Migration Strategy

### Phase 1: Setup (Week 1)
- ✅ Install Tailwind CSS
- ✅ Create V2 folder structure
- ✅ Create middleware and traits
- ✅ Backup web.php
- ✅ Create v2.php routes file
- ✅ Update ai_instructions.md

### Phase 2: Core Modules (Weeks 2-4)
- Migrate Orders module to V2
- Migrate Customers module to V2
- Migrate Manifests module to V2
- Test thoroughly

### Phase 3: Supporting Modules (Weeks 5-8)
- Migrate Carriers module
- Migrate Equipment module
- Migrate Drivers module
- Migrate Settings/Admin

### Phase 4: Polish & Launch (Week 9-10)
- Complete dark mode testing
- Performance optimization
- User acceptance testing
- Deploy to production
- Update documentation

## Testing Checklist

For each migrated module:
- [ ] URLs include company slug
- [ ] Multi-tenancy verified (User A can't see Company B data)
- [ ] Dark mode works correctly
- [ ] Responsive design on mobile/tablet
- [ ] Search functionality works
- [ ] Create/Edit/Delete operations work
- [ ] Validation messages display correctly
- [ ] Toast notifications appear
- [ ] Breadcrumbs are correct
- [ ] Activity logging works

## Resources

- **Main Guide:** `ai_instructions.md` (READ THIS FIRST!)
- **Workflow:** `workflows/ai-instructions.md`
- **This Guide:** `V2_MIGRATION_GUIDE.md`
- **Tailwind Docs:** https://tailwindcss.com/docs
- **Alpine.js Docs:** https://alpinejs.dev
- **Heroicons:** https://heroicons.com

## Support

For questions or issues during migration:
1. Read `ai_instructions.md` thoroughly
2. Check existing V2 implementations for patterns
3. Verify multi-tenancy is working correctly
4. Test dark mode compatibility
5. Review this migration guide

---

**Remember:** V1 and V2 can coexist. Migrate gradually, test thoroughly, and maintain consistency with the patterns defined in ai_instructions.md.
