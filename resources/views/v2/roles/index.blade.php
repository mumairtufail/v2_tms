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
                        <x-search-highlight :text="$role->display_name" :search="request('search')" />
                        @if($role->isProtectedAdmin())
                            <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-medium rounded bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 align-middle" title="Created with the company. It always has full access and cannot be renamed or deleted.">Built-in</span>
                        @endif
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
                        @php
                            $isAdminRole = $role->isProtectedAdmin();
                            $roleUsersCount = $role->users_count ?? $role->users()->count();
                        @endphp
                        <div class="flex items-center justify-end gap-1">
                            <!-- Permissions Button. Admin's grid is locked: it always carries full
                                 access, so there is nothing here to edit (RoleController::updatePermissions
                                 rejects the request regardless; this just saves the round trip). -->
                            @if(auth()->user()->hasPermission('roles', 'update'))
                            <x-tooltip :text="$isAdminRole ? 'Admin always has full access and cannot be edited' : 'Manage Permissions'" position="top">
                                <button type="button"
                                    @if($isAdminRole) disabled @else @click="$dispatch('open-modal', 'permissions-role-{{ $role->id }}')" @endif
                                    class="p-1 {{ $isAdminRole ? 'text-gray-300 dark:text-gray-700 cursor-not-allowed' : 'text-gray-400 hover:text-blue-600' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11.536 19.464a3 3 0 01-.879.879l-2.121 2.121h-3v-3l2.121-2.121a3 3 0 01.879-.879L13.257 14.257A6 6 0 0119.5 13h.002z"/></svg>
                                </button>
                            </x-tooltip>

                            <!-- Edit Button. The built-in Admin role keeps its name: renaming it
                                 used to switch off its protection (RoleController::update refuses
                                 it regardless; this just explains it before the click). -->
                            <x-tooltip :text="$isAdminRole ? 'The Admin role is built in, so its name cannot be changed' : 'Edit Role Name'" position="top">
                                <button type="button"
                                    @if($isAdminRole) disabled aria-disabled="true" @else @click="$dispatch('open-modal', 'edit-role-{{ $role->id }}')" @endif
                                    class="p-1 {{ $isAdminRole ? 'text-gray-300 dark:text-gray-700 cursor-not-allowed' : 'text-gray-400 hover:text-primary-600' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                            </x-tooltip>
                            @endif

                            <!-- Delete Button. Admin can never be deleted; a role with assigned
                                 users is blocked with a dedicated error modal that names why,
                                 rather than opening the normal delete-confirm dialog. -->
                            @if(auth()->user()->hasPermission('roles', 'delete'))
                            <x-tooltip :text="$isAdminRole ? 'The Admin role cannot be deleted' : 'Delete Role'" position="top">
                                <button type="button"
                                    @if($isAdminRole)
                                        disabled
                                    @elseif($roleUsersCount > 0)
                                        @click="$dispatch('open-modal', 'role-in-use-{{ $role->id }}')"
                                    @else
                                        @click="$dispatch('open-modal', 'delete-role-{{ $role->id }}')"
                                    @endif
                                    class="p-1 {{ $isAdminRole ? 'text-gray-300 dark:text-gray-700 cursor-not-allowed' : 'text-gray-400 hover:text-red-600' }}">
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

@if(session('open_role_modal'))
{{-- A rejected create/rename comes back here with its dialog reopened and the
     reason under the field. Validation errors are not shown as toasts, so
     before this the dialog simply closed and nothing appeared to happen. --}}
<div x-data x-init="$nextTick(() => $dispatch('open-modal', @js(session('open_role_modal'))))"></div>
@endif

<!-- Create Role Modal -->
<x-modal name="create-role-modal" maxWidth="md">
    <div class="p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Create New Role</h3>
        <form action="{{ route('v2.roles.store', app('current.company')) }}" method="POST">
            @csrf
            <div class="space-y-4">
                @php $createHasError = session('open_role_modal') === 'create-role-modal'; @endphp
                <div>
                    <x-input-label for="create_name" value="Role Name" :required="true" />
                    <input type="text" id="create_name" name="name" maxlength="255" required placeholder="e.g. Dispatcher"
                           value="{{ $createHasError ? old('name') : '' }}"
                           @if($createHasError) aria-invalid="true" aria-describedby="create_name_error" @endif
                           class="w-full mt-1 px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border {{ $createHasError ? 'border-red-400 dark:border-red-600' : 'border-gray-200 dark:border-gray-700' }} rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    @if($createHasError)
                        <p id="create_name_error" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $errors->roleForm->first('name') }}</p>
                    @endif
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
                @php $editHasError = session('open_role_modal') === 'edit-role-'.$role->id; @endphp
                <div>
                    <x-input-label for="edit_name_{{ $role->id }}" value="Role Name" :required="true" />
                    <input type="text" id="edit_name_{{ $role->id }}" name="name" maxlength="255" required
                           value="{{ $editHasError ? old('name') : $role->name }}"
                           @if($editHasError) aria-invalid="true" aria-describedby="edit_name_{{ $role->id }}_error" @endif
                           class="w-full mt-1 px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border {{ $editHasError ? 'border-red-400 dark:border-red-600' : 'border-gray-200 dark:border-gray-700' }} rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    @if($editHasError)
                        <p id="edit_name_{{ $role->id }}_error" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $errors->roleForm->first('name') }}</p>
                    @endif
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

<!-- Role In Use Modal. Shown instead of the delete-confirm dialog when the
     role still has users on it - deleting would silently strip their access,
     so the only way forward is unassigning them first. The server enforces
     the same rule (RoleController::destroy), this is the UI-level warning. -->
<x-confirm-modal name="role-in-use-{{ $role->id }}" title="Can't delete this role">
    <div class="flex items-start gap-3">
        <div class="shrink-0 w-9 h-9 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            <strong>{{ $role->name }}</strong> is currently assigned to
            <strong>{{ $role->users_count ?? $role->users()->count() }} {{ \Illuminate\Support\Str::plural('user', $role->users_count ?? $role->users()->count()) }}</strong>.
            Deleting it would remove their permissions, so it can't be deleted while anyone is on it.
            Reassign {{ (($role->users_count ?? $role->users()->count()) === 1) ? 'that user' : 'those users' }} to another role first, then try again.
        </p>
    </div>
    <x-slot name="footer">
        <button type="button" @click="$dispatch('close-modal', 'role-in-use-{{ $role->id }}')" class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg">Got it</button>
    </x-slot>
</x-confirm-modal>
@endforeach
@endsection
