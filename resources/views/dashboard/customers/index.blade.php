@extends('layouts.app')
@section('content')

<div class="container-fluid py-4 min-vh-100">
    <!-- Breadcrumb -->
    <x-breadcrumb 
        title="Customers Management" 
        subtitle="Manage your organization's customers" 
        icon="fa-building">
        <li class="breadcrumb-item active" aria-current="page">Customers</li>
        
        <x-slot name="actions">
            @canPermission('customers', 'create')
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-2"></i>Add Customer
            </a>
            @endcanPermission
        </x-slot>
    </x-breadcrumb>

    @include('partials.message')
    <!-- Filters Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="{{ route('customers.index') }}" method="GET" class="row g-3 align-items-end" id="filtersForm">
                <div class="col-md-3">
                    <label for="search" class="form-label small">Search</label>
                    <input type="text" id="search" name="search" class="form-control form-control-sm"
                        placeholder="Search customers..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label for="customer_type" class="form-label small">Customer Type</label>
                    <select id="customer_type" name="customer_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="shipper" {{ request('customer_type')=='shipper' ? 'selected' : '' }}>Shipper</option>
                        <option value="broker" {{ request('customer_type')=='broker' ? 'selected' : '' }}>Broker</option>
                        <option value="carrier" {{ request('customer_type')=='carrier' ? 'selected' : '' }}>Carrier</option>
                        <option value="other" {{ request('customer_type')=='other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="is_active" class="form-label small">Status</label>
                    <select id="is_active" name="is_active" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active')=='1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active')=='0' ? 'selected' : '' }}>Inactive</option>
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
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">
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
                            <th>Short Code</th>
                            <th>Type</th>
                            <th>Location Sharing</th>
                            <th>Portal</th>
                            <th>Network</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="customer-initial me-3">
                                        @php
                                        $words = explode(' ', $customer->name);
                                        $initials = '';
                                        if(count($words) >= 2) {
                                        $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                        } else {
                                        $initials = strtoupper(substr($customer->name, 0, 2));
                                        }
                                        $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEEAD', '#D4A5A5',
                                        '#9B59B6', '#3498DB', '#1ABC9C', '#F1C40F'];
                                        $colorIndex = crc32($customer->name) % count($colors);
                                        $bgColor = $colors[$colorIndex];
                                        @endphp
                                        <div class="initial-circle" style="background-color: {{ $bgColor }}">
                                            {{ $initials }}
                                        </div>
                                    </div>
                                    <div class="ml-2">
                                        <div class="fw-bold">{{ $customer->name }}</div>
                                        <div class="text-muted small">{{ $customer->city }}, {{ $customer->state }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $customer->short_code }}</td>
                            <td>
                                <span class="badge bg-{{ $customer->customer_type == 'shipper' ? 'primary' : 
                                    ($customer->customer_type == 'broker' ? 'info' : 
                                    ($customer->customer_type == 'carrier' ? 'success' : 'secondary')) }}">
                                    {{ ucfirst($customer->customer_type) }}
                                </span>
                            </td>
                            <td>{{ $customer->location_sharing }}</td>
                            <td class="text-center">
                                @if($customer->portal)
                                <i class="fa fa-check-circle text-success"></i>
                                @else
                                <i class="fa fa-times-circle text-danger"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($customer->network_customer)
                                <i class="fa fa-check-circle text-success"></i>
                                @else
                                <i class="fa fa-times-circle text-danger"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $customer->is_active ? 'success' : 'danger' }}">
                                    {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @if($customer->quickbooks_id)
                                        <span class="btn btn-sm btn-success" title="Synced to QuickBooks (ID: {{ $customer->quickbooks_id }})">
                                            <i class="bi bi-check-circle"></i>
                                        </span>
                                    @else
                                        <span class="btn btn-sm btn-outline-secondary" title="Not synced to QuickBooks">
                                            <i class="bi bi-x-circle"></i>
                                        </span>
                                    @endif
                                    
                                    @canPermission('customers', 'update')
                                    <a href="{{ route('customers.edit', $customer->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    @endcanPermission
                                    @canPermission('customers', 'delete')
                                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST"
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
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fa fa-building text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted">No Customers Found</h5>
                                    <p class="text-muted mb-3">No customers have been added to the system yet.</p>
                                    @canPermission('customers', 'create')
                                    <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                        <i class="fa fa-plus me-2"></i><span> Add First Customer</span>
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
        @if($customers->hasPages())
            <div class="card-footer bg-light border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} results
                    </div>
                    <div>
                        {{ $customers->links() }}
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
            
            if (confirm('Are you sure you want to delete this customer?')) {
                this.closest('form').submit();
            }
        });
    });

    // Auto-submit filters on change
    const customerTypeFilter = document.getElementById('customer_type');
    const statusFilter = document.getElementById('is_active');
    
    [customerTypeFilter, statusFilter].forEach(element => {
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

<style>
    .initial-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }

    .badge {
        font-weight: 500;
        padding: 0.5em 0.75em;
        color: #ffffff;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Handle delete confirmations
    const deleteButtons = document.querySelectorAll('.delete-button');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (confirm('Are you sure you want to delete this customer?')) {
                this.closest('form').submit();
            }
        });
    });
});
</script>
@endpush

@endsection