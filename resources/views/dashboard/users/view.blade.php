@extends('layouts.app')
@section('content')

<div class="container-fluid py-4 min-vh-100">

    <!-- Breadcrumb -->
    <x-breadcrumb 
        title="Users Management" 
        subtitle="Manage your system users" 
        icon="fa-users">
        <li class="breadcrumb-item active" aria-current="page">Users</li>
        
        <x-slot name="actions">
            @canPermission('users', 'create')
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-2"></i>Add User
            </a>
            @endcanPermission
        </x-slot>
    </x-breadcrumb>

    <!-- Filters Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="{{ route('users.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" id="search" name="search" class="form-control"
                        placeholder="Name, email, phone..." value="{{ request('search') }}">
                </div>
                
                <div class="col-md-3">
                    <label for="role_id" class="form-label">Role</label>
                    <select id="role_id" name="role_id" class="form-select">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-filter me-2"></i><span>Filter</span>
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary w-100">
                        <i class="fa fa-undo me-2"></i><span>Reset</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    @include('partials.message')
    <!-- Table Section -->
    <div class="card shadow-sm">
        <div class="card-body card-body-form">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px">ID</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $key => $user)
                        <tr>
                            <td class="text-center">{{ $users->firstItem() + $key }}</td>
                            <td>{{ $user->f_name }} {{ $user->l_name }}</td>
                            <td>
                                @if($user->roles->count() > 0)
                                    {{ $user->roles->first()->name }}
                                @else
                                    <span class="text-muted">No role</span>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @canPermission('users', 'update')
                                    <a class="btn btn-sm btn-outline-primary edit-user"
                                        href="{{ route('users.edit', $user->id) }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    @endcanPermission
                                    @canPermission('users', 'delete')
                                    <form action="{{ route('users.destroy', ['id' => $user->id]) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-button">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcanPermission
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fa fa-users text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted">No Users Found</h5>
                                    <p class="text-muted mb-3">
                                        @if(request()->hasAny(['search', 'role_id', 'status']))
                                            No users match your current filters. Try adjusting your search criteria.
                                        @else
                                            No users have been added to the system yet.
                                        @endif
                                    </p>
                                    @canPermission('users', 'create')
                                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                                        <i class="fa fa-plus me-2"></i> <span> Add First User</span>
                                    </a>
                                    @endcanPermission
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $users->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.05);
}

.badge {
    font-size: 0.75rem;
}

.empty-state {
    padding: 2rem;
}

/* Filter form styling */
.card-body form .form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.form-select, .form-control {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.form-select:focus, .form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.badge.bg-success { background-color: #198754 !important; }
.badge.bg-danger { background-color: #dc3545 !important; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function(e) {
            if (confirm('Are you sure you want to delete this user?')) {
                this.closest('form').submit();
            }
        });
    });

    // Auto-submit form on filter change for better UX
    document.querySelectorAll('#role_id, #status').forEach(select => {
        select.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
});
</script>
@endpush