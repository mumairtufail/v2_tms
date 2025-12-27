@php
    // These variables would be passed from your edit() method in the controller.
    // $order, $allAccessorials
    $pickupStop = $order->stops->where('stop_type', 'pickup')->first();
    $deliveryStop = $order->stops->where('stop_type', 'delivery')->first();
    $commodities = $pickupStop?->commodities ?? collect();
    $selectedAccessorials = $deliveryStop?->accessorials ?? collect();
    
    // Debug: Log commodity data being loaded
    if($commodities->count() > 0) {
        \Log::info('Point-to-point commodities loaded for display:', [
            'commodities' => $commodities->toArray()
        ]);
    }
@endphp

{{-- Point-to-Point Order Form - Modern UI matching sequence order --}}

<form action="{{ route('orders.update', $order->id) }}" method="POST" id="pointToPointForm" novalidate>
    @csrf
    @method('PUT')
    <input type="hidden" name="order_type" value="point_to_point">

    <!-- Informational Header -->
    <div class="alert alert-secondary border-0 bg-light small mb-4">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Point-to-Point Order:</strong> Single pickup location to single delivery location.
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
                    <div class="p2p-indicator">
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bi bi-arrow-right me-2"></i>Point-to-Point
                        </span>
                    </div>
                    <small class="text-muted d-block mt-2">Single pickup → Single delivery</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Stop Container -->
    <div class="point-to-point-container">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0">Route Details</h5>
            <span class="badge bg-primary">1 Stop</span>
        </div>
        
        <!-- The Single Stop Card -->
        <div class="stop-card mb-4">
            <div class="card shadow-sm">
                <!-- Stop Header -->
                <div class="card-header bg-light">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary rounded-pill me-3 px-3 py-2">1</span>
                        <h6 class="fw-bold mb-0">Pickup & Delivery Details</h6>
                    </div>
                </div>
                
                <!-- Stop Content -->
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Shipper Information -->
                        <div class="col-lg-4">
                            <div class="pickup-section border rounded p-3 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="pickup-icon me-2">
                                        <i class="bi bi-box-arrow-up text-success fs-5"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-success">SHIPPER</h6>
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small">Company name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('shipper_company_name') is-invalid @enderror" name="shipper_company_name" value="{{ old('shipper_company_name', $pickupStop?->company_name) }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Address 1 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('shipper_address_1') is-invalid @enderror" name="shipper_address_1" value="{{ old('shipper_address_1', $pickupStop?->address_1) }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Address 2</label>
                                    <input type="text" class="form-control form-control-sm" name="shipper_address_2" value="{{ old('shipper_address_2', $pickupStop?->address_2) }}">
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">City <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="shipper_city" value="{{ old('shipper_city', $pickupStop?->city) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">State <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="shipper_state" value="{{ old('shipper_state', $pickupStop?->state) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">ZIP <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="shipper_zip" value="{{ old('shipper_zip', $pickupStop?->postal_code) }}" required>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Country</label>
                                    <input type="text" class="form-control form-control-sm" name="shipper_country" value="{{ old('shipper_country', $pickupStop?->country ?? 'US') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Contact name</label>
                                    <input type="text" class="form-control form-control-sm" name="shipper_contact_name" value="{{ old('shipper_contact_name', $pickupStop?->contact_name) }}">
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Phone</label>
                                        <input type="tel" class="form-control form-control-sm" name="shipper_phone" value="{{ old('shipper_phone', $pickupStop?->contact_phone) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Email</label>
                                        <input type="email" class="form-control form-control-sm" name="shipper_contact_email" value="{{ old('shipper_contact_email', $pickupStop?->contact_email) }}">
                                    </div>
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Opening time</label>
                                        <input type="time" class="form-control form-control-sm" name="shipper_opening_time" value="{{ old('shipper_opening_time', $pickupStop?->opening_time) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Closing time</label>
                                        <input type="time" class="form-control form-control-sm" name="shipper_closing_time" value="{{ old('shipper_closing_time', $pickupStop?->closing_time) }}">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Notes</label>
                                    <textarea class="form-control form-control-sm" name="shipper_notes" rows="2">{{ old('shipper_notes', $pickupStop?->notes) }}</textarea>
                                </div>
                                
                                <!-- Ready Time Section -->
                                <div class="mt-3 pt-3 border-top">
                                    <h6 class="fw-bold mb-2 text-success small">READY TIME</h6>
                                    <div class="row g-1 mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label small">Start time</label>
                                            <input type="datetime-local" class="form-control form-control-sm" name="ready_start_time" value="{{ old('ready_start_time', $pickupStop?->start_time?->format('Y-m-d\TH:i')) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">End time</label>
                                            <input type="datetime-local" class="form-control form-control-sm" name="ready_end_time" value="{{ old('ready_end_time', $pickupStop?->end_time?->format('Y-m-d\TH:i')) }}">
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="p2p_ready_appointment" name="ready_appointment" {{ old('ready_appointment', $pickupStop?->is_appointment) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="p2p_ready_appointment">Make this an appointment</label>
                                    </div>
                                </div>
                            </div>
                        </div>
        
                        <!-- Consignee Information -->
                        <div class="col-lg-4">
                            <div class="delivery-section border rounded p-3 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="delivery-icon me-2">
                                        <i class="bi bi-box-arrow-down text-danger fs-5"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-danger">CONSIGNEE</h6>
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small">Company name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('consignee_company_name') is-invalid @enderror" name="consignee_company_name" value="{{ old('consignee_company_name', $deliveryStop?->company_name) }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Address 1 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('consignee_address_1') is-invalid @enderror" name="consignee_address_1" value="{{ old('consignee_address_1', $deliveryStop?->address_1) }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Address 2</label>
                                    <input type="text" class="form-control form-control-sm" name="consignee_address_2" value="{{ old('consignee_address_2', $deliveryStop?->address_2) }}">
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">City <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="consignee_city" value="{{ old('consignee_city', $deliveryStop?->city) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">State <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="consignee_state" value="{{ old('consignee_state', $deliveryStop?->state) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">ZIP <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="consignee_zip" value="{{ old('consignee_zip', $deliveryStop?->postal_code) }}" required>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Country</label>
                                    <input type="text" class="form-control form-control-sm" name="consignee_country" value="{{ old('consignee_country', $deliveryStop?->country ?? 'US') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Contact name</label>
                                    <input type="text" class="form-control form-control-sm" name="consignee_contact_name" value="{{ old('consignee_contact_name', $deliveryStop?->contact_name) }}">
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Phone</label>
                                        <input type="tel" class="form-control form-control-sm" name="consignee_phone" value="{{ old('consignee_phone', $deliveryStop?->contact_phone) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Email</label>
                                        <input type="email" class="form-control form-control-sm" name="consignee_contact_email" value="{{ old('consignee_contact_email', $deliveryStop?->contact_email) }}">
                                    </div>
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Opening time</label>
                                        <input type="time" class="form-control form-control-sm" name="consignee_opening_time" value="{{ old('consignee_opening_time', $deliveryStop?->opening_time) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Closing time</label>
                                        <input type="time" class="form-control form-control-sm" name="consignee_closing_time" value="{{ old('consignee_closing_time', $deliveryStop?->closing_time) }}">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Notes</label>
                                    <textarea class="form-control form-control-sm" name="consignee_notes" rows="2">{{ old('consignee_notes', $deliveryStop?->notes) }}</textarea>
                                </div>
                                
                                <!-- Delivery Time Section -->
                                <div class="mt-3 pt-3 border-top">
                                    <h6 class="fw-bold mb-2 text-danger small">DELIVERY TIME</h6>
                                    <div class="row g-1 mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label small">Start time</label>
                                            <input type="datetime-local" class="form-control form-control-sm" name="delivery_start_time" value="{{ old('delivery_start_time', $deliveryStop?->start_time?->format('Y-m-d\TH:i')) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">End time</label>
                                            <input type="datetime-local" class="form-control form-control-sm" name="delivery_end_time" value="{{ old('delivery_end_time', $deliveryStop?->end_time?->format('Y-m-d\TH:i')) }}">
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="p2p_delivery_appointment" name="delivery_appointment" {{ old('delivery_appointment', $deliveryStop?->is_appointment) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="p2p_delivery_appointment">Make this an appointment</label>
                                    </div>
                                </div>
                            </div>
                        </div>
        
                        <!-- Commodities & Details -->
                        <div class="col-lg-4">
                            <div class="commodities-section border rounded p-3 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="commodities-icon me-2">
                                        <i class="bi bi-boxes text-primary fs-5"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-primary">COMMODITIES</h6>
                                </div>

                                <div class="commodities-container">
                                    @forelse($commodities as $index => $commodity)
                                    <div class="commodity-item mb-3 p-2 bg-light rounded">
                                        <div class="mb-2">
                                            <label class="form-label small">Description <span class="text-danger">*</span></label>
                                            <input type="text" name="commodities[{{ $index }}][description]" class="form-control form-control-sm" value="{{ $commodity->description }}">
                                        </div>
                                        <div class="row g-1 mb-2">
                                            <div class="col-4">
                                                <label class="form-label small">QTY</label>
                                                <input type="number" name="commodities[{{ $index }}][quantity]" class="form-control form-control-sm" value="{{ $commodity->quantity }}">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label small">Weight</label>
                                                <input type="number" name="commodities[{{ $index }}][weight]" class="form-control form-control-sm" value="{{ $commodity->weight }}">
                                            </div>
                                            <div class="col-4">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-commodity-btn" style="margin-top: 23px;">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row g-1">
                                            <div class="col-4">
                                                <label class="form-label small">L</label>
                                                <input type="number" name="commodities[{{ $index }}][length]" class="form-control form-control-sm" value="{{ $commodity->length }}">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label small">W</label>
                                                <input type="number" name="commodities[{{ $index }}][width]" class="form-control form-control-sm" value="{{ $commodity->width }}">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label small">H</label>
                                                <input type="number" name="commodities[{{ $index }}][height]" class="form-control form-control-sm" value="{{ $commodity->height }}">
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="commodity-item mb-3 p-2 bg-light rounded">
                                        <div class="mb-2">
                                            <label class="form-label small">Description <span class="text-danger">*</span></label>
                                            <input type="text" name="commodities[0][description]" class="form-control form-control-sm">
                                        </div>
                                        <div class="row g-1 mb-2">
                                            <div class="col-4">
                                                <label class="form-label small">QTY</label>
                                                <input type="number" name="commodities[0][quantity]" class="form-control form-control-sm" value="1">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label small">Weight</label>
                                                <input type="number" name="commodities[0][weight]" class="form-control form-control-sm" value="0">
                                            </div>
                                            <div class="col-4">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-commodity-btn" style="margin-top: 23px;">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row g-1">
                                            <div class="col-4">
                                                <label class="form-label small">L</label>
                                                <input type="number" name="commodities[0][length]" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label small">W</label>
                                                <input type="number" name="commodities[0][width]" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label small">H</label>
                                                <input type="number" name="commodities[0][height]" class="form-control form-control-sm" value="0">
                                            </div>
                                        </div>
                                    </div>
                                    @endforelse
                                </div>
                                
                                <button type="button" class="btn btn-sm btn-outline-primary add-commodity-btn w-100 mb-3">
                                    <i class="bi bi-plus"></i> Add Commodity
                                </button>

                                <!-- Special Instructions -->
                                <div class="mt-3 pt-3 border-top">
                                    @include('dashboard.orders.components.special-instructions', [
                                        'namePrefix' => 'order',
                                        'instructions' => old('special_instructions', $order->special_instructions ?? ''),
                                        'showTitle' => false,
                                        'rows' => 3,
                                        'placeholder' => 'Any special handling instructions...'
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accessorials Section -->
    <div class="row mt-4">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-gear text-warning me-2"></i>
                        <h6 class="fw-bold mb-0">ACCESSORIALS</h6>
                        <small class="text-muted ms-2">(Special services for delivery)</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="selected-accessorials-container mb-3">
                        @foreach($selectedAccessorials as $accessorial)
                            <span class="badge bg-light text-dark border p-2 me-2 mb-2 d-inline-flex align-items-center">
                                {{ $accessorial->name }}
                                <input type="hidden" name="accessorials[]" value="{{ $accessorial->id }}">
                                <i class="bi bi-x-lg ms-2 cursor-pointer remove-accessorial-btn" data-id="{{ $accessorial->id }}"></i>
                            </span>
                        @endforeach
                    </div>
                    <div class="dropdown accessorial-dropdown">
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <i class="bi bi-plus"></i> Add Accessorial
                        </button>
                        <div class="dropdown-menu p-2" style="min-width: 250px;">
                            <input type="text" class="accessorial-search form-control form-control-sm mb-2" placeholder="Search accessorials...">
                            <ul class="accessorial-list list-unstyled mb-0" style="max-height: 200px; overflow-y: auto;">
                                @foreach ($allAccessorials as $accessorial)
                                    <li data-id="{{ $accessorial->id }}" data-name="{{ $accessorial->name }}" style="{{ $selectedAccessorials->contains('id', $accessorial->id) ? 'display: none;' : '' }}">
                                        <a class="dropdown-item rounded small" href="#">{{ $accessorial->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-check-lg"></i> Save Changes</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Completely disable all datetime and time validation
    function disableTimeValidation() {
        const timeInputs = document.querySelectorAll('input[type="datetime-local"], input[type="time"], input[type="date"]');
        timeInputs.forEach(input => {
            // Remove any existing validation
            input.setCustomValidity('');
            
            // Prevent invalid event
            input.addEventListener('invalid', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.setCustomValidity('');
                return false;
            });
            
            // Clear any validation on input
            input.addEventListener('input', function() {
                this.setCustomValidity('');
            });
            
            // Clear validation on change
            input.addEventListener('change', function() {
                this.setCustomValidity('');
            });
            
            // Remove any HTML5 validation attributes
            input.removeAttribute('min');
            input.removeAttribute('max');
            input.removeAttribute('pattern');
        });
    }
    
    // Run immediately
    disableTimeValidation();
    
    // Run again after any dynamic content is added
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                disableTimeValidation();
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Override form validation on submit
    const pointToPointForm = document.getElementById('pointToPointForm');
    if (pointToPointForm) {
        pointToPointForm.addEventListener('submit', function(e) {
            // Ensure order_type is correctly set to point_to_point
            let orderTypeInput = pointToPointForm.querySelector('input[name="order_type"]');
            if (!orderTypeInput) {
                orderTypeInput = document.createElement('input');
                orderTypeInput.type = 'hidden';
                orderTypeInput.name = 'order_type';
                pointToPointForm.appendChild(orderTypeInput);
            }
            orderTypeInput.value = 'point_to_point';
            
            // Debug: Check what order_type is being submitted
            console.log('Point-to-point form submitting with order_type:', orderTypeInput.value);
            
            // Clear all validation messages before submit
            const timeInputs = pointToPointForm.querySelectorAll('input[type="datetime-local"], input[type="time"], input[type="date"]');
            timeInputs.forEach(input => {
                input.setCustomValidity('');
            });
        });
    }
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // This is a generic script that can be used on both forms
        function setupFormInteractions(form) {
            if (!form) return;

            // This must be scoped to the specific form
            const addCommodityBtn = form.querySelector('.add-commodity-btn');
            const commoditiesContainer = form.querySelector('.commodities-container');
            let commodityIndex = commoditiesContainer ? commoditiesContainer.querySelectorAll('.commodity-item').length : 0;

            form.addEventListener('click', function(e) {
                // Add Commodity
                if (addCommodityBtn && addCommodityBtn.contains(e.target)) {
                    const newCommodityHtml = `
                        <div class="commodity-item mb-3 p-2 bg-light rounded">
                            <div class="mb-2">
                                <label class="form-label small">Description <span class="text-danger">*</span></label>
                                <input type="text" name="commodities[${commodityIndex}][description]" class="form-control form-control-sm">
                            </div>
                            <div class="row g-1 mb-2">
                                <div class="col-4">
                                    <label class="form-label small">QTY</label>
                                    <input type="number" name="commodities[${commodityIndex}][quantity]" class="form-control form-control-sm" value="1">
                                </div>
                                <div class="col-4">
                                    <label class="form-label small">Weight</label>
                                    <input type="number" name="commodities[${commodityIndex}][weight]" class="form-control form-control-sm" value="0">
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-commodity-btn" style="margin-top: 23px;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row g-1">
                                <div class="col-4">
                                    <label class="form-label small">L</label>
                                    <input type="number" name="commodities[${commodityIndex}][length]" class="form-control form-control-sm">
                                </div>
                                <div class="col-4">
                                    <label class="form-label small">W</label>
                                    <input type="number" name="commodities[${commodityIndex}][width]" class="form-control form-control-sm">
                                </div>
                                <div class="col-4">
                                    <label class="form-label small">H</label>
                                    <input type="number" name="commodities[${commodityIndex}][height]" class="form-control form-control-sm" value="0">
                                </div>
                            </div>
                        </div>`;
                    commoditiesContainer.insertAdjacentHTML('beforeend', newCommodityHtml);
                    commodityIndex++;
                }

                // Remove Commodity
                if (e.target.closest('.remove-commodity-btn')) {
                    if (form.querySelectorAll('.commodity-item').length > 1) {
                        e.target.closest('.commodity-item').remove();
                    } else {
                        alert('You must have at least one commodity.');
                    }
                }

                // Add Accessorial
                if (e.target.closest('.accessorial-list a')) {
                    e.preventDefault();
                    const listItem = e.target.closest('li[data-id]');
                    if (!listItem) return;
                    
                    listItem.style.display = 'none';
                    const container = form.querySelector('.selected-accessorials-container');
                    const badgeHtml = `
                        <span class="badge bg-light text-dark border p-2 me-2 mb-2 d-inline-flex align-items-center">
                            ${listItem.dataset.name}
                            <input type="hidden" name="accessorials[]" value="${listItem.dataset.id}">
                            <i class="bi bi-x-lg ms-2 cursor-pointer remove-accessorial-btn" data-id="${listItem.dataset.id}"></i>
                        </span>`;
                    container.insertAdjacentHTML('beforeend', badgeHtml);
                }

                // Remove Accessorial
                if (e.target.closest('.remove-accessorial-btn')) {
                    const accessorialId = e.target.closest('.remove-accessorial-btn').dataset.id;
                    e.target.closest('.badge').remove();
                    const listItem = form.querySelector(`.accessorial-list li[data-id="${accessorialId}"]`);
                    if (listItem) listItem.style.display = '';
                }
            });

            const searchInput = form.querySelector('.accessorial-search');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const filter = this.value.toUpperCase();
                    const list = form.querySelector(".accessorial-list");
                    const items = list.getElementsByTagName("li");
                    for (let item of items) {
                        let a = item.getElementsByTagName("a")[0];
                        item.style.display = a.innerHTML.toUpperCase().indexOf(filter) > -1 ? "" : "none";
                    }
                });
            }
        }
        setupFormInteractions(document.getElementById('pointToPointForm'));

        function setupTimeValidation(form) {
            if (!form) return;

            const timeFields = [
                { start: 'shipper_opening_time', end: 'shipper_closing_time' },
                { start: 'consignee_opening_time', end: 'consignee_closing_time' },
                { start: 'ready_start_time', end: 'ready_end_time' },
                { start: 'delivery_start_time', end: 'delivery_end_time' }
            ];

            timeFields.forEach(pair => {
                const startEl = form.querySelector(`[name="${pair.start}"]`);
                const endEl = form.querySelector(`[name="${pair.end}"]`);

                if (startEl && endEl) {
                    const validate = () => {
                        if (startEl.value && endEl.value && endEl.value < startEl.value) {
                            endEl.setCustomValidity('End time must be after start time.');
                            endEl.reportValidity();
                        } else {
                            endEl.setCustomValidity('');
                        }
                    };
                    startEl.addEventListener('change', validate);
                    endEl.addEventListener('change', validate);
                }
            });
        }

        setupTimeValidation(document.getElementById('pointToPointForm'));
    });
</script>

<!-- Include Order Components JavaScript -->
<script src="{{ asset('js/order-components.js') }}"></script>