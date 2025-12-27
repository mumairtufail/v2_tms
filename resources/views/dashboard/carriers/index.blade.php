@extends('layouts.app')
@section('content')

<div class="container-fluid py-4 min-vh-100">
    <!-- Breadcrumb -->
    <x-breadcrumb 
        title="Carrier Management" 
        subtitle="Manage your organization's carriers" 
        icon="fa-truck-moving">
        <li class="breadcrumb-item active" aria-current="page">Carriers</li>
        
        <x-slot name="actions">
            @canPermission('carriers', 'create')
            <a href="{{ route('carriers.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-2"></i>Add Carrier
            </a>
            @endcanPermission
        </x-slot>
    </x-breadcrumb>
    <!-- Filters Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="{{ route('carriers.index') }}" method="GET" class="row g-3 align-items-end" id="filtersForm">
                <div class="col-md-2">
                    <label for="search" class="form-label small">Search</label>
                    <input type="text" id="search" name="search" class="form-control form-control-sm"
                        placeholder="Search by name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label for="dot_id" class="form-label small">DOT ID</label>
                    <input type="text" id="dot_id" name="dot_id" class="form-control form-control-sm"
                        placeholder="DOT ID" value="{{ request('dot_id') }}">
                </div>
                <div class="col-md-2">
                    <label for="currency" class="form-label small">Currency</label>
                    <select id="currency" name="currency" class="form-select form-select-sm">
                        <option value="">All Currencies</option>
                        <option value="USD" {{ request('currency')=='USD' ? 'selected' : '' }}>USD</option>
                        <option value="CAD" {{ request('currency')=='CAD' ? 'selected' : '' }}>CAD</option>
                        <option value="MXN" {{ request('currency')=='MXN' ? 'selected' : '' }}>MXN</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="country" class="form-label small">Country</label>
                    <select id="country" name="country" class="form-select form-select-sm">
                        <option value="">All Countries</option>
                        <option value="US" {{ request('country')=='US' ? 'selected' : '' }}>United States</option>
                        <option value="CA" {{ request('country')=='CA' ? 'selected' : '' }}>Canada</option>
                        <option value="MX" {{ request('country')=='MX' ? 'selected' : '' }}>Mexico</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label for="status" class="form-label small">Status</label>
                    <select id="status" name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status')=='1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status')=='0' ? 'selected' : '' }}>Inactive</option>
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
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="fa fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('carriers.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">
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
                            <th>Carrier Name</th>
                            <th>City</th>
                            <th>Country</th>
                            <th>MC ID</th>
                            <th>DOT ID</th>
                            <th>Status</th>

                            <th class="text-center" style="width: 120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($carriers as $carrier)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!-- <i class="fa fa-truck-moving text-primary me-3 fa-lg"></i> -->
                                    <div>
                                        <div class="fw-bold">{{ $carrier->carrier_name }}</div>
                                        <!-- <div class="text-muted small">{{ $carrier->email }}</div> -->
                                    </div>
                                </div>
                            </td>
                            <td>{{ $carrier->city }}</td>
                            <td>{{ $carrier->country }}</td>
                            <td>{{ $carrier->docket_number }}</td>
                            <td>{{ $carrier->dot_id }}</td>
                            <td>
                                <span class="badge bg-{{ $carrier->is_active ? 'success' : 'danger' }}">
                                    {{ $carrier->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @canPermission('carriers', 'update')
                                    <a href="{{ route('carriers.edit', $carrier->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    @endcanPermission
                                    @canPermission('carriers', 'delete')
                                    <form action="{{ route('carriers.destroy', $carrier->id) }}" method="POST" class="d-inline delete-form">
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
                                    <i class="fa fa-truck-moving text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted">No Carriers Found</h5>
                                    <p class="text-muted mb-3">No carriers have been added to the system yet.</p>
                                    @canPermission('carriers', 'create')
                                    <a href="{{ route('carriers.create') }}" class="btn btn-primary">
                                        <i class="fa fa-plus me-2"></i><span> Add First Carrier</span>
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
        @if($carriers->hasPages())
            <div class="card-footer bg-light border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing {{ $carriers->firstItem() }} to {{ $carriers->lastItem() }} of {{ $carriers->total() }} results
                    </div>
                    <div>
                        {{ $carriers->links() }}
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
            
            if (confirm('Are you sure you want to delete this carrier?')) {
                this.closest('form').submit();
            }
        });
    });

    // Auto-submit filters on change
    const currencyFilter = document.getElementById('currency');
    const countryFilter = document.getElementById('country');
    const statusFilter = document.getElementById('status');
    
    [currencyFilter, countryFilter, statusFilter].forEach(element => {
        if (element) {
            element.addEventListener('change', function() {
                document.getElementById('filtersForm').submit();
            });
        }
    });

    // Search with debounce for both search fields
    const searchInput = document.getElementById('search');
    const dotIdInput = document.getElementById('dot_id');
    
    [searchInput, dotIdInput].forEach(input => {
        if (input) {
            let searchTimeout;
            input.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    document.getElementById('filtersForm').submit();
                }, 500); // Wait 500ms after user stops typing
            });
        }
    });
});
</script>
@endpush

@endsection