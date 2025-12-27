@extends('layouts.app')
@section('content')

{{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"> --}}

<div class="container-fluid py-4 min-vh-100">

    <!-- Breadcrumb -->
    <x-breadcrumb 
        title="Roles & Permission Management" 
        subtitle="Manage your system roles and permissions" 
        icon="fa-user-shield">
        <li class="breadcrumb-item active" aria-current="page">Roles</li>
        
        <x-slot name="actions">
            @canPermission('roles', 'create')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                <i class="fa fa-plus me-2"></i>Add Role
            </button>
            @endcanPermission
        </x-slot>
    </x-breadcrumb>

    <!-- Table Section -->
    <div class="card shadow-sm">
        <div class="card-body card-body-form">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Role Name</th>
                            <th>Permissions</th>
                            <th class="text-center" style="width: 180px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>
                            <td>{{ $role->name }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <button class="btn btn-sm btn-link text-primary ms-2" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#permissionsModal" 
                                            data-role-id="{{ $role->id }}"
                                            data-role-name="{{ $role->name }}">
                                        Attach Permissions ({{ $role->permissions_count ?? 0 }})
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @canPermission('roles', 'update')
                                    <button class="btn btn-sm btn-outline-primary edit-role" 
                                            data-bs-toggle="modal"
                                            data-bs-target="#editRoleModal" 
                                            data-id="{{ $role->id }}" 
                                            data-name="{{ $role->name }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    @endcanPermission
                                    @canPermission('roles', 'delete')
                                    <button class="btn btn-sm btn-outline-danger delete-role" 
                                            data-id="{{ $role->id }}"
                                            data-name="{{ $role->name }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    @endcanPermission
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Role Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editRoleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Role Name</label>
                        <input type="text" name="name" id="editRoleName" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Permissions Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Permissions - <span id="roleNameTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="permissionsForm" method="POST">
                    @csrf
                    <input type="hidden" name="role_id" id="roleId">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Permission</th>
                                    <th class="text-center">Create</th>
                                    <th class="text-center">Update</th>
                                    <th class="text-center">View</th>
                                    <th class="text-center">Delete</th>
                                    {{-- <th class="text-center">Logs</th>
                                    <th class="text-center">Others</th> --}}
                                    <th class="text-center select-all-header">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fa fa-check-double text-primary mb-1"></i>
                                            <span class="fw-bold">Select All</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="permissionsTableBody">
                                <!-- Will be populated via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="savePermissions">Save Permissions</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Role Modal Handler
    const editRoleModal = document.getElementById('editRoleModal');
    editRoleModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const roleId = button.getAttribute('data-id');
        const roleName = button.getAttribute('data-name');
        
        const form = this.querySelector('#editRoleForm');
        form.action = `/roles/${roleId}`;
        this.querySelector('#editRoleName').value = roleName;
    });

    // Delete Role Handler
    document.querySelectorAll('.delete-role').forEach(button => {
        button.addEventListener('click', function() {
            const roleId = this.getAttribute('data-id');
            const roleName = this.getAttribute('data-name');

            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete the role "${roleName}"`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/roles/${roleId}`;
                    form.innerHTML = `
                        @csrf
                        @method('DELETE')
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

    // Permissions Modal Handler
    const permissionsModal = document.getElementById('permissionsModal');
    permissionsModal.addEventListener('show.bs.modal', async function(event) {
        const button = event.relatedTarget;
        const roleId = button.getAttribute('data-role-id');
        const roleName = button.getAttribute('data-role-name');
        
        document.getElementById('roleId').value = roleId;
        document.getElementById('roleNameTitle').textContent = roleName;

        try {
            const response = await fetch(`/roles/${roleId}/permissions`);
            const data = await response.json();
            
            const tableBody = document.getElementById('permissionsTableBody');
            tableBody.innerHTML = data.permissions.map(permission => `
                <tr>
                    <td>${permission.name}</td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input permission-checkbox" 
                            name="permissions[${permission.id}][create]"
                            data-permission-id="${permission.id}"
                            ${permission.create ? 'checked' : ''}>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input permission-checkbox" 
                            name="permissions[${permission.id}][update]"
                            data-permission-id="${permission.id}"
                            ${permission.update ? 'checked' : ''}>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input permission-checkbox" 
                            name="permissions[${permission.id}][view]"
                            data-permission-id="${permission.id}"
                            ${permission.view ? 'checked' : ''}>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input permission-checkbox" 
                            name="permissions[${permission.id}][delete]"
                            data-permission-id="${permission.id}"
                            ${permission.delete ? 'checked' : ''}>
                    </td>
                    {{-- 
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input permission-checkbox" 
                            name="permissions[${permission.id}][logs]"
                            data-permission-id="${permission.id}"
                            ${permission.logs ? 'checked' : ''}>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input permission-checkbox" 
                            name="permissions[${permission.id}][others]"
                            data-permission-id="${permission.id}"
                            ${permission.others ? 'checked' : ''}>
                    </td>
                    --}}
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input select-all-checkbox select-all-enhanced" 
                            data-permission-id="${permission.id}">
                    </td>
                </tr>
            `).join('');

            // Add event listeners for select all functionality
            addSelectAllListeners();
        } catch (error) {
            console.error('Error fetching permissions:', error);
        }
    });

    // Save Permissions Handler - Updated to properly handle form data
    document.getElementById('savePermissions').addEventListener('click', async function() {
        const form = document.getElementById('permissionsForm');
        const formData = new FormData(form);
        const roleId = document.getElementById('roleId').value;

        // Add CSRF token to form data
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        try {
            const response = await fetch(`/roles/${roleId}/permissions`, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Permissions updated successfully',
                    icon: 'success'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error('Failed to update permissions');
            }
        } catch (error) {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to update permissions',
                icon: 'error'
            });
        }
    });

    // Add this new function before the closing script tag
    function addSelectAllListeners() {
        // Handle select all checkboxes
        document.querySelectorAll('.select-all-checkbox').forEach(selectAllCheckbox => {
            const permissionId = selectAllCheckbox.getAttribute('data-permission-id');
            const permissionCheckboxes = document.querySelectorAll(`input[data-permission-id="${permissionId}"].permission-checkbox`);
            
            // Check if all permission checkboxes are checked to set select all state
            const allChecked = Array.from(permissionCheckboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
            
            // Handle select all checkbox change
            selectAllCheckbox.addEventListener('change', function() {
                permissionCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        });

        // Handle individual permission checkboxes
        document.querySelectorAll('.permission-checkbox').forEach(permissionCheckbox => {
            permissionCheckbox.addEventListener('change', function() {
                const permissionId = this.getAttribute('data-permission-id');
                const selectAllCheckbox = document.querySelector(`.select-all-checkbox[data-permission-id="${permissionId}"]`);
                const permissionCheckboxes = document.querySelectorAll(`input[data-permission-id="${permissionId}"].permission-checkbox`);
                
                // Update select all checkbox based on individual checkboxes
                const allChecked = Array.from(permissionCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(permissionCheckboxes).some(cb => cb.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            });
        });
    }
});
</script>

<style>
/* Your existing styles... */
.table td {
    vertical-align: middle;
}

.form-check-input {
    cursor: pointer;
}

.modal-lg {
    max-width: 900px;
}

.select-all-header {
    background-color: #f8f9ff !important;
    border-left: 3px solid #0d6efd !important;
    min-width: 120px;
}

.select-all-enhanced {
    transform: scale(1.3);
    accent-color: #0d6efd;
    cursor: pointer;
}

.select-all-enhanced:hover {
    transform: scale(1.4);
    transition: transform 0.2s ease;
}

.select-all-enhanced:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>

@endsection