@extends('layouts.app')
@section('content')

<div class="container-fluid py-4 min-vh-100">
    <!-- Breadcrumb -->
    <x-breadcrumb 
        title="Equipment Management" 
        subtitle="Manage your organization's equipment" 
        icon="fa-truck">
        <li class="breadcrumb-item active" aria-current="page">Equipment</li>
        
        <x-slot name="actions">
            @canPermission('equipment', 'create')
            <a href="{{ route('equipment.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-2"></i>Add Equipment
            </a>
            @endcanPermission
        </x-slot>
    </x-breadcrumb>
    <!-- Filters Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="{{ route('equipment.index') }}" method="GET" class="row g-3 align-items-end" id="filtersForm">
                <div class="col-md-3">
                    <label for="search" class="form-label small">Search</label>
                    <input type="text" id="search" name="search" class="form-control form-control-sm"
                        placeholder="Search equipment..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label for="type" class="form-label small">Equipment Type</label>
                    <select id="type" name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="Trailer" {{ request('type')=='Trailer' ? 'selected' : '' }}>Trailer</option>
                        <option value="Vehicle" {{ request('type')=='Vehicle' ? 'selected' : '' }}>Vehicle</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label small">Status</label>
                    <select id="status" name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="in_use" {{ request('status')=='in_use' ? 'selected' : '' }}>In Use</option>
                        <option value="available" {{ request('status')=='available' ? 'selected' : '' }}>Available</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label for="per_page" class="form-label small">Show</label>
                    <select id="per_page" name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="fa fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('equipment.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">
                        <i class="fa fa-undo me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Sub Type</th>
                            <th>Status</th>
                            <th>Last Seen</th>
                            <th>Last Location</th>
                            <th class="text-center" style="width: 120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipments as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!-- <i class="fa fa-{{ $item->type == 'Trailer' ? 'truck' : 'car' }} text-primary me-3 fa-lg"></i> -->
                                    <div>
                                        <div class="fw-bold">{{ $item->name }}</div>
                                        <div class="text-muted small">{{ $item->desc }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item->type }}</td>
                            <td>{{ $item->sub_type }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $item->status == 'active' ? 'success' : 
                                    ($item->status == 'inactive' ? 'danger' : 
                                    ($item->status == 'in_use' ? 'warning' : 
                                    ($item->status == 'available' ? 'info' : 'secondary'))) 
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </td>
                            <td>{{ $item->last_seen }}</td>
                            <td>{{ $item->last_location }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @canPermission('equipment', 'update')
                                    <a href="{{ route('equipment.edit', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    @endcanPermission
                                    @canPermission('equipment', 'delete')
                                    <form action="{{ route('equipment.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
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
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fa fa-truck text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted">No Equipment Found</h5>
                                    <p class="text-muted mb-3">No equipment has been added to the system yet.</p>
                                    @canPermission('equipment', 'create')
                                    <a href="{{ route('equipment.create') }}" class="btn btn-primary">
                                        <i class="fa fa-plus me-2"></i><span> Add First Equipment</span>
                                    </a>
                                    @endcanPermission
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination Footer -->
        @if($equipments->hasPages())
            <div class="card-footer bg-light border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing {{ $equipments->firstItem() }} to {{ $equipments->lastItem() }} of {{ $equipments->total() }} results
                    </div>
                    <div>
                        {{ $equipments->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete button functionality
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (confirm('Are you sure you want to delete this equipment?')) {
                this.closest('form').submit();
            }
        });
    });

    // Auto-submit filters on change
    const typeFilter = document.getElementById('type');
    const statusFilter = document.getElementById('status');
    
    [typeFilter, statusFilter].forEach(element => {
        if (element) {
            element.addEventListener('change', function() {
                document.getElementById('filtersForm').submit();
            });
        }
    });

    // Search with debounce
    const searchInput = document.getElementById('search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filtersForm').submit();
            }, 500); // Wait 500ms after user stops typing
        });
    }
});
</script>
@endpush

@endsection