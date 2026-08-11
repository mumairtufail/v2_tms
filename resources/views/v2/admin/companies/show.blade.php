@extends('v2.layouts.app')

@section('title', 'Company Details - ' . $company->name)

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[
        ['label' => 'Companies', 'url' => route('admin.companies.index')],
        ['label' => $company->name]
    ]" />

    <!-- Page Header with back button -->
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.companies.index') }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <x-page-header 
                :title="$company->name" 
                :description="'Registered Shortcode: ' . ($company->shortcode ?? 'N/A')" 
            />
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.companies.edit', $company) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Company
            </a>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Company Metadata Info -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider border-b border-gray-100 dark:border-gray-800 pb-3">Company Profile</h3>
                
                <div class="space-y-4">
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</span>
                        <div class="mt-1">
                            @if($company->is_active)
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Active</span>
                            @else
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Shortcode</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $company->shortcode ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Slug / Domain identifier</span>
                        <div class="mt-1">
                            <code class="text-xs bg-gray-50 dark:bg-gray-800 px-2 py-1 rounded text-gray-600 dark:text-gray-400 block w-fit">{{ $company->slug }}</code>
                        </div>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Phone Number</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $company->phone ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Address</span>
                        <p class="text-sm font-medium text-gray-900 dark:text-white leading-relaxed">{{ $company->address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Created At</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $company->created_at->format('M d, Y \a\t H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="lg:col-span-2 space-y-6">
            <x-table-container>
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-sm">Staff & Users</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $company->users->count() }} active registrations</p>
                    </div>
                    <a href="{{ route('admin.users.create', ['company_id' => $company->id]) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add User
                    </a>
                </div>

                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($company->users as $user)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                    {{ $user->primaryRoleLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->status === 'active' || $user->is_active)
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Active</span>
                                @else
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(!$user->is_super_admin && $user->id !== auth()->id())
                                    <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 text-xs font-semibold rounded-lg transition-colors border border-indigo-100 dark:border-indigo-900/50" 
                                                title="Login as this user">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            Impersonate
                                        </button>
                                    </form>
                                    @endif
                                    <a href="{{ route('admin.users.edit', $user) }}" class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors" title="Edit User">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No registered users found for this company.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table-container>
        </div>
    </div>
</div>
@endsection
