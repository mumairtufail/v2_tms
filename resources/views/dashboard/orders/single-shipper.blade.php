@php
    $shipperStop = $order->stops->where('stop_type', 'pickup')->first();
    $consigneeStops = $order->stops->where('stop_type', 'delivery');
    $commodities = $shipperStop?->commodities ?? collect();
    $selectedAccessorials = collect();
    // Collect all accessorials from delivery stops
    foreach($consigneeStops as $stop) {
        $selectedAccessorials = $selectedAccessorials->merge($stop->accessorials ?? collect());
    }
    $selectedAccessorials = $selectedAccessorials->unique('id');
@endphp

{{-- Single Shipper Order Form - Modern UI matching sequence order --}}

<form action="{{ route('orders.saveSingleShipper', $order->id) }}" method="POST" id="singleShipperForm" novalidate>
    @csrf
    <input type="hidden" name="order_type" value="single_shipper">
    <div id="dynamic-form-inputs"></div>

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
        <strong>Single Shipper Order:</strong> One pickup location delivering to multiple consignee locations.
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
                    <div class="ss-indicator">
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bi bi-arrow-down-right me-2"></i>Single Shipper
                        </span>
                    </div>
                    <small class="text-muted d-block mt-2">One pickup → Multiple deliveries</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Fixed Shipper Section -->
    <div class="fixed-shipper-section mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-box-arrow-up text-success me-2"></i>
                Single Shipper Details
            </h5>
            <span class="badge bg-success">Pickup Location</span>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Shipper Information Column -->
                    <div class="col-lg-4">
                        <div class="pickup-section border rounded p-3 h-100">
                            <h6 class="fw-bold mb-3 text-success">SHIPPER INFORMATION</h6>
                            
                            <div class="mb-2">
                                <label class="form-label small">Company name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="ss_company_name" value="{{ old('ss_company_name', $shipperStop?->company_name) }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Address 1 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="ss_address_1" value="{{ old('ss_address_1', $shipperStop?->address_1) }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Address 2</label>
                                <input type="text" class="form-control form-control-sm" name="ss_address_2" value="{{ old('ss_address_2', $shipperStop?->address_2) }}">
                            </div>
                            <div class="row g-1 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small">City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="ss_city" value="{{ old('ss_city', $shipperStop?->city) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">State <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="ss_state" value="{{ old('ss_state', $shipperStop?->state) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">ZIP <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="ss_zip" value="{{ old('ss_zip', $shipperStop?->postal_code) }}" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Country</label>
                                <input type="text" class="form-control form-control-sm" name="ss_country" value="{{ old('ss_country', $shipperStop?->country ?? 'US') }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Contact name</label>
                                <input type="text" class="form-control form-control-sm" name="ss_contact_name" value="{{ old('ss_contact_name', $shipperStop?->contact_name) }}">
                            </div>
                            <div class="row g-1 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Phone</label>
                                    <input type="tel" class="form-control form-control-sm" name="ss_phone" value="{{ old('ss_phone', $shipperStop?->contact_phone) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Email</label>
                                    <input type="email" class="form-control form-control-sm" name="ss_contact_email" value="{{ old('ss_contact_email', $shipperStop?->contact_email) }}">
                                </div>
                            </div>
                            <div class="row g-1 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Opening time</label>
                                    <input type="time" class="form-control form-control-sm" name="ss_opening_time" value="{{ old('ss_opening_time', $shipperStop?->opening_time) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Closing time</label>
                                    <input type="time" class="form-control form-control-sm" name="ss_closing_time" value="{{ old('ss_closing_time', $shipperStop?->closing_time) }}">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Notes</label>
                                <textarea class="form-control form-control-sm" name="ss_notes" rows="2">{{ old('ss_notes', $shipperStop?->notes) }}</textarea>
                            </div>
                            
                            <!-- Ready Schedule -->
                            <div class="mt-3 pt-3 border-top">
                                <h6 class="fw-bold mb-2 text-success small">READY TIME</h6>
                                <div class="mb-2">
                                    <label class="form-label small">Start time</label>
                                    <input type="datetime-local" class="form-control form-control-sm" name="ss_ready_start_time" value="{{ old('ss_ready_start_time', $shipperStop?->start_time?->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">End time</label>
                                    <input type="datetime-local" class="form-control form-control-sm" name="ss_ready_end_time" value="{{ old('ss_ready_end_time', $shipperStop?->end_time?->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="ss_make_appointment" name="ss_make_appointment" {{ old('ss_make_appointment', $shipperStop?->is_appointment) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="ss_make_appointment">Make this an appointment</label>
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

                    <!-- Commodities Column -->
                    <div class="col-lg-4">
                        <div class="commodities-section border rounded p-3 h-100">
                            <h6 class="fw-bold mb-3 text-primary">COMMODITIES (PICKUP)</h6>

                            <div class="commodities-container">
                                @forelse($commodities as $index => $commodity)
                                <div class="commodity-item mb-2 p-2 bg-light rounded">
                                    <div class="mb-2">
                                        <label class="form-label small">Description <span class="text-danger">*</span></label>
                                        <input type="text" name="commodities[{{ $index }}][description]" class="form-control form-control-sm" value="{{ $commodity->description }}">
                                    </div>
                                    <div class="row g-1 mb-1">
                                        <div class="col-4">
                                            <label class="form-label small">QTY</label>
                                            <input type="number" name="commodities[{{ $index }}][quantity]" class="form-control form-control-sm" value="{{ $commodity->quantity }}">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">Weight</label>
                                            <input type="number" name="commodities[{{ $index }}][weight]" class="form-control form-control-sm" value="{{ $commodity->weight }}">
                                        </div>
                                        <div class="col-4">
                                            @if($loop->iteration > 1 || $commodities->count() > 1)
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-commodity-btn" style="margin-top: 23px;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
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
                                <div class="commodity-item mb-2 p-2 bg-light rounded">
                                    <div class="mb-2">
                                        <label class="form-label small">Description <span class="text-danger">*</span></label>
                                        <input type="text" name="commodities[0][description]" class="form-control form-control-sm">
                                    </div>
                                    <div class="row g-1 mb-1">
                                        <div class="col-6">
                                            <label class="form-label small">QTY</label>
                                            <input type="number" name="commodities[0][quantity]" class="form-control form-control-sm" value="1">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Weight</label>
                                            <input type="number" name="commodities[0][weight]" class="form-control form-control-sm" value="0">
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
                            
                            <button type="button" class="btn btn-sm btn-outline-primary add-commodity-btn w-100">
                                <i class="bi bi-plus"></i> Add Commodity
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Consignee Stops Section -->
    <div class="consignee-stops-section mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-box-arrow-down text-danger me-2"></i>
                Consignee Stops
            </h5>
            <button type="button" class="btn btn-primary add-consignee-btn">
                <i class="bi bi-plus"></i> Add Consignee Stop
            </button>
        </div>

        <!-- Stops Container -->
        <div class="stops-container sortable-stops" id="stopsContainer">
            @forelse($consigneeStops as $index => $stop)
                <div class="stop-card card shadow-sm mb-3" data-stop-index="{{ $index }}">
                    <!-- Collapsed View (Default) -->
                    <div class="stop-row-collapsed p-3" onclick="toggleStopExpansion(this)" style="cursor: pointer;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="stop-sequence-badge me-3">
                                    <span class="badge bg-danger rounded-pill px-3 py-2 fw-bold stop-number">{{ $index + 2 }}</span>
                                </div>
                                <div class="stop-summary-content flex-grow-1">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-box-arrow-in-down text-primary me-2"></i>
                                                <div>
                                                    <div class="delivery-company-summary text-truncate fw-medium" style="max-width: 300px;">
                                                        {{ $stop->company_name ?? 'Click to add consignee location' }}
                                                    </div>
                                                    <small class="delivery-address-summary text-muted text-truncate d-block" style="max-width: 300px;">
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
                            <button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn ms-2" title="Remove this stop" onclick="event.stopPropagation(); removeConsigneeStop(this)">
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
                                    Consignee Stop <span class="sequence-number">{{ $index + 2 }}</span> Details
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-secondary collapse-stop-btn" onclick="toggleStopExpansion(this.closest('.stop-card').querySelector('.stop-row-collapsed'))">
                                    <i class="bi bi-chevron-up me-1"></i> Collapse
                                </button>
                            </div>
                        </div>
                        
                        <!-- Stop Content -->
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Consignee Information -->
                                <div class="col-lg-6">
                                    <div class="delivery-section border rounded p-2 h-100">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="delivery-icon me-2">
                                                <i class="bi bi-box-arrow-in-down text-primary"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-primary small">CONSIGNEE</h6>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <label class="form-label small">Company name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" name="stops[{{ $index }}][consignee_company_name]" value="{{ $stop->company_name }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small">Address 1 <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" name="stops[{{ $index }}][consignee_address_1]" value="{{ $stop->address_1 }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small">Address 2</label>
                                            <input type="text" class="form-control form-control-sm" name="stops[{{ $index }}][consignee_address_2]" value="{{ $stop->address_2 }}">
                                        </div>
                                        <div class="row g-1 mb-2">
                                            <div class="col-4">
                                                <label class="form-label small">City <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm" name="stops[{{ $index }}][consignee_city]" value="{{ $stop->city }}">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label small">State <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm" name="stops[{{ $index }}][consignee_state]" value="{{ $stop->state }}">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label small">ZIP <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm" name="stops[{{ $index }}][consignee_zip]" value="{{ $stop->postal_code }}">
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small">Country</label>
                                            <input type="text" class="form-control form-control-sm" name="stops[{{ $index }}][consignee_country]" value="{{ $stop->country ?? 'USA' }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small">Contact name</label>
                                            <input type="text" class="form-control form-control-sm" name="stops[{{ $index }}][consignee_contact_name]" value="{{ $stop->contact_name }}">
                                        </div>
                                        <div class="row g-1 mb-2">
                                            <div class="col-6">
                                                <label class="form-label small">Phone</label>
                                                <input type="text" class="form-control form-control-sm" name="stops[{{ $index }}][consignee_phone]" value="{{ $stop->contact_phone }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small">Email</label>
                                                <input type="email" class="form-control form-control-sm" name="stops[{{ $index }}][consignee_contact_email]" value="{{ $stop->contact_email }}">
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small">Notes</label>
                                            <textarea class="form-control form-control-sm" name="stops[{{ $index }}][consignee_notes]" rows="2">{{ $stop->notes }}</textarea>
                                        </div>
                                        
                                        <!-- Operating Hours -->
                                        <div class="row g-1 mb-2">
                                            <div class="col-6">
                                                <label class="form-label small">Opening</label>
                                                <input type="time" class="form-control form-control-sm" name="stops[{{ $index }}][consignee_opening_time]" value="{{ $stop->opening_time }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small">Closing</label>
                                                <input type="time" class="form-control form-control-sm" name="stops[{{ $index }}][consignee_closing_time]" value="{{ $stop->closing_time }}">
                                            </div>
                                        </div>
                                        
                                        <!-- Delivery Schedule -->
                                        <div class="mb-2">
                                            <label class="form-label small">Start time</label>
                                            <input type="datetime-local" class="form-control form-control-sm" name="stops[{{ $index }}][delivery_start_time]" value="{{ $stop->start_time?->format('Y-m-d\TH:i') }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small">End time</label>
                                            <input type="datetime-local" class="form-control form-control-sm" name="stops[{{ $index }}][delivery_end_time]" value="{{ $stop->end_time?->format('Y-m-d\TH:i') }}">
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" value="1" id="delivery_appointment_{{ $index }}" name="stops[{{ $index }}][delivery_appointment]" {{ $stop->is_appointment ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="delivery_appointment_{{ $index }}">Make this an appointment</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Accessorials -->
                                <div class="col-lg-6">
                                    <div class="accessorials-section border rounded p-2 h-100">
                                        <h6 class="fw-bold mb-2 text-warning small">ACCESSORIALS</h6>
                                        <div class="accessorials-container">
                                            @if(isset($accessorials) && $accessorials->count() > 0)
                                                @foreach($accessorials as $accessorial)
                                                    <div class="form-check mb-1">
                                                        <input class="form-check-input" type="checkbox" id="accessorial_{{ $index }}_{{ $accessorial->id }}" 
                                                               name="stops[{{ $index }}][accessorials][]" value="{{ $accessorial->id }}"
                                                               {{ $stop->accessorials->contains('id', $accessorial->id) ? 'checked' : '' }}>
                                                        <label class="form-check-label small" for="accessorial_{{ $index }}_{{ $accessorial->id }}">
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
                </div>
            @empty
                <!-- Empty State -->
                <div class="empty-stops-message text-center py-5">
                    <i class="bi bi-box-arrow-down text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-3">No consignee stops added yet</h5>
                    <p class="text-muted">Click "Add Consignee Stop" to create delivery locations</p>
                </div>
            @endforelse
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
                        <small class="text-muted ms-2">(Special services for all deliveries)</small>
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
    
    const companyNameInput = stopCard.querySelector('input[name*="[consignee_company_name]"]');
    const address1Input = stopCard.querySelector('input[name*="[consignee_address_1]"]');
    const cityInput = stopCard.querySelector('input[name*="[consignee_city]"]');
    const stateInput = stopCard.querySelector('input[name*="[consignee_state]"]');
    
    const companySummary = stopCard.querySelector('.delivery-company-summary');
    const addressSummary = stopCard.querySelector('.delivery-address-summary');
    
    if (companySummary && companyNameInput) {
        companySummary.textContent = companyNameInput.value || 'Click to add consignee location';
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

// Function to remove consignee stop
function removeConsigneeStop(button) {
    if (!button) return;
    
    const stopCard = button.closest('.stop-card');
    if (!stopCard) return;
    
    // Show confirmation
    if (confirm('Are you sure you want to remove this consignee stop?')) {
        stopCard.remove();
        updateStopNumbers();
        checkEmptyState();
    }
}
</script>

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
    const singleShipperForm = document.getElementById('singleShipperForm');
    const dynamicFormInputs = document.getElementById('dynamic-form-inputs');
    const stopsContainer = document.getElementById('stopsContainer');
    
    let stopCounter = 0;

    // Setup form interactions
    function setupFormInteractions(form) {
        if (!form) return;

        const addCommodityBtn = form.querySelector('.add-commodity-btn');
        const commoditiesContainer = form.querySelector('.commodities-container');
        let commodityIndex = commoditiesContainer ? commoditiesContainer.querySelectorAll('.commodity-item').length : 0;

        form.addEventListener('click', function(e) {
            // Add Commodity
            if (addCommodityBtn && addCommodityBtn.contains(e.target)) {
                const newCommodityHtml = `
                    <div class="commodity-item mb-2 p-2 bg-light rounded">
                        <div class="mb-2">
                            <label class="form-label small">Description <span class="text-danger">*</span></label>
                            <input type="text" name="commodities[${commodityIndex}][description]" class="form-control form-control-sm">
                        </div>
                        <div class="row g-1 mb-1">
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

    setupFormInteractions(singleShipperForm);

    // Event listeners for consignee stops
    document.addEventListener('click', function(e) {
        // Add Consignee Stop
        if (e.target.closest('.add-consignee-btn')) {
            addNewConsigneeStop();
        }

        // Input changes for real-time updates
        if (e.target.matches('input, textarea')) {
            const stopCard = e.target.closest('.stop-card');
            if (stopCard) {
                updateStopSummary(stopCard);
            }
        }
    });

    // Function to add a new consignee stop
    function addNewConsigneeStop() {
        const index = stopsContainer.querySelectorAll('.stop-card').length;
        
        const stopHtml = `
            <div class="stop-card card shadow-sm mb-3" data-stop-index="${index}">
                <!-- Collapsed View (Default) -->
                <div class="stop-row-collapsed p-3" onclick="toggleStopExpansion(this)" style="cursor: pointer;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center flex-grow-1">
                            <div class="stop-sequence-badge me-3">
                                <span class="badge bg-danger rounded-pill px-3 py-2 fw-bold stop-number">${index + 2}</span>
                            </div>
                            <div class="stop-summary-content flex-grow-1">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-box-arrow-in-down text-primary me-2"></i>
                                            <div>
                                                <div class="delivery-company-summary text-truncate fw-medium" style="max-width: 300px;">
                                                    Click to add consignee location
                                                </div>
                                                <small class="delivery-address-summary text-muted text-truncate d-block" style="max-width: 300px;">
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
                        <button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn ms-2" title="Remove this stop" onclick="event.stopPropagation(); removeConsigneeStop(this)">
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
                                Consignee Stop <span class="sequence-number">${index + 2}</span> Details
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-secondary collapse-stop-btn" onclick="toggleStopExpansion(this.closest('.stop-card').querySelector('.stop-row-collapsed'))">
                                <i class="bi bi-chevron-up me-1"></i> Collapse
                            </button>
                        </div>
                    </div>
                    
                    <!-- Stop Content -->
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Consignee Information -->
                            <div class="col-lg-6">
                                <div class="delivery-section border rounded p-2 h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="delivery-icon me-2">
                                            <i class="bi bi-box-arrow-in-down text-primary"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-primary small">CONSIGNEE</h6>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <label class="form-label small">Company name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="stops[${index}][consignee_company_name]">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Address 1 <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="stops[${index}][consignee_address_1]">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Address 2</label>
                                        <input type="text" class="form-control form-control-sm" name="stops[${index}][consignee_address_2]">
                                    </div>
                                    <div class="row g-1 mb-2">
                                        <div class="col-4">
                                            <label class="form-label small">City <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" name="stops[${index}][consignee_city]">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">State <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" name="stops[${index}][consignee_state]">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">ZIP <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" name="stops[${index}][consignee_zip]">
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Country</label>
                                        <input type="text" class="form-control form-control-sm" name="stops[${index}][consignee_country]" value="USA">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Contact name</label>
                                        <input type="text" class="form-control form-control-sm" name="stops[${index}][consignee_contact_name]">
                                    </div>
                                    <div class="row g-1 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small">Phone</label>
                                            <input type="text" class="form-control form-control-sm" name="stops[${index}][consignee_phone]">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Email</label>
                                            <input type="email" class="form-control form-control-sm" name="stops[${index}][consignee_contact_email]">
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Notes</label>
                                        <textarea class="form-control form-control-sm" name="stops[${index}][consignee_notes]" rows="2"></textarea>
                                    </div>
                                    
                                    <!-- Operating Hours -->
                                    <div class="row g-1 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small">Opening</label>
                                            <input type="time" class="form-control form-control-sm" name="stops[${index}][consignee_opening_time]">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Closing</label>
                                            <input type="time" class="form-control form-control-sm" name="stops[${index}][consignee_closing_time]">
                                        </div>
                                    </div>
                                    
                                    <!-- Delivery Schedule -->
                                    <div class="mb-2">
                                        <label class="form-label small">Start time</label>
                                        <input type="datetime-local" class="form-control form-control-sm" name="stops[${index}][delivery_start_time]">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">End time</label>
                                        <input type="datetime-local" class="form-control form-control-sm" name="stops[${index}][delivery_end_time]">
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" value="1" id="delivery_appointment_${index}" name="stops[${index}][delivery_appointment]">
                                        <label class="form-check-label small" for="delivery_appointment_${index}">Make this an appointment</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Accessorials -->
                            <div class="col-lg-6">
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

    function updateStopNumbers() {
        const stopCards = stopsContainer.querySelectorAll('.stop-card');
        stopCards.forEach((card, index) => {
            const stopNumber = card.querySelector('.stop-number');
            if (stopNumber) {
                stopNumber.textContent = index + 2; // +2 because shipper is #1
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
                        <i class="bi bi-box-arrow-down text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No consignee stops added yet</h5>
                        <p class="text-muted">Click "Add Consignee Stop" to create delivery locations</p>
                    </div>`;
            }
        } else {
            if (emptyMessage) {
                emptyMessage.remove();
            }
        }
    }

    // Form submission
    singleShipperForm.addEventListener('submit', function(e) {
        // Ensure order_type is set
        let orderTypeInput = singleShipperForm.querySelector('input[name="order_type"]');
        if (!orderTypeInput) {
            orderTypeInput = document.createElement('input');
            orderTypeInput.type = 'hidden';
            orderTypeInput.name = 'order_type';
            singleShipperForm.appendChild(orderTypeInput);
        }
        orderTypeInput.value = 'single_shipper';
        
        // The form inputs are now inline, so no need to generate hidden inputs
        // Just clear validation
        const timeInputs = singleShipperForm.querySelectorAll('input[type="datetime-local"], input[type="time"], input[type="date"]');
        timeInputs.forEach(input => input.setCustomValidity(''));
    });
    
    // Initialize
    updateStopNumbers();
    checkEmptyState();
});
</script>

<!-- Include Order Components JavaScript -->
<script src="{{ asset('js/order-components.js') }}"></script>