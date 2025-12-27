@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header with Breadcrumbs, Status, and Last Updated -->
<div class="d-flex align-items-center justify-content-between mb-4">
    
    <!-- Left Side: Breadcrumbs and Status Badge -->
    <div class="d-flex align-items-center">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('orders.index') }}" class="text-decoration-none">Orders</a>
                </li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page">
                    {{-- This checks if an order is being edited or created --}}
                    {{ isset($order) ? $order->order_number : 'New Order' }}
                </li>
            </ol>
        </nav>

        {{-- This section displays the appropriate status badge --}}
        @if(isset($order))
            {{-- Badge for an existing order with dynamic color based on status --}}
            <span class="badge bg-{{ $order->status == 'new' ? 'info' : ($order->status == 'in_progress' ? 'warning' : ($order->status == 'completed' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'secondary'))) }} text-white ms-3 py-2 px-3">
                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
            </span>
        @else
            {{-- Default badge for a new order --}}
            <span class="badge bg-light text-dark ms-3 py-2 px-3 border">NEW</span>
        @endif
    </div>

    <!-- Right Side: Last Updated Timestamp and Actions -->
    <div class="d-flex align-items-center">
        @if(isset($order) && $order->updated_at)
            <span class="me-3 text-muted small">
                Last updated: {{ $order->updated_at->diffForHumans() }}
            </span>
        @elseif(isset($order) && !$order->updated_at)
            <span class="me-3 text-muted small">
                New Order
            </span>
        @endif

        @if(isset($order) && $order->customer && $order->customer->quickbooks_id)
            <form action="{{ route('orders.syncQuickBooks', $order->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-success btn-sm d-flex align-items-center gap-2" title="Create Invoice in QuickBooks">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Sync to QuickBooks
                </button>
            </form>
        @endif
    </div>
    
</div>

    {{-- Customer & Manifest Information Section - Global for all order types --}}
    @if(isset($order))
        @include('dashboard.orders.partials._customer_section')
    @endif

        <!-- =============================================================== -->
        <!-- STEP 2: UPDATE THE NAV BUTTONS HTML (See below for this code) -->
        <!-- =============================================================== -->
        @include('dashboard.orders.create-orders-nav')


        <!-- Tab Content -->
        <div class="tab-content order-tabs-content" id="orderTabsContent">
            <div class="tab-pane fade order-tab-pane {{ (!isset($order) || $order->order_type == 'point_to_point') ? 'show active' : '' }}" id="point-to-point-content" role="tabpanel">
                @include('dashboard.orders.point-to-point')
            </div>
            <div class="tab-pane fade order-tab-pane {{ (isset($order) && $order->order_type == 'single_shipper') ? 'show active' : '' }}" id="single-shipper-content" role="tabpanel">
                @include('dashboard.orders.single-shipper')
            </div>
            {{-- Add other tab panes here for single-consignee, sequence, etc. --}}
            <div class="tab-pane fade order-tab-pane {{ (isset($order) && $order->order_type == 'single_consignee') ? 'show active' : '' }}" id="single-consignee-content" role="tabpanel">
                @include('dashboard.orders.single-consignee')
            </div>

            <div class="tab-pane fade order-tab-pane {{ (isset($order) && $order->order_type == 'sequence') ? 'show active' : '' }}" id="sequence-content" role="tabpanel">
                @include('dashboard.orders.sequence')
            </div>
            {{-- <div class="tab-pane fade order-tab-pane {{ (isset($order) && $order->order_type == 'multi_stop') ? 'show active' : '' }}" id="multi-stop-content" role="tabpanel">
                @include('dashboard.orders.multi-stop')
            </div> --}}


        </div>

        <!-- Action Buttons -->
        {{-- <div class="d-flex justify-content-end mb-4 mt-4">
            <a href="{{ route('orders.index') }}" class="btn btn-secondary me-2">Cancel</a>
        </div> --}}
</div>


@endsection

@push('styles')
<style>
.customer-avatar-lg {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    color: white;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    border: 3px solid rgba(255,255,255,0.8);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Order tab content styles - unique classes to avoid conflicts */
.order-tabs-content .order-tab-pane {
    display: none;
}

.order-tabs-content .order-tab-pane.active {
    display: block !important;
}

.order-tabs-content .order-tab-pane.show {
    display: block !important;
}
</style>
@endpush

@push('scripts')
<script>
function changeCustomer() {
    // Redirect back to orders index to select a different customer
    window.location.href = "{{ route('orders.index') }}";
}
</script>
@endpush