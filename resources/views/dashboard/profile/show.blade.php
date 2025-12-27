@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="avatar-xl me-4">
                        @if($user->profile_image)
                            <img src="{{ asset('storage/avatars/' . $user->profile_image) }}" 
                                 alt="Profile Picture" 
                                 class="avatar-img rounded-circle"
                                 style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="avatar-img rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                 style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                                {{ strtoupper(substr($user->f_name, 0, 1)) }}{{ strtoupper(substr($user->l_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <h4 class="mb-1">{{ $user->full_name }}</h4>
                        <p class="text-muted mb-1">{{ $user->email }}</p>
                        <div class="d-flex align-items-center">
                            <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }} me-2">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($user->is_super_admin)
                                <span class="badge badge-warning">Super Admin</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="fa fa-edit me-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('partials.message')

    <div class="row">
        <!-- Personal Information -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fa fa-user text-primary me-2"></i>Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">First Name</label>
                                <p class="info-value">{{ $user->f_name ?: 'Not specified' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Last Name</label>
                                <p class="info-value">{{ $user->l_name ?: 'Not specified' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Email Address</label>
                                <p class="info-value">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Phone Number</label>
                                <p class="info-value">{{ $user->phone ?: 'Not specified' }}</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-item">
                                <label class="info-label">Address</label>
                                <p class="info-value">{{ $user->address ?: 'Not specified' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Company & Role Information -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fa fa-building text-primary me-2"></i>Company Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <label class="info-label">Company</label>
                        <p class="info-value">{{ $user->company->name ?? 'No company assigned' }}</p>
                    </div>
                    <div class="info-item">
                        <label class="info-label">User Type</label>
                        <p class="info-value">
                            @if($user->is_super_admin)
                                <span class="badge badge-warning">Super Administrator</span>
                            @else
                                <span class="badge badge-info">Company User</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Roles & Permissions -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fa fa-shield-alt text-primary me-2"></i>Roles & Permissions
                    </h5>
                </div>
                <div class="card-body">
                    @if($user->roles->count() > 0)
                        <div class="mb-3">
                            <label class="info-label">Assigned Roles</label>
                            <div class="mt-2">
                                @foreach($user->roles as $role)
                                    <span class="badge badge-secondary me-2 mb-2">{{ $role->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <label class="info-label">Permissions Summary</label>
                            <div class="permissions-summary mt-2">
                                @php
                                    $permissions = $user->getAllPermissions();
                                    $totalModules = count($permissions);
                                    $hasCreateAccess = collect($permissions)->where('create', true)->count();
                                    $hasUpdateAccess = collect($permissions)->where('update', true)->count();
                                    $hasDeleteAccess = collect($permissions)->where('delete', true)->count();
                                @endphp
                                
                                <div class="row text-center">
                                    <div class="col-3">
                                        <div class="permission-stat">
                                            <div class="stat-number text-primary">{{ $totalModules }}</div>
                                            <div class="stat-label">Modules</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="permission-stat">
                                            <div class="stat-number text-success">{{ $hasCreateAccess }}</div>
                                            <div class="stat-label">Create</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="permission-stat">
                                            <div class="stat-number text-warning">{{ $hasUpdateAccess }}</div>
                                            <div class="stat-label">Update</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="permission-stat">
                                            <div class="stat-number text-danger">{{ $hasDeleteAccess }}</div>
                                            <div class="stat-label">Delete</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted">No roles assigned</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Account Activity -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fa fa-clock text-primary me-2"></i>Account Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="activity-item">
                                <label class="info-label">Member Since</label>
                                <p class="info-value">{{ $user->created_at->format('F d, Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="activity-item">
                                <label class="info-label">Last Updated</label>
                                <p class="info-value">{{ $user->updated_at->format('F d, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="activity-item">
                                <label class="info-label">Account Status</label>
                                <p class="info-value">
                                    <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-item {
    margin-bottom: 1rem;
}

.info-label {
    display: block;
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.info-value {
    margin-bottom: 0;
    color: #212529;
    font-size: 1rem;
}

.permission-stat {
    text-align: center;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375em 0.75em;
    font-weight: 500;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-info {
    background-color: #17a2b8;
    color: white;
}

.badge-secondary {
    background-color: #6c757d;
    color: white;
}

.permissions-summary {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    padding: 1rem;
}

.activity-item {
    text-align: center;
    padding: 1rem;
}

@media (max-width: 768px) {
    .avatar-xl {
        margin-bottom: 1rem;
    }
    
    .stat-number {
        font-size: 1.25rem;
    }
}
</style>

@endsection
