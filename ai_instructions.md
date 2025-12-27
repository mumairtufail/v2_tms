# TMS AI Instructions

**This file contains the complete project manifest and coding guidelines for the TMS Transportation Management System. READ THIS FIRST before implementing any feature.**

---

## 1. Architecture Overview

### Technology Stack
- **Backend:** Laravel 11 (PHP 8.3)
- **Frontend:** Tailwind CSS + Alpine.js + Sheaf UI
- **Build:** Vite
- **Database:** MySQL with multi-tenancy

### Centralized Color System
**CRITICAL:** All colors are defined in `tailwind.config.js`. Never hardcode colors!

```javascript
// tailwind.config.js - Color Tokens
primary: {
    50-950   // Main theme color (emerald by default)
},
accent: {
    50-950   // Secondary accent color (teal by default)
}
```

**Usage in Blade files:**
```blade
<!-- ✅ Correct - uses centralized colors -->
<div class="bg-primary-500 text-primary-600 dark:text-primary-400">

<!-- ❌ Wrong - hardcoded color -->
<div class="bg-emerald-500 text-emerald-600">
```

**To change the entire app theme:** Edit `tailwind.config.js` → Change primary/accent values → Entire app updates automatically.

### Multi-Tenancy Pattern
All V2 routes use the `{company}` slug in URLs:
```
/{company}/dashboard
/{company}/orders
/{company}/orders/{order}
```

### Global Admin Routes (Superadmin Only)
```
/admin/dashboard
/admin/companies
/admin/logs
```

---

## 2. Folder Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── V2/                    <- V2 Controllers (company-scoped)
│   │   └── [AdminController, CompanyController, etc.] <- Admin controllers
│   ├── Middleware/
│   │   ├── CompanyScope.php       <- Multi-tenant middleware
│   │   └── IsSuperAdmin.php       <- Superadmin protection
│   └── Requests/                  <- Form Request validations
├── Models/
│   └── Traits/
│       └── BelongsToCompany.php   <- Multi-tenancy trait
├── Services/                      <- Business logic services
│
resources/
├── views/
│   ├── components/                <- Reusable Blade components
│   │   ├── breadcrumb.blade.php
│   │   ├── page-header.blade.php
│   │   ├── table-container.blade.php
│   │   ├── modal.blade.php
│   │   └── [form inputs, buttons, etc.]
│   ├── v2/
│   │   ├── layouts/
│   │   │   └── app.blade.php      <- Main V2 layout
│   │   ├── partials/
│   │   │   ├── sidebar.blade.php
│   │   │   └── navbar.blade.php
│   │   ├── dashboard/
│   │   ├── admin/                 <- Admin module views
│   │   │   ├── dashboard.blade.php
│   │   │   ├── companies/
│   │   │   └── logs/
│   │   └── [other modules]/
│
routes/
├── web.php                        <- Main routes + admin routes
└── v2.php                         <- Company-scoped V2 routes
```

---

## 3. Controller Pattern

### Standard Controller Header
Every controller MUST define the log channel at the top:

```php
<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // Define log channel for this module
    protected string $logChannel = 'orders';

    public function __construct()
    {
        // Log channel initialization
        Log::channel($this->logChannel);
    }
    
    // ... methods
}
```

### Using Services for Business Logic
```php
// Inject service in constructor
public function __construct(
    protected OrderService $orderService,
    protected string $logChannel = 'orders'
) {}

public function store(OrderRequest $request)
{
    try {
        $order = $this->orderService->create($request->validated());
        Log::channel($this->logChannel)->info('Order created', ['id' => $order->id]);
        return redirect()->route('v2.orders.index', app('current.company'))
            ->with('success', 'Order created successfully!');
    } catch (\Exception $e) {
        Log::channel($this->logChannel)->error('Order creation failed', ['error' => $e->getMessage()]);
        return back()->with('error', 'Failed to create order.');
    }
}
```

---

## 4. Reusable Blade Components

### CRITICAL: Use existing components for ALL views!

**IMPORTANT: Every page MUST have breadcrumbs!**

| Component | Usage | Path |
|-----------|-------|------|
| `<x-v2-breadcrumb>` | **REQUIRED** - Breadcrumb navigation | `components/v2-breadcrumb.blade.php` |
| `<x-page-header>` | Page title, description, and actions | `components/page-header.blade.php` |
| `<x-table-container>` | Table wrapper with styling | `components/table-container.blade.php` |
| `<x-search-filters>` | Search bar with filter slots | `components/search-filters.blade.php` |
| `<x-select-input>` | Dropdown select input | `components/select-input.blade.php` |
| `<x-text-input>` | Text input fields with label support | `components/text-input.blade.php` |
| `<x-input-label>` | Form labels with required asterisk | `components/input-label.blade.php` |
| `<x-input-error>` | Validation errors | `components/input-error.blade.php` |
| `<x-confirm-modal>` | Delete/confirmation dialogs | `components/confirm-modal.blade.php` |
| `<x-loader>` | Loading spinner for buttons | `components/loader.blade.php` |
| `<x-search-active>` | Active search indicator | `components/search-active.blade.php` |
| `<x-toast-notifications>` | **GLOBAL** - Flash message toasts | `components/toast-notifications.blade.php` |
| `<x-toggle-switch>` | Modern toggle for boolean fields | `components/toggle-switch.blade.php` |
| `<x-tooltip>` | Hover tooltips for buttons | `components/tooltip.blade.php` |
| `<x-search-highlight>` | Highlight search matches | `components/search-highlight.blade.php` |
| `<x-form-section>` | Form group with title/description | `components/form-section.blade.php` |

### Breadcrumb Usage (REQUIRED on every page)
```blade
<!-- At the TOP of every page content -->
<x-v2-breadcrumb :items="[
    ['label' => 'Companies', 'url' => route('admin.companies.index')],
    ['label' => 'Edit']
]" />
```

### Search & Filters Usage
```blade
<x-search-filters :route="route('admin.companies.index')" placeholder="Search companies...">
    <x-slot name="filters">
        <x-select-input 
            name="status" 
            :value="request('status')"
            :options="['1' => 'Active', '0' => 'Inactive']"
            placeholder="All Status"
        />
    </x-slot>
</x-search-filters>
```

### Example Index Page Pattern
```blade
@extends('v2.layouts.app')

@section('title', 'Orders')

@section('content')
<div class="space-y-6">
    <!-- Page Header with Actions -->
    <x-page-header title="Orders" description="Manage all orders">
        <x-slot name="actions">
            <x-primary-button :href="route('v2.orders.create', app('current.company'))">
                <x-icon name="plus" class="w-5 h-5 mr-2" />
                Add Order
            </x-primary-button>
        </x-slot>
    </x-page-header>

    <!-- Search & Filters -->
    <x-search-filters :route="route('v2.orders.index', app('current.company'))">
        <x-slot name="filters">
            <!-- Filter dropdowns -->
        </x-slot>
    </x-search-filters>

    <!-- Active Search Indicator -->
    @if(request('search'))
    <x-search-active :term="request('search')" :clearRoute="route('v2.orders.index', app('current.company'))" />
    @endif

    <!-- Table -->
    <x-table-container>
        <table class="w-full">
            <!-- Table content -->
        </table>
        {{ $orders->links() }}
    </x-table-container>
</div>
@endsection
```

---

## 5. Form Pattern (Create + Edit Combined)

### Single form.blade.php for Both Create and Edit
```blade
@extends('v2.layouts.app')

@section('title', isset($order) ? 'Edit Order' : 'Create Order')

@section('content')
<div class="space-y-6">
    <x-page-header 
        :title="isset($order) ? 'Edit Order' : 'Create Order'"
        :description="isset($order) ? 'Update order #' . $order->id : 'Create a new order'"
    />

    <x-table-container>
        <form 
            action="{{ isset($order) ? route('v2.orders.update', [app('current.company'), $order]) : route('v2.orders.store', app('current.company')) }}"
            method="POST"
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf
            @if(isset($order))
                @method('PUT')
            @endif

            <div class="p-6 space-y-6">
                <!-- Form fields -->
                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input 
                        id="name" 
                        name="name" 
                        :value="old('name', $order->name ?? '')"
                        required
                    />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <!-- More fields... -->
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 flex items-center gap-4">
                <x-primary-button type="submit" x-bind:disabled="submitting">
                    <x-loader x-show="submitting" />
                    <span x-show="!submitting">{{ isset($order) ? 'Update' : 'Create' }}</span>
                </x-primary-button>
                <x-secondary-button :href="route('v2.orders.index', app('current.company'))">
                    Cancel
                </x-secondary-button>
            </div>
        </form>
    </x-table-container>
</div>
@endsection
```

---

## 6. Delete Confirmation with Sheaf UI Modal

### ALWAYS use modal confirmation for destructive actions:
```blade
<!-- Delete Button -->
<button 
    type="button"
    x-data
    @click="$dispatch('open-modal', 'delete-order-{{ $order->id }}')"
    class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400"
>
    <x-icon name="trash" class="w-5 h-5" />
</button>

<!-- Delete Modal -->
<x-modal name="delete-order-{{ $order->id }}" maxWidth="md">
    <div class="p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Confirm Delete</h3>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
            Are you sure you want to delete this order? This action cannot be undone.
        </p>
        <div class="mt-6 flex justify-end gap-4">
            <x-secondary-button @click="$dispatch('close-modal', 'delete-order-{{ $order->id }}')">
                Cancel
            </x-secondary-button>
            <form action="{{ route('v2.orders.destroy', [app('current.company'), $order]) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-danger-button type="submit">Delete</x-danger-button>
            </form>
        </div>
    </div>
</x-modal>
```

---

## 7. Loading States

### Button Loader Component
Every submit button MUST show a loading state:
```blade
<x-primary-button 
    type="submit" 
    x-data="{ loading: false }"
    @click="loading = true"
    x-bind:disabled="loading"
>
    <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <span x-text="loading ? 'Processing...' : 'Submit'"></span>
</x-primary-button>
```

---

## 8. Search with Active Indicator

### Search Input with Clear Button
When a search is active, show an indicator with a clear button:
```blade
@if(request('search'))
<div class="flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800">
    <span class="text-sm text-blue-700 dark:text-blue-300">
        Showing results for: <strong>"{{ request('search') }}"</strong>
    </span>
    <a href="{{ route('v2.orders.index', app('current.company')) }}" class="ml-auto text-blue-600 hover:text-blue-800 dark:text-blue-400">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </a>
</div>
@endif
```

---

## 9. Dark Mode

### CRITICAL: ALL components MUST include dark mode classes!
```blade
<!-- ✅ Correct -->
<div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">

<!-- ❌ Wrong - missing dark mode -->
<div class="bg-white text-gray-900">
```

### Standard Color Mappings
| Light | Dark |
|-------|------|
| `bg-white` | `dark:bg-gray-900` |
| `bg-gray-50` | `dark:bg-gray-800` |
| `bg-gray-100` | `dark:bg-gray-800/50` |
| `text-gray-900` | `dark:text-white` |
| `text-gray-600` | `dark:text-gray-400` |
| `border-gray-200` | `dark:border-gray-700` |

---

## 10. Validation & Error Handling

### Form Request Validation
Always use Form Request classes:
```php
// app/Http/Requests/V2/CompanyRequest.php
class CompanyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }
}
```

### Display Errors in Views
```blade
<x-input-error :messages="$errors->get('name')" class="mt-2" />
```

---

## 11. Route Helpers

### Always use the company parameter:
```blade
<!-- Index -->
route('v2.orders.index', ['company' => app('current.company')])

<!-- Create -->
route('v2.orders.create', ['company' => app('current.company')])

<!-- Store -->
route('v2.orders.store', ['company' => app('current.company')])

<!-- Edit -->
route('v2.orders.edit', ['company' => app('current.company'), 'order' => $order])

<!-- Update -->
route('v2.orders.update', ['company' => app('current.company'), 'order' => $order])

<!-- Destroy -->
route('v2.orders.destroy', ['company' => app('current.company'), 'order' => $order])

<!-- Admin routes (no company) -->
route('admin.companies.index')
route('admin.logs')
```

---

## 12. Checklist Before Implementing Any Feature

### Core Structure
- [ ] Read this file completely?
- [ ] Using V2 folder structure?
- [ ] Controller has log channel defined?
- [ ] Using existing Blade components?
- [ ] Create/Edit using single form.blade.php?

### Navigation & Layout
- [ ] Breadcrumbs on EVERY page using `<x-v2-breadcrumb>`?
- [ ] Page header with title and description?
- [ ] Routes include company parameter (except admin routes)?

### Forms & Validation
- [ ] Required fields show asterisk (*) using `<x-input-label :required="true">`?
- [ ] Using Form Request for validation?
- [ ] Form inputs show validation errors using `<x-input-error>`?
- [ ] Submit buttons have loading states?
- [ ] Using `<x-toggle-switch>` for boolean fields (not checkboxes)?

### Tables & Lists
- [ ] Pagination for list views?
- [ ] Search has active indicator with clear button?
- [ ] Search highlighting using `<x-search-highlight>`?
- [ ] Action buttons have tooltips using `<x-tooltip>`?

### Modals & Notifications
- [ ] Delete confirmation uses `<x-confirm-modal>` (NOT JS confirm())?
- [ ] Session messages display via `<x-toast-notifications>` (global in app.blade.php)?

### Styling
- [ ] Dark mode classes on ALL elements (`dark:` variants)?
- [ ] Using centralized colors (`primary-*`, `accent-*`) not hardcoded colors?

---

## 13. Component Quick Reference

### Toast Notifications (Global)
Already included in `app.blade.php`. Use flash messages in controllers:
```php
return redirect()->route('...')
    ->with('success', 'Action completed successfully!');  // or 'error', 'warning', 'info'
```

### Toggle Switch (for boolean fields)
```blade
<x-toggle-switch 
    name="is_active"
    label="Active Status"
    description="Enable or disable this item"
    :checked="old('is_active', $model->is_active ?? true)"
/>
```

### Tooltip (for action buttons)
```blade
<x-tooltip text="View Details" position="top">
    <a href="..." class="p-2 ...">
        <svg>...</svg>
    </a>
</x-tooltip>
```

### Search Highlighting
```blade
<x-search-highlight :text="$item->name" :search="request('search')" />
```

### Required Field Label
```blade
<x-input-label for="name" value="Name" :required="true" />
```

### Delete Modal Pattern
```blade
{{-- Button to trigger --}}
<button @click="$dispatch('open-modal', 'delete-item-{{ $item->id }}')">Delete</button>

{{-- Modal (outside table) --}}
<x-confirm-modal name="delete-item-{{ $item->id }}" title="Delete Item">
    <p>Are you sure you want to delete <strong>{{ $item->name }}</strong>?</p>
    <x-slot name="footer">
        <button @click="$dispatch('close-modal', 'delete-item-{{ $item->id }}')">Cancel</button>
        <form action="{{ route('...destroy', $item) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit">Delete</button>
        </form>
    </x-slot>
</x-confirm-modal>
```

---

**Remember: REUSABLE COMPONENTS = CONSISTENT UI = EASY MAINTENANCE**
