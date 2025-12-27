@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 min-vh-100">
    <!-- Breadcrumb -->
    <x-breadcrumb 
        title="Order Management" 
        subtitle="Manage and track all your shipping orders" 
        icon="fa-shopping-cart">
        <li class="breadcrumb-item active" aria-current="page">Orders</li>
        
        <x-slot name="actions">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerSelectionModal">
                <i class="fa fa-plus me-2"></i>New Order
            </button>
        </x-slot>
    </x-breadcrumb>

    <!-- Stats Cards -->
    {{-- <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-white-50 mb-0">Total Orders</h6>
                            <h4 class="mb-0">{{ $orders->total() }}</h4>
                        </div>
                        <div class="ms-3">
                            <i class="fa fa-shopping-cart fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-white-50 mb-0">In Progress</h6>
                            <h4 class="mb-0">{{ $orders->where('status', 'in_progress')->count() }}</h4>
                        </div>
                        <div class="ms-3">
                            <i class="fa fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-white-50 mb-0">Completed</h6>
                            <h4 class="mb-0">{{ $orders->where('status', 'completed')->count() }}</h4>
                        </div>
                        <div class="ms-3">
                            <i class="fa fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-white-50 mb-0">Today</h6>
                            <h4 class="mb-0">{{ $orders->where('created_at', '>=', today())->count() }}</h4>
                        </div>
                        <div class="ms-3">
                            <i class="fa fa-calendar-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Filters Section -->
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            
            <!-- Filters Row -->
            <form action="{{ route('orders.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="search" class="form-label small">Search</label>
                    <input type="text" id="search" name="search" class="form-control form-control-sm"
                        placeholder="Order #, PO Number..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label small">Status</label>
                    <select id="status" name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label small">From Date</label>
                    <input type="date" id="date_from" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label small">To Date</label>
                    <input type="date" id="date_to" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="fa fa-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">
                        <i class="fa fa-undo me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Compact Orders Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Order Details</th>
                            {{-- <th>Route</th> --}}
                            <th>Customer</th>
                            <th>Status & Type</th>
                            <th>Stats</th>
                            <th>Date</th>
                            <th class="text-center pe-3" style="width: 100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr class="order-row" onclick="window.location='{{ route('orders.edit', $order->id) }}'" style="cursor: pointer;">
                            <td class="ps-3">
                                <div class="order-info">
                                    <div class="fw-bold text-primary">{{ $order->order_number }}</div>
                                    @if($order->po_number)
                                        <small class="text-muted">PO: {{ $order->po_number }}</small>
                                    @endif
                                    @if($order->manifest)
                                        <div class="mt-1">
                                            <span class="badge bg-light text-dark border small">
                                                <i class="fa fa-clipboard-list me-1"></i>{{ $order->manifest->code }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            {{-- <td>
                                @php
                                    $shipperStop = $order->stops->where('stop_type', 'pickup')->first();
                                    $consigneeStop = $order->stops->where('stop_type', 'delivery')->first();
                                    $totalStops = $order->stops->count();
                                @endphp
                                <div class="route-compact">
                                    @if($shipperStop)
                                        <div class="pickup-info">
                                            <i class="fa fa-arrow-up text-success me-1"></i>
                                            <span class="fw-semibold">{{ Str::limit($shipperStop->company_name, 20) }}</span>
                                            @if($shipperStop->city && $shipperStop->state)
                                                <br><small class="text-muted ms-3">{{ $shipperStop->city }}, {{ $shipperStop->state }}</small>
                                            @endif
                                        </div>
                                    @endif
                                    @if($consigneeStop)
                                        <div class="delivery-info mt-1">
                                            <i class="fa fa-arrow-down text-primary me-1"></i>
                                            <span class="fw-semibold">{{ Str::limit($consigneeStop->company_name, 20) }}</span>
                                            @if($consigneeStop->city && $consigneeStop->state)
                                                <br><small class="text-muted ms-3">{{ $consigneeStop->city }}, {{ $consigneeStop->state }}</small>
                                            @endif
                                        </div>
                                    @endif
                                    @if($totalStops > 2)
                                        <small class="text-muted">+{{ $totalStops - 2 }} more stops</small>
                                    @endif
                                </div>
                            </td> --}}
                            <td>
                                @if($order->customer)
                                    <div class="customer-info">
                                        <div class="fw-semibold">{{ Str::limit($order->customer->name, 25) }}</div>
                                        @if($order->customer->city && $order->customer->state)
                                            <small class="text-muted">{{ $order->customer->city }}, {{ $order->customer->state }}</small>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small">Not assigned</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusClass = match($order->status) {
                                        'new' => 'bg-info',
                                        'in_progress' => 'bg-warning',
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                        'draft' => 'bg-secondary',
                                        default => 'bg-dark',
                                    };
                                    $orderTypeClass = match($order->order_type) {
                                        'point_to_point' => 'bg-primary',
                                        'single_shipper' => 'bg-success',
                                        'single_consignee' => 'bg-warning',
                                        'sequence' => 'bg-info',
                                        'multi_stop' => 'bg-dark',
                                        default => 'bg-secondary',
                                    };
                                    $orderTypeLabel = match($order->order_type) {
                                        'point_to_point' => 'P2P',
                                        'single_shipper' => 'Single Ship',
                                        'single_consignee' => 'Single Con',
                                        'sequence' => 'Sequence',
                                        'multi_stop' => 'Multi Stop',
                                        default => 'Unknown',
                                    };
                                @endphp
                                <div class="status-badges">
                                    <span class="badge {{ $statusClass }} small">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                    <br>
                                    <span class="badge {{ $orderTypeClass }} text-white small mt-1">{{ $orderTypeLabel }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="order-stats-compact">
                                    <div class="stat-item">
                                        <i class="fa fa-map-marker-alt text-muted me-1"></i>
                                        <span class="fw-semibold">{{ $order->stops->count() }}</span>
                                        <small class="text-muted">stops</small>
                                    </div>
                                    @php
                                        $commoditiesCount = 0;
                                        $totalWeight = 0;
                                        foreach($order->stops as $stop) {
                                            if($stop->commodities) {
                                                $commoditiesCount += $stop->commodities->count();
                                                foreach($stop->commodities as $commodity) {
                                                    $totalWeight += $commodity->weight ?? 0;
                                                }
                                            }
                                        }
                                    @endphp
                                    @if($commoditiesCount > 0)
                                        <div class="stat-item">
                                            <i class="fa fa-boxes text-muted me-1"></i>
                                            <span class="fw-semibold">{{ $commoditiesCount }}</span>
                                            <small class="text-muted">items</small>
                                        </div>
                                    @endif
                                    @if($totalWeight > 0)
                                        <div class="stat-item">
                                            <i class="fa fa-weight-hanging text-muted me-1"></i>
                                            <span class="fw-semibold">{{ number_format($totalWeight, 0) }}</span>
                                            <small class="text-muted">lbs</small>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="date-info">
                                    <div class="fw-semibold">{{ $order->created_at->format('M d') }}</div>
                                    <small class="text-muted">{{ $order->created_at->format('Y') }}</small>
                                </div>
                            </td>
                            <td class="pe-3">
                                <div class="d-flex justify-content-center gap-1" onclick="event.stopPropagation();">
                                    <a href="{{ route('orders.edit', $order->id) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Edit Order">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger delete-button" 
                                                title="Delete Order">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fa fa-shipping-fast text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <h5 class="text-muted mb-2">No Orders Found</h5>
                                    <p class="text-muted mb-3">There are no orders that match your current filters.</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerSelectionModal">
                                        <i class="fa fa-plus me-2"></i>Create Your First Order
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete button functionality
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                if (confirm('Are you sure you want to delete this order?')) {
                    this.closest('form').submit();
                }
            });
        });
    });
</script>
@endpush

<!-- Customer Selection Modal -->
<div class="modal fade" id="customerSelectionModal" tabindex="-1" aria-labelledby="customerSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerSelectionModalLabel">
                    <i class="fa fa-users text-primary me-2"></i>
                    Select Customer for New Order
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Search Input -->
                <div class="mb-3">
                    <label for="customerSearch" class="form-label">Search Customers</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="customerSearch" placeholder="Search by company name, contact, email..." autocomplete="off">
                    </div>
                </div>
                
                <!-- Loading State -->
                <div id="customersLoading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"></span>
                    </div>
                    <p class="mt-2 text-muted">Searching customers...</p>
                </div>
                
                <!-- Customers List -->
                <div id="customersList" style="max-height: 400px; overflow-y: auto;">
                    <!-- Customers will be loaded here via AJAX -->
                </div>
                
                <!-- No Results -->
                <div id="noCustomers" class="text-center py-5" style="display: none;">
                    <i class="fa fa-users text-muted mb-3" style="font-size: 3rem;"></i>
                    <h6 class="text-muted">No customers found</h6>
                    <p class="text-muted small">Try adjusting your search or create a new customer first.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <div class="d-flex gap-2">
                    <button type="button" id="selectCustomerBtn" class="btn btn-success" disabled data-bs-dismiss="modal">
                        <i class="fa fa-check me-1"></i>
                        Select Customer
                    </button>
                    {{-- <a href="#" class="btn btn-outline-primary">
                        <i class="fa fa-plus me-1"></i>
                        Create New Customer
                    </a> --}}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerModal = document.getElementById('customerSelectionModal');
    const customerSearch = document.getElementById('customerSearch');
    const customersList = document.getElementById('customersList');
    const customersLoading = document.getElementById('customersLoading');
    const noCustomers = document.getElementById('noCustomers');
    let searchTimeout;

    // Load customers when modal opens
    customerModal.addEventListener('shown.bs.modal', function() {
        selectedCustomerId = null;
        document.getElementById('selectCustomerBtn').disabled = true;
        loadCustomers('');
        customerSearch.focus();
    });

    // Search customers with debounce
    customerSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        console.log('Search input changed:', query);
        
        searchTimeout = setTimeout(() => {
            console.log('Executing search after debounce:', query);
            loadCustomers(query);
        }, 300);
    });

    // Load customers function
    function loadCustomers(searchQuery = '') {
        console.log('Loading customers with query:', searchQuery);
        showLoading(true);
        
        fetch('/api/customers/search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ query: searchQuery })
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            showLoading(false);
            
            if (data.success && data.customers && data.customers.length > 0) {
                displayCustomers(data.customers);
            } else {
                showNoResults();
            }
        })
        .catch(error => {
            console.error('Error loading customers:', error);
            console.error('Full error details:', error);
            showLoading(false);
            showNoResults();
        });
    }

    // Display customers in list
    function displayCustomers(customers) {
        customersList.style.display = 'block';
        noCustomers.style.display = 'none';
        
        // Reset selection when new customers are loaded
        selectedCustomerId = null;
        document.getElementById('selectCustomerBtn').disabled = true;
        
        const html = customers.map(customer => `
            <div class="customer-item border rounded p-2 mb-2 cursor-pointer" data-customer-id="${customer.id}" onclick="selectCustomerItem(this, ${customer.id})">
                <div class="d-flex align-items-center">
                    <div class="customer-avatar me-2">
                        ${getCustomerInitials(customer.company_name)}
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">${customer.company_name || 'N/A'}</div>
                        <div class="text-muted small">
                            ${customer.email ? customer.email : ''} ${customer.city ? '• ' + customer.city : ''}
                        </div>
                    </div>
                    <div class="text-end">
                        <i class="fa fa-check text-success d-none selected-icon"></i>
                        <i class="fa fa-chevron-right text-muted not-selected-icon"></i>
                    </div>
                </div>
            </div>
        `).join('');
        
        customersList.innerHTML = html;
    }

    // Generate customer initials
    function getCustomerInitials(companyName) {
        if (!companyName) return '<div class="avatar-circle bg-secondary">?</div>';
        
        const initials = companyName
            .split(' ')
            .slice(0, 2)
            .map(word => word.charAt(0).toUpperCase())
            .join('');
            
        const colors = [
            'bg-primary', 'bg-success', 'bg-info', 'bg-warning', 
            'bg-danger', 'bg-secondary', 'bg-dark'
        ];
        
        const colorIndex = companyName.length % colors.length;
        const colorClass = colors[colorIndex];
        
        return `<div class="avatar-circle ${colorClass}">${initials}</div>`;
    }

    // Show/hide loading state
    function showLoading(show) {
        customersLoading.style.display = show ? 'block' : 'none';
        customersList.style.display = show ? 'none' : 'block';
        noCustomers.style.display = 'none';
    }

    // Show no results
    function showNoResults() {
        customersList.style.display = 'none';
        noCustomers.style.display = 'block';
    }

    let selectedCustomerId = null;
    
    // Function to select a customer item
    window.selectCustomerItem = function(element, customerId) {
        // Remove selection from all items
        document.querySelectorAll('.customer-item').forEach(item => {
            item.classList.remove('border-success', 'bg-light');
            item.querySelector('.selected-icon').classList.add('d-none');
            item.querySelector('.not-selected-icon').classList.remove('d-none');
        });
        
        // Add selection to clicked item
        element.classList.add('border-success', 'bg-light');
        element.querySelector('.selected-icon').classList.remove('d-none');
        element.querySelector('.not-selected-icon').classList.add('d-none');
        
        // Store selected customer ID
        selectedCustomerId = customerId;
        
        // Enable select button
        document.getElementById('selectCustomerBtn').disabled = false;
    };
    
    // Handle select customer button click
    document.getElementById('selectCustomerBtn').addEventListener('click', function() {
        if (selectedCustomerId) {
            // Create order immediately via POST request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/orders';
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            // Add customer_id
            const customerInput = document.createElement('input');
            customerInput.type = 'hidden';
            customerInput.name = 'customer_id';
            customerInput.value = selectedCustomerId;
            form.appendChild(customerInput);
            
            // Add default order_type
            const orderTypeInput = document.createElement('input');
            orderTypeInput.type = 'hidden';
            orderTypeInput.name = 'order_type';
            orderTypeInput.value = 'point_to_point';
            form.appendChild(orderTypeInput);
            
            // Submit form
            document.body.appendChild(form);
            form.submit();
        }
    });
});
</script>

<style>
.customer-item {
    transition: all 0.2s ease;
    cursor: pointer;
}

.customer-item:hover {
    background-color: #f8f9fa;
    border-color: #007bff !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.customer-item.border-success {
    border-color: #28a745 !important;
    background-color: #f8fff9 !important;
}

.customer-item.border-success:hover {
    border-color: #28a745 !important;
    background-color: #f8fff9 !important;
}

.avatar-circle {
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

.cursor-pointer {
    cursor: pointer;
}

.btn-close {
    background: none;
    border: none;
    font-size: 20px;
    color: #6c757d;
    opacity: 0.8;
    padding: 8px;
    border-radius: 4px;
}

.btn-close:hover {
    color: #000;
    opacity: 1;
    background-color: #f8f9fa;
}

/* Avatar styles for orders table */
.customer-avatar-xs, .manifest-icon-xs {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: bold;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    flex-shrink: 0;
}

.manifest-icon-xs {
    font-size: 8px;
}

.badge-xs {
    font-size: 0.65rem;
    padding: 0.15rem 0.35rem;
}

/* Table row hover effect */
.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.05);
}

/* Order type badges */
.badge.bg-primary { background-color: #0d6efd !important; }
.badge.bg-success { background-color: #198754 !important; }
.badge.bg-warning { background-color: #ffc107 !important; color: #000 !important; }
.badge.bg-info { background-color: #0dcaf0 !important; color: #000 !important; }
.badge.bg-dark { background-color: #212529 !important; }

/* Compact Table Styles */
.table-sm td, .table-sm th {
    padding: 0.75rem 0.5rem;
}

.order-row {
    transition: all 0.2s ease;
}

.order-row:hover {
    background-color: rgba(0, 123, 255, 0.05) !important;
    transform: scale(1.01);
}

/* Order Info Styles */
.order-info .fw-bold {
    font-size: 0.95rem;
}

/* Route Compact Styles */
.route-compact {
    font-size: 0.85rem;
    line-height: 1.3;
}

.pickup-info, .delivery-info {
    margin-bottom: 0.25rem;
}

/* Customer Info */
.customer-info {
    font-size: 0.9rem;
}

/* Status Badges */
.status-badges .badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
}

/* Order Stats Compact */
.order-stats-compact {
    font-size: 0.8rem;
}

.stat-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.25rem;
    gap: 0.25rem;
}

.stat-item:last-child {
    margin-bottom: 0;
}

.stat-item .fw-semibold {
    font-size: 0.85rem;
}

/* Date Info */
.date-info {
    font-size: 0.85rem;
    text-align: center;
}

/* Badge Sizing */
.badge.small {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
}

/* Action Buttons */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}

.btn-outline-primary:hover,
.btn-outline-danger:hover {
    transform: scale(1.05);
}

/* Action Buttons */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}

.btn-outline-primary:hover,
.btn-outline-danger:hover {
    transform: scale(1.05);
}

/* Empty State */
.empty-state {
    padding: 2rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.85rem;
    }
    
    .route-compact {
        font-size: 0.8rem;
    }
    
    .stat-item {
        font-size: 0.75rem;
    }
}
</style>

@endsection