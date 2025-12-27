
@php
    // Initialize sequence order data - for edit mode, load existing stops
    $existingStops = isset($order) && $order->stops ? $order->stops->sortBy('sequence_number') : collect();
    $hasExistingStops = $existingStops->count() > 0;
    
    // Prepare existing stops data for JavaScript
    $existingStopsForJS = [];
    if ($hasExistingStops) {
        foreach ($existingStops as $stop) {
            $consigneeData = json_decode($stop->consignee_data ?? '{}', true);
            $billingData = json_decode($stop->billing_data ?? '{}', true);
            
            $existingStopsForJS[] = [
                'id' => $stop->id,
                'sequence_number' => $stop->sequence_number,
                'manifest_id' => $stop->manifest_id ?? null,
                'shipper_company_name' => $stop->company_name ?? '',
                'shipper_address_1' => $stop->address_1 ?? '',
                'shipper_address_2' => $stop->address_2 ?? '',
                'shipper_city' => $stop->city ?? '',
                'shipper_state' => $stop->state ?? '',
                'shipper_zip' => $stop->postal_code ?? '',
                'shipper_country' => $stop->country ?? 'US',
                'shipper_contact_name' => $stop->contact_name ?? '',
                'shipper_phone' => $stop->contact_phone ?? '',
                'shipper_contact_email' => $stop->contact_email ?? '',
                'shipper_notes' => $stop->notes ?? '',
                'shipper_opening_time' => $stop->opening_time ?? '',
                'shipper_closing_time' => $stop->closing_time ?? '',
                'ready_start_time' => $stop->start_time ?? '',
                'ready_end_time' => $stop->end_time ?? '',
                'ready_appointment' => $stop->is_appointment ?? false,
                'consignee_company_name' => $consigneeData['company_name'] ?? '',
                'consignee_address_1' => $consigneeData['address_1'] ?? '',
                'consignee_address_2' => $consigneeData['address_2'] ?? '',
                'consignee_city' => $consigneeData['city'] ?? '',
                'consignee_state' => $consigneeData['state'] ?? '',
                'consignee_zip' => $consigneeData['zip'] ?? '',
                'consignee_country' => $consigneeData['country'] ?? 'US',
                'consignee_opening_time' => $consigneeData['opening_time'] ?? '',
                'consignee_closing_time' => $consigneeData['closing_time'] ?? '',
                'consignee_contact_name' => $consigneeData['contact_name'] ?? '',
                'consignee_phone' => $consigneeData['phone'] ?? '',
                'consignee_contact_email' => $consigneeData['email'] ?? '',
                'consignee_notes' => $consigneeData['notes'] ?? '',
                'delivery_start_time' => $consigneeData['delivery_start_time'] ?? '',
                'delivery_end_time' => $consigneeData['delivery_end_time'] ?? '',
                'delivery_appointment' => $consigneeData['delivery_appointment'] ?? false,
                'customs_broker' => $billingData['customs_broker'] ?? '',
                'port_of_entry' => $billingData['port_of_entry'] ?? '',
                'declared_value' => $billingData['declared_value'] ?? '',
                'currency' => $billingData['currency'] ?? 'USD',
                'container_number' => $billingData['container_number'] ?? '',
                'ref_number' => $billingData['ref_number'] ?? '',
                'customer_po_number' => $billingData['customer_po_number'] ?? '',
                'commodities' => $stop->commodities ?? [],
                'accessorials' => $stop->accessorials ? $stop->accessorials->pluck('id')->toArray() : []
            ];
        }
    }
@endphp

<!-- Sequence Order Form -->
<form action="{{ isset($order) ? route('orders.update', $order->id) : route('orders.store') }}" method="POST" id="sequenceOrderForm" novalidate>
    @csrf
    @if(isset($order))
        @method('PUT')
    @endif
    <input type="hidden" name="order_type" value="sequence">
    
    <!-- Informational Header -->
    <div class="alert alert-secondary border-0 bg-light small mb-4">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Sequence Order:</strong> Create a multi-stop route by adding pickup and delivery locations in order. Each stop connects to the next in sequence.
    </div>

                    <!-- Order Details Header -->
                    <div class="row mb-4">
                        <div class="col-lg-8">
                            <h5 class="fw-bold mb-3">Order Details</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">REF Number</label>
                                    <input type="text" class="form-control" name="ref_number" value="{{ old('ref_number', $order->ref_number ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Customer PO Number</label>
                                    <input type="text" class="form-control" name="customer_po_number" value="{{ old('customer_po_number', $order->customer_po_number ?? '') }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <div class="sequence-counter">
                                        <span class="badge bg-primary fs-6 px-3 py-2">
                                            <span class="stop-count">{{ $hasExistingStops ? $existingStops->count() : 1 }}</span> Stop{{ ($hasExistingStops && $existingStops->count() > 1) || !$hasExistingStops ? 's' : '' }}
                                        </span>
                                    </div>
                                    <small class="text-muted d-block mt-2">Total stops in sequence</small>
                                </div>
                            </div>
                        </div>
                    </div>    <!-- Sequence Stops Container -->
    <div class="sequence-stops-container" id="sequenceStopsContainer">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0">Route Sequence</h5>
            <button type="button" class="btn btn-primary btn-sm" id="addStopBtn">
                <i class="bi bi-plus-lg me-1"></i> Add Stop
            </button>
        </div>
        
        <!-- Stops List -->
        <div class="stops-list" id="stopsList">
            <div id="stopsLoadingIndicator" style="padding: 20px; text-align: center; color: #666;">
                <i class="bi bi-hourglass-split"></i> Loading stops...
            </div>
        </div>
    </div>

    <!-- Special Instructions -->
    @include('dashboard.orders.components.special-instructions', [
        'namePrefix' => '',
        'instructions' => old('special_instructions', $order->special_instructions ?? ''),
        'title' => 'Special Instructions',
        'rows' => 3,
        'placeholder' => 'Enter any special instructions for this sequence order...'
    ])

    <!-- Form Actions -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="saveAsDraft" name="save_as_draft" value="1">
            <label class="form-check-label" for="saveAsDraft">
                Save as draft
            </label>
        </div>
        <div>
            <button type="button" class="btn btn-secondary me-2" onclick="history.back()">
                <i class="bi bi-arrow-left me-1"></i> Cancel
            </button>
            @if(isset($order))
                <button type="button" class="btn btn-success btn-lg" id="updateOrderBtn">
                    <i class="bi bi-check-lg me-1"></i> Save Order
                </button>
            @else
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-check-lg me-1"></i> Create Order
                </button>
            @endif
        </div>
    </div>
</form>

<!-- Manifest Assignment / Quote Modal -->
<div class="modal fade" id="manifestAssignmentModal" tabindex="-1" aria-labelledby="manifestAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary py-2">
                <h6 class="modal-title text-white" id="manifestAssignmentModalLabel">
                    <i class="bi bi-clipboard-check me-2"></i>Order Actions
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Left Column: Manifest Assignment -->
                    <div class="col-lg-5 border-end d-flex flex-column" style="height: 500px;">
                        <!-- Header with Toggle -->
                        <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark small">
                                <i class="bi bi-truck me-2"></i>Manifest Assignment
                            </h6>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input form-check-input-sm" type="checkbox" id="enableBulkAssignment">
                                <label class="form-check-label small fw-bold text-muted" style="font-size: 0.75rem;" for="enableBulkAssignment">Bulk Mode</label>
                            </div>
                        </div>
                        
                        <!-- Bulk Controls (Hidden by default) -->
                        <div id="bulkAssignmentSection" class="p-2 bg-white border-bottom" style="display: none;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-collection"></i></span>
                                <select class="form-select border-start-0 ps-0" id="bulkManifestSelect" style="font-size: 0.8rem;">
                                    <option value="">Choose a manifest...</option>
                                </select>
                                <button type="button" class="btn btn-primary" id="applyBulkAssignment">
                                    Apply
                                </button>
                            </div>
                        </div>

                        <!-- Individual Assignments -->
                        <div class="table-responsive flex-grow-1">
                            <table class="table table-sm table-hover mb-0 border-top-0">
                                <thead class="table-light sticky-top">
                                    <tr style="font-size: 0.7rem;">
                                        <th width="35" class="text-center">#</th>
                                        <th>Stop Details</th>
                                        <th width="150">Manifest</th>
                                    </tr>
                                </thead>
                                <tbody id="stopManifestAssignments" style="font-size: 0.75rem;">
                                    <!-- Populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Right Column: Quoting -->
                    <div class="col-lg-7">
                        <div class="p-3">
                            <h6 class="fw-bold mb-2 text-dark border-bottom pb-1 small">
                                <i class="bi bi-file-earmark-text me-2"></i>Create Quote
                            </h6>

                            <!-- Quote Details -->
                            <div class="row g-2 mb-2">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold mb-0" style="font-size: 0.7rem;">Service</label>
                                    <select class="form-select form-select-sm py-0" id="quote_service_id" style="font-size: 0.8rem;">
                                        <option value="">Select Service...</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" {{ (isset($order) && $order->quote && $order->quote->service_id == $service->id) ? 'selected' : '' }}>
                                                {{ $service->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold mb-0" style="font-size: 0.7rem;">Delivery Start</label>
                                    <input type="date" class="form-control form-control-sm py-0" id="quote_delivery_start" style="font-size: 0.8rem;"
                                        value="{{ isset($order) && $order->quote && $order->quote->delivery_start_date ? $order->quote->delivery_start_date->format('Y-m-d') : '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold mb-0" style="font-size: 0.7rem;">Delivery End</label>
                                    <input type="date" class="form-control form-control-sm py-0" id="quote_delivery_end" style="font-size: 0.8rem;"
                                        value="{{ isset($order) && $order->quote && $order->quote->delivery_end_date ? $order->quote->delivery_end_date->format('Y-m-d') : '' }}">
                                </div>
                            </div>

                            <!-- Costs Grid -->
                            <div class="row g-2">
                                <!-- Carrier Costs -->
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small fw-bold text-secondary" style="font-size: 0.75rem;">Carrier Costs</span>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0" style="font-size: 0.7rem;" onclick="addCostRow('carrier')">
                                            <i class="bi bi-plus"></i> Add
                                        </button>
                                    </div>
                                    <div class="table-responsive border rounded" style="max-height: 250px; overflow-y: auto;">
                                        <table class="table table-sm table-borderless mb-0" id="carrierCostsTable">
                                            <thead class="bg-light text-muted sticky-top">
                                                <tr style="font-size: 0.7rem;">
                                                    <th>Description</th>
                                                    <th width="80">Cost</th>
                                                    <th width="20"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(isset($order) && $order->quote)
                                                    @foreach($order->quote->costs->where('category', 'carrier') as $index => $cost)
                                                    <tr>
                                                        <td class="p-1">
                                                            <input type="text" class="form-control form-control-sm py-0" style="font-size: 0.8rem;" name="carrier_costs[{{$index}}][description]" value="{{ $cost->description }}" placeholder="Description">
                                                        </td>
                                                        <td class="p-1">
                                                            <input type="number" class="form-control form-control-sm py-0" style="font-size: 0.8rem;" name="carrier_costs[{{$index}}][cost]" value="{{ $cost->cost }}" step="0.01" placeholder="0.00">
                                                        </td>
                                                        <td class="p-1 align-middle text-center">
                                                            <button type="button" class="btn btn-sm btn-link text-danger p-0" style="font-size: 0.8rem;" onclick="this.closest('tr').remove()"><i class="bi bi-x-lg"></i></button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Customer Quote -->
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small fw-bold text-primary" style="font-size: 0.75rem;">Customer Quote</span>
                                        <button type="button" class="btn btn-xs btn-outline-primary py-0" style="font-size: 0.7rem;" onclick="addCostRow('customer')">
                                            <i class="bi bi-plus"></i> Add
                                        </button>
                                    </div>
                                    <div class="table-responsive border rounded" style="max-height: 250px; overflow-y: auto;">
                                        <table class="table table-sm table-borderless mb-0" id="customerQuoteTable">
                                            <thead class="bg-light text-muted sticky-top">
                                                <tr style="font-size: 0.7rem;">
                                                    <th>Description</th>
                                                    <th width="80">Cost</th>
                                                    <th width="20"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(isset($order) && $order->quote)
                                                    @foreach($order->quote->costs->where('category', 'quote') as $index => $cost)
                                                    <tr>
                                                        <td class="p-1">
                                                            <input type="text" class="form-control form-control-sm py-0" style="font-size: 0.8rem;" name="customer_quotes[{{$index}}][description]" value="{{ $cost->description }}" placeholder="Description">
                                                        </td>
                                                        <td class="p-1">
                                                            <input type="number" class="form-control form-control-sm py-0" style="font-size: 0.8rem;" name="customer_quotes[{{$index}}][cost]" value="{{ $cost->cost }}" step="0.01" placeholder="0.00">
                                                        </td>
                                                        <td class="p-1 align-middle text-center">
                                                            <button type="button" class="btn btn-sm btn-link text-danger p-0" style="font-size: 0.8rem;" onclick="this.closest('tr').remove()"><i class="bi bi-x-lg"></i></button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="form-label small fw-bold mb-0" style="font-size: 0.7rem;">Notes</label>
                                <textarea class="form-control form-control-sm py-1" id="quote_notes" rows="2" style="font-size: 0.8rem;">{{ isset($order) && $order->quote ? $order->quote->notes : '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-1">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-success px-4" id="saveManifestAssignments">
                    <i class="bi bi-check-lg me-1"></i>Save Order & Quote
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stop Template (Hidden) -->
<template id="stopTemplate">
    <div class="stop-card" data-stop-index="">
        <div class="card shadow-sm mb-3 position-relative">
            <!-- Collapsed Row View -->
            <div class="stop-row-collapsed cursor-pointer" onclick="toggleStopExpansion(this)">
                <div class="d-flex align-items-center justify-content-between p-3">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="drag-handle me-3" title="Drag to reorder" onclick="event.stopPropagation()">
                            <i class="bi bi-grip-vertical text-muted"></i>
                        </div>
                        <div class="stop-sequence-badge me-3">
                            <span class="badge bg-primary rounded-pill px-2 py-1">
                                <span class="sequence-number">1</span>
                            </span>
                        </div>
                        <div class="stop-summary flex-grow-1">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-box-arrow-up text-success me-2"></i>
                                        <div>
                                            <div class="pickup-company-summary text-truncate fw-medium" style="max-width: 250px;">
                                                <span class="text-muted">Click to add shipper location</span>
                                            </div>
                                            <small class="pickup-address-summary text-muted text-truncate d-block" style="max-width: 250px;">
                                                Address not set
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-box-arrow-in-down text-primary me-2"></i>
                                        <div>
                                            <div class="delivery-company-summary text-truncate fw-medium" style="max-width: 250px;">
                                                <span class="text-muted">Click to add consignee location</span>
                                            </div>
                                            <small class="delivery-address-summary text-muted text-truncate d-block" style="max-width: 250px;">
                                                Address not set
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Manifest Assignment Display -->
                            {{-- <div class="row mt-2">
                                <div class="col-12">
                                    <div class="manifest-assignment-display">
                                        <small class="text-muted">
                                            <i class="bi bi-clipboard-check me-1"></i>
                                            Manifest: <span class="manifest-info text-info fw-medium">Not assigned</span>
                                        </small>
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                        <div class="stop-connection-indicator ms-3">
                            <i class="bi bi-chevron-down expand-icon"></i>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn ms-2" title="Remove this stop">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            
            <!-- Expanded Form View (Initially Hidden) -->
            <div class="stop-form-expanded" style="display: none;">
                <div class="border-top bg-light px-3 py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-pencil-square me-2"></i>
                            Stop <span class="sequence-number">1</span> Details
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary collapse-stop-btn" onclick="toggleStopExpansion(this.closest('.stop-card').querySelector('.stop-row-collapsed'))">
                            <i class="bi bi-chevron-up me-1"></i> Collapse
                        </button>
                    </div>
                </div>
                
                <!-- Stop Content -->
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Shipper Information -->
                        <div class="col-lg-4">
                            <div class="pickup-section border rounded p-2 h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="pickup-icon me-2">
                                        <i class="bi bi-box-arrow-up text-success"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-success small">SHIPPER</h6>
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small">Company name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][shipper_company_name]" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Address 1 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][shipper_address_1]" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Address 2</label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][shipper_address_2]">
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-4">
                                        <label class="form-label small">City <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="stops[INDEX][shipper_city]" required>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small">State <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="stops[INDEX][shipper_state]" required>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small">ZIP <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="stops[INDEX][shipper_zip]" required>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Country</label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][shipper_country]" value="US">
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small">Opening</label>
                                        <input type="time" class="form-control form-control-sm" name="stops[INDEX][shipper_opening_time]">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Closing</label>
                                        <input type="time" class="form-control form-control-sm" name="stops[INDEX][shipper_closing_time]">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Contact name</label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][shipper_contact_name]">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Phone</label>
                                    <input type="tel" class="form-control form-control-sm" name="stops[INDEX][shipper_phone]">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Contact email</label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][shipper_contact_email]">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Notes</label>
                                    <textarea class="form-control form-control-sm" name="stops[INDEX][shipper_notes]" rows="2"></textarea>
                                </div>
                                
                                <!-- Ready Time Section -->
                                <div class="mt-2 pt-2 border-top">
                                    <h6 class="fw-bold mb-2 text-success small">READY TIME</h6>
                                    <div class="mb-2">
                                        <label class="form-label small">Start time</label>
                                        <input type="datetime-local" class="form-control form-control-sm" name="stops[INDEX][ready_start_time]">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">End time</label>
                                        <input type="datetime-local" class="form-control form-control-sm" name="stops[INDEX][ready_end_time]">
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="stops[INDEX][ready_appointment]">
                                        <label class="form-check-label small">Make appointment</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Consignee Information -->
                        <div class="col-lg-4">
                            <div class="delivery-section border rounded p-2 h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="delivery-icon me-2">
                                        <i class="bi bi-box-arrow-in-down text-primary"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-primary small">CONSIGNEE</h6>
                                </div>
                            
                                <div class="mb-2">
                                    <label class="form-label small">Company name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][consignee_company_name]" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Address 1 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][consignee_address_1]" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Address 2</label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][consignee_address_2]">
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-4">
                                        <label class="form-label small">City <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="stops[INDEX][consignee_city]" required>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small">State <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="stops[INDEX][consignee_state]" required>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small">ZIP <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="stops[INDEX][consignee_zip]" required>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Country</label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][consignee_country]" value="US">
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small">Opening</label>
                                        <input type="time" class="form-control form-control-sm" name="stops[INDEX][consignee_opening_time]">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Closing</label>
                                        <input type="time" class="form-control form-control-sm" name="stops[INDEX][consignee_closing_time]">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Contact name</label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][consignee_contact_name]">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Phone</label>
                                    <input type="tel" class="form-control form-control-sm" name="stops[INDEX][consignee_phone]">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Contact email</label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][consignee_contact_email]">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Notes</label>
                                    <textarea class="form-control form-control-sm" name="stops[INDEX][consignee_notes]" rows="2"></textarea>
                                </div>
                                
                                <!-- Requested Delivery Section -->
                                <div class="mt-2 pt-2 border-top">
                                    <h6 class="fw-bold mb-2 text-primary small">REQUESTED DELIVERY</h6>
                                    <div class="mb-2">
                                        <label class="form-label small">Start time</label>
                                        <input type="datetime-local" class="form-control form-control-sm" name="stops[INDEX][delivery_start_time]">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">End time</label>
                                        <input type="datetime-local" class="form-control form-control-sm" name="stops[INDEX][delivery_end_time]">
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="stops[INDEX][delivery_appointment]">
                                        <label class="form-check-label small">Make appointment</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="col-lg-4">
                            <div class="billing-section border rounded p-2 h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="billing-icon me-2">
                                        <i class="bi bi-info-circle text-warning"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-warning small">Additional information</h6>
                                </div>
                            
                                <div class="mb-2">
                                    <label class="form-label small">Customs broker</label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][customs_broker]" placeholder="Customs broker">
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small">Port of entry</label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][port_of_entry]" placeholder="Port of entry">
                                </div>
                                
                                <div class="row g-1 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small">Declared value</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" name="stops[INDEX][declared_value]" placeholder="0.00" step="0.01" min="0">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Currency</label>
                                        <select class="form-select form-select-sm" name="stops[INDEX][currency]">
                                            <option value="USD">USD</option>
                                            <option value="CAD" selected>CAD</option>
                                            <option value="EUR">EUR</option>
                                            <option value="GBP">GBP</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small">Container Number</label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][container_number]" placeholder="Container Number">
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small">REF Number</label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][ref_number]" placeholder="REF Number">
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small">Customer Po Number</label>
                                    <input type="text" class="form-control form-control-sm" name="stops[INDEX][customer_po_number]" placeholder="Customer Po Number">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commodities Section -->
                <div class="commodities-section mt-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold mb-0">Commodities for this stop</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary add-commodity-btn">
                            <i class="bi bi-plus me-1"></i> Add Commodity
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Description <span class="text-danger">*</span></th>
                                    <th width="100">Pieces</th>
                                    <th width="120">Weight (lbs)</th>
                                    <th width="140">Dimensions (L×W×H)</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody class="commodities-tbody">
                                <!-- Commodities will be added here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Accessorials Section -->
                <div class="accessorials-section mt-4 border-top pt-4 pb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold mb-0">Accessorials for this stop</h6>
                    </div>
                    <div class="selected-accessorials-container mb-3">
                        <!-- Selected accessorials will appear here -->
                    </div>
                    <div class="dropdown accessorial-dropdown mb-4">
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <i class="bi bi-plus"></i> Add accessorial
                        </button>
                        <div class="dropdown-menu p-2">
                            <input type="text" class="accessorial-search form-control form-control-sm mb-2" placeholder="Search accessorials...">
                            <ul class="accessorial-list list-unstyled mb-0" style="max-height: 200px; overflow-y: auto;">
                                <li data-id="1"><a href="#" class="dropdown-item small">Liftgate Service</a></li>
                                <li data-id="2"><a href="#" class="dropdown-item small">Inside Pickup</a></li>
                                <li data-id="3"><a href="#" class="dropdown-item small">Inside Delivery</a></li>
                                <li data-id="4"><a href="#" class="dropdown-item small">Appointment Required</a></li>
                                <li data-id="5"><a href="#" class="dropdown-item small">Limited Access</a></li>
                                <li data-id="6"><a href="#" class="dropdown-item small">Residential Delivery</a></li>
                                <li data-id="7"><a href="#" class="dropdown-item small">Call Before Delivery</a></li>
                                <li data-id="8"><a href="#" class="dropdown-item small">Freeze Protection</a></li>
                                <li data-id="9"><a href="#" class="dropdown-item small">Hazmat</a></li>
                                <li data-id="10"><a href="#" class="dropdown-item small">White Glove Service</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Commodity Row Template (Hidden) -->
<template id="commodityTemplate">
    <tr class="commodity-row">
        <td><input type="text" class="form-control form-control-sm" name="stops[INDEX][commodities][COMMODITY_INDEX][description]" placeholder="Commodity description" required list="commodity-descriptions"></td>
        <td><input type="number" class="form-control form-control-sm" name="stops[INDEX][commodities][COMMODITY_INDEX][pieces]" min="1" placeholder="1"></td>
        <td><input type="number" class="form-control form-control-sm" name="stops[INDEX][commodities][COMMODITY_INDEX][weight]" step="0.01" placeholder="0.00"></td>
        <td><input type="text" class="form-control form-control-sm" name="stops[INDEX][commodities][COMMODITY_INDEX][dimensions]" placeholder="L×W×H"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-commodity-btn"><i class="bi bi-trash"></i></button></td>
    </tr>
</template>

<datalist id="commodity-descriptions">
    <option value="Empty Commodity">
</datalist>

<!-- Sequence Order Styles -->
<style>
/* Sequence-specific styles */
.sequence-stops-container {
    position: relative;
}

.stop-card {
    transition: all 0.3s ease;
}

.stop-row-collapsed {
    cursor: pointer;
    transition: all 0.2s ease;
}

.stop-row-collapsed:hover {
    background-color: #f8f9fa;
}

.stop-form-expanded {
    border-top: 1px solid #dee2e6;
}

.pickup-section, .delivery-section, .billing-section {
    min-height: 650px;
}

.form-control-sm {
    font-size: 0.775rem;
}

.form-label.small {
    font-size: 0.75rem;
    margin-bottom: 0.25rem;
    font-weight: 500;
}

@media (max-width: 991px) {
    .pickup-section, .delivery-section, .billing-section {
        min-height: auto;
        margin-bottom: 1rem;
    }
}

.cursor-pointer {
    cursor: pointer;
}

.expand-icon {
    transition: transform 0.2s ease;
}

.stop-row-collapsed.expanded .expand-icon {
    transform: rotate(180deg);
}

.stop-card.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
}

.drag-handle {
    cursor: grab;
    padding: 8px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.drag-handle:hover {
    background-color: rgba(0,0,0,0.05);
}

.stop-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 2px solid #dee2e6;
}

.pickup-section {
    background: linear-gradient(135deg, #d4edda 0%, #f8fff9 100%);
    border: 1px solid #c3e6cb !important;
}

.delivery-section {
    background: linear-gradient(135deg, #cce7ff 0%, #f8fbff 100%);
    border: 1px solid #b3d7ff !important;
}

.billing-section {
    background: linear-gradient(135deg, #fff3cd 0%, #fffdf7 100%);
    border: 1px solid #ffeaa7 !important;
}

.sequence-counter .badge {
    font-size: 1rem;
    padding: 0.75rem 1.25rem;
    border-radius: 50px;
}

.stop-sequence-badge .badge {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
    font-weight: 600;
}

.stop-connection-line {
    position: absolute;
    left: 50%;
    bottom: -20px;
    width: 3px;
    height: 40px;
    background: linear-gradient(to bottom, #007bff, #6c757d);
    transform: translateX(-50%);
    z-index: 1;
}

.stop-connection-line::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-top: 8px solid #6c757d;
}

.stops-list .stop-card:last-child .stop-connection-line {
    display: none;
}

.commodities-section {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
    margin-top: 1rem;
}

.drop-zone {
    min-height: 100px;
    border: 2px dashed #007bff;
    border-radius: 8px;
    background: rgba(0, 123, 255, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 1rem 0;
    opacity: 0;
    transition: all 0.3s ease;
}

.drop-zone.active {
    opacity: 1;
}

.drop-zone p {
    color: #007bff;
    font-weight: 500;
    margin: 0;
}

/* Responsive adjustments */

/* Form validation styles */
.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
}
</style>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Sequence Order JavaScript -->
<script>
// Pass existing stops data to JavaScript
const existingStopsData = @json($existingStopsForJS);
console.log('Existing stops data:', existingStopsData);

// Debug accessorials in existing data
if (existingStopsData && existingStopsData.length > 0) {
    existingStopsData.forEach((stop, index) => {
        if (stop.accessorials && stop.accessorials.length > 0) {
            console.log(`Stop ${index} has accessorials:`, stop.accessorials);
        }
    });
}

// Function to toggle stop expansion (global scope)
function toggleStopExpansion(element) {
    if (!element) return;
    
    const stopCard = element.closest('.stop-card');
    if (!stopCard) return;
    
    const collapsedRow = stopCard.querySelector('.stop-row-collapsed');
    const expandedForm = stopCard.querySelector('.stop-form-expanded');
    const expandIcon = collapsedRow ? collapsedRow.querySelector('.expand-icon') : null;
    
    if (!collapsedRow || !expandedForm || !expandIcon) return;
    
    if (expandedForm.style.display === 'none' || expandedForm.style.display === '') {
        // Expand
        expandedForm.style.display = 'block';
        collapsedRow.classList.add('expanded');
        expandIcon.classList.remove('bi-chevron-down');
        expandIcon.classList.add('bi-chevron-up');
    } else {
        // Collapse
        expandedForm.style.display = 'none';
        collapsedRow.classList.remove('expanded');
        expandIcon.classList.remove('bi-chevron-up');
        expandIcon.classList.add('bi-chevron-down');
    }
    
    updateStopSummary(stopCard);
}

// Function to update manifest display
function updateManifestDisplay(stopCard, manifestId = null) {
    if (!stopCard) return;
    
    const manifestInfoElement = stopCard.querySelector('.manifest-info');
    if (!manifestInfoElement) return;
    
    if (manifestId && typeof manifestsData !== 'undefined' && manifestsData && manifestsData.length > 0) {
        const manifest = manifestsData.find(m => m.id == manifestId);
        if (manifest) {
            manifestInfoElement.textContent = manifest.manifest_number || `Manifest #${manifestId}`;
            manifestInfoElement.classList.remove('text-muted');
            manifestInfoElement.classList.add('text-info');
        } else {
            manifestInfoElement.textContent = `Manifest #${manifestId}`;
            manifestInfoElement.classList.remove('text-muted');
            manifestInfoElement.classList.add('text-info');
        }
    } else {
        manifestInfoElement.textContent = 'Not assigned';
        manifestInfoElement.classList.remove('text-info');
        manifestInfoElement.classList.add('text-muted');
    }
}

// Function to update stop summary in collapsed view
function updateStopSummary(stopCard) {
    const pickupCompanyInput = stopCard.querySelector('input[name*="shipper_company_name"]');
    const pickupAddress1Input = stopCard.querySelector('input[name*="shipper_address_1"]');
    const pickupCityInput = stopCard.querySelector('input[name*="shipper_city"]');
    const pickupStateInput = stopCard.querySelector('input[name*="shipper_state"]');
    
    const deliveryCompanyInput = stopCard.querySelector('input[name*="consignee_company_name"]');
    const deliveryAddress1Input = stopCard.querySelector('input[name*="consignee_address_1"]');
    const deliveryCityInput = stopCard.querySelector('input[name*="consignee_city"]');
    const deliveryStateInput = stopCard.querySelector('input[name*="consignee_state"]');
    
    const pickupCompanySummary = stopCard.querySelector('.pickup-company-summary');
    const pickupAddressSummary = stopCard.querySelector('.pickup-address-summary');
    const deliveryCompanySummary = stopCard.querySelector('.delivery-company-summary');
    const deliveryAddressSummary = stopCard.querySelector('.delivery-address-summary');
    
    // Update shipper summary
    if (pickupCompanyInput && pickupCompanyInput.value) {
        pickupCompanySummary.innerHTML = pickupCompanyInput.value;
        pickupCompanySummary.classList.remove('text-muted');
    } else {
        pickupCompanySummary.innerHTML = '<span class="text-muted">Click to add shipper location</span>';
    }
    
    // Build pickup address
    let pickupAddress = '';
    if (pickupAddress1Input && pickupAddress1Input.value) {
        pickupAddress = pickupAddress1Input.value;
    }
    if (pickupCityInput && pickupCityInput.value) {
        pickupAddress += (pickupAddress ? ', ' : '') + pickupCityInput.value;
    }
    if (pickupStateInput && pickupStateInput.value) {
        pickupAddress += (pickupAddress ? ', ' : '') + pickupStateInput.value;
    }
    
    if (pickupAddress) {
        pickupAddressSummary.textContent = pickupAddress;
        pickupAddressSummary.classList.remove('text-muted');
    } else {
        pickupAddressSummary.textContent = 'Address not set';
        pickupAddressSummary.classList.add('text-muted');
    }
    
    // Update consignee summary
    if (deliveryCompanyInput && deliveryCompanyInput.value) {
        deliveryCompanySummary.innerHTML = deliveryCompanyInput.value;
        deliveryCompanySummary.classList.remove('text-muted');
    } else {
        deliveryCompanySummary.innerHTML = '<span class="text-muted">Click to add consignee location</span>';
    }
    
    // Build delivery address
    let deliveryAddress = '';
    if (deliveryAddress1Input && deliveryAddress1Input.value) {
        deliveryAddress = deliveryAddress1Input.value;
    }
    if (deliveryCityInput && deliveryCityInput.value) {
        deliveryAddress += (deliveryAddress ? ', ' : '') + deliveryCityInput.value;
    }
    if (deliveryStateInput && deliveryStateInput.value) {
        deliveryAddress += (deliveryAddress ? ', ' : '') + deliveryStateInput.value;
    }
    
    if (deliveryAddress) {
        deliveryAddressSummary.textContent = deliveryAddress;
        deliveryAddressSummary.classList.remove('text-muted');
    } else {
        deliveryAddressSummary.textContent = 'Address not set';
        deliveryAddressSummary.classList.add('text-muted');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Sequence JavaScript loading...');
    
    const sequenceForm = document.getElementById('sequenceOrderForm');
    const stopsList = document.getElementById('stopsList');
    const addStopBtn = document.getElementById('addStopBtn');
    const stopTemplate = document.getElementById('stopTemplate');
    const commodityTemplate = document.getElementById('commodityTemplate');
    
    console.log('Elements found:', {
        sequenceForm: !!sequenceForm,
        stopsList: !!stopsList, 
        addStopBtn: !!addStopBtn,
        stopTemplate: !!stopTemplate
    });
    
    let stopIndex = 0;
    let draggedElement = null;

    // Initialize stops - load existing or add first stop
    if (existingStopsData && existingStopsData.length > 0) {
        console.log('Loading existing stops:', existingStopsData.length);
        // Load existing stops for edit mode
        existingStopsData.forEach((stopData, index) => {
            console.log(`Loading stop ${index}:`, stopData);
            addStop(stopData);
        });
    } else {
        console.log('No existing stops, adding first default stop');
        // Initialize with first stop for create mode
        addStop();
    }

    // Add Stop functionality
    addStopBtn.addEventListener('click', function() {
        const lastStopData = getLastStopConsigneeData();
        addStop(null, lastStopData);
        updateStopNumbers();
        updateStopCounter();
    });

    // Get last stop's consignee data to use as next stop's shipper
    function getLastStopConsigneeData() {
        const stopCards = stopsList.querySelectorAll('.stop-card');
        if (stopCards.length === 0) return null;
        
        const lastStop = stopCards[stopCards.length - 1];
        const lastStopIndex = lastStop.dataset.stopIndex;
        
        return {
            shipper_company_name: lastStop.querySelector(`[name="stops[${lastStopIndex}][consignee_company_name]"]`)?.value || '',
            shipper_address_1: lastStop.querySelector(`[name="stops[${lastStopIndex}][consignee_address_1]"]`)?.value || '',
            shipper_address_2: lastStop.querySelector(`[name="stops[${lastStopIndex}][consignee_address_2]"]`)?.value || '',
            shipper_city: lastStop.querySelector(`[name="stops[${lastStopIndex}][consignee_city]"]`)?.value || '',
            shipper_state: lastStop.querySelector(`[name="stops[${lastStopIndex}][consignee_state]"]`)?.value || '',
            shipper_zip: lastStop.querySelector(`[name="stops[${lastStopIndex}][consignee_zip]"]`)?.value || '',
            shipper_country: lastStop.querySelector(`[name="stops[${lastStopIndex}][consignee_country]"]`)?.value || 'US',
            shipper_contact_name: lastStop.querySelector(`[name="stops[${lastStopIndex}][consignee_contact_name]"]`)?.value || '',
            shipper_phone: lastStop.querySelector(`[name="stops[${lastStopIndex}][consignee_phone]"]`)?.value || '',
            shipper_contact_email: lastStop.querySelector(`[name="stops[${lastStopIndex}][consignee_contact_email]"]`)?.value || '',
            shipper_notes: lastStop.querySelector(`[name="stops[${lastStopIndex}][consignee_notes]"]`)?.value || '',
            shipper_opening_time: lastStop.querySelector(`[name="stops[${lastStopIndex}][consignee_opening_time]"]`)?.value || '',
            shipper_closing_time: lastStop.querySelector(`[name="stops[${lastStopIndex}][consignee_closing_time]"]`)?.value || ''
        };
    }

    function addStop(stopData = null, autofillData = null) {
        const stopClone = stopTemplate.content.cloneNode(true);
        const stopCard = stopClone.querySelector('.stop-card');
        
        // Set unique index
        stopCard.dataset.stopIndex = stopIndex;
        
        // Replace INDEX placeholders in names
        const inputs = stopClone.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            if (input.name && input.name.includes('INDEX')) {
                input.name = input.name.replace('INDEX', stopIndex);
            }
        });

        // Populate with existing data if provided, otherwise use autofill data
        if (stopData) {
            populateStopData(stopClone, stopData, stopIndex);
        } else if (autofillData) {
            populateStopData(stopClone, autofillData, stopIndex);
        }

        // Add connection line (except for first stop)
        if (stopsList.children.length > 0) {
            const connectionLine = document.createElement('div');
            connectionLine.className = 'stop-connection-line';
            stopCard.querySelector('.card').appendChild(connectionLine);
        }

        // Remove loading indicator if it exists
        const loadingIndicator = document.getElementById('stopsLoadingIndicator');
        if (loadingIndicator) {
            loadingIndicator.remove();
        }
        
        // Add default commodity row
        addCommodityToStop(stopClone, stopIndex);
        
        stopsList.appendChild(stopClone);
        stopIndex++;

        // Setup drag and drop
        setupDragAndDrop(stopCard);
        
        // Setup input change listeners to update summary
        const formInputs = stopCard.querySelectorAll('input[name*="shipper_company_name"], input[name*="shipper_address_1"], input[name*="shipper_city"], input[name*="shipper_state"], input[name*="consignee_company_name"], input[name*="consignee_address_1"], input[name*="consignee_city"], input[name*="consignee_state"]');
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                updateStopSummary(stopCard);
            });
            input.addEventListener('blur', function() {
                updateStopSummary(stopCard);
            });
        });
        
        // Update summary after adding stop (especially for existing data)
        setTimeout(() => {
            updateStopSummary(stopCard);
        }, 100);
        
        return stopCard;
    }

    function populateStopData(stopElement, stopData, stopIndexValue) {
        // Populate shipper fields
        const shipperFields = {
            'company_name': stopData.shipper_company_name,
            'address_1': stopData.shipper_address_1,
            'address_2': stopData.shipper_address_2,
            'city': stopData.shipper_city,
            'state': stopData.shipper_state,
            'zip': stopData.shipper_zip,
            'country': stopData.shipper_country,
            'contact_name': stopData.shipper_contact_name,
            'phone': stopData.shipper_phone,
            'contact_email': stopData.shipper_contact_email,
            'notes': stopData.shipper_notes,
            'opening_time': stopData.shipper_opening_time,
            'closing_time': stopData.shipper_closing_time
        };
        
        Object.entries(shipperFields).forEach(([field, value]) => {
            const element = stopElement.querySelector(`[name="stops[${stopIndexValue}][shipper_${field}]"]`);
            if (element && value) element.value = value;
        });
        
        // Populate ready times
        const readyStartElement = stopElement.querySelector(`[name="stops[${stopIndexValue}][ready_start_time]"]`);
        const readyEndElement = stopElement.querySelector(`[name="stops[${stopIndexValue}][ready_end_time]"]`);
        const readyAppointmentElement = stopElement.querySelector(`[name="stops[${stopIndexValue}][ready_appointment]"]`);
        
        if (readyStartElement && stopData.ready_start_time) readyStartElement.value = stopData.ready_start_time;
        if (readyEndElement && stopData.ready_end_time) readyEndElement.value = stopData.ready_end_time;
        if (readyAppointmentElement) readyAppointmentElement.checked = stopData.ready_appointment;
        
        // Populate consignee fields
        const consigneeFields = {
            'company_name': stopData.consignee_company_name,
            'address_1': stopData.consignee_address_1,
            'address_2': stopData.consignee_address_2,
            'city': stopData.consignee_city,
            'state': stopData.consignee_state,
            'zip': stopData.consignee_zip,
            'country': stopData.consignee_country,
            'contact_name': stopData.consignee_contact_name,
            'phone': stopData.consignee_phone,
            'contact_email': stopData.consignee_contact_email,
            'notes': stopData.consignee_notes,
            'opening_time': stopData.consignee_opening_time,
            'closing_time': stopData.consignee_closing_time
        };
        
        Object.entries(consigneeFields).forEach(([field, value]) => {
            const element = stopElement.querySelector(`[name="stops[${stopIndexValue}][consignee_${field}]"]`);
            if (element && value) element.value = value;
        });
        
        // Populate delivery times
        const deliveryStartElement = stopElement.querySelector(`[name="stops[${stopIndexValue}][delivery_start_time]"]`);
        const deliveryEndElement = stopElement.querySelector(`[name="stops[${stopIndexValue}][delivery_end_time]"]`);
        const deliveryAppointmentElement = stopElement.querySelector(`[name="stops[${stopIndexValue}][delivery_appointment]"]`);
        
        if (deliveryStartElement && stopData.delivery_start_time) deliveryStartElement.value = stopData.delivery_start_time;
        if (deliveryEndElement && stopData.delivery_end_time) deliveryEndElement.value = stopData.delivery_end_time;
        if (deliveryAppointmentElement) deliveryAppointmentElement.checked = stopData.delivery_appointment;
        
        // Populate additional information fields
        const additionalFields = {
            'customs_broker': stopData.customs_broker,
            'port_of_entry': stopData.port_of_entry,
            'declared_value': stopData.declared_value,
            'currency': stopData.currency,
            'container_number': stopData.container_number,
            'ref_number': stopData.ref_number,
            'customer_po_number': stopData.customer_po_number
        };
        
        Object.entries(additionalFields).forEach(([field, value]) => {
            const element = stopElement.querySelector(`[name="stops[${stopIndexValue}][${field}]"]`);
            if (element && value) element.value = value;
        });

        // Populate commodities
        if (stopData.commodities && stopData.commodities.length > 0) {
            const tbody = stopElement.querySelector('.commodities-tbody');
            tbody.innerHTML = ''; // Clear default commodity
            
            stopData.commodities.forEach((commodity, index) => {
                addCommodityToStop(stopElement, stopIndexValue, commodity);
            });
        }
        
        // Populate accessorials
        if (stopData.accessorials && stopData.accessorials.length > 0) {
            const container = stopElement.querySelector('.selected-accessorials-container');
            const accessorialList = stopElement.querySelector('.accessorial-list');
            
            stopData.accessorials.forEach(accessorialId => {
                // Find the accessorial name from the dropdown
                const listItem = accessorialList.querySelector(`li[data-id="${accessorialId}"]`);
                if (listItem) {
                    const accessorialName = listItem.querySelector('a').textContent;
                    listItem.style.display = 'none'; // Hide from dropdown
                    
                    // Add to selected container
                    const badgeHtml = `
                        <span class="badge bg-light text-dark border p-2 me-2 mb-2 d-inline-flex align-items-center">
                            ${accessorialName}
                            <button type="button" class="btn-close btn-close-sm ms-2 remove-accessorial-btn" data-id="${accessorialId}" style="font-size: 0.7rem;"></button>
                            <input type="hidden" name="stops[${stopIndexValue}][accessorials][]" value="${accessorialId}">
                        </span>`;
                    container.insertAdjacentHTML('beforeend', badgeHtml);
                }
            });
        }
        
        // Update manifest display will be done after manifests are loaded
    }

    function addCommodityToStop(stopElement, currentStopIndex, commodityData = null) {
        const tbody = stopElement.querySelector('.commodities-tbody');
        const commodityClone = commodityTemplate.content.cloneNode(true);
        
        const commodityIndex = tbody.children.length;
        
        // Replace placeholders
        const inputs = commodityClone.querySelectorAll('input');
        inputs.forEach(input => {
            if (input.name) {
                input.name = input.name.replace('INDEX', currentStopIndex)
                                   .replace('COMMODITY_INDEX', commodityIndex);
            }
        });

        // Populate with data if provided
        if (commodityData) {
            const descInput = commodityClone.querySelector('input[name*="description"]');
            const piecesInput = commodityClone.querySelector('input[name*="pieces"]');
            const weightInput = commodityClone.querySelector('input[name*="weight"]');
            const dimensionsInput = commodityClone.querySelector('input[name*="dimensions"]');
            
            if (descInput) descInput.value = commodityData.description || '';
            if (piecesInput) piecesInput.value = commodityData.pieces || '';
            if (weightInput) weightInput.value = commodityData.weight || '';
            if (dimensionsInput) dimensionsInput.value = commodityData.dimensions || '';
        }
        
        tbody.appendChild(commodityClone);
    }

            // Event delegation for dynamic elements
            stopsList.addEventListener('click', function(e) {
                // Remove stop
                if (e.target.closest('.remove-stop-btn')) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (stopsList.children.length > 1) {
                            const stopCard = e.target.closest('.stop-card');
                        const sequenceNumber = stopCard.querySelector('.sequence-number').textContent;
                        
                        Swal.fire({
                            title: 'Delete Stop?',
                            text: `Are you sure you want to delete Stop ${sequenceNumber}? This action cannot be undone.`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                            stopCard.remove();
                            updateStopNumbers();
                            updateStopCounter();
                            //     Swal.fire({
                            //         title: 'Deleted!',
                            //         text: 'The stop has been deleted.',
                            //         icon: 'success',
                            //         timer: 2000,
                            //         showConfirmButton: false
                            //     });
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Cannot Delete',
                            text: 'You must have at least one stop in the sequence.',
                            icon: 'info',
                            confirmButtonColor: '#007bff'
                        });
                    }
                }

                // Add commodity
                if (e.target.closest('.add-commodity-btn')) {
                    const stopCard = e.target.closest('.stop-card');
                    const currentStopIndex = stopCard.dataset.stopIndex;
                    addCommodityToStop(stopCard, currentStopIndex);
                }

                // Remove commodity
                if (e.target.closest('.remove-commodity-btn')) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const tbody = e.target.closest('tbody');
                    if (tbody.children.length > 1) {
                        Swal.fire({
                            title: 'Delete Commodity?',
                            text: 'Are you sure you want to delete this commodity?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                            e.target.closest('tr').remove();
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'The commodity has been deleted.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Cannot Delete',
                            text: 'Each stop must have at least one commodity.',
                            icon: 'info',
                            confirmButtonColor: '#007bff'
                        });
                    }
                }

                // Add Accessorial
                if (e.target.closest('.accessorial-list a')) {
                    e.preventDefault();
                    const listItem = e.target.closest('li[data-id]');
                    if (!listItem) return;
                    
                    const stopCard = e.target.closest('.stop-card');
                    const accessorialName = e.target.textContent;
                    const accessorialId = listItem.dataset.id;
                    
                    listItem.style.display = 'none';
                    const container = stopCard.querySelector('.selected-accessorials-container');
                    const badgeHtml = `
                        <span class="badge bg-light text-dark border p-2 me-2 mb-2 d-inline-flex align-items-center">
                            ${accessorialName}
                            <button type="button" class="btn-close btn-close-sm ms-2 remove-accessorial-btn" data-id="${accessorialId}" style="font-size: 0.7rem;"></button>
                            <input type="hidden" name="stops[${stopCard.dataset.stopIndex}][accessorials][]" value="${accessorialId}">
                        </span>`;
                    container.insertAdjacentHTML('beforeend', badgeHtml);
                }

                // Remove Accessorial
                if (e.target.closest('.remove-accessorial-btn')) {
                    const accessorialId = e.target.closest('.remove-accessorial-btn').dataset.id;
                    const stopCard = e.target.closest('.stop-card');
                    e.target.closest('.badge').remove();
                    const listItem = stopCard.querySelector(`.accessorial-list li[data-id="${accessorialId}"]`);
                    if (listItem) listItem.style.display = '';
                }
            });

            // Accessorial search functionality
            stopsList.addEventListener('keyup', function(e) {
                if (e.target.classList.contains('accessorial-search')) {
                    const filter = e.target.value.toUpperCase();
                    const stopCard = e.target.closest('.stop-card');
                    const list = stopCard.querySelector('.accessorial-list');
                    const items = list.getElementsByTagName('li');
                    for (let item of items) {
                        let a = item.getElementsByTagName('a')[0];
                        item.style.display = a.innerHTML.toUpperCase().indexOf(filter) > -1 ? '' : 'none';
                    }
                }
            });    function updateStopNumbers() {
        const stopCards = stopsList.querySelectorAll('.stop-card');
        stopCards.forEach((card, index) => {
            const sequenceNumbers = card.querySelectorAll('.sequence-number');
            sequenceNumbers.forEach(num => {
                num.textContent = index + 1;
            });

            // Update connection indicators - only show expand/collapse arrow
            const connectionIndicator = card.querySelector('.stop-connection-indicator');
            if (connectionIndicator) {
                connectionIndicator.innerHTML = '<i class="bi bi-chevron-down expand-icon"></i>';
            }
        });
    }

    function updateStopCounter() {
        const stopCount = stopsList.children.length;
        const counterElement = document.querySelector('.stop-count');
        if (counterElement) {
            counterElement.textContent = stopCount;
        }
    }

    // Drag and Drop functionality
    function setupDragAndDrop(stopCard) {
        const dragHandle = stopCard.querySelector('.drag-handle');
        
        dragHandle.addEventListener('mousedown', function(e) {
            e.preventDefault();
            draggedElement = stopCard;
            stopCard.classList.add('dragging');
            
            document.addEventListener('mousemove', handleMouseMove);
            document.addEventListener('mouseup', handleMouseUp);
        });
    }

    function handleMouseMove(e) {
        if (!draggedElement) return;
        
        const afterElement = getDragAfterElement(stopsList, e.clientY);
        if (afterElement == null) {
            stopsList.appendChild(draggedElement);
        } else {
            stopsList.insertBefore(draggedElement, afterElement);
        }
    }

    function handleMouseUp() {
        if (draggedElement) {
            draggedElement.classList.remove('dragging');
            draggedElement = null;
        }
        
        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('mouseup', handleMouseUp);
        
        updateStopNumbers();
    }

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.stop-card:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    // Form submission handler
    sequenceForm.addEventListener('submit', function(e) {
        // Update sequence numbers before submission
        const stopCards = stopsList.querySelectorAll('.stop-card');
        stopCards.forEach((card, index) => {
            const sequenceInput = document.createElement('input');
            sequenceInput.type = 'hidden';
            sequenceInput.name = `stops[${card.dataset.stopIndex}][sequence_number]`;
            sequenceInput.value = index + 1;
            card.appendChild(sequenceInput);
        });

        // Debug: Log all form data including accessorials
        const formData = new FormData(sequenceForm);
        console.log('Form submission data:');
        for (let [key, value] of formData.entries()) {
            if (key.includes('accessorial')) {
                console.log(`${key}: ${value}`);
            }
        }

        // No validation required - allow form to submit with any data entered        console.log('Sequence order form submitting with', stopCards.length, 'stops');
    });

    // Initialize stop counter
    updateStopCounter();

    // Manifest Assignment Modal Functionality
    const manifestModal = document.getElementById('manifestAssignmentModal');
    const manifestModalInstance = new bootstrap.Modal(manifestModal);
    const updateOrderBtn = document.getElementById('updateOrderBtn');

    // Handle Update Order Button Click
    if (updateOrderBtn) {
        updateOrderBtn.addEventListener('click', function() {
            // Validate Form
            if (!sequenceForm.checkValidity()) {
                sequenceForm.reportValidity();
                return;
            }

            // Custom Validation: Check commodities
            const stopCards = stopsList.querySelectorAll('.stop-card');
            let valid = true;
            stopCards.forEach((card, index) => {
                const commodities = card.querySelectorAll('.commodity-row');
                if (commodities.length === 0) {
                    valid = false;
                    Swal.fire({
                        title: 'Missing Commodity',
                        text: `Stop ${index + 1} must have at least one commodity.`,
                        icon: 'error'
                    });
                }
            });

            if (!valid) return;

            // If valid, show modal
            manifestModalInstance.show();
        });
    }
    const bulkAssignmentToggle = document.getElementById('enableBulkAssignment');
    const bulkAssignmentSection = document.getElementById('bulkAssignmentSection');
    const bulkManifestSelect = document.getElementById('bulkManifestSelect');
    const applyBulkBtn = document.getElementById('applyBulkAssignment');
    const saveManifestBtn = document.getElementById('saveManifestAssignments');
    const stopAssignmentsTable = document.getElementById('stopManifestAssignments');

    let manifestsData = [];

    // Load manifests when modal is shown
    manifestModal.addEventListener('show.bs.modal', async function() {
        await loadManifests();
        populateStopAssignments();
    });

    // Toggle bulk assignment section
    bulkAssignmentToggle.addEventListener('change', function() {
        if (this.checked) {
            bulkAssignmentSection.style.display = 'block';
            // Clear individual selections when bulk is enabled
            const individualSelects = stopAssignmentsTable.querySelectorAll('select');
            individualSelects.forEach(select => {
                select.value = '';
                select.disabled = true;
            });
        } else {
            bulkAssignmentSection.style.display = 'none';
            // Re-enable individual selections
            const individualSelects = stopAssignmentsTable.querySelectorAll('select');
            individualSelects.forEach(select => {
                select.disabled = false;
            });
        }
    });

    // Apply bulk assignment
    applyBulkBtn.addEventListener('click', function() {
        const selectedManifest = bulkManifestSelect.value;
        if (!selectedManifest) {
            Swal.fire('Warning', 'Please select a manifest first.', 'warning');
            return;
        }

        const individualSelects = stopAssignmentsTable.querySelectorAll('select');
        const stopCards = stopsList.querySelectorAll('.stop-card');
        
        console.log(`Applying bulk assignment to ${individualSelects.length} selects and ${stopCards.length} stop cards`);
        
        individualSelects.forEach((select, index) => {
            select.value = selectedManifest;
            console.log(`Set select ${index} to manifest ${selectedManifest}`);
            
            // Update the stop card display immediately
            if (stopCards[index]) {
                updateManifestDisplay(stopCards[index], selectedManifest);
                console.log(`Updated stop card ${index} display`);
            }
        });

        Swal.fire({
            title: 'Applied!',
            text: `Manifest assigned to all ${individualSelects.length} stops.`,
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
    });

    // Save manifest assignments and update order
    saveManifestBtn.addEventListener('click', function() {
        const assignments = [];
        const assignmentRows = stopAssignmentsTable.querySelectorAll('tr');
        
        assignmentRows.forEach(row => {
            const select = row.querySelector('select');
            const stopIndex = row.dataset.stopIndex;
            if (select && stopIndex !== undefined) {
                assignments.push({
                    stop_index: stopIndex,
                    manifest_id: select.value || null
                });
            }
        });

        // Add manifest assignments to the form data
        const form = document.getElementById('sequenceOrderForm');
        
        // Remove existing manifest assignment inputs
        const existingInputs = form.querySelectorAll('input[name*="manifest_id"]');
        existingInputs.forEach(input => input.remove());
        
        // Add new manifest assignment inputs and update displays
        assignments.forEach((assignment, index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `stops[${assignment.stop_index}][manifest_id]`;
            input.value = assignment.manifest_id || '';
            form.appendChild(input);
            
            // Update the manifest display in the stop card
            const stopCard = stopsList.querySelector(`[data-stop-index="${assignment.stop_index}"]`);
            if (stopCard) {
                updateManifestDisplay(stopCard, assignment.manifest_id);
            }
        });

        // --- GATHER QUOTE DATA ---
        // Service, Dates, Notes
        const quoteInputs = [
            { id: 'quote_service_id', name: 'service_id' },
            { id: 'quote_delivery_start', name: 'quote_delivery_start' },
            { id: 'quote_delivery_end', name: 'quote_delivery_end' },
            { id: 'quote_notes', name: 'quote_notes' }
        ];

        quoteInputs.forEach(field => {
            const el = document.getElementById(field.id);
            if (el) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = field.name;
                input.value = el.value;
                form.appendChild(input);
            }
        });

        // Carrier Costs
        const carrierRows = document.querySelectorAll('#carrierCostsTable tbody tr');
        carrierRows.forEach((row, index) => {
            const desc = row.querySelector(`input[name*="[description]"]`).value;
            const cost = row.querySelector(`input[name*="[cost]"]`).value;

            if (desc || cost) {
                form.appendChild(createHiddenInput(`carrier_costs[${index}][description]`, desc));
                form.appendChild(createHiddenInput(`carrier_costs[${index}][cost]`, cost));
            }
        });

        // Customer Quotes
        const customerRows = document.querySelectorAll('#customerQuoteTable tbody tr');
        customerRows.forEach((row, index) => {
            const desc = row.querySelector(`input[name*="[description]"]`).value;
            const cost = row.querySelector(`input[name*="[cost]"]`).value;

            if (desc || cost) {
                form.appendChild(createHiddenInput(`customer_quotes[${index}][description]`, desc));
                form.appendChild(createHiddenInput(`customer_quotes[${index}][cost]`, cost));
            }
        });

        function createHiddenInput(name, value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            return input;
        }

        // Close modal and submit form
        const modal = bootstrap.Modal.getInstance(manifestModal);
        modal.hide();
        
        // Show loading state
        const isUpdate = @json(isset($order));
        saveManifestBtn.innerHTML = `<i class="bi bi-hourglass-split me-1"></i>${isUpdate ? 'Updating' : 'Creating'}...`;
        saveManifestBtn.disabled = true;
        
        // Submit form after modal closes
        setTimeout(() => {
            form.submit();
        }, 300);
    });

    async function loadManifests() {
        try {
            console.log('Loading manifests from API...');
            const response = await fetch('/api/manifests', {
                method: 'GET',
                credentials: 'same-origin', // Include session cookies for authentication
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            console.log('Manifest API response status:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Manifest API error:', errorText);
                throw new Error('Failed to load manifests');
            }
            
            manifestsData = await response.json();
            console.log('Loaded manifests:', manifestsData);
            
            // Populate bulk assignment select
            bulkManifestSelect.innerHTML = '<option value="">Choose a manifest...</option>';
            
            // Add Create Option
            const createOption = document.createElement('option');
            createOption.value = 'create_new';
            createOption.textContent = '+ Create New Manifest';
            createOption.classList.add('fw-bold', 'text-primary');
            bulkManifestSelect.appendChild(createOption);
            
            manifestsData.forEach(manifest => {
                const option = document.createElement('option');
                option.value = manifest.id;
                option.textContent = manifest.manifest_number;
                bulkManifestSelect.appendChild(option);
            });
            
            // Update all individual manifest selects if they exist
            const individualSelects = document.querySelectorAll('.manifest-select');
            individualSelects.forEach(select => {
                const currentValue = select.value; // Preserve current selection
                select.innerHTML = '<option value="">No manifest</option>';
                
                // Add Create Option
                const createOption = document.createElement('option');
                createOption.value = 'create_new';
                createOption.textContent = '+ Create New Manifest';
                createOption.classList.add('fw-bold', 'text-primary');
                select.appendChild(createOption);

                manifestsData.forEach(manifest => {
                    const option = document.createElement('option');
                    option.value = manifest.id;
                    option.textContent = manifest.manifest_number;
                    select.appendChild(option);
                });
                
                // Restore selection if possible, otherwise it stays empty (or "No manifest")
                // If the previously selected manifest is still in the list, it will be selected.
                // If we just created a new one, this loop runs before we set the new value in createManifest,
                // so currentValue might be 'create_new' or empty.
                if (currentValue && currentValue !== 'create_new') {
                    select.value = currentValue;
                }
            });
            
            console.log('Bulk manifest select populated with', manifestsData.length, 'manifests');
            
            // Update all existing stop displays with manifest info
            updateAllManifestDisplays();
            
        } catch (error) {
            console.error('Error loading manifests:', error);
            Swal.fire('Error', 'Failed to load manifests. Please try again.', 'error');
        }
    }

    // Function to update manifest displays for all existing stops
    function updateAllManifestDisplays() {
        if (!existingStopsData || !manifestsData) return;
        
        const stopCards = stopsList.querySelectorAll('.stop-card');
        stopCards.forEach((stopCard, index) => {
            if (existingStopsData[index] && existingStopsData[index].manifest_id) {
                updateManifestDisplay(stopCard, existingStopsData[index].manifest_id);
            }
        });
    }

    function populateStopAssignments() {
        const stops = stopsList.querySelectorAll('.stop-card');
        stopAssignmentsTable.innerHTML = '';
        
        console.log('Populating stop assignments with manifests:', manifestsData.length);
        
        stops.forEach((stop, index) => {
            const stopIndex = stop.dataset.stopIndex;
            
            // Get shipper and consignee names from the form inputs
            const shipperNameInput = stop.querySelector(`input[name="stops[${stopIndex}][shipper_company_name]"]`);
            const consigneeNameInput = stop.querySelector(`input[name="stops[${stopIndex}][consignee_company_name]"]`);
            
            const shipperName = shipperNameInput?.value.trim() || 'Not set';
            const consigneeName = consigneeNameInput?.value.trim() || 'Not set';
            
            console.log(`Stop ${index}: Shipper="${shipperName}", Consignee="${consigneeName}"`);
            
            // Find existing manifest assignment for this stop
            const existingManifest = existingStopsData && existingStopsData[index] ? existingStopsData[index].manifest_id : null;
            
            const row = document.createElement('tr');
            row.dataset.stopIndex = stopIndex;
            
            row.innerHTML = `
                <td class="p-1 align-middle text-center">
                    <span class="badge bg-light text-dark border rounded-pill" style="font-size: 0.7rem;">${index + 1}</span>
                </td>
                <td class="p-1 align-middle">
                    <div class="d-flex flex-column">
                        <div class="mb-1">
                            <span class="text-muted small" style="font-size: 0.65rem;">Shipper:</span>
                            <span class="fw-bold text-dark" style="font-size: 0.75rem;">${shipperName}</span>
                        </div>
                        <div>
                            <span class="text-muted small" style="font-size: 0.65rem;">Consignee:</span>
                            <span class="fw-bold text-dark" style="font-size: 0.75rem;">${consigneeName}</span>
                        </div>
                    </div>
                </td>
                <td class="p-1 align-middle">
                    <select class="form-select form-select-sm manifest-select py-0 ps-1" name="stop_manifest_${stopIndex}" style="font-size: 0.75rem;">
                        <option value="">No manifest</option>
                    </select>
                </td>
            `;
            
            stopAssignmentsTable.appendChild(row);
            
            // Populate manifest options for this row
            const select = row.querySelector('select');
            
            // Add Create Option
            const createOption = document.createElement('option');
            createOption.value = 'create_new';
            createOption.textContent = '+ Create New Manifest';
            createOption.classList.add('fw-bold', 'text-primary');
            select.appendChild(createOption);
            
            manifestsData.forEach(manifest => {
                const option = document.createElement('option');
                option.value = manifest.id;
                option.textContent = manifest.manifest_number;
                select.appendChild(option);
            });
            
            // Pre-select existing manifest if any
            if (existingManifest) {
                select.value = existingManifest;
            }
            
            // Add change listener for individual manifest selection
            select.addEventListener('change', function() {
                const stopCards = stopsList.querySelectorAll('.stop-card');
                if (stopCards[index]) {
                    updateManifestDisplay(stopCards[index], this.value);
                }
            });
        });
    }

    // Function to add cost rows
    window.addCostRow = function(type) {
        const tableId = type === 'carrier' ? 'carrierCostsTable' : 'customerQuoteTable';
        const tbody = document.querySelector(`#${tableId} tbody`);
        if (!tbody) return;
        
        const index = tbody.children.length;
        const prefix = type === 'carrier' ? 'carrier_costs' : 'customer_quotes';
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="p-1">
                <input type="text" class="form-control form-control-sm py-0" style="font-size: 0.8rem;" name="${prefix}[${index}][description]" placeholder="Description">
            </td>
            <td class="p-1">
                <input type="number" class="form-control form-control-sm py-0" style="font-size: 0.8rem;" name="${prefix}[${index}][cost]" step="0.01" placeholder="0.00">
            </td>
            <td class="p-1 align-middle text-center">
                <button type="button" class="btn btn-sm btn-link text-danger p-0" style="font-size: 0.8rem;" onclick="this.closest('tr').remove()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    };

    // --- Create Manifest Logic ---
    let pendingManifestSelect = null; // Store which select triggered the creation

    // Handle "Create New Manifest" selection in bulk dropdown
    bulkManifestSelect.addEventListener('change', function() {
        if (this.value === 'create_new') {
            this.value = ''; // Reset selection
            pendingManifestSelect = this;
            createManifest();
        }
    });

    // Handle "Create New Manifest" selection in individual dropdowns
    stopAssignmentsTable.addEventListener('change', function(e) {
        if (e.target.classList.contains('manifest-select') && e.target.value === 'create_new') {
            e.target.value = ''; // Reset selection
            pendingManifestSelect = e.target;
            createManifest();
        }
    });

    // Immediate Create Manifest Function
    async function createManifest() {
        // Get current order ID if available
        const orderId = {{ isset($order) ? $order->id : 'null' }};

        try {
            // Show loading state
            Swal.fire({
                title: 'Creating Manifest...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await fetch('/api/manifests/quick-create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    start_date: new Date().toISOString().split('T')[0], // Default to today
                    status: 'active', // Default to active
                    assign_order_id: orderId
                })
            });

            const result = await response.json();

            if (result.success) {
                // Refresh manifest list
                await loadManifests();
                
                // Select the new manifest in the pending select
                if (pendingManifestSelect) {
                    pendingManifestSelect.value = result.manifest.id;
                    
                    // If it was an individual select, trigger the change event to update display
                    if (pendingManifestSelect !== bulkManifestSelect) {
                        pendingManifestSelect.dispatchEvent(new Event('change'));
                    } else {
                        // Bulk select - Auto apply to all stops
                        document.getElementById('applyBulkAssignment').click();
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Manifest Created',
                    text: `Manifest ${result.manifest.manifest_number} created successfully!`,
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                throw new Error(result.message || 'Failed to create manifest');
            }

        } catch (error) {
            console.error('Create manifest error:', error);
            Swal.fire('Error', error.message, 'error');
        }
    }
});
</script>

<!-- Order Components JavaScript -->
<script src="{{ asset('js/order-components.js') }}"></script>
