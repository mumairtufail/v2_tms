@extends('v2.layouts.app')

@section('title', $user->name)

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Users', 'url' => route('admin.users.index')],
        ['label' => $user->name]
    ]" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Profile Card -->
        <div class="lg:col-span-1">
            <x-table-container>
                <div class="p-6 text-center">
                    <!-- Avatar -->
                    <div class="w-24 h-24 mx-auto bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                        {{ strtoupper(substr($user->f_name ?? $user->name ?? 'U', 0, 1)) }}{{ strtoupper(substr($user->l_name ?? '', 0, 1)) }}
                    </div>
                    
                    <!-- Name & Email -->
                    <h2 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    
                    <!-- Status & Role -->
                    <div class="mt-4 flex items-center justify-center gap-2">
                        @if($user->status === 'active' || $user->is_active)
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
                        
                        @if($user->roles->first())
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300">
                            {{ $user->roles->first()->name }}
                        </span>
                        @endif
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="mt-6 flex gap-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors text-center">
                            Edit
                        </a>
                        @if($user->id !== auth()->id())
                        <button type="button" x-data @click="$dispatch('open-modal', 'delete-user')" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-red-100 dark:hover:bg-red-900/30 text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 text-sm font-medium rounded-lg transition-colors text-center">
                            Delete
                        </button>
                        @endif
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="border-t border-gray-200 dark:border-gray-700 p-4 grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->last_login_at ? $user->last_login_at->diffForHumans(null, true) : '-' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Last Login</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->created_at->diffForHumans(null, true) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Member since</p>
                    </div>
                </div>
            </x-table-container>
        </div>

        <!-- Right: Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <x-table-container>
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Personal Information</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">First Name</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $user->f_name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Last Name</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $user->l_name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Phone</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $user->phone ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </x-table-container>

            <!-- Work Information -->
            <x-table-container>
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Work Information</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Company</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $user->company->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Role</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $user->roles->first()->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Super Admin</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $user->is_super_admin ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">User ID</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">#{{ $user->id }}</dd>
                        </div>
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
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->created_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                            <div class="relative pl-8">
                                <div class="absolute left-0 w-4 h-4 bg-gray-100 dark:bg-gray-800 border-2 border-gray-400 rounded-full"></div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Last Updated</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->updated_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                            @if($user->last_login_at)
                            <div class="relative pl-8">
                                <div class="absolute left-0 w-4 h-4 bg-green-100 dark:bg-green-900/30 border-2 border-green-500 rounded-full"></div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Last Login</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->last_login_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-table-container>
        </div>
    </div>
</div>

<!-- Delete Modal -->
@if($user->id !== auth()->id())
<x-confirm-modal name="delete-user" title="Delete User">
    <p class="text-gray-600 dark:text-gray-400">Are you sure you want to delete <strong class="text-gray-900 dark:text-white">{{ $user->name }}</strong>?</p>
    <x-slot name="footer">
        <button type="button" @click="$dispatch('close-modal', 'delete-user')" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">Cancel</button>
        <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">Delete</button>
        </form>
    </x-slot>
</x-confirm-modal>
@endif
@endsection
