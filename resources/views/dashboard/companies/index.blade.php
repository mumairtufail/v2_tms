@extends('layouts.app')
@section('content')

<div class="container-fluid py-4 min-vh-100">

    <!-- Breadcrumb -->
    <x-breadcrumb 
        title="Companies Management" 
        subtitle="Manage your system companies" 
        icon="fa-building">
        <li class="breadcrumb-item active" aria-current="page">Companies</li>
        
        <x-slot name="actions">
            <a href="{{ route('companies.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-2"></i>Add Company
            </a>
        </x-slot>
    </x-breadcrumb>

    @include('partials.message')

    <!-- Search and Filter Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('companies.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Search Companies</label>
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search by name, address, or phone...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Status</label>
                    <select class="form-control" name="status">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-5 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search me-2"></i>Search
                    </button>
                    <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-refresh me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card shadow-sm">
        <div class="card-body card-body-form">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px">ID</th>
                            <th>Company Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th class="text-center" style="width: 100px">Status</th>
                            <th class="text-center" style="width: 120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $key => $company)
                        <tr>
                            <td class="text-center">{{ $companies->firstItem() + $key }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-light rounded-circle me-3 d-flex align-items-center justify-content-center">
                                        <i class="fa fa-building text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $company->name }}</div>
                                        <small class="text-muted">{{ $company->users->count() }} users</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $company->phone ?? 'N/A' }}</td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $company->address }}">
                                    {{ $company->address ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="text-center">
                                @if($company->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                  
                                    <a class="btn btn-sm btn-outline-primary" 
                                       href="{{ route('companies.edit', $company->id) }}"
                                       title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('companies.destroy', ['id' => $company->id]) }}" 
                                          method="POST" 
                                          class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger delete-button"
                                                title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fa fa-building text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted">No Companies Found</h5>
                                    <p class="text-muted mb-3">
                                        @if(request('search'))
                                            No companies match your search criteria.
                                        @else
                                            No companies have been added to the system yet.
                                        @endif
                                    </p>
                                    @if(!request('search'))
                                        <a href="{{ route('companies.create') }}" class="btn btn-primary">
                                            <i class="fa fa-plus me-2"></i> <span>Add First Company</span>
                                        </a>
                                    @else
                                        <a href="{{ route('companies.index') }}" class="btn btn-outline-primary">
                                            <i class="fa fa-refresh me-2"></i>Clear Search
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($companies->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $companies->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation
    const deleteButtons = document.querySelectorAll('.delete-button');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('.delete-form');
            
            Swal.fire({
                title: 'Are you sure?',
                text: 'You are about to delete this company. This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>

@endsection