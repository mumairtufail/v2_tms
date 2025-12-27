@extends('v2.layouts.app')

@section('title', 'Users')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Users']
    ]" />

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <x-page-header title="Users" description="Manage user accounts and permissions" />
        <a href="{{ route('v2.users.create', ['company' => app('current.company')]) }}" 
           class="flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add User
        </a>
    </div>

    <!-- Search & Filters -->
    <x-search-filters :route="route('v2.users.index', ['company' => app('current.company')])" placeholder="Search users...">
        <x-slot name="filters">
            <x-select-input 
                name="role" 
                :value="request('role')"
                :options="collect($roles ?? [])->pluck('name', 'id')->toArray()"
                placeholder="All Roles"
            />
            <x-select-input 
                name="status" 
                :value="request('status')"
                :options="['active' => 'Active', 'inactive' => 'Inactive']"
                placeholder="All Status"
            />
        </x-slot>
    </x-search-filters>

    <!-- Active Search Indicator -->
    @if(request('search'))
    <x-search-active :term="request('search')" :clearRoute="route('v2.users.index', ['company' => app('current.company')])" />
    @endif

    <!-- Users Table -->
    <x-table-container>
        <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Active</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-accent-600 rounded-xl flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        <x-search-highlight :text="$user->name" :search="request('search')" />
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">ID: {{ $user->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                            <x-search-highlight :text="$user->email" :search="request('search')" />
                        </td>
                        <td class="px-6 py-4">
                            @foreach($user->roles as $role)
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-800 dark:text-primary-300">
                                {{ $role->name }}
                            </span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4">
                            @if($user->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                {{-- View User --}}
                                <x-tooltip text="View User Details" position="top">
                                    <a href="{{ route('v2.users.show', ['company' => app('current.company'), 'user' => $user->id]) }}" 
                                       class="p-2 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                </x-tooltip>
                                
                                {{-- Edit User --}}
                                <x-tooltip text="Edit User" position="top">
                                    <a href="{{ route('v2.users.edit', ['company' => app('current.company'), 'user' => $user->id]) }}" 
                                       class="p-2 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                </x-tooltip>
                                
                                {{-- Delete User --}}
                                <x-tooltip text="Delete User" position="top">
                                    <button 
                                        type="button"
                                        x-data
                                        @click="$dispatch('open-modal', 'delete-user-{{ $user->id }}')"
                                        class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </x-tooltip>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">No users found</p>
                                <a href="{{ route('v2.users.create', ['company' => app('current.company')]) }}" class="mt-4 text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">Create your first user</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
        </table>
        
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
            {{ $users->links() }}
        </div>
        @endif
    </x-table-container>
</div>

<!-- Delete Modals - Placed outside table to avoid HTML structure issues -->
@foreach($users as $user)
<x-confirm-modal name="delete-user-{{ $user->id }}" title="Delete User">
    <p class="text-gray-600 dark:text-gray-400">
        Are you sure you want to delete <strong class="text-gray-900 dark:text-white">{{ $user->name }}</strong>? This action cannot be undone.
    </p>
    <x-slot name="footer">
        <button 
            type="button"
            @click="$dispatch('close-modal', 'delete-user-{{ $user->id }}')" 
            class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors"
        >
            Cancel
        </button>
        <form action="{{ route('v2.users.destroy', ['company' => app('current.company'), 'user' => $user->id]) }}" method="POST" x-data="{ deleting: false }" @submit="deleting = true">
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
