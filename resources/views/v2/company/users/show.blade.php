@extends('v2.layouts.app')

@section('title', 'User Details')

@section('content')
<x-page-header 
    title="{{ $user->name }}"
    description="View user details and activity"
    :breadcrumbs="[
        ['label' => 'Users', 'url' => route('v2.users.index', ['company' => app('current.company')])],
        ['label' => $user->name]
    ]"
>
    <x-slot name="actions">
        <a href="{{ route('v2.users.edit', ['company' => app('current.company'), 'user' => $user->id]) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit User
        </a>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- User Info Card -->
    <div class="lg:col-span-1">
        <x-form-section title="User Information">
            <div class="flex flex-col items-center text-center mb-6">
                <div class="w-24 h-24 bg-gradient-to-br from-primary-500 to-primary-700 rounded-full flex items-center justify-center mb-4">
                    <span class="text-white text-3xl font-bold">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                <span class="mt-3 inline-flex px-3 py-1 text-xs font-medium rounded-full 
                    {{ $user->status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' }}">
                    {{ ucfirst($user->status) }}
                </span>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Phone</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Joined</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Last Login</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Roles</p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($user->roles as $role)
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-800 dark:text-primary-300">
                            {{ $role->name }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-form-section>
    </div>

    <!-- Activity & Stats -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->orders_count ?? 0 }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Orders Created</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->activity_logs_count ?? 0 }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Activities</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->login_count ?? 0 }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Logins</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <x-form-section title="Recent Activity">
            <div class="space-y-3">
                @forelse($user->activityLogs()->latest()->take(10)->get() as $log)
                <div class="flex items-start gap-3 pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                    <div class="flex-shrink-0 w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 dark:text-white">{{ $log->description }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-center text-sm text-gray-500 dark:text-gray-400 py-4">No recent activity</p>
                @endforelse
            </div>
        </x-form-section>
    </div>
</div>
@endsection
