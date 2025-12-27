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
      class="w-full"
      x-data="{ submitting: false }"
      @submit="submitting = true">
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
                label="First Name"
                name="f_name"
                :value="$user->f_name ?? ''"
                placeholder="John"
                required
            />

            <x-text-input
                label="Last Name"
                name="l_name"
                :value="$user->l_name ?? ''"
                placeholder="Doe"
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
            <x-password-input
                label="Password"
                name="password"
                placeholder="••••••••"
                required
            />

            <x-password-input
                label="Confirm Password"
                name="password_confirmation"
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
            <x-password-input
                label="New Password"
                name="password"
                placeholder="••••••••"
            />

            <x-password-input
                label="Confirm Password"
                name="password_confirmation"
                placeholder="••••••••"
            />
        </div>
    </x-form-section>
    @endif

    <!-- Role Assignment -->
    <x-form-section 
        title="Role Assignment"
        description="Assign role and status"
        class="mt-6"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
            <x-select-input
                label="Role"
                name="role"
                :value="isset($user) && $user->roles->first() ? $user->roles->first()->id : ''"
                :options="collect($roles ?? [])->pluck('name', 'id')->toArray()"
                placeholder="Select a role"
                required
            />

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <div class="mt-2">
                    <input type="hidden" name="status" value="inactive">
                    <x-toggle-input
                        label="Active"
                        name="status"
                        value="active"
                        :checked="($user->status ?? 'active') === 'active'"
                        description="User can log in"
                    />
                </div>
            </div>
        </div>
    </x-form-section>

    <!-- Additional Settings (Commented out) -->
    {{--
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
    --}}

    <!-- Actions -->
    <div class="mt-6 flex items-center gap-3">
        <button type="submit" 
                :disabled="submitting"
                class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
            <template x-if="submitting">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </template>
            {{ isset($user) ? 'Update User' : 'Create User' }}
        </button>
        <a href="{{ route('v2.users.index', ['company' => app('current.company')]) }}" 
           class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-sm font-medium rounded-lg transition-colors">
            Cancel
        </a>
    </div>
</form>
@endsection
