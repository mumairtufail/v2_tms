@php
    $pickupStops = $order->stops->where('stop_type', 'pickup');
    $consigneeStop = $order->stops->where('stop_type', 'delivery')->first();
    $commodities = collect();
    $selectedAccessorials = $consigneeStop?->accessorials ?? collect();
    // Collect all commodities from pickup stops
    foreach($pickupStops as $stop) {
        $commodities = $commodities->merge($stop->commodities ?? collect());
    }
@endphp

{{-- Single Consignee Order Form - Modern UI matching sequence order --}}

<form action="{{ route('orders.saveSingleConsignee', $order->id) }}" method="POST" id="singleConsigneeForm" novalidate>
    @csrf
    <input type="hidden" name="order_type" value="single_consignee">
    <div id="dynamic-sc-form-inputs"></div>

    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <h6 class="alert-heading">Please fix the following errors:</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Informational Header -->
    <div class="alert alert-secondary border-0 bg-light small mb-4">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Single Consignee Order:</strong> Multiple pickup locations delivering to one consignee location.
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
                    <div class="sc-indicator">
                        <span class="badge bg-danger fs-6 px-3 py-2">
                            <i class="bi bi-arrow-down-left me-2"></i>Single Consignee
                        </span>
                    </div>
                    <small class="text-muted d-block mt-2">Multiple pickups → One delivery</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Fixed Consignee Section -->
    <div class="fixed-consignee-section mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-box-arrow-down text-danger me-2"></i>
                Single Consignee Details
            </h5>
            <span class="badge bg-danger">Delivery Location</span>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Consignee Information Column -->
                    <div class="col-lg-4">
                        <div class="delivery-section border rounded p-3 h-100">
                            <h6 class="fw-bold mb-3 text-danger">CONSIGNEE INFORMATION</h6>
                            
                            <div class="mb-2">
                                <label class="form-label small">Company name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="sc_company_name" value="{{ old('sc_company_name', $consigneeStop?->company_name) }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Address 1 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="sc_address_1" value="{{ old('sc_address_1', $consigneeStop?->address_1) }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Address 2</label>
                                <input type="text" class="form-control form-control-sm" name="sc_address_2" value="{{ old('sc_address_2', $consigneeStop?->address_2) }}">
                            </div>
                            <div class="row g-1 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small">City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="sc_city" value="{{ old('sc_city', $consigneeStop?->city) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">State <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="sc_state" value="{{ old('sc_state', $consigneeStop?->state) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">ZIP <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="sc_zip" value="{{ old('sc_zip', $consigneeStop?->postal_code) }}" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Country</label>
                                <input type="text" class="form-control form-control-sm" name="sc_country" value="{{ old('sc_country', $consigneeStop?->country ?? 'US') }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Contact name</label>
                                <input type="text" class="form-control form-control-sm" name="sc_contact_name" value="{{ old('sc_contact_name', $consigneeStop?->contact_name) }}">
                            </div>
                            <div class="row g-1 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Phone</label>
                                    <input type="tel" class="form-control form-control-sm" name="sc_phone" value="{{ old('sc_phone', $consigneeStop?->contact_phone) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Email</label>
                                    <input type="email" class="form-control form-control-sm" name="sc_contact_email" value="{{ old('sc_contact_email', $consigneeStop?->contact_email) }}">
                                </div>
                            </div>
                            <div class="row g-1 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Opening time</label>
                                    <input type="time" class="form-control form-control-sm" name="sc_opening_time" value="{{ old('sc_opening_time', $consigneeStop?->opening_time) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Closing time</label>
                                    <input type="time" class="form-control form-control-sm" name="sc_closing_time" value="{{ old('sc_closing_time', $consigneeStop?->closing_time) }}">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Notes</label>
                                <textarea class="form-control form-control-sm" name="sc_notes" rows="2">{{ old('sc_notes', $consigneeStop?->notes) }}</textarea>
                            </div>
                            
                            <!-- Delivery Schedule -->
                            <div class="mt-3 pt-3 border-top">
                                <h6 class="fw-bold mb-2 text-danger small">DELIVERY TIME</h6>
                                <div class="mb-2">
                                    <label class="form-label small">Start time</label>
                                    <input type="datetime-local" class="form-control form-control-sm" name="sc_delivery_start_time" value="{{ old('sc_delivery_start_time', $consigneeStop?->start_time?->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">End time</label>
                                    <input type="datetime-local" class="form-control form-control-sm" name="sc_delivery_end_time" value="{{ old('sc_delivery_end_time', $consigneeStop?->end_time?->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="sc_make_appointment" name="sc_make_appointment" {{ old('sc_make_appointment', $consigneeStop?->is_appointment) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="sc_make_appointment">Make this an appointment</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Special Instructions Column -->
                    <div class="col-lg-4">
                        @include('dashboard.orders.components.special-instructions', [
                            'namePrefix' => 'order',
                            'instructions' => old('special_instructions', $order->special_instructions ?? ''),
                            'title' => 'SPECIAL INSTRUCTIONS',
                            'titleColor' => 'text-primary',
                            'rows' => 8
                        ])
                    </div>

                    <!-- Accessorials Column -->
                    <div class="col-lg-4">
                        <div class="accessorials-section border rounded p-3 h-100">
                            <h6 class="fw-bold mb-3 text-warning">ACCESSORIALS (DELIVERY)</h6>

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
        </div>
    </div>

    <!-- Shipper Stops Section -->
    <div class="shipper-stops-section mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-box-arrow-up text-success me-2"></i>
                Shipper Stops
            </h5>
            <button type="button" class="btn btn-primary add-shipper-btn">
                <i class="bi bi-plus"></i> Add Shipper Stop
            </button>
        </div>

        <!-- Stops Container -->
        <div class="stops-container sortable-stops" id="stopsContainer">
            @forelse($pickupStops as $index => $stop)
                <div class="stop-card card shadow-sm mb-3" data-stop-index="{{ $index }}">
                    <!-- Collapsed View (Default) -->
                    <div class="stop-row-collapsed p-3" onclick="toggleStopExpansion(this)" style="cursor: pointer;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="stop-sequence-badge me-3">
                                    <span class="badge bg-success rounded-pill px-3 py-2 fw-bold stop-number">{{ $index + 1 }}</span>
                                </div>
                                <div class="stop-summary-content flex-grow-1">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-box-arrow-up text-success me-2"></i>
                                                <div>
                                                    <div class="pickup-company-summary text-truncate fw-medium" style="max-width: 300px;">
                                                        {{ $stop->company_name ?? 'Click to add shipper location' }}
                                                    </div>
                                                    <small class="pickup-address-summary text-muted text-truncate d-block" style="max-width: 300px;">
                                                        @if($stop->address_1)
                                                            {{ $stop->address_1 }}, {{ $stop->city }}, {{ $stop->state }}
                                                        @else
                                                            Address not set
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="stop-connection-indicator ms-3">
                                    <i class="bi bi-chevron-down expand-icon"></i>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn ms-2" title="Remove this stop" onclick="event.stopPropagation(); removeShipperStop(this)">
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
                                    Shipper Stop <span class="sequence-number">{{ $index + 1 }}</span> Details
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
                                    @include('dashboard.orders.components.shipper-form', [
                                        'index' => $index,
                                        'namePrefix' => 'stops',
                                        'data' => $stop,
                                        'required' => true,
                                        'showSchedule' => true,
                                        'title' => 'SHIPPER',
                                        'icon' => 'box-arrow-up',
                                        'iconColor' => 'text-success'
                                    ])
                                </div>

                                <!-- Commodities -->
                                <div class="col-lg-4">
                                    @include('dashboard.orders.components.commodities-section', [
                                        'index' => $index,
                                        'namePrefix' => 'stops',
                                        'commodities' => $stop->commodities ?? collect(),
                                        'title' => 'COMMODITIES',
                                        'titleColor' => 'text-info'
                                    ])
                                </div>

                                <!-- Accessorials -->
                                <div class="col-lg-4">
                                    @if(isset($accessorials) && $accessorials->count() > 0)
                                        @include('dashboard.orders.components.accessorials-section', [
                                            'namePrefix' => "stops[{$index}]",
                                            'selectedAccessorials' => $stop->accessorials ?? collect(),
                                            'title' => 'ACCESSORIALS',
                                            'titleColor' => 'text-warning'
                                        ])
                                    @else
                                        <div class="accessorials-section border rounded p-2 h-100">
                                            <h6 class="fw-bold mb-2 text-warning small">ACCESSORIALS</h6>
                                            <p class="text-muted small mb-0">No accessorials available</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="empty-stops-message text-center py-5">
                    <i class="bi bi-box-arrow-up text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-3">No shipper stops added yet</h5>
                    <p class="text-muted">Click "Add Shipper Stop" to create pickup locations</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-check-lg"></i> Save Changes</button>
    </div>
</form>

<style>
.stop-form-expanded {
    animation: slideDown 0.3s ease-out;
}

.stop-row-collapsed.expanded {
    background-color: #f8f9fa;
}

.expand-icon {
    transition: transform 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
    }
    to {
        opacity: 1;
        max-height: 1000px;
    }
}
</style>

<!-- Include Order Components JavaScript -->
<script src="{{ asset('js/order-components.js') }}"></script>

<script>
// Toggle function for stop expansion
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

// Function to update stop summary display
function updateStopSummary(stopCard) {
    if (!stopCard) return;
    
    const companyNameInput = stopCard.querySelector('input[name*="[shipper_company_name]"]');
    const address1Input = stopCard.querySelector('input[name*="[shipper_address_1]"]');
    const cityInput = stopCard.querySelector('input[name*="[shipper_city]"]');
    const stateInput = stopCard.querySelector('input[name*="[shipper_state]"]');
    
    const companySummary = stopCard.querySelector('.pickup-company-summary');
    const addressSummary = stopCard.querySelector('.pickup-address-summary');
    
    if (companySummary && companyNameInput) {
        companySummary.textContent = companyNameInput.value || 'Click to add shipper location';
    }
    
    if (addressSummary && address1Input && cityInput && stateInput) {
        const address = address1Input.value;
        const city = cityInput.value;
        const state = stateInput.value;
        
        if (address && city && state) {
            addressSummary.textContent = `${address}, ${city}, ${state}`;
        } else {
            addressSummary.textContent = 'Address not set';
        }
    }
}

// Function to remove shipper stop
function removeShipperStop(button) {
    if (!button) return;
    
    const stopCard = button.closest('.stop-card');
    if (!stopCard) return;
    
    // Show confirmation
    if (confirm('Are you sure you want to remove this shipper stop?')) {
        stopCard.remove();
        updateStopNumbers();
        checkEmptyState();
    }
}
</script>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shipperStopModalLabel">
                    <i class="bi bi-box-arrow-up text-success me-2"></i>
                    <span class="modal-title-text">Add Shipper Stop</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <!-- Shipper Information -->
                    <div class="col-lg-6">
                        <div class="shipper-section border rounded p-3">
                            <h6 class="fw-bold mb-3 text-success">SHIPPER INFORMATION</h6>
                            
                            <div class="mb-2">
                                <label class="form-label small">Company name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="shipper_company_name" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Address 1 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="shipper_address_1" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Address 2</label>
                                <input type="text" class="form-control form-control-sm" name="shipper_address_2">
                            </div>
                            <div class="row g-1 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small">City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="shipper_city" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">State <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="shipper_state" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">ZIP <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="shipper_zip" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Country</label>
                                <input type="text" class="form-control form-control-sm" name="shipper_country" value="US">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Contact name</label>
                                <input type="text" class="form-control form-control-sm" name="shipper_contact_name">
                            </div>
                            <div class="row g-1 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Phone</label>
                                    <input type="tel" class="form-control form-control-sm" name="shipper_phone">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Email</label>
                                    <input type="email" class="form-control form-control-sm" name="shipper_contact_email">
                                </div>
                            </div>
                            <div class="row g-1 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Opening time</label>
                                    <input type="time" class="form-control form-control-sm" name="shipper_opening_time">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Closing time</label>
                                    <input type="time" class="form-control form-control-sm" name="shipper_closing_time">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Notes</label>
                                <textarea class="form-control form-control-sm" name="shipper_notes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Ready Time, Commodities & Accessorials -->
                    <div class="col-lg-6">
                        <!-- Ready Time -->
                        <div class="ready-time-section border rounded p-3 mb-3">
                            <h6 class="fw-bold mb-3 text-success">READY TIME</h6>
                            
                            <div class="row g-1 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Start time</label>
                                    <input type="datetime-local" class="form-control form-control-sm" name="ready_start_time">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">End time</label>
                                    <input type="datetime-local" class="form-control form-control-sm" name="ready_end_time">
                                </div>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" name="ready_appointment">
                                <label class="form-check-label small">Make this an appointment</label>
                            </div>
                        </div>

                        <!-- Commodities -->
                        <div class="commodities-section border rounded p-3 mb-3">
                            <h6 class="fw-bold mb-2 text-primary">COMMODITIES</h6>
                            
                            <div class="commodities-container">
                                <div class="commodity-item mb-2 p-2 bg-light rounded">
                                    <div class="mb-1">
                                        <label class="form-label small">Description <span class="text-danger">*</span></label>
                                        <input type="text" name="description" class="form-control form-control-sm">
                                    </div>
                                    <div class="row g-1 mb-1">
                                        <div class="col-4">
                                            <label class="form-label small">QTY</label>
                                            <input type="number" name="quantity" class="form-control form-control-sm" value="1">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">Weight</label>
                                            <input type="number" name="weight" class="form-control form-control-sm" value="0">
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
                                            <input type="number" name="length" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">W</label>
                                            <input type="number" name="width" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">H</label>
                                            <input type="number" name="height" class="form-control form-control-sm" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-outline-primary add-commodity-btn w-100">
                                <i class="bi bi-plus"></i> Add Commodity
                            </button>
                        </div>

                        <!-- Stop Accessorials -->
                        <div class="accessorials-section border rounded p-3">
                            <h6 class="fw-bold mb-2 text-warning">STOP ACCESSORIALS</h6>
                            
                            <div class="selected-accessorials-container mb-3">
                                <!-- Selected accessorials will be added here -->
                            </div>
                            
                            <div class="dropdown accessorial-dropdown">
                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                    <i class="bi bi-plus"></i> Add Accessorial
                                </button>
                                <div class="dropdown-menu p-2" style="min-width: 250px;">
                                    <input type="text" class="accessorial-search form-control form-control-sm mb-2" placeholder="Search accessorials...">
                                    <ul class="accessorial-list list-unstyled mb-0" style="max-height: 200px; overflow-y: auto;">
                                        @foreach ($allAccessorials as $accessorial)
                                            <li data-id="{{ $accessorial->id }}" data-name="{{ $accessorial->name }}">
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveShipperStopBtn">Save Stop</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Disable time validation
    function disableTimeValidation() {
        const timeInputs = document.querySelectorAll('input[type="datetime-local"], input[type="time"], input[type="date"]');
        timeInputs.forEach(input => {
            input.setCustomValidity('');
            input.addEventListener('invalid', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.setCustomValidity('');
                return false;
            });
            input.addEventListener('input', function() { this.setCustomValidity(''); });
            input.addEventListener('change', function() { this.setCustomValidity(''); });
            input.removeAttribute('min');
            input.removeAttribute('max');
            input.removeAttribute('pattern');
        });
    }
    
    disableTimeValidation();
    
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                disableTimeValidation();
            }
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });
    
    // Main form and elements
    const singleConsigneeForm = document.getElementById('singleConsigneeForm');
    const dynamicFormInputs = document.getElementById('dynamic-sc-form-inputs');
    const stopsContainer = document.getElementById('stopsContainer');
    
    let stopCounter = 0;

    // Setup form interactions for consignee accessorials
    function setupFormInteractions(form) {
        if (!form) return;

        form.addEventListener('click', function(e) {
            // Add Accessorial (for main form)
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

            // Remove Accessorial (for main form)
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

    setupFormInteractions(singleConsigneeForm);

    // Event listeners for shipper stops
    document.addEventListener('click', function(e) {
        // Add Shipper Stop
        if (e.target.closest('.add-shipper-btn')) {
            addNewShipperStop();
        }

        // Add commodity button inside expanded forms
        if (e.target.closest('.add-commodity-btn')) {
            const commoditiesContainer = e.target.closest('.commodities-section').querySelector('.commodities-container');
            addCommodityItem(commoditiesContainer);
        }

        // Remove commodity button
        if (e.target.closest('.remove-commodity-btn')) {
            e.target.closest('.commodity-item').remove();
        }

        // Input changes for real-time updates
        if (e.target.matches('input, textarea')) {
            const stopCard = e.target.closest('.stop-card');
            if (stopCard) {
                updateStopSummary(stopCard);
            }
        }
    });

        // Modal interactions
        if (e.target.closest('#shipperStopModal')) {
            const modal = e.target.closest('#shipperStopModal');
            
            // Add Commodity
            if (e.target.closest('.add-commodity-btn')) {
                const commoditiesContainer = modal.querySelector('.commodities-container');
                const commodityIndex = commoditiesContainer.querySelectorAll('.commodity-item').length;
                const newCommodityHtml = `
                    <div class="commodity-item mb-2 p-2 bg-light rounded">
                        <div class="mb-1">
                            <label class="form-label small">Description <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control form-control-sm">
                        </div>
                        <div class="row g-1 mb-1">
                            <div class="col-4">
                                <label class="form-label small">QTY</label>
                                <input type="number" name="quantity" class="form-control form-control-sm" value="1">
                            </div>
                            <div class="col-4">
                                <label class="form-label small">Weight</label>
                                <input type="number" name="weight" class="form-control form-control-sm" value="0">
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
                                <input type="number" name="length" class="form-control form-control-sm">
                            </div>
                            <div class="col-4">
                                <label class="form-label small">W</label>
                                <input type="number" name="width" class="form-control form-control-sm">
                            </div>
                            <div class="col-4">
                                <label class="form-label small">H</label>
                                <input type="number" name="height" class="form-control form-control-sm" value="0">
                            </div>
                        </div>
                    </div>`;
                commoditiesContainer.insertAdjacentHTML('beforeend', newCommodityHtml);
            }

            // Remove Commodity
            if (e.target.closest('.remove-commodity-btn')) {
                if (modal.querySelectorAll('.commodity-item').length > 1) {
                    e.target.closest('.commodity-item').remove();
                } else {
                    alert('You must have at least one commodity.');
                }
            }

            // Modal accessorial interactions
            if (e.target.closest('.accessorial-list a')) {
                e.preventDefault();
                const listItem = e.target.closest('li[data-id]');
                if (!listItem) return;
                
                listItem.style.display = 'none';
                const container = modal.querySelector('.selected-accessorials-container');
                const badgeHtml = `
                    <span class="badge bg-light text-dark border p-2 me-2 mb-2 d-inline-flex align-items-center">
                        ${listItem.dataset.name}
                        <input type="hidden" class="modal-accessorial-input" value="${listItem.dataset.id}">
                        <i class="bi bi-x-lg ms-2 cursor-pointer remove-accessorial-btn" data-id="${listItem.dataset.id}"></i>
                    </span>`;
                container.insertAdjacentHTML('beforeend', badgeHtml);
            }

            if (e.target.closest('.remove-accessorial-btn') && e.target.closest('.selected-accessorials-container')) {
                const accessorialId = e.target.closest('.remove-accessorial-btn').dataset.id;
                e.target.closest('.badge').remove();
                const listItem = modal.querySelector(`.accessorial-list li[data-id="${accessorialId}"]`);
                if (listItem) listItem.style.display = '';
            }
        }
    });

    // Function to add a new shipper stop
    function addNewShipperStop() {
        const index = stopsContainer.querySelectorAll('.stop-card').length;
        
        const stopHtml = `
            <div class="stop-card card shadow-sm mb-3" data-stop-index="${index}">
                <!-- Collapsed View (Default) -->
                <div class="stop-row-collapsed p-3" onclick="toggleStopExpansion(this)" style="cursor: pointer;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center flex-grow-1">
                            <div class="stop-sequence-badge me-3">
                                <span class="badge bg-success rounded-pill px-3 py-2 fw-bold stop-number">${index + 1}</span>
                            </div>
                            <div class="stop-summary-content flex-grow-1">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-box-arrow-up text-success me-2"></i>
                                            <div>
                                                <div class="pickup-company-summary text-truncate fw-medium" style="max-width: 300px;">
                                                    Click to add shipper location
                                                </div>
                                                <small class="pickup-address-summary text-muted text-truncate d-block" style="max-width: 300px;">
                                                    Address not set
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="stop-connection-indicator ms-3">
                                <i class="bi bi-chevron-down expand-icon"></i>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn ms-2" title="Remove this stop" onclick="event.stopPropagation(); removeShipperStop(this)">
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
                                Shipper Stop <span class="sequence-number">${index + 1}</span> Details
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
                                        <input type="text" class="form-control form-control-sm" name="stops[${index}][shipper_company_name]">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Address 1 <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="stops[${index}][shipper_address_1]">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Address 2</label>
                                        <input type="text" class="form-control form-control-sm" name="stops[${index}][shipper_address_2]">
                                    </div>
                                    <div class="row g-1 mb-2">
                                        <div class="col-4">
                                            <label class="form-label small">City <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" name="stops[${index}][shipper_city]">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">State <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" name="stops[${index}][shipper_state]">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">ZIP <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" name="stops[${index}][shipper_zip]">
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Country</label>
                                        <input type="text" class="form-control form-control-sm" name="stops[${index}][shipper_country]" value="USA">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Contact name</label>
                                        <input type="text" class="form-control form-control-sm" name="stops[${index}][shipper_contact_name]">
                                    </div>
                                    <div class="row g-1 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small">Phone</label>
                                            <input type="text" class="form-control form-control-sm" name="stops[${index}][shipper_phone]">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Email</label>
                                            <input type="email" class="form-control form-control-sm" name="stops[${index}][shipper_contact_email]">
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Notes</label>
                                        <textarea class="form-control form-control-sm" name="stops[${index}][shipper_notes]" rows="2"></textarea>
                                    </div>
                                    
                                    <!-- Operating Hours -->
                                    <div class="row g-1 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small">Opening</label>
                                            <input type="time" class="form-control form-control-sm" name="stops[${index}][shipper_opening_time]">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Closing</label>
                                            <input type="time" class="form-control form-control-sm" name="stops[${index}][shipper_closing_time]">
                                        </div>
                                    </div>
                                    
                                    <!-- Ready Schedule -->
                                    <div class="mb-2">
                                        <label class="form-label small">Start time</label>
                                        <input type="datetime-local" class="form-control form-control-sm" name="stops[${index}][ready_start_time]">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">End time</label>
                                        <input type="datetime-local" class="form-control form-control-sm" name="stops[${index}][ready_end_time]">
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" value="1" id="ready_appointment_${index}" name="stops[${index}][ready_appointment]">
                                        <label class="form-check-label small" for="ready_appointment_${index}">Make this an appointment</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Commodities -->
                            <div class="col-lg-4">
                                <div class="commodities-section border rounded p-2 h-100">
                                    <h6 class="fw-bold mb-2 text-info small">COMMODITIES</h6>
                                    <div class="commodities-container">
                                        <div class="commodity-item mb-2 p-2 bg-light rounded">
                                            <div class="mb-2">
                                                <label class="form-label small">Description</label>
                                                <input type="text" class="form-control form-control-sm" name="stops[${index}][commodities][0][description]">
                                            </div>
                                            <div class="row g-1 mb-1">
                                                <div class="col-6">
                                                    <label class="form-label small">QTY</label>
                                                    <input type="number" class="form-control form-control-sm" name="stops[${index}][commodities][0][quantity]" value="1">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small">Weight</label>
                                                    <input type="number" class="form-control form-control-sm" name="stops[${index}][commodities][0][weight]" value="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-info add-commodity-btn">
                                        <i class="bi bi-plus"></i> Add Commodity
                                    </button>
                                </div>
                            </div>

                            <!-- Accessorials -->
                            <div class="col-lg-4">
                                <div class="accessorials-section border rounded p-2 h-100">
                                    <h6 class="fw-bold mb-2 text-warning small">ACCESSORIALS</h6>
                                    <div class="accessorials-container">
                                        @if(isset($accessorials) && $accessorials->count() > 0)
                                            @foreach($accessorials as $accessorial)
                                                <div class="form-check mb-1">
                                                    <input class="form-check-input" type="checkbox" id="accessorial_${index}_{{ $accessorial->id }}" 
                                                           name="stops[${index}][accessorials][]" value="{{ $accessorial->id }}">
                                                    <label class="form-check-label small" for="accessorial_${index}_{{ $accessorial->id }}">
                                                        {{ $accessorial->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-muted small mb-0">No accessorials available</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        
        stopsContainer.insertAdjacentHTML('beforeend', stopHtml);
        
        // Auto expand the new stop
        const newStop = stopsContainer.lastElementChild;
        const collapsedRow = newStop.querySelector('.stop-row-collapsed');
        toggleStopExpansion(collapsedRow);
        
        updateStopNumbers();
        checkEmptyState();
        disableTimeValidation(); // Apply to new elements
    }

    // Function to add a commodity item
    function addCommodityItem(container) {
        const stopCard = container.closest('.stop-card');
        const stopIndex = Array.from(stopsContainer.children).indexOf(stopCard);
        const commodityIndex = container.querySelectorAll('.commodity-item').length;
        
        const commodityHtml = `
            <div class="commodity-item mb-2 p-2 bg-light rounded">
                <div class="mb-2">
                    <label class="form-label small">Description</label>
                    <input type="text" class="form-control form-control-sm" name="stops[${stopIndex}][commodities][${commodityIndex}][description]">
                </div>
                <div class="row g-1 mb-1">
                    <div class="col-4">
                        <label class="form-label small">QTY</label>
                        <input type="number" class="form-control form-control-sm" name="stops[${stopIndex}][commodities][${commodityIndex}][quantity]" value="1">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">Weight</label>
                        <input type="number" class="form-control form-control-sm" name="stops[${stopIndex}][commodities][${commodityIndex}][weight]" value="0">
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
                        <input type="number" class="form-control form-control-sm" name="stops[${stopIndex}][commodities][${commodityIndex}][length]">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">W</label>
                        <input type="number" class="form-control form-control-sm" name="stops[${stopIndex}][commodities][${commodityIndex}][width]">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">H</label>
                        <input type="number" class="form-control form-control-sm" name="stops[${stopIndex}][commodities][${commodityIndex}][height]">
                    </div>
                </div>
            </div>`;
        
        container.insertAdjacentHTML('beforeend', commodityHtml);
    }

    function openShipperModal(stopCard = null) {
        const modalTitleText = shipperModalEl.querySelector('.modal-title-text');
        
        if (stopCard) {
            modalTitleText.textContent = 'Edit Shipper Stop';
            populateModalForm(stopCard);
        } else {
            modalTitleText.textContent = 'Add Shipper Stop';
            clearModalForm();
        }
        
        shipperModal.show();
    }

    function populateModalForm(stopCard) {
        const data = stopCard.dataset;
        
        // Clear existing form and accessorials
        clearModalForm();
        
        // Populate form fields
        for (const key in data) {
            let fieldName = key;
            if (key.startsWith('shipper_')) {
                fieldName = key;
            } else if (key.startsWith('ready_')) {
                fieldName = key;
            }
            
            const field = shipperModalEl.querySelector(`[name="${fieldName}"]`);
            if (field) {
                if (field.type === 'checkbox') {
                    field.checked = (data[key] === '1');
                } else {
                    field.value = data[key];
                }
            }
        }
        
        // Populate commodities
        if (data.commodities) {
            const commodities = JSON.parse(data.commodities);
            const commoditiesContainer = shipperModalEl.querySelector('.commodities-container');
            commoditiesContainer.innerHTML = '';
            
            commodities.forEach((commodity, index) => {
                const commodityHtml = `
                    <div class="commodity-item mb-2 p-2 bg-light rounded">
                        <div class="mb-1">
                            <label class="form-label small">Description <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control form-control-sm" value="${commodity.description || ''}">
                        </div>
                        <div class="row g-1 mb-1">
                            <div class="col-4">
                                <label class="form-label small">QTY</label>
                                <input type="number" name="quantity" class="form-control form-control-sm" value="${commodity.quantity || 1}">
                            </div>
                            <div class="col-4">
                                <label class="form-label small">Weight</label>
                                <input type="number" name="weight" class="form-control form-control-sm" value="${commodity.weight || 0}">
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
                                <input type="number" name="length" class="form-control form-control-sm" value="${commodity.length || ''}">
                            </div>
                            <div class="col-4">
                                <label class="form-label small">W</label>
                                <input type="number" name="width" class="form-control form-control-sm" value="${commodity.width || ''}">
                            </div>
                            <div class="col-4">
                                <label class="form-label small">H</label>
                                <input type="number" name="height" class="form-control form-control-sm" value="${commodity.height || ''}">
                            </div>
                        </div>
                    </div>`;
                commoditiesContainer.insertAdjacentHTML('beforeend', commodityHtml);
            });
        }
        
        // Populate accessorials
        if (data.accessorials) {
            const accessorials = JSON.parse(data.accessorials);
            const accContainer = shipperModalEl.querySelector('.selected-accessorials-container');
            shipperModalEl.querySelectorAll('.accessorial-list li').forEach(li => {
                if (accessorials.includes(parseInt(li.dataset.id))) {
                    li.style.display = 'none';
                    const badgeHtml = `
                        <span class="badge bg-light text-dark border p-2 me-2 mb-2 d-inline-flex align-items-center">
                            ${li.dataset.name}
                            <input type="hidden" class="modal-accessorial-input" value="${li.dataset.id}">
                            <i class="bi bi-x-lg ms-2 cursor-pointer remove-accessorial-btn" data-id="${li.dataset.id}"></i>
                        </span>`;
                    accContainer.insertAdjacentHTML('beforeend', badgeHtml);
                }
            });
        }
    }

    function clearModalForm() {
        // Clear all form fields
        shipperModalEl.querySelectorAll('input, textarea').forEach(input => {
            if (input.type === 'checkbox') {
                input.checked = false;
            } else if (input.name === 'shipper_country') {
                input.value = 'US';
            } else if (input.name === 'quantity') {
                input.value = '1';
            } else if (input.name === 'weight' || input.name === 'height') {
                input.value = '0';
            } else {
                input.value = '';
            }
        });
        
        // Reset commodities to one item
        const commoditiesContainer = shipperModalEl.querySelector('.commodities-container');
        commoditiesContainer.innerHTML = `
            <div class="commodity-item mb-2 p-2 bg-light rounded">
                <div class="mb-1">
                    <label class="form-label small">Description <span class="text-danger">*</span></label>
                    <input type="text" name="description" class="form-control form-control-sm">
                </div>
                <div class="row g-1 mb-1">
                    <div class="col-4">
                        <label class="form-label small">QTY</label>
                        <input type="number" name="quantity" class="form-control form-control-sm" value="1">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">Weight</label>
                        <input type="number" name="weight" class="form-control form-control-sm" value="0">
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
                        <input type="number" name="length" class="form-control form-control-sm">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">W</label>
                        <input type="number" name="width" class="form-control form-control-sm">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">H</label>
                        <input type="number" name="height" class="form-control form-control-sm" value="0">
                    </div>
                </div>
            </div>`;
        
        // Clear accessorials
        shipperModalEl.querySelector('.selected-accessorials-container').innerHTML = '';
        shipperModalEl.querySelectorAll('.accessorial-list li').forEach(li => {
            li.style.display = '';
        });
    }

    function saveShipperStop() {
        const modalBody = shipperModalEl.querySelector('.modal-body');
        const data = {};
        
        // Collect form data
        modalBody.querySelectorAll('input, textarea').forEach(field => {
            if (field.type === 'checkbox') {
                data[field.name] = field.checked ? '1' : '0';
            } else {
                data[field.name] = field.value;
            }
        });
        
        // Collect commodities
        const commodityItems = modalBody.querySelectorAll('.commodity-item');
        const commodities = Array.from(commodityItems).map(item => ({
            description: item.querySelector('[name="description"]').value,
            quantity: item.querySelector('[name="quantity"]').value,
            weight: item.querySelector('[name="weight"]').value,
            length: item.querySelector('[name="length"]').value,
            width: item.querySelector('[name="width"]').value,
            height: item.querySelector('[name="height"]').value
        }));
        data.commodities = JSON.stringify(commodities);
        
        // Collect accessorials
        const accessorials = Array.from(modalBody.querySelectorAll('.modal-accessorial-input')).map(input => input.value);
        data.accessorials = JSON.stringify(accessorials);
        
        if (currentEditingStop) {
            // Update existing stop
            updateStopCard(currentEditingStop, data);
        } else {
            // Create new stop
            createStopCard(data);
        }
        
        updateStopNumbers();
        checkEmptyState();
        shipperModal.hide();
    }

    function createStopCard(data) {
        const stopHtml = generateStopCardHtml(data, stopCounter++);
        stopsContainer.insertAdjacentHTML('beforeend', stopHtml);
    }

    function updateStopCard(stopCard, data) {
        // Update dataset
        for (const key in data) {
            stopCard.dataset[key] = data[key];
        }
        
        // Update visible content
        const companyName = data.shipper_company_name || 'Unknown Company';
        const location = `${data.shipper_city || 'Unknown'}, ${data.shipper_state || 'Unknown'}`;
        
        stopCard.querySelector('.card-header h6').textContent = companyName;
        stopCard.querySelector('.card-header small').textContent = location;
        
        // Update details in card body
        updateStopCardContent(stopCard, data);
    }

    function generateStopCardHtml(data) {
        const companyName = data.shipper_company_name || 'Unknown Company';
        const location = `${data.shipper_city || 'Unknown'}, ${data.shipper_state || 'Unknown'}`;
        const address = data.shipper_address_1 || 'No address';
        const contact = data.shipper_contact_name || 'Not specified';
        const phone = data.shipper_phone || 'Not specified';
        
        let dataAttrs = '';
        for (const key in data) {
            dataAttrs += ` data-${key}="${(data[key] || '').replace(/"/g, '&quot;')}"`;
        }
        
        return `
            <div class="stop-card mb-3"${dataAttrs}>
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success rounded-pill me-3 px-3 py-2 stop-number">1</span>
                                <div>
                                    <h6 class="fw-bold mb-0">${companyName}</h6>
                                    <small class="text-muted">${location}</small>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary edit-stop-btn" title="Edit Stop">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn" title="Remove Stop">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 text-sm">
                            <div class="col-md-6">
                                <strong class="small">Address:</strong><br>
                                <span class="text-muted small">${address}</span>
                            </div>
                            <div class="col-md-3">
                                <strong class="small">Contact:</strong><br>
                                <span class="text-muted small">${contact}</span>
                            </div>
                            <div class="col-md-3">
                                <strong class="small">Phone:</strong><br>
                                <span class="text-muted small">${phone}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function updateStopCardContent(stopCard, data) {
        const cardBody = stopCard.querySelector('.card-body');
        const address = data.shipper_address_1 || 'No address';
        const contact = data.shipper_contact_name || 'Not specified';
        const phone = data.shipper_phone || 'Not specified';
        
        cardBody.innerHTML = `
            <div class="row g-2 text-sm">
                <div class="col-md-6">
                    <strong class="small">Address:</strong><br>
                    <span class="text-muted small">${address}</span>
                </div>
                <div class="col-md-3">
                    <strong class="small">Contact:</strong><br>
                    <span class="text-muted small">${contact}</span>
                </div>
                <div class="col-md-3">
                    <strong class="small">Phone:</strong><br>
                    <span class="text-muted small">${phone}</span>
                </div>
            </div>`;
    }

    function updateStopNumbers() {
        const stopCards = stopsContainer.querySelectorAll('.stop-card');
        stopCards.forEach((card, index) => {
            const stopNumber = card.querySelector('.stop-number');
            if (stopNumber) {
                stopNumber.textContent = index + 1; // Pickups start at 1
            }
        });
    }

    function checkEmptyState() {
        const stopCards = stopsContainer.querySelectorAll('.stop-card');
        const emptyMessage = stopsContainer.querySelector('.empty-stops-message');
        
        if (stopCards.length === 0) {
            if (!emptyMessage) {
                stopsContainer.innerHTML = `
                    <div class="empty-stops-message text-center py-5">
                        <i class="bi bi-box-arrow-up text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No shipper stops added yet</h5>
                        <p class="text-muted">Click "Add Shipper Stop" to create pickup locations</p>
                    </div>`;
            }
        } else {
            if (emptyMessage) {
                emptyMessage.remove();
            }
        }
    }

    // Form submission
    singleConsigneeForm.addEventListener('submit', function(e) {
        // Ensure order_type is set
        let orderTypeInput = singleConsigneeForm.querySelector('input[name="order_type"]');
        if (!orderTypeInput) {
            orderTypeInput = document.createElement('input');
            orderTypeInput.type = 'hidden';
            orderTypeInput.name = 'order_type';
            singleConsigneeForm.appendChild(orderTypeInput);
        }
        orderTypeInput.value = 'single_consignee';
        
        // The form inputs are now inline, so no need to generate hidden inputs
        // Just clear validation
        const timeInputs = singleConsigneeForm.querySelectorAll('input[type="datetime-local"], input[type="time"], input[type="date"]');
        timeInputs.forEach(input => input.setCustomValidity(''));
    });
    
    // Initialize
    updateStopNumbers();
    checkEmptyState();
});
</script>