@extends('v2.layouts.app')

@section('title', isset($user) ? 'Edit User' : 'Create User')

@section('content')
<x-page-header 
    :title="isset($user) ? 'Edit User' : 'Create User'"
    :description="isset($user) ? 'Update user information and permissions' : 'Add a new user to your organization'"
    :breadcrumbs="[
        ['label' => 'Users', 'url' => route('v2.users.index', ['company' => app('current.company')])],
        ['label' => isset($user) ? 'Edit User' : 'Create User']
    ]"
/>

<form method="POST" 
      action="{{ isset($user) ? route('v2.users.update', ['company' => app('current.company'), 'user' => $user->id]) : route('v2.users.store', ['company' => app('current.company')]) }}"
      class="max-w-4xl">
    @csrf
    @if(isset($user))
        @method('PUT')
    @endif

    <!-- Basic Information -->
    <x-form-section 
        title="Basic Information"
        description="Enter the user's personal details"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-text-input
                label="Full Name"
                name="name"
                :value="$user->name ?? ''"
                placeholder="John Doe"
                required
            />

            <x-text-input
                label="Email Address"
                name="email"
                type="email"
                :value="$user->email ?? ''"
                placeholder="john@example.com"
                required
            />

            <x-text-input
                label="Phone Number"
                name="phone"
                type="tel"
                :value="$user->phone ?? ''"
                placeholder="+1 (555) 000-0000"
            />

            <x-select-input
                label="Status"
                name="status"
                :value="$user->status ?? 'active'"
                :options="['active' => 'Active', 'inactive' => 'Inactive']"
                required
            />
        </div>
    </x-form-section>

    <!-- Password -->
    @if(!isset($user))
    <x-form-section 
        title="Password"
        description="Set the initial password for this user"
        class="mt-6"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-text-input
                label="Password"
                name="password"
                type="password"
                placeholder="••••••••"
                required
            />

            <x-text-input
                label="Confirm Password"
                name="password_confirmation"
                type="password"
                placeholder="••••••••"
                required
            />
        </div>
    </x-form-section>
    @else
    <x-form-section 
        title="Password"
        description="Leave blank to keep current password"
        class="mt-6"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-text-input
                label="New Password"
                name="password"
                type="password"
                placeholder="••••••••"
            />

            <x-text-input
                label="Confirm Password"
                name="password_confirmation"
                type="password"
                placeholder="••••••••"
            />
        </div>
    </x-form-section>
    @endif

    <!-- Roles & Permissions -->
    <x-form-section 
        title="Roles & Permissions"
        description="Assign roles to control access"
        class="mt-6"
    >
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Select Roles</label>
            <div class="space-y-2">
                @foreach($roles ?? [] as $role)
                <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="roles[]" 
                        value="{{ $role->id }}"
                        {{ isset($user) && $user->roles->contains($role->id) ? 'checked' : '' }}
                        class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500"
                    >
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $role->name }}</p>
                        @if($role->description)
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $role->description }}</p>
                        @endif
                    </div>
                </label>
                @endforeach
            </div>
            @error('roles')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </x-form-section>

    <!-- Additional Settings -->
    <x-form-section 
        title="Additional Settings"
        description="Configure user preferences"
        class="mt-6"
    >
        <div class="space-y-4">
            <x-toggle-input
                label="Email Notifications"
                name="email_notifications"
                :checked="$user->email_notifications ?? true"
                description="Receive email notifications for important updates"
            />

            <x-toggle-input
                label="Two-Factor Authentication"
                name="two_factor_enabled"
                :checked="$user->two_factor_enabled ?? false"
                description="Require 2FA for enhanced security"
            />
        </div>
    </x-form-section>

    <!-- Actions -->
    <div class="mt-6 flex items-center gap-3">
        <button type="submit" 
                class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            {{ isset($user) ? 'Update User' : 'Create User' }}
        </button>
        <a href="{{ route('v2.users.index', ['company' => app('current.company')]) }}" 
           class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-sm font-medium rounded-lg transition-colors">
            Cancel
        </a>
    </div>
</form>
@endsection
