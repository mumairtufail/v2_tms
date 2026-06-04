@extends('v2.layouts.app')

@section('title', 'Profile')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[['label' => 'Profile']]" />

    <!-- Header -->
    <x-page-header title="Profile" description="Manage your account settings" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Information -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Profile Information</h3>
                <form method="post" action="{{ isset($company) ? route('v2.profile.update', ['company' => $company->slug]) : route('profile.update') }}" class="space-y-6">
                    @csrf
                    @method('patch')

                    <div class="grid grid-cols-1 gap-6">
                        <x-text-input
                            label="Name"
                            name="name"
                            :value="old('name', $user->name)"
                            required
                            autofocus
                            autocomplete="name"
                        />

                        <x-text-input
                            label="Email"
                            name="email"
                            type="email"
                            :value="old('email', $user->email)"
                            required
                            autocomplete="username"
                        />
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Save Changes
                        </button>

                        @if (session('status') === 'profile-updated')
                            <p
                                x-data="{ show: true }"
                                x-show="show"
                                x-transition
                                x-init="setTimeout(() => show = false, 2000)"
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >Saved.</p>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Update Password -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Update Password</h3>
                <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                    @csrf
                    @method('put')

                    <div class="grid grid-cols-1 gap-6">
                        <x-password-input
                            label="Current Password"
                            name="current_password"
                            autocomplete="current-password"
                        />

                        <x-password-input
                            label="New Password"
                            name="password"
                            autocomplete="new-password"
                        />

                        <x-password-input
                            label="Confirm Password"
                            name="password_confirmation"
                            autocomplete="new-password"
                        />
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Save Password
                        </button>

                        @if (session('status') === 'password-updated')
                            <p
                                x-data="{ show: true }"
                                x-show="show"
                                x-transition
                                x-init="setTimeout(() => show = false, 2000)"
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >Saved.</p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar / Additional Info -->
        <div class="space-y-6">
            <!-- Account Status -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Account Status</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Role</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400">
                            {{ $user->roles->first()->name ?? 'User' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Joined</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $user->created_at->format('M d, Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
