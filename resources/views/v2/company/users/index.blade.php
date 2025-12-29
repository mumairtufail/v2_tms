@extends('v2.layouts.app')

@section('title', 'Users')

@section('content')
<div class="space-y-4">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[['label' => 'Users']]" />

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <x-page-header title="Users" description="Manage users and permissions" />
        @if(auth()->user()->hasPermission('users', 'create'))
        <a href="{{ route('v2.users.create', ['company' => app('current.company')]) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add User
        </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3">
        <form action="{{ route('v2.users.index', ['company' => app('current.company')]) }}" method="GET" class="flex flex-col gap-3 sm:grid sm:grid-cols-12">
            <!-- Search -->
            <div class="sm:col-span-12 lg:col-span-6 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..." class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>

            <!-- Status -->
            <div class="sm:col-span-6 lg:col-span-2">
                <x-filter-select name="status" :value="request('status')" :options="['active' => 'Active', 'inactive' => 'Inactive']" placeholder="All Status" class="w-full" />
            </div>

            <!-- Role -->
            <div class="sm:col-span-6 lg:col-span-2">
                <x-filter-select name="role" :value="request('role')" :options="collect($roles)->pluck('name', 'id')->toArray()" placeholder="All Roles" class="w-full" />
            </div>

            <!-- Buttons -->
            <div class="sm:col-span-12 lg:col-span-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <button type="submit" class="w-full sm:w-auto flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">Search</button>
                @if(request()->hasAny(['search', 'status', 'role']))
                <a href="{{ route('v2.users.index', ['company' => app('current.company')]) }}" class="px-3 py-2 text-sm text-center text-gray-500 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Active Filters Indicator -->
    @if(request()->hasAny(['search', 'status', 'role']))
    <div class="flex flex-wrap items-center gap-2 text-sm">
        <span class="text-gray-500">Filtering by:</span>
        @if(request('search'))
        <span class="px-2 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded">Search: "{{ request('search') }}"</span>
        @endif
        @if(request('status'))
        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded">Status: {{ ucfirst(request('status')) }}</span>
        @endif
        @if(request('role'))
        @php $roleName = collect($roles)->firstWhere('id', request('role'))->name ?? request('role'); @endphp
        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded">Role: {{ $roleName }}</span>
        @endif
    </div>
    @endif

    <!-- Table -->
    <x-table-container>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="w-12 px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">#</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">User</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 hidden md:table-cell">Role</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Status</th>
                    <th class="w-24 px-3 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($users as $index => $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $users->firstItem() + $index }}</td>
                    <td class="px-3 py-2">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-primary-600 rounded-md flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white truncate">
                                    <x-search-highlight :text="$user->name" :search="request('search')" />
                                </p>
                                <p class="text-xs text-gray-500 truncate">
                                    <x-search-highlight :text="$user->email" :search="request('search')" />
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-2 hidden md:table-cell">
                        @if($user->roles->first())
                        <span class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">{{ $user->roles->first()->name }}</span>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        @if($user->status === 'active')
                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Active</span>
                        @else
                        <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Inactive</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right">
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ route('v2.users.show', ['company' => app('current.company'), 'user' => $user->id]) }}" class="p-1 text-gray-400 hover:text-primary-600" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @if(auth()->user()->hasPermission('users', 'update'))
                            <a href="{{ route('v2.users.edit', ['company' => app('current.company'), 'user' => $user->id]) }}" class="p-1 text-gray-400 hover:text-primary-600" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @endif
                            @if(auth()->user()->hasPermission('users', 'delete') && $user->id !== auth()->id())
                            <button type="button" x-data @click="$dispatch('open-modal', 'delete-user-{{ $user->id }}')" class="p-1 text-gray-400 hover:text-red-600" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">No users found</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
        <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-800">{{ $users->links() }}</div>
        @endif
    </x-table-container>
</div>

<!-- Delete Modals -->
@foreach($users as $user)
@if($user->id !== auth()->id())
<x-confirm-modal name="delete-user-{{ $user->id }}" title="Delete User">
    <p class="text-sm text-gray-600 dark:text-gray-400">Delete <strong>{{ $user->name }}</strong>?</p>
    <x-slot name="footer">
        <button type="button" @click="$dispatch('close-modal', 'delete-user-{{ $user->id }}')" class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg">Cancel</button>
        <form action="{{ route('v2.users.destroy', ['company' => app('current.company'), 'user' => $user->id]) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Delete</button>
        </form>
    </x-slot>
</x-confirm-modal>
@endif
@endforeach
@endsection
