@extends('layouts.app')
@section('content')


<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid py-4 min-vh-100">
    <!-- Header Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body card-body-form">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="fa fa-shopping-cart text-primary me-2"></i>
                        Orders Management
                    </h4>
                    <p class="text-muted mb-0">Manage your system Orders</p>
                </div>                <div>
                    <button type="button" class="btn btn-secondary me-2" data-bs-toggle="modal"
                        data-bs-target="#columnCustomizationModal">
                        
                        <i class="fa fa-columns me-2"></i><span> Customize Columns</span>
                    </button>
                    @canPermission('orders', 'create')
                    <a href="" class="btn btn-primary">
                        <i class="fa fa-plus me-2"></i> <span>Add Order</span>
                    </a>
                    @endcanPermission
                </div>
            </div>
        </div>
    </div>

    @include('partials.message')

    <!-- Table Section -->
    <div class="card shadow-sm">
        <div class="card-body card-body-form">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="ordersTable">
                    <thead class="table-light">
                        <tr id="tableHeaders">
                            <!-- Headers will be dynamically populated -->
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Table content will be dynamically populated -->
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fa fa-shopping-cart text-muted mb-3" style="font-size: 3rem;"></i>                                    <h5 class="text-muted">No Orders Found</h5>
                                    <p class="text-muted mb-3">No orders has been added to the system yet.</p>
                                    @canPermission('orders', 'create')
                                    <a href="" class="btn btn-primary">
                                        <i class="fa fa-plus me-2"></i> <span> Add First Order</span>
                                    </a>
                                    @endcanPermission
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


@include('dashboard.orders.column-customization-modal')

@endsection