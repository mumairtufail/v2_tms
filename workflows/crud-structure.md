# TMS CRUD Module Structure

This document defines the standard structure for all CRUD modules in TMS V2.

---

## 📁 File Structure

### Backend (PHP)
```
app/
├── Http/
│   ├── Controllers/
│   │   └── [ModuleName]Controller.php  ← Main controller
│   └── Requests/
│       └── [ModuleName]Request.php     ← Form request validation (optional)
├── Models/
│   └── [ModuleName].php                ← Eloquent model
├── Services/
│   └── [ModuleName]Service.php         ← Business logic (optional, for complex modules)
│
routes/
└── web.php                             ← Route definitions
```

### Frontend (Blade Views)
```
resources/views/v2/[module-name]/       ← (or v2/admin/[module-name] for admin modules)
├── index.blade.php                     ← List view with search, filters, table
├── form.blade.php                      ← Single form for BOTH create and edit
└── show.blade.php                      ← Detail view (optional)
```

---

## 🎯 Controller Pattern

```php
<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Company::query();

        // Search functionality - use filled() for clean checks
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $items = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString(); // IMPORTANT: Preserve filters in pagination

        return view('v2.[module].index', compact('items'));
    }

    /**
     * Show form for creating - uses form.blade.php
     */
    public function create()
    {
        return view('v2.[module].form');
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            // ... other rules
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Please check the form for errors.')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $item = Company::create([...]);
            return redirect()->route('[module].index')
                ->with('success', 'Item created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create item.')
                ->withInput();
        }
    }

    /**
     * Show form for editing - uses SAME form.blade.php
     */
    public function edit(Company $company)
    {
        return view('v2.[module].form', compact('company'));
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, Company $company)
    {
        // Similar to store() with PUT method
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Company $company)
    {
        try {
            $company->delete(); // or soft delete
            return redirect()->route('[module].index')
                ->with('success', 'Item deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete item.');
        }
    }
}
```

---

## 📄 Index View Pattern (index.blade.php)

```blade
@extends('v2.layouts.app')

@section('title', 'Companies')

@section('content')
<div class="space-y-4">
    <!-- 1. Breadcrumb -->
    <x-v2-breadcrumb :items="[['label' => 'Companies']]" />

    <!-- 2. Header with Add Button -->
    <div class="flex items-center justify-between">
        <x-page-header title="Companies" description="Manage all companies" />
        <a href="{{ route('[module].create') }}" class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Company
        </a>
    </div>

    <!-- 3. Inline Filters -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3">
        <form action="{{ route('[module].index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>
            </div>
            <x-filter-select name="status" :value="request('status')" :options="['1' => 'Active', '0' => 'Inactive']" placeholder="All Status" />
            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Search</button>
            @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('[module].index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-900 dark:hover:text-white">Clear</a>
            @endif
        </form>
    </div>

    <!-- 4. Compact Table with Serial # -->
    <x-table-container>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="w-12 px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">#</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Name</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Status</th>
                    <th class="w-24 px-3 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($items as $index => $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                    <td class="px-3 py-2 text-gray-500">{{ $items->firstItem() + $index }}</td>
                    <td class="px-3 py-2 text-gray-900 dark:text-white">
                        <x-search-highlight :text="$item->name" :search="request('search')" />
                    </td>
                    <td class="px-3 py-2">
                        @if($item->is_active)
                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Active</span>
                        @else
                        <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Inactive</span>
                        @endif
                    </td>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-0.5">
                                <x-tooltip text="Edit" position="top">
                                    <a href="{{ route('[module].edit', $item) }}" class="p-1.5 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                </x-tooltip>
                                <x-tooltip text="Delete" position="top">
                                    <button type="button" x-data @click="$dispatch('open-modal', 'delete-item-{{ $item->id }}')" class="p-1.5 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </x-tooltip>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center">
                            <p class="text-gray-500 dark:text-gray-400 text-sm">No items found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- 6. Pagination -->
        @if($items->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-800">
            {{ $items->links() }}
        </div>
        @endif
    </x-table-container>
</div>

<!-- 7. Delete Modals (OUTSIDE table, at end of content) -->
@foreach($items as $item)
<x-confirm-modal name="delete-item-{{ $item->id }}" title="Delete Item">
    <p class="text-gray-600 dark:text-gray-400">
        Are you sure you want to delete <strong class="text-gray-900 dark:text-white">{{ $item->name }}</strong>?
    </p>
    <x-slot name="footer">
        <button 
            type="button"
            @click="$dispatch('close-modal', 'delete-item-{{ $item->id }}')" 
            class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors"
        >
            Cancel
        </button>
        <form action="{{ route('[module].destroy', $item) }}" method="POST" x-data="{ deleting: false }" @submit="deleting = true">
            @csrf
            @method('DELETE')
            <button 
                type="submit" 
                :disabled="deleting" 
                class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white font-medium rounded-lg transition-colors inline-flex items-center gap-2"
            >
                <x-loader x-show="deleting" size="sm" class="text-white" />
                <span x-text="deleting ? 'Deleting...' : 'Delete'"></span>
            </button>
        </form>
    </x-slot>
</x-confirm-modal>
@endforeach
@endsection
```

---

## 📝 Form View Pattern (form.blade.php)

**One file for BOTH create and edit!**

```blade
@extends('v2.layouts.app')

@section('title', isset($company) ? 'Edit Company' : 'Create Company')

@section('content')
<div class="space-y-6">
    <!-- 1. Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Companies', 'url' => route('[module].index')],
        ['label' => isset($company) ? 'Edit' : 'Create']
    ]" />

    <!-- 2. Page Header with back button -->
    <div class="flex items-center gap-4">
        <a href="{{ route('[module].index') }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <x-page-header 
            :title="isset($company) ? 'Edit Company' : 'Create Company'" 
            :description="isset($company) ? 'Update information' : 'Add a new company'" 
        />
    </div>

    <!-- 3. Form Container -->
    <x-table-container>
        <form 
            action="{{ isset($company) ? route('[module].update', $company) : route('[module].store') }}" 
            method="POST"
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf
            @if(isset($company))
                @method('PUT')
            @endif

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Required field with asterisk -->
                    <div>
                        <x-input-label for="name" value="Company Name" :required="true" />
                        <x-text-input 
                            id="name" 
                            name="name" 
                            type="text"
                            :value="old('name', $company->name ?? '')"
                            required
                            placeholder="Enter company name"
                            class="mt-1 w-full"
                        />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Optional field (no asterisk) -->
                    <div>
                        <x-input-label for="phone" value="Phone" />
                        <x-text-input 
                            id="phone" 
                            name="phone" 
                            type="text"
                            :value="old('phone', $company->phone ?? '')"
                            placeholder="+1-000-000-0000"
                            class="mt-1 w-full"
                        />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                </div>

                <!-- Boolean field with Toggle Switch -->
                <x-toggle-switch 
                    name="is_active"
                    label="Active Status"
                    description="Enable or disable this company"
                    :checked="old('is_active', $company->is_active ?? true)"
                />
            </div>

            <!-- 4. Form Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 flex items-center gap-4 rounded-b-xl">
                <button 
                    type="submit" 
                    :disabled="submitting"
                    class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
                >
                    <x-loader x-show="submitting" size="sm" />
                    <span x-text="submitting ? 'Saving...' : '{{ isset($company) ? 'Update' : 'Create' }}'"></span>
                </button>
                <a href="{{ route('[module].index') }}" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </x-table-container>
</div>
@endsection
```

---

## ✅ Checklist for Every CRUD Module

### Backend
- [ ] Controller with index, create, store, edit, update, destroy methods
- [ ] Using `$request->filled()` for clean param checks
- [ ] Using `->withQueryString()` on pagination
- [ ] Returning `with('success', ...)` and `with('error', ...)` flash messages
- [ ] Form validation with proper error messages

### Frontend - Index Page
- [ ] Breadcrumb at top
- [ ] Page header with title and Add button
- [ ] Search filters component with status dropdown
- [ ] Active search indicator when search is active
- [ ] Table with proper dark mode classes
- [ ] Search highlighting on searchable columns
- [ ] Tooltips on action buttons
- [ ] Delete modal (Sheaf UI) - NOT JavaScript confirm()
- [ ] Pagination at bottom

### Frontend - Form Page
- [ ] Single form.blade.php for BOTH create and edit
- [ ] Breadcrumb with link back to index
- [ ] Back arrow button
- [ ] Required fields show asterisk (*)
- [ ] Toggle switch for boolean fields
- [ ] Loading state on submit button
- [ ] Cancel link back to index
- [ ] Validation errors displayed

### Styling
- [ ] All elements have dark: classes
- [ ] Using primary-* colors (not hardcoded colors)
- [ ] Consistent spacing with space-y-6

---

## 🔧 Components Used

| Component | Purpose |
|-----------|---------|
| `<x-v2-breadcrumb>` | Navigation breadcrumb |
| `<x-page-header>` | Title and description |
| `<x-search-filters>` | Search and filter form |
| `<x-search-active>` | Shows active search term |
| `<x-table-container>` | Styled table wrapper |
| `<x-select-input>` | Basic dropdown select (for forms) |
| `<x-filter-select>` | Compact inline filter dropdown (for index pages) |
| `<x-searchable-select>` | Dropdown with search (for large lists) |
| `<x-multi-select>` | Multi-select with search (for roles, tags) |
| `<x-text-input>` | Text input with label |
| `<x-input-label>` | Form label with required asterisk |
| `<x-input-error>` | Validation error message |
| `<x-toggle-switch>` | Boolean toggle (Active/Inactive) |
| `<x-tooltip>` | Hover tooltip |
| `<x-confirm-modal>` | Delete confirmation modal |
| `<x-loader>` | Loading spinner |
| `<x-search-highlight>` | Highlight search matches |
| `<x-toast-notifications>` | Flash message toasts (bottom-right) |

---

## 📐 Compact Table Styling

For more compact tables, use these guidelines:
- **Header cells**: `px-4 py-3` (not py-4)
- **Body cells**: `px-4 py-3`
- **Icons in actions**: `w-4 h-4` with `p-1.5` buttons
- **Action button gaps**: `gap-0.5`
- **Divider**: `divide-gray-100` (lighter)
- **Pagination**: `px-4 py-3`

---

## 💡 Toast Notifications

Toast notifications appear at **bottom-right** of the screen.
They auto-dismiss after 5 seconds or can be manually closed.
