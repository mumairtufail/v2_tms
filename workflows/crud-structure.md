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
│   └── [ModuleName]Service.php         ← Business logic (optional)
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

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query()->where('is_deleted', false);

        // Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString(); // Preserve filters in pagination

        return view('v2.[module].index', compact('items'));
    }

    public function create()
    {
        return view('v2.[module].form');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Please check the form for errors.')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            Item::create([...]);
            return redirect()->route('[module].index')
                ->with('success', 'Item created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create item.')
                ->withInput();
        }
    }

    public function show(Item $item)
    {
        return view('v2.[module].show', compact('item'));
    }

    public function edit(Item $item)
    {
        return view('v2.[module].form', compact('item'));
    }

    public function update(Request $request, Item $item)
    {
        // Similar to store() with PUT method
    }

    public function destroy(Item $item)
    {
        try {
            $item->update(['is_deleted' => true]); // Soft delete
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

@section('title', 'Items')

@section('content')
<div class="space-y-4">
    <!-- 1. Breadcrumb -->
    <x-v2-breadcrumb :items="[['label' => 'Items']]" />

    <!-- 2. Header with Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <x-page-header title="Items" description="Manage all items" />
        <a href="{{ route('[module].create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Item
        </a>
    </div>

    <!-- 3. Inline Filters (Responsive Grid) -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3">
        <form action="{{ route('[module].index') }}" method="GET" class="flex flex-col gap-3 sm:grid sm:grid-cols-12">
            <!-- Search Input (Full width on mobile, larger on desktop) -->
            <div class="sm:col-span-6 lg:col-span-8 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>
            
            <!-- Filter Dropdowns -->
            <div class="sm:col-span-3 lg:col-span-2">
                <x-filter-select name="status" :value="request('status')" :options="['active' => 'Active', 'inactive' => 'Inactive']" placeholder="All Status" class="w-full" />
            </div>
            
            <!-- Buttons (Stacked on mobile) -->
            <div class="sm:col-span-3 lg:col-span-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <button type="submit" class="w-full sm:w-auto flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">Search</button>
                @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('[module].index') }}" class="px-3 py-2 text-sm text-center text-gray-500 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. Active Filters Indicator -->
    @if(request()->hasAny(['search', 'status']))
    <div class="flex flex-wrap items-center gap-2 text-sm">
        <span class="text-gray-500">Filtering by:</span>
        @if(request('search'))
        <span class="px-2 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded">Search: "{{ request('search') }}"</span>
        @endif
        @if(request('status'))
        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded">Status: {{ ucfirst(request('status')) }}</span>
        @endif
    </div>
    @endif

    <!-- 5. Compact Table with Serial # -->
    <!-- Note: x-table-container includes min-w-[800px] and overflow-x-auto for mobile responsiveness -->
    <x-table-container>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="w-12 px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">#</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Name</th>
                    <!-- Hide less critical columns on mobile -->
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 hidden md:table-cell">Details</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Status</th>
                    <th class="w-24 px-3 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($items as $index => $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                    <!-- Serial Number -->
                    <td class="px-3 py-2 text-gray-500">{{ $items->firstItem() + $index }}</td>
                    
                    <!-- Name with Search Highlight -->
                    <td class="px-3 py-2 text-gray-900 dark:text-white">
                        <x-search-highlight :text="$item->name" :search="request('search')" />
                    </td>

                    <!-- Hidden Column Data -->
                    <td class="px-3 py-2 text-gray-500 hidden md:table-cell">
                        {{ $item->details }}
                    </td>
                    
                    <!-- Status Badge -->
                    <td class="px-3 py-2">
                        @if($item->is_active)
                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Active</span>
                        @else
                        <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Inactive</span>
                        @endif
                    </td>
                    
                    <!-- Action Icons -->
                    <td class="px-3 py-2 text-right">
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ route('[module].show', $item) }}" class="p-1 text-gray-400 hover:text-primary-600" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('[module].edit', $item) }}" class="p-1 text-gray-400 hover:text-primary-600" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button type="button" x-data @click="$dispatch('open-modal', 'delete-item-{{ $item->id }}')" class="p-1 text-gray-400 hover:text-red-600" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">No items found</td></tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- 6. Pagination -->
        @if($items->hasPages())
        <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-800">{{ $items->links() }}</div>
        @endif
    </x-table-container>
</div>

<!-- 7. Delete Modals -->
@foreach($items as $item)
<x-confirm-modal name="delete-item-{{ $item->id }}" title="Delete Item">
    <p class="text-sm text-gray-600 dark:text-gray-400">Delete <strong>{{ $item->name }}</strong>?</p>
    <x-slot name="footer">
        <button type="button" @click="$dispatch('close-modal', 'delete-item-{{ $item->id }}')" class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg">Cancel</button>
        <form action="{{ route('[module].destroy', $item) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Delete</button>
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

@section('title', isset($item) ? 'Edit Item' : 'Create Item')

@section('content')
<div class="space-y-6">
    <!-- 1. Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Items', 'url' => route('[module].index')],
        ['label' => isset($item) ? 'Edit Item' : 'Create Item']
    ]" />

    <!-- 2. Page Header with back button -->
    <div class="flex items-center gap-4">
        <a href="{{ route('[module].index') }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <x-page-header 
            :title="isset($item) ? 'Edit Item' : 'Create Item'" 
            :description="isset($item) ? 'Update item information' : 'Add a new item'" 
        />
    </div>

    <!-- 3. Form Container -->
    <x-table-container>
        <form 
            action="{{ isset($item) ? route('[module].update', $item) : route('[module].store') }}" 
            method="POST"
            x-data="{ submitting: false }"
            @submit="submitting = true"
        >
            @csrf
            @if(isset($item)) @method('PUT') @endif

            <div class="p-6 space-y-8">
                <!-- Section 1: Basic Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <!-- Icon -->
                        </svg>
                        Basic Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="name" value="Name" :required="true" />
                            <x-text-input 
                                id="name" 
                                name="name" 
                                type="text"
                                :value="old('name', $item->name ?? '')"
                                required
                                placeholder="Enter name"
                                class="mt-1 w-full"
                            />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <!-- More fields... -->
                    </div>
                </div>

                <!-- Section 2: Additional Details (with border separator) -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <!-- Icon -->
                        </svg>
                        Additional Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- More fields... -->
                    </div>
                </div>
            </div>

            <!-- 4. Form Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 flex items-center gap-4 rounded-b-xl border-t border-gray-200 dark:border-gray-700">
                <button 
                    type="submit" 
                    :disabled="submitting"
                    class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
                >
                    <template x-if="submitting">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    {{ isset($item) ? 'Update Item' : 'Create Item' }}
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

## 👁️ Show View Pattern (show.blade.php)

**Modern 2-column layout with profile card + details grid**

```blade
@extends('v2.layouts.app')

@section('title', $item->name)

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Items', 'url' => route('[module].index')],
        ['label' => $item->name]
    ]" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Profile Card -->
        <div class="lg:col-span-1">
            <x-table-container>
                <div class="p-6 text-center">
                    <!-- Avatar -->
                    <div class="w-24 h-24 mx-auto bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                        {{ strtoupper(substr($item->name, 0, 2)) }}
                    </div>
                    
                    <!-- Name -->
                    <h2 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">{{ $item->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->email ?? 'No email' }}</p>
                    
                    <!-- Status Badge -->
                    <div class="mt-4 flex items-center justify-center gap-2">
                        @if($item->is_active)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                            Active
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            Inactive
                        </span>
                        @endif
                    </div>
                    
                    <!-- Quick Actions (Equal Size Buttons) -->
                    <div class="mt-6 flex gap-2">
                        <a href="{{ route('[module].edit', $item) }}" class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors text-center">
                            Edit
                        </a>
                        <button type="button" x-data @click="$dispatch('open-modal', 'delete-item')" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-red-100 dark:hover:bg-red-900/30 text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 text-sm font-medium rounded-lg transition-colors text-center">
                            Delete
                        </button>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="border-t border-gray-200 dark:border-gray-700 p-4 grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $item->last_login_at ? $item->last_login_at->diffForHumans(null, true) : '-' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Last Login</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $item->created_at->diffForHumans(null, true) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Member since</p>
                    </div>
                </div>
            </x-table-container>
        </div>

        <!-- Right: Details Cards -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Section Card -->
            <x-table-container>
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Details</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Field Label</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $item->field ?? '-' }}</dd>
                        </div>
                        <!-- More fields... -->
                    </dl>
                </div>
            </x-table-container>

            <!-- Account Timeline -->
            <x-table-container>
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Account Timeline</h3>
                </div>
                <div class="p-6">
                    <div class="relative">
                        <div class="absolute left-2 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                        <div class="space-y-6">
                            <div class="relative pl-8">
                                <div class="absolute left-0 w-4 h-4 bg-primary-100 dark:bg-primary-900/30 border-2 border-primary-500 rounded-full"></div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Account Created</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->created_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                            <div class="relative pl-8">
                                <div class="absolute left-0 w-4 h-4 bg-gray-100 dark:bg-gray-800 border-2 border-gray-400 rounded-full"></div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Last Updated</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->updated_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-table-container>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<x-confirm-modal name="delete-item" title="Delete Item">
    <p class="text-gray-600 dark:text-gray-400">Are you sure you want to delete <strong class="text-gray-900 dark:text-white">{{ $item->name }}</strong>?</p>
    <x-slot name="footer">
        <button type="button" @click="$dispatch('close-modal', 'delete-item')" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">Cancel</button>
        <form action="{{ route('[module].destroy', $item) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">Delete</button>
        </form>
    </x-slot>
</x-confirm-modal>
@endsection
```

---

## 🎨 Styling Reference

### Table Styling (Compact)
| Element | Classes |
|---------|---------|
| Table | `w-full text-sm` |
| Header Row | `bg-gray-50 dark:bg-gray-800/50` |
| Header Cell | `px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400` |
| Body Cell | `px-3 py-2` |
| Row Hover | `hover:bg-gray-50 dark:hover:bg-gray-800/30` |
| Divider | `divide-y divide-gray-100 dark:divide-gray-800` |
| Action Icons | `w-4 h-4` with `p-1` buttons |
| Serial # Column | `w-12` fixed width |
| Actions Column | `w-24 text-right` fixed width |

### Status Badges
```blade
<!-- Active -->
<span class="px-2 py-0.5 text-xs rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Active</span>

<!-- Inactive -->
<span class="px-2 py-0.5 text-xs rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Inactive</span>

<!-- With Dot -->
<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
    Active
</span>
```

### Form Sections
```blade
<!-- Section with icon header -->
<div>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-primary-500">...</svg>
        Section Title
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Fields -->
    </div>
</div>

<!-- Section with border separator -->
<div class="border-t border-gray-200 dark:border-gray-700 pt-6">
    ...
</div>
```

---

## 🧩 Components Used

| Component | Purpose |
|-----------|---------|
| `<x-v2-breadcrumb>` | Navigation breadcrumbs |
| `<x-page-header>` | Page title and description |
| `<x-table-container>` | Styled table/card wrapper |
| `<x-filter-select>` | Compact inline filter dropdown (for index pages) |
| `<x-select-input>` | Basic dropdown select (for forms) |
| `<x-text-input>` | Text input field |
| `<x-input-label>` | Form label with required asterisk |
| `<x-input-error>` | Validation error message |
| `<x-search-highlight>` | Highlight search matches |
| `<x-confirm-modal>` | Delete confirmation modal |
| `<x-toast-notifications>` | Flash message toasts (bottom-right) |

---

## ✅ Checklist for New CRUD Module

- [ ] Create Model with fillable fields
- [ ] Create Controller with all 7 methods
- [ ] Add routes in web.php with resource
- [ ] Create index.blade.php with filters, table, pagination
- [ ] Create form.blade.php for create/edit
- [ ] Create show.blade.php (optional)
- [ ] Add soft delete (is_deleted flag)
- [ ] Add search functionality
- [ ] Add filter functionality
- [ ] Add flash messages (success/error)
- [ ] Test create, read, update, delete
- [ ] Test pagination
- [ ] Test search highlighting

---

## 💡 Key Reminders

1. **Serial Numbers**: Use `$items->firstItem() + $index` for correct numbering across pages
2. **Search Highlighting**: Always wrap searchable text with `<x-search-highlight>`
3. **Active Filters**: Show filter indicators when any filter is active
4. **Pagination**: Use `->withQueryString()` to preserve filters
5. **Form Sections**: Use border-t separators between sections
6. **Equal Buttons**: Use `flex-1` on both Edit/Delete for equal width
7. **Toast Position**: Bottom-right with slide-up animation
8. **Use Dynamic Colors**: Always use `primary-*` and `accent-*` classes, never hardcoded colors like `blue-*` or `green-*`

---

## 🎨 Dynamic Color System

Colors are defined in `tailwind.config.js` and can be changed globally.

### Primary Colors (for main UI elements)
```
primary-50   → Lightest background
primary-100  → Light background, borders
primary-500  → Main color
primary-600  → Buttons, active states
primary-700  → Hover states
```

### Accent Colors (for secondary highlights)
```
accent-50 to accent-700 → Secondary elements, badges, icons
```

### Usage Examples

```blade
<!-- Buttons -->
<button class="bg-primary-600 hover:bg-primary-700">Primary Button</button>

<!-- Badges -->
<span class="bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300">Badge</span>

<!-- Icons -->
<div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30">
    <svg class="text-primary-600 dark:text-primary-400">...</svg>
</div>

<!-- Links -->
<a href="#" class="text-primary-600 hover:text-primary-700 dark:text-primary-400">Link</a>

<!-- Focus States -->
<input class="focus:border-primary-500 focus:ring-1 focus:ring-primary-500">

<!-- Avatars -->
<div class="bg-gradient-to-br from-primary-500 to-primary-700">AB</div>
```

### ⚠️ NEVER Use Hardcoded Colors
```blade
❌ BAD:  bg-blue-600, bg-green-500, text-indigo-600
✅ GOOD: bg-primary-600, bg-accent-500, text-primary-600
```

### Exception: Status Colors
Only use hardcoded colors for semantic status badges:
```blade
<!-- These are OK because they convey meaning -->
<span class="bg-green-100 text-green-700">Active</span>
<span class="bg-red-100 text-red-700">Inactive</span>
<span class="bg-yellow-100 text-yellow-700">Pending</span>
```
