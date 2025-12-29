@extends('v2.layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <x-v2-breadcrumb :items="[['label' => 'Roles & Permissions']]" />

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <x-page-header title="Roles & Permissions" description="Manage user roles and access levels" />
        @if(auth()->user()->hasPermission('roles', 'create'))
        <button type="button" @click="$dispatch('open-modal', 'create-role-modal')" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Role
        </button>
        @endif
    </div>

    <!-- Table -->
    <x-table-container>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="w-12 px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">#</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Role Name</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Users</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Permissions</th>
                    <th class="w-32 px-3 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($roles as $index => $role)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                    <td class="px-3 py-2 text-gray-500">{{ $roles->firstItem() + $index }}</td>
                    <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">
                        <x-search-highlight :text="$role->name" :search="request('search')" />
                    </td>
                    <td class="px-3 py-2 text-gray-500">
                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 rounded-full text-xs">
                            {{ $role->users_count ?? $role->users()->count() }} users
                        </span>
                    </td>
                    <td class="px-3 py-2 text-gray-500">
                        {{ $role->permissions()->count() }} modules
                    </td>
                    <td class="px-3 py-2 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <!-- Permissions Button -->
                            @if(auth()->user()->hasPermission('roles', 'update'))
                            <x-tooltip text="Manage Permissions" position="top">
                                <button type="button" @click="$dispatch('open-modal', 'permissions-role-{{ $role->id }}')" class="p-1 text-gray-400 hover:text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11.536 19.464a3 3 0 01-.879.879l-2.121 2.121h-3v-3l2.121-2.121a3 3 0 01.879-.879L13.257 14.257A6 6 0 0119.5 13h.002z"/></svg>
                                </button>
                            </x-tooltip>

                            <!-- Edit Button -->
                            <x-tooltip text="Edit Role Name" position="top">
                                <button type="button" @click="$dispatch('open-modal', 'edit-role-{{ $role->id }}')" class="p-1 text-gray-400 hover:text-primary-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                            </x-tooltip>
                            @endif

                            <!-- Delete Button -->
                            @if(auth()->user()->hasPermission('roles', 'delete'))
                            <x-tooltip text="Delete Role" position="top">
                                <button type="button" @click="$dispatch('open-modal', 'delete-role-{{ $role->id }}')" class="p-1 text-gray-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </x-tooltip>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">No roles found</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($roles->hasPages())
        <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-800">{{ $roles->links() }}</div>
        @endif
    </x-table-container>
</div>

<!-- Create Role Modal -->
<x-modal name="create-role-modal" maxWidth="md">
    <div class="p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Create New Role</h3>
        <form action="{{ route('v2.roles.store', app('current.company')) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <x-input-label for="create_name" value="Role Name" :required="true" />
                    <x-text-input id="create_name" name="name" class="w-full mt-1" required placeholder="e.g. Dispatcher" />
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button @click="$dispatch('close-modal', 'create-role-modal')">Cancel</x-secondary-button>
                <x-primary-button>Create Role</x-primary-button>
            </div>
        </form>
    </div>
</x-modal>

@foreach($roles as $role)
<!-- Edit Role Modal -->
<x-modal name="edit-role-{{ $role->id }}" maxWidth="md">
    <div class="p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Edit Role</h3>
        <form action="{{ route('v2.roles.update', ['company' => app('current.company'), 'role' => $role->id]) }}" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <x-input-label for="edit_name_{{ $role->id }}" value="Role Name" :required="true" />
                    <x-text-input id="edit_name_{{ $role->id }}" name="name" value="{{ $role->name }}" class="w-full mt-1" required />
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button @click="$dispatch('close-modal', 'edit-role-{{ $role->id }}')">Cancel</x-secondary-button>
                <x-primary-button>Update Role</x-primary-button>
            </div>
        </form>
    </div>
</x-modal>

<!-- Manage Permissions Modal -->
<x-modal name="permissions-role-{{ $role->id }}" maxWidth="4xl">
    <div class="p-6 h-[80vh] flex flex-col" x-data="{
        init() {
            this.updateRowCheckboxes();
        },
        toggleAll(e) {
            const checkboxes = this.$el.closest('form').querySelectorAll('.permission-checkbox');
            checkboxes.forEach(cb => cb.checked = e.target.checked);
            this.updateRowCheckboxes();
        },
        toggleRow(e, rowId) {
            const row = this.$refs['row-' + rowId];
            const checkboxes = row.querySelectorAll('.permission-checkbox');
            checkboxes.forEach(cb => cb.checked = e.target.checked);
            this.updateMasterCheckbox();
        },
        updateRowCheckboxes() {
            const rows = this.$el.closest('form').querySelectorAll('tr[x-ref]');
            rows.forEach(row => {
                const checkboxes = row.querySelectorAll('.permission-checkbox');
                const rowCheckbox = row.querySelector('input[type=checkbox]:not(.permission-checkbox)');
                if(checkboxes.length > 0 && rowCheckbox) {
                     const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                     const someChecked = Array.from(checkboxes).some(cb => cb.checked);
                     rowCheckbox.checked = allChecked;
                     rowCheckbox.indeterminate = someChecked && !allChecked;
                }
            });
            this.updateMasterCheckbox();
        },
        updateMasterCheckbox() {
             const checkboxes = this.$el.closest('form').querySelectorAll('.permission-checkbox');
             const masterCheckbox = this.$el.closest('form').querySelector('thead input[type=checkbox]');
             if(checkboxes.length > 0 && masterCheckbox) {
                 const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                 const someChecked = Array.from(checkboxes).some(cb => cb.checked);
                 masterCheckbox.checked = allChecked;
                 masterCheckbox.indeterminate = someChecked && !allChecked;
             }
        }
    }">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Permissions for {{ $role->name }}</h3>
        
        <form action="{{ route('v2.roles.update-permissions', ['company' => app('current.company'), 'role' => $role->id]) }}" method="POST" class="flex-1 flex flex-col min-h-0">
            @csrf @method('PUT')
            
            <div class="flex-1 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400 sticky top-0 z-10">
                        <tr>
                            <th scope="col" class="px-6 py-3 bg-gray-50 dark:bg-gray-800">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" @change="toggleAll($event)" class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <span>Module</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-center bg-gray-50 dark:bg-gray-800">View</th>
                            <th scope="col" class="px-6 py-3 text-center bg-gray-50 dark:bg-gray-800">Create</th>
                            <th scope="col" class="px-6 py-3 text-center bg-gray-50 dark:bg-gray-800">Update</th>
                            <th scope="col" class="px-6 py-3 text-center bg-gray-50 dark:bg-gray-800">Delete</th>
                            <th scope="col" class="px-6 py-3 text-center bg-gray-50 dark:bg-gray-800">Logs</th>
                            <th scope="col" class="px-6 py-3 text-center bg-gray-50 dark:bg-gray-800">Others</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($permissions as $permission)
                        <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50" x-ref="row-{{ $permission->id }}">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" @change="toggleRow($event, {{ $permission->id }})" class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <span>{{ ucfirst($permission->name) }}</span>
                                </div>
                            </td>
                            @foreach(['view', 'create', 'update', 'delete', 'logs', 'others'] as $action)
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center">
                                    <input 
                                        type="checkbox" 
                                        name="permissions[{{ $permission->id }}][actions][]" 
                                        value="{{ $action }}"
                                        @change="updateRowCheckboxes()"
                                        class="permission-checkbox w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                        @if($role->hasPermission($permission->name, $action)) checked @endif
                                    >
                                </div>
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-secondary-button @click="$dispatch('close-modal', 'permissions-role-{{ $role->id }}')">Cancel</x-secondary-button>
                <x-primary-button>Save Permissions</x-primary-button>
            </div>
        </form>
    </div>
</x-modal>

<!-- Delete Modal -->
<x-confirm-modal name="delete-role-{{ $role->id }}" title="Delete Role">
    <p class="text-sm text-gray-600 dark:text-gray-400">Delete <strong>{{ $role->name }}</strong>? This will remove permissions for assigned users.</p>
    <x-slot name="footer">
        <button type="button" @click="$dispatch('close-modal', 'delete-role-{{ $role->id }}')" class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg">Cancel</button>
        <form action="{{ route('v2.roles.destroy', ['company' => app('current.company'), 'role' => $role->id]) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Delete</button>
        </form>
    </x-slot>
</x-confirm-modal>
@endforeach
@endsection
