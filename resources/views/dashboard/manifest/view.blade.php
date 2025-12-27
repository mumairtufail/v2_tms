@extends('layouts.app')
@section('content')

    <div class="container-fluid py-4 min-vh-100">

        <!-- Combined Header & Stats Section -->
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <!-- Header Row -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="mb-1">
                            <i class="fa fa-clipboard-list text-primary me-2"></i>
                            Manifest Management
                        </h4>
                        <!-- Inline Stats -->
                        {{-- <div class="d-flex align-items-center gap-4 mt-2">
                            <small class="text-muted">
                                <i class="fa fa-clipboard-list me-1"></i>
                                <span class="fw-semibold">{{ $manifests->count() }}</span> Total Manifests
                            </small>
                            <small class="text-success">
                                <i class="fa fa-truck me-1"></i>
                                <span class="fw-semibold">{{ $manifests->where('status', 'active')->count() }}</span> Active
                            </small>
                            <small class="text-warning">
                                <i class="fa fa-pause-circle me-1"></i>
                                <span class="fw-semibold">{{ $manifests->where('status', 'pending')->count() }}</span> Pending
                            </small>
                            <small class="text-info">
                                <i class="fa fa-check-circle me-1"></i>
                                <span class="fw-semibold">{{ $manifests->where('status', 'completed')->count() }}</span> Completed
                            </small>
                        </div> --}}
                    </div>
                    @canPermission('manifests', 'create')
                    <a href="{{ route('manifest.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus me-2"></i>New Manifest
                    </a>
                    @endcanPermission
                </div>
                
                <!-- Filters Form -->
                <form method="GET" action="{{ route('manifest.index') }}" id="filtersForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label for="search" class="form-label small">Search</label>
                            <input type="text" id="search" name="search" class="form-control form-control-sm"
                                placeholder="Manifest code, driver..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label small">Status</label>
                            <select id="status" name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label small">From Date</label>
                            <input type="date" id="date_from" name="date_from" class="form-control form-control-sm" 
                                value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label small">To Date</label>
                            <input type="date" id="date_to" name="date_to" class="form-control form-control-sm" 
                                value="{{ request('date_to') }}">
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
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fa fa-filter me-1"></i>Filter
                            </button>
                            <a href="{{ route('manifest.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">
                                <i class="fa fa-undo me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Manifests Table -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Manifest</th>
                                <th>Driver & Equipment</th>
                                <th>Orders</th>
                                <th>Route Progress</th>
                                <th>Stats</th>
                                <th>Revenue</th>
                                <th>Status</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($manifests as $manifest)
                                @php
                                    $statusClass = match($manifest->status ?? 'pending') {
                                        'active' => 'bg-success',
                                        'completed' => 'bg-info',
                                        'cancelled' => 'bg-danger',
                                        'pending' => 'bg-warning',
                                        default => 'bg-secondary',
                                    };
                                    
                                    // Get orders assigned to this manifest (via order stops)
                                    $manifestOrders = \App\Models\Order::whereHas('stops', function($query) use ($manifest) {
                                        $query->where('manifest_id', $manifest->id);
                                    })->get();
                                    
                                    // Get stops for progress calculation  
                                    $manifestStops = \App\Models\OrderStop::where('manifest_id', $manifest->id)->get();
                                    $completedStops = $manifestStops->where('status', 'completed')->count();
                                    $totalStops = $manifestStops->count();
                                    $progressPercentage = $totalStops > 0 ? ($completedStops / $totalStops) * 100 : 0;
                                    
                                    $totalRevenue = $manifest->costEstimates->sum('est_cost');
                                    $totalWeight = 0;
                                    foreach($manifestStops as $stop) {
                                        if($stop->commodities) {
                                            foreach($stop->commodities as $commodity) {
                                                $totalWeight += $commodity->weight ?? 0;
                                            }
                                        }
                                    }
                                @endphp
                                <tr class="manifest-row" style="cursor: pointer;" onclick="window.location.href='{{ route('manifest.edit', $manifest->id) }}'">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="manifest-indicator me-3">
                                                <i class="fa fa-clipboard-list text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-primary">{{ $manifest->code }}</div>
                                                <small class="text-muted">ID: {{ $manifest->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="driver-equipment-info">
                                            <!-- Driver -->
                                            @if($manifest->manifestDrivers->isNotEmpty())
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="driver-avatar me-2">
                                                        {{ strtoupper(substr($manifest->manifestDrivers->first()->driver->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold small">{{ $manifest->manifestDrivers->first()->driver->name ?? 'Unnamed' }}</div>
                                                        @if($manifest->manifestDrivers->count() > 1)
                                                            <small class="text-muted">+{{ $manifest->manifestDrivers->count() - 1 }} more</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <small class="text-muted">No driver</small>
                                            @endif
                                            
                                            <!-- Equipment -->
                                            @if($manifest->manifestEquipment->isNotEmpty())
                                                <div class="small text-muted">
                                                    <i class="fa fa-truck me-1"></i>
                                                    {{ $manifest->manifestEquipment->first()->equipment->name ?? 'Unnamed' }}
                                                    @if($manifest->manifestEquipment->count() > 1)
                                                        +{{ $manifest->manifestEquipment->count() - 1 }}
                                                    @endif
                                                </div>
                                            @else
                                                <small class="text-muted">No equipment</small>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Orders Column -->
                                    <td>
                                        @if($manifestOrders->count() > 0)
                                            <div class="orders-column">
                                                @foreach($manifestOrders->take(3) as $order)
                                                    <div class="mb-1">
                                                        <a href="{{ route('orders.edit', $order->id) }}" class="text-primary text-decoration-none fw-medium" onclick="event.stopPropagation()">
                                                            <i class="fa fa-file-text me-1"></i>{{ $order->order_number }}
                                                        </a>
                                                    </div>
                                                @endforeach
                                                @if($manifestOrders->count() > 3)
                                                    <small class="text-muted">+{{ $manifestOrders->count() - 3 }} more</small>
                                                @endif
                                            </div>
                                        @else
                                            <small class="text-muted">No orders assigned</small>
                                        @endif
                                    </td>
                                    
                                    <td>
                                        @if($totalStops > 0)
                                            <div class="progress mb-1" style="height: 6px;">
                                                <div class="progress-bar bg-success" style="width: {{ $progressPercentage }}%"></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">{{ $completedStops }}/{{ $totalStops }} stops</small>
                                                <small class="fw-semibold">{{ number_format($progressPercentage, 0) }}%</small>
                                            </div>
                                            
                                            @php
                                                $currentStop = $manifestStops->where('status', 'in_progress')->first();
                                                $nextStop = $manifestStops->where('status', 'pending')->first();
                                            @endphp
                                            
                                            @if($currentStop)
                                                <div class="mt-1">
                                                    <small class="text-success">
                                                        <i class="fa fa-map-marker-alt me-1"></i>
                                                        Current: {{ Str::limit($currentStop->company_name ?? 'Unknown', 20) }}
                                                    </small>
                                                </div>
                                            @elseif($nextStop)
                                                <div class="mt-1">
                                                    <small class="text-info">
                                                        <i class="fa fa-arrow-right me-1"></i>
                                                        Next: {{ Str::limit($nextStop->company_name ?? 'Unknown', 20) }}
                                                    </small>
                                                </div>
                                            @endif
                                        @else
                                            <small class="text-muted">No stops assigned</small>
                                        @endif
                                    </td>
                                    
                                    <td>
                                        <div class="stats-column">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fa fa-map-marker-alt text-muted me-1"></i>
                                                <span class="fw-semibold">{{ $totalStops }}</span>
                                                <small class="text-muted ms-1">stops</small>
                                            </div>
                                            @if($totalWeight > 0)
                                                <div class="d-flex align-items-center">
                                                    <i class="fa fa-weight text-muted me-1"></i>
                                                    <span class="fw-semibold">{{ number_format($totalWeight, 0) }}</span>
                                                    <small class="text-muted ms-1">lbs</small>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <td>
                                        @if($totalRevenue > 0)
                                            <div class="fw-semibold text-success">${{ number_format($totalRevenue, 0) }}</div>
                                            <small class="text-muted">Est. Revenue</small>
                                        @else
                                            <small class="text-muted">No estimate</small>
                                        @endif
                                    </td>
                                    
                                    <td>
                                        <span class="badge {{ $statusClass }}">
                                            {{ ucfirst($manifest->status ?? 'Pending') }}
                                        </span>
                                    </td>
                                    
                                    <td class="text-center pe-4">
                                        <div class="btn-group" onclick="event.stopPropagation();">
                                            @canPermission('manifests', 'update')
                                            <a href="{{ route('manifest.edit', $manifest->id) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Edit Manifest">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            @endcanPermission
                                            @canPermission('manifests', 'delete')
                                            <form action="{{ route('manifest.destroy', $manifest->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger delete-button" 
                                                        title="Delete Manifest">
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
                                            <div class="mb-3">
                                                <i class="fa fa-clipboard-list text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                            </div>
                                            <h6 class="text-muted mb-2">No Manifests Found</h6>
                                            <p class="text-muted mb-3">No manifests have been added to the system yet.</p>
                                            @canPermission('manifests', 'create')
                                            <a href="{{ route('manifest.create') }}" class="btn btn-primary">
                                                <i class="fa fa-plus me-2"></i>Create Your First Manifest
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
            @if($manifests->hasPages())
                <div class="card-footer bg-light border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $manifests->firstItem() }} to {{ $manifests->lastItem() }} of {{ $manifests->total() }} results
                        </div>
                        <div>
                            {{ $manifests->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection

@push('styles')
<style>
/* Table Styles */
.table-responsive {
    border-radius: 0.5rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #5a5c69;
    font-size: 0.875rem;
    padding: 1rem 0.75rem;
}

.table td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
    border-color: #e3e6f0;
}

/* Manifest Row Hover */
.manifest-row {
    transition: all 0.2s ease;
}

.manifest-row:hover {
    background-color: #f8f9fa;
    transform: translateX(2px);
}

/* Manifest Indicator */
.manifest-indicator {
    width: 32px;
    height: 32px;
    border-radius: 0.375rem;
    background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

/* Driver Avatar */
.driver-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
    flex-shrink: 0;
}

/* Driver Equipment Info */
.driver-equipment-info {
    min-width: 160px;
}

/* Orders Column */
.orders-column {
    min-width: 140px;
}

.orders-column a {
    font-size: 0.85rem;
    display: block;
    line-height: 1.3;
}

.orders-column a:hover {
    text-decoration: underline !important;
}

/* Route Progress */
.progress {
    border-radius: 3px;
    height: 6px;
}

.progress-bar {
    border-radius: 3px;
}

/* Stats Column */
.stats-column {
    min-width: 80px;
}

/* Status Badges */
.badge {
    font-weight: 500;
    font-size: 0.75rem;
    padding: 0.375rem 0.5rem;
}

/* Action Buttons */
.btn-group .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}

/* Empty State */
.empty-state {
    padding: 2rem;
}

/* Pagination */
.card-footer {
    padding: 1rem 1.25rem;
}

.pagination {
    margin: 0;
}

.page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    color: #5a5c69;
    border-color: #e3e6f0;
}

.page-item.active .page-link {
    background-color: #5a5c69;
    border-color: #5a5c69;
}

/* Responsive Table */
@media (max-width: 1200px) {
    .table-responsive table {
        min-width: 900px;
    }
    
    .table th,
    .table td {
        padding: 0.75rem 0.5rem;
    }
}

@media (max-width: 768px) {
    .table th,
    .table td {
        padding: 0.5rem 0.375rem;
        font-size: 0.8rem;
    }
    
    .driver-equipment-info {
        min-width: 120px;
    }
    
    .stats-column {
        min-width: 60px;
    }
    
    .btn-group .btn {
        padding: 0.125rem 0.25rem;
        font-size: 0.7rem;
    }
    
    .manifest-indicator {
        width: 24px;
        height: 24px;
        font-size: 12px;
    }
    
    .driver-avatar {
        width: 24px;
        height: 24px;
        font-size: 10px;
    }
}

/* Clickable Row Cursor */
.manifest-row[onclick] {
    cursor: pointer;
}

/* Prevent text selection on clickable rows */
.manifest-row[onclick] {
    user-select: none;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete button functionality
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (confirm('Are you sure you want to delete this manifest?')) {
                this.closest('form').submit();
            }
        });
    });

    // Auto-submit filters on change
    const statusFilter = document.getElementById('status');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    [statusFilter, dateFromInput, dateToInput].forEach(element => {
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

    // Row click handling with proper event delegation
    document.querySelectorAll('.manifest-row[onclick]').forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't navigate if clicking on buttons or form elements
            if (e.target.closest('.btn-group') || e.target.closest('button') || e.target.closest('a')) {
                return;
            }
            
            const href = this.getAttribute('onclick').match(/window\.location\.href='([^']+)'/);
            if (href && href[1]) {
                window.location.href = href[1];
            }
        });
    });
});
</script>
@endpush