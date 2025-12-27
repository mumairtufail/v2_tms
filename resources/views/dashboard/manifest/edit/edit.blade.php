@extends('layouts.app')
@section('content')

<div class="container-fluid py-4">
    <!-- Header with Navigation -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Manifest</a></li>
                    <li class="breadcrumb-item active fw-semibold">{{ $manifest->code }}</li>
                </ol>
            </nav>
            <!-- <span class="badge bg-light text-dark ms-3 py-2 px-3 border">Planning</span> -->
        </div>
    </div>

    <!-- Dispatcher Info -->
    {{-- <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="text-muted me-3">Dispatcher:</div>
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <span class="fw-semibold ml-3">JW</span>
                    </div>
                    <span class="ms-2 fw-medium">John Williams</span>
                    <button class="btn btn-link text-muted p-0 ms-2">
                        <i class="fas fa-pen"></i>
                    </button>
                </div>
            </div>
        </div> --}}
    {{-- </div> --}}

    <!-- Navigation Tabs -->
     <!-- Navigation Tabs -->
     <ul class="nav nav-tabs nav-tabs-modern mb-4" id="manifestTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active d-flex align-items-center" id="map-tab" data-bs-toggle="tab" href="#map-content" role="tab">
                <i data-feather="map" class="me-2 feather-tab" href="#maps"></i>MAP
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link d-flex align-items-center" id="resources-tab" data-bs-toggle="tab" href="#resources-content" role="tab">
                <i data-feather="box" class="me-2 feather-tab" href="#resources"></i>RESOURCES
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link d-flex align-items-center" id="stops-tab" data-bs-toggle="tab" href="#stops" role="tab">
                <i data-feather="navigation" class="me-2 feather-tab" href="#stops"></i>STOPS
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link d-flex align-items-center" id="financials-tab" data-bs-toggle="tab" href="#financials-content" role="tab">
                <i data-feather="dollar-sign" class="me-2 feather-tab" id="#finance"></i>FINANCIALS
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link d-flex align-items-center" id="documents-tab" data-bs-toggle="tab" href="#documents-content" role="tab">
                <i data-feather="file-text" class="me-2 feather-tab" id="#documents"></i>DOCUMENTS
            </a>
        </li>
    </ul>


    <!-- Map Section -->
    <div class="card shadow-sm mb-4" id="maps">
        <div class="card-body p-0">
            <div id="map" style="height: 400px; background-color: #f8f9fa;">
                <iframe style="width: 100%; height: 100%; border: 0;" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d46830151.11795828!2d-119.8093025!3d44.24236485!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x54eab584e432360b%3A0x1c3bb99243deb742!2sUnited%20States!5e0!3m2!1sen!2s!4v1738424902047!5m2!1sen!2s"></iframe>
            </div>
        </div>
    </div>

   <!-- Action Buttons -->
   <div class="row g-4 mb-4">
<!-- Trigger Button for Driver Modal -->
<div class="col-md-4">
  <button class="btn btn-outline-primary w-100 py-3 position-relative hover-lift" 
          onclick="JSM_OpenDriverModal()" data-action="add-driver">
    <i data-feather="users" class="d-block mx-auto mb-2"></i>
    <span class="fw-medium">ADD DRIVER</span>
  </button>
</div>
        <!-- Trigger Button -->
  <div class="col-md-4">
    <button class="btn btn-outline-primary w-100 py-3 position-relative hover-lift" onclick="openModal()">
      <i data-feather="truck" class="d-block mx-auto mb-2"></i>
      <span class="fw-medium">ADD EQUIPMENT</span>
    </button>
  </div>
        <div class="col-md-4">
            <button class="btn btn-outline-primary w-100 py-3 position-relative hover-lift" onclick="openAssignModal()">
                <i data-feather="package" class="d-block mx-auto mb-2"></i>
                <span class="fw-medium">ADD CARRIER</span>
            </button>
        </div>
    </div>
    <!-- Stops Section -->
    <div class="card shadow mb-4" id="stops">
    <div class="card-body">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="card-title fw-bold mb-0">Stops</h4>
            {{-- <button class="btn btn-outline-primary d-flex align-items-center">
                <i class="bi bi-list me-2"></i>
                Reorder stops
            </button> --}}
        </div>
        <!-- Stops Cards Section -->
        @php
            // Get all order stops assigned to this manifest
            $manifestStops = \App\Models\OrderStop::where('manifest_id', $manifest->id)
                ->with(['order.customer', 'commodities', 'accessorials'])
                ->orderBy('sequence_number')
                ->get();
        @endphp
        
        @if($manifestStops && $manifestStops->count())
            <div class="stops-list">
                @foreach($manifestStops as $index => $stop)
                    @php
                        $consigneeData = json_decode($stop->consignee_data ?? '{}', true);
                        $billingData = json_decode($stop->billing_data ?? '{}', true);
                        $statusClass = match($stop->status ?? 'pending') {
                            'completed' => 'bg-success',
                            'in_progress' => 'bg-warning',
                            'pending' => 'bg-secondary',
                            default => 'bg-secondary',
                        };
                        
                        // Get shipper data from stop
                        $shipperData = [
                            'company_name' => $stop->company_name,
                            'address_1' => $stop->address_1,
                            'address_2' => $stop->address_2,
                            'city' => $stop->city,
                            'state' => $stop->state,
                            'postal_code' => $stop->postal_code,
                            'contact_name' => $stop->contact_name,
                            'contact_phone' => $stop->contact_phone
                        ];
                    @endphp
                    
                    <div class="stop-card mb-3" data-stop-sequence="{{ $index + 1 }}">
                        <div class="card shadow-sm position-relative">
                            <!-- Collapsed Row View -->
                            <div class="stop-row-collapsed cursor-pointer" onclick="toggleStopExpansion(this)">
                                <div class="d-flex align-items-center justify-content-between p-3">
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <div class="stop-sequence-badge me-3">
                                            <span class="badge bg-primary rounded-pill px-2 py-1">
                                                <span class="sequence-number">{{ $index + 1 }}</span>
                                            </span>
                                        </div>
                                        <div class="stop-summary flex-grow-1">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-box-arrow-up text-success me-2"></i>
                                                        <div>
                                                            <div class="pickup-company-summary text-truncate fw-medium" style="max-width: 250px;">
                                                                {{ $shipperData['company_name'] ?: 'Shipper location not set' }}
                                                            </div>
                                                            <small class="pickup-address-summary text-muted text-truncate d-block" style="max-width: 250px;">
                                                                @if($shipperData['address_1'] || $shipperData['city'])
                                                                    {{ $shipperData['address_1'] }}{{ $shipperData['address_1'] && $shipperData['city'] ? ', ' : '' }}{{ $shipperData['city'] }}{{ $shipperData['state'] ? ', ' . $shipperData['state'] : '' }}
                                                                @else
                                                                    Address not set
                                                                @endif
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-box-arrow-in-down text-primary me-2"></i>
                                                        <div>
                                                            <div class="delivery-company-summary text-truncate fw-medium" style="max-width: 250px;">
                                                                {{ $consigneeData['company_name'] ?? 'Consignee location not set' }}
                                                            </div>
                                                            <small class="delivery-address-summary text-muted text-truncate d-block" style="max-width: 250px;">
                                                                @if(($consigneeData['address_1'] ?? '') || ($consigneeData['city'] ?? ''))
                                                                    {{ $consigneeData['address_1'] ?? '' }}{{ ($consigneeData['address_1'] ?? '') && ($consigneeData['city'] ?? '') ? ', ' : '' }}{{ $consigneeData['city'] ?? '' }}{{ ($consigneeData['state'] ?? '') ? ', ' . $consigneeData['state'] : '' }}
                                                                @else
                                                                    Address not set
                                                                @endif
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Order and Status Info -->
                                            <div class="row mt-2">
                                                <div class="col-12">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <small class="text-muted">
                                                            <i class="bi bi-receipt me-1"></i>
                                                            Order: 
                                                            @if($stop->order)
                                                                <a href="{{ route('orders.edit', $stop->order->id) }}" class="text-primary text-decoration-none fw-medium" onclick="event.stopPropagation()">
                                                                    {{ $stop->order->order_number }}
                                                                </a>
                                                            @else
                                                                N/A
                                                            @endif
                                                        </small>
                                                        @if($stop->order && $stop->order->customer)
                                                            <small class="text-muted">
                                                                <i class="bi bi-building me-1"></i>
                                                                {{ $stop->order->customer->name }}
                                                            </small>
                                                        @endif
                                                        <span class="badge {{ $statusClass }}">
                                                            {{ ucfirst($stop->status ?? 'Pending') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="stop-connection-indicator ms-3">
                                            <i class="bi bi-chevron-down expand-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
            <!-- Expanded Form View (Initially Hidden) -->
            <div class="stop-form-expanded" style="display: none;">
                <div class="border-top bg-light px-3 py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-pencil-square me-2"></i>
                            Stop <span class="sequence-number">{{ $index + 1 }}</span> Details
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
                            <div class="pickup-section border rounded p-3 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="pickup-icon me-2">
                                        <i class="bi bi-box-arrow-up text-success"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-success small">SHIPPER</h6>
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Company name</label>
                                    <div class="text-dark">{{ $shipperData['company_name'] ?: 'Not set' }}</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Address</label>
                                    <div class="text-dark">
                                        {{ $shipperData['address_1'] ?: 'Not set' }}
                                        @if($shipperData['address_2'])
                                            <br>{{ $shipperData['address_2'] }}
                                        @endif
                                    </div>
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-4">
                                        <label class="form-label small fw-medium">City</label>
                                        <div class="text-dark small">{{ $shipperData['city'] ?: 'Not set' }}</div>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-medium">State</label>
                                        <div class="text-dark small">{{ $shipperData['state'] ?: 'Not set' }}</div>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-medium">ZIP</label>
                                        <div class="text-dark small">{{ $shipperData['postal_code'] ?: 'Not set' }}</div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Contact name</label>
                                    <div class="text-dark">{{ $shipperData['contact_name'] ?: 'Not set' }}</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Phone</label>
                                    <div class="text-dark">{{ $shipperData['contact_phone'] ?: 'Not set' }}</div>
                                </div>
                                
                                <!-- Ready Time Section -->
                                <div class="mt-3 pt-2 border-top">
                                    <h6 class="fw-bold mb-2 text-success small">READY TIME</h6>
                                    @if($stop->start_time || $stop->end_time)
                                        <div class="small">
                                            <strong>From:</strong> {{ $stop->start_time ? \Carbon\Carbon::parse($stop->start_time)->format('M d, Y H:i') : 'Not set' }}<br>
                                            <strong>To:</strong> {{ $stop->end_time ? \Carbon\Carbon::parse($stop->end_time)->format('M d, Y H:i') : 'Not set' }}
                                        </div>
                                    @else
                                        <div class="text-muted small">No time window set</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Consignee Information -->
                        <div class="col-lg-4">
                            <div class="delivery-section border rounded p-3 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="delivery-icon me-2">
                                        <i class="bi bi-box-arrow-in-down text-primary"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-primary small">CONSIGNEE</h6>
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Company name</label>
                                    <div class="text-dark">{{ $consigneeData['company_name'] ?? 'Not set' }}</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Address</label>
                                    <div class="text-dark">
                                        {{ $consigneeData['address_1'] ?? 'Not set' }}
                                        @if($consigneeData['address_2'] ?? false)
                                            <br>{{ $consigneeData['address_2'] }}
                                        @endif
                                    </div>
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-4">
                                        <label class="form-label small fw-medium">City</label>
                                        <div class="text-dark small">{{ $consigneeData['city'] ?? 'Not set' }}</div>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-medium">State</label>
                                        <div class="text-dark small">{{ $consigneeData['state'] ?? 'Not set' }}</div>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-medium">ZIP</label>
                                        <div class="text-dark small">{{ $consigneeData['zip'] ?? 'Not set' }}</div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Contact name</label>
                                    <div class="text-dark">{{ $consigneeData['contact_name'] ?? 'Not set' }}</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Phone</label>
                                    <div class="text-dark">{{ $consigneeData['phone'] ?? 'Not set' }}</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Contact email</label>
                                    <div class="text-dark">{{ $consigneeData['email'] ?? 'Not set' }}</div>
                                </div>
                                
                                <!-- Delivery Time Section -->
                                <div class="mt-3 pt-2 border-top">
                                    <h6 class="fw-bold mb-2 text-primary small">DELIVERY TIME</h6>
                                    @if(($consigneeData['delivery_start_time'] ?? false) || ($consigneeData['delivery_end_time'] ?? false))
                                        <div class="small">
                                            <strong>From:</strong> {{ ($consigneeData['delivery_start_time'] ?? false) ? \Carbon\Carbon::parse($consigneeData['delivery_start_time'])->format('M d, Y H:i') : 'Not set' }}<br>
                                            <strong>To:</strong> {{ ($consigneeData['delivery_end_time'] ?? false) ? \Carbon\Carbon::parse($consigneeData['delivery_end_time'])->format('M d, Y H:i') : 'Not set' }}
                                        </div>
                                    @else
                                        <div class="text-muted small">No delivery window set</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="col-lg-4">
                            <div class="billing-section border rounded p-3 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="billing-icon me-2">
                                        <i class="bi bi-receipt text-warning"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-warning small">ADDITIONAL INFO</h6>
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">REF Number</label>
                                    <div class="text-dark">{{ $billingData['ref_number'] ?? 'Not set' }}</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Customer PO</label>
                                    <div class="text-dark">{{ $billingData['customer_po_number'] ?? 'Not set' }}</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Container Number</label>
                                    <div class="text-dark">{{ $billingData['container_number'] ?? 'Not set' }}</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Declared Value</label>
                                    <div class="text-dark">
                                        @if($billingData['declared_value'] ?? false)
                                            ${{ number_format($billingData['declared_value'], 2) }} {{ $billingData['currency'] ?? 'USD' }}
                                        @else
                                            Not set
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Customs Broker</label>
                                    <div class="text-dark">{{ $billingData['customs_broker'] ?? 'Not set' }}</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Port of Entry</label>
                                    <div class="text-dark">{{ $billingData['port_of_entry'] ?? 'Not set' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Commodities & Freight Info -->
                    @if($stop->commodities && $stop->commodities->count())
                        <div class="commodities-section mt-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-box me-2 text-info"></i>
                                <h6 class="mb-0 fw-semibold">FREIGHT ({{ $stop->commodities->count() }} items)</h6>
                            </div>
                            <div class="row g-2">
                                @foreach($stop->commodities as $commodity)
                                    <div class="col-md-6">
                                        <div class="freight-item p-2 bg-light rounded small">
                                            <div class="fw-semibold">{{ $commodity->description ?? 'N/A' }}</div>
                                            <div class="text-muted">
                                                Qty: {{ $commodity->quantity ?? 0 }} | 
                                                Weight: {{ $commodity->weight ?? 0 }} lbs
                                                @if($commodity->length && $commodity->width && $commodity->height)
                                                    | {{ $commodity->length }}×{{ $commodity->width }}×{{ $commodity->height }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Notes Section -->
                    @if($stop->notes || !empty($consigneeData['special_instructions']))
                        <div class="notes-section mt-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-sticky me-2 text-warning"></i>
                                <h6 class="mb-0 fw-semibold">NOTES</h6>
                            </div>
                            <div class="p-2 bg-warning bg-opacity-10 rounded small">
                                @if($stop->notes)
                                    <div><strong>Pickup Notes:</strong> {{ $stop->notes }}</div>
                                @endif
                                @if(!empty($consigneeData['special_instructions']))
                                    <div><strong>Delivery Notes:</strong> {{ $consigneeData['special_instructions'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-geo-alt text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
                <h5 class="text-muted mb-2">No Stops Assigned</h5>
                <p class="text-muted mb-4">No order stops have been assigned to this manifest yet.</p>
                {{-- <button class="btn btn-primary" onclick="openOrderAssignmentModal()">
                    <i class="bi bi-plus me-2"></i>Assign Orders to Manifest
                </button> --}}
            </div>
        @endif
        <!-- Action Buttons for Stops -->
        <div class="d-flex justify-content-center gap-3" id="stops">
           
              <button class="btn btn-primary px-4 d-flex align-items-center" data-action="add-stop">
                <i class="bi bi-geo-alt me-2"></i>
                Add Stop
            </button>
        </div>
        <!-- New Table Section for Tasks -->
        <div class="table-responsive mt-5">
            <h4 class="fw-bold mb-3">Tasks</h4>
            <table class="table table-hover border">
                <thead class="table-light">
                    <tr>
                        <th class="fw-semibold">Type</th>
                        <th class="fw-semibold">Start Date</th>
                        <th class="fw-semibold">Start Time</th>
                        <th class="fw-semibold">End Date</th>
                        <th class="fw-semibold">End Time</th>
                        <th class="fw-semibold">Assignee</th>
                        <th class="fw-semibold">Trailer ID</th>
                        <th class="fw-semibold">Security ID</th>
                        <th class="fw-semibold">Hours</th>
                        <th class="fw-semibold">Notes</th>
                        <th class="fw-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if($tasks && $tasks->count())
                        @foreach($tasks as $task)
                        <tr>
                            <td>{{ $task->type }}</td>
                            <td>{{ $task->task_start_date }}</td>
                            <td>{{ $task->task_start_time }}</td>
                            <td>{{ $task->task_end_date }}</td>
                            <td>{{ $task->task_end_time }}</td>
                            <td>{{ $task->assignee }}</td>
                            <td>{{ $task->trailer_id }}</td>
                            <td>{{ $task->security_id }}</td>
                            <td>{{ $task->hours }}</td>
                            <td>{{ $task->notes }}</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="editTask({{ json_encode($task) }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteTask({{ $task->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                <p class="mb-0">No tasks found. Add a task to get started.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center gap-3" id="stops">
           
          <button class="btn btn-outline-secondary px-4 d-flex align-items-center" onclick="openTaskModal()">
                <i class="bi bi-check-square me-2"></i>
                Add Task
            </button>
           
        </div>

    </div>
</div>

<!-- Add this in your <head> section for Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Cost Estimates -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title fw-bold mb-0" id="finance">Cost Estimates</h5>
            <button type="button" class="btn btn-outline-primary btn-sm" id="addRow">
                <i class="bi bi-plus-lg"></i> Add Row
            </button>
        </div>
        
        <!-- Use the same manifest_id from the controller (passed as $manifest->id or similar) -->
        <input type="hidden" id="manifest_id" name="manifest_id" value="{{ $manifest->id }}">

        <!-- Change form action to update endpoint and use method spoofing for PUT -->
        <form  action="{{ route('cost-estimates.update', $manifest->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="manifest_id" value="{{ $manifest->id }}">
            <div class="table-responsive">
                <table class="table table-bordered border-light">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3">TYPE</th>
                            <th class="py-3">DESCRIPTION</th>
                            <th class="py-3" style="width: 100px">QTY</th>
                            <th class="py-3" style="width: 120px">RATE</th>
                            <th class="py-3" style="width: 120px">EST. COST</th>
                            <th style="width: 50px"></th>
                        </tr>
                    </thead>
                    <tbody id="costEstimateRows">
                        @forelse($costEstimates as $estimate)
                        <tr class="estimate-row">
                            <td>
                                <select class="form-select border-0 bg-light type-input" name="type[]">
                                    <option value="Miscellaneous" {{ $estimate->type == 'Miscellaneous' ? 'selected' : '' }}>Miscellaneous</option>
                                    <!-- Add other options as needed -->
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control border-0 description-input" name="description[]" placeholder="Description" value="{{ $estimate->description }}">
                            </td>
                            <td>
                                <input type="number" class="form-control border-0 text-end qty-input" name="qty[]" value="{{ $estimate->qty }}" min="0">
                            </td>
                            <td>
                                <input type="number" class="form-control border-0 text-end rate-input" name="rate[]" value="{{ number_format($estimate->rate,2,'.','') }}" step="0.01" min="0">
                            </td>
                            <td class="text-end est-cost">${{ number_format($estimate->est_cost,2) }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-link text-danger remove-row" style="display: {{ count($costEstimates)==1 ? 'none' : 'block'}};">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr class="estimate-row">
                            <td>
                                <select class="form-select border-0 bg-light type-input" name="type[]">
                                    <option value="Miscellaneous">Miscellaneous</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control border-0 description-input" name="description[]" placeholder="Description">
                            </td>
                            <td>
                                <input type="number" class="form-control border-0 text-end qty-input" name="qty[]" value="0" min="0">
                            </td>
                            <td>
                                <input type="number" class="form-control border-0 text-end rate-input" name="rate[]" value="0.00" step="0.01" min="0">
                            </td>
                            <td class="text-end est-cost">$0.00</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-link text-danger remove-row" style="display: none;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <td colspan="4" class="text-end fw-bold">TOTAL</td>
                            <td class="text-end fw-bold" id="totalCost">$0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary">Save Estimates</button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('costEstimateRows');
    const addRowBtn = document.getElementById('addRow');
    const form = document.querySelector('form[action*="cost-estimates"]');

    // Function to format number as currency
    function formatCurrency(number) {
        return '$' + number.toFixed(2);
    }

    // Calculate estimated cost for a row
    function calculateRowCost(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
        const estCost = qty * rate;
        row.querySelector('.est-cost').textContent = formatCurrency(estCost);
        return estCost;
    }

    // Calculate total cost
    function updateTotal() {
        const rows = document.querySelectorAll('.estimate-row');
        const total = Array.from(rows).reduce((sum, row) => {
            return sum + calculateRowCost(row);
        }, 0);
        document.getElementById('totalCost').textContent = formatCurrency(total);
    }

    // Add new row
    function addNewRow() {
        const firstRow = tbody.querySelector('.estimate-row');
        const newRow = firstRow.cloneNode(true);
        
        // Reset values
        newRow.querySelector('.description-input').value = '';
        newRow.querySelector('.qty-input').value = '0';
        newRow.querySelector('.rate-input').value = '0.00';
        newRow.querySelector('.est-cost').textContent = '$0.00';
        
        // Show remove button for all rows
        newRow.querySelector('.remove-row').style.display = 'block';
        
        // Make sure all existing rows have delete buttons visible
        document.querySelectorAll('.estimate-row .remove-row').forEach(btn => {
            btn.style.display = 'block';
        });
        
        tbody.appendChild(newRow);
        attachRowEvents(newRow);
        updateTotal(); // Update totals after adding row
    }

    // Attach events to row inputs
    function attachRowEvents(row) {
        const qtyInput = row.querySelector('.qty-input');
        const rateInput = row.querySelector('.rate-input');
        const removeBtn = row.querySelector('.remove-row');
        
        // Use 'input' event instead of 'change' for immediate updates
        qtyInput.addEventListener('input', function() {
            calculateRowCost(row);
            updateTotal();
        });
        
        rateInput.addEventListener('input', function() {
            calculateRowCost(row);
            updateTotal();
        });
        
        removeBtn.addEventListener('click', function() {
            const allRows = document.querySelectorAll('.estimate-row');
            if (allRows.length > 1) {
                row.remove();
                
                // If only one row remains, hide its delete button
                const remainingRows = document.querySelectorAll('.estimate-row');
                if (remainingRows.length === 1) {
                    remainingRows[0].querySelector('.remove-row').style.display = 'none';
                }
                
                updateTotal();
            }
        });
    }

    // Add row button click
    addRowBtn.addEventListener('click', addNewRow);

    // Initialize all existing rows
    document.querySelectorAll('.estimate-row').forEach(function(row) {
        attachRowEvents(row);
        calculateRowCost(row); // Calculate initial cost for each row
    });
    
    // Calculate initial total
    updateTotal();

    // Fix delete button visibility on page load
    const allRows = document.querySelectorAll('.estimate-row');
    if (allRows.length > 1) {
        allRows.forEach(row => {
            row.querySelector('.remove-row').style.display = 'block';
        });
    } else if (allRows.length === 1) {
        allRows[0].querySelector('.remove-row').style.display = 'none';
    }
    
    // Stop status update functionality
    window.updateStopStatus = function(stopId, status) {
        if (!confirm(`Are you sure you want to mark this stop as ${status.replace('_', ' ')}?`)) {
            return;
        }
        
        fetch(`/order-stops/${stopId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh to show updated status
            } else {
                alert('Error updating stop status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating stop status');
        });
    };
    
    // Order assignment modal functionality
    window.openOrderAssignmentModal = function() {
        // This would open a modal to assign orders to the manifest
        alert('Order assignment functionality to be implemented');
    };
});

// Function to toggle stop expansion (global scope for inline onclick)
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
}
</script>

<style>
/* Manifest Stops Styles */
.stops-list {
    margin-bottom: 2rem;
}

.stop-card {
    transition: all 0.3s ease;
    position: relative;
}

.stop-card:hover {
    transform: translateY(-2px);
}

.stop-row-collapsed {
    cursor: pointer;
    transition: all 0.2s ease;
    border-radius: 16px 16px 0 0;
}

.stop-row-collapsed:hover {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.pickup-company-summary, .delivery-company-summary {
    font-size: 0.95rem;
}

.pickup-address-summary, .delivery-address-summary {
    font-size: 0.8rem;
}

.stop-form-expanded {
    border-top: 1px solid #dee2e6;
}

.expand-icon {
    transition: transform 0.2s ease;
}

.stop-row-collapsed.expanded .expand-icon {
    transform: rotate(180deg);
}

.stop-sequence-badge .badge {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
    font-weight: 600;
}

.pickup-section {
    background: linear-gradient(135deg, #d4edda 0%, #f8fff9 100%);
    border: 2px solid #28a745 !important;
    border-radius: 12px;
    min-height: 400px;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.1);
}

.delivery-section {
    background: linear-gradient(135deg, #cce7ff 0%, #f8fbff 100%);
    border: 2px solid #007bff !important;
    border-radius: 12px;
    min-height: 400px;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.1);
}

.billing-section {
    background: linear-gradient(135deg, #fff3cd 0%, #fffdf7 100%);
    border: 2px solid #ffc107 !important;
    border-radius: 12px;
    min-height: 400px;
    box-shadow: 0 2px 4px rgba(255, 193, 7, 0.1);
}

.stop-card {
    border-radius: 16px;
    overflow: hidden;
}

.stop-card .card {
    border: 1px solid #e3e6f0;
    border-radius: 16px;
    transition: all 0.2s ease;
}

.stop-card:hover .card {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.stop-sequence-badge .badge {
    background: linear-gradient(45deg, #007bff, #0056b3);
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
}

.pickup-icon, .delivery-icon, .billing-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.pickup-icon {
    background: rgba(40, 167, 69, 0.15);
    color: #28a745;
}

.delivery-icon {
    background: rgba(0, 123, 255, 0.15);
    color: #007bff;
}

.billing-icon {
    background: rgba(255, 193, 7, 0.15);
    color: #ffc107;
}

.cursor-pointer {
    cursor: pointer;
}

.pickup-icon, .delivery-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}

.pickup-icon {
    background: rgba(40, 167, 69, 0.1);
}

.delivery-icon {
    background: rgba(0, 123, 255, 0.1);
}

.time-window {
    border-left: 3px solid #007bff;
}

.commodities-section {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
}

.freight-item {
    border-left: 3px solid #17a2b8;
}

.notes-section {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
}

.contact-info .small {
    margin-bottom: 0.25rem;
}

.contact-info .small:last-child {
    margin-bottom: 0;
}

/* Status badges */
.badge.bg-success { background-color: #198754 !important; }
.badge.bg-warning { background-color: #ffc107 !important; color: #000 !important; }
.badge.bg-secondary { background-color: #6c757d !important; }

/* Empty state */
.empty-state {
    background: white;
    border: 2px dashed #dee2e6;
    border-radius: 0.5rem;
    margin: 2rem 0;
    padding: 3rem 2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .stop-card .card-body .row > .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .stop-card .card-body .row > .col-md-6:last-child {
        margin-bottom: 0;
    }
    
    .pickup-section, .delivery-section {
        min-height: auto;
    }
}

/* Dropdown improvements */
.dropdown-menu {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.dropdown-item {
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    transform: translateX(4px);
}
</style>

      <!-- Cost Estimates -->
      <!-- <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-4">Other Costs</h5>
            <div class="table-responsive">
                <table class="table table-bordered border-light">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3">TYPE</th>
                            <th class="py-3">DESCRIPTION</th>
                            <th class="py-3" style="width: 100px">QTY</th>
                            <th class="py-3" style="width: 120px">RATE</th>
                            <th class="py-3" style="width: 120px">EST. COST</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select class="form-select border-0 bg-light">
                                    <option>Miscellaneous</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control border-0" placeholder="Description">
                            </td>
                            <td>
                                <input type="number" class="form-control border-0 text-end" value="0">
                            </td>
                            <td>
                                <input type="number" class="form-control border-0 text-end" value="0.00">
                            </td>
                            <td class="text-end">$0.00</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <td colspan="4" class="text-end fw-bold">TOTAL</td>
                            <td class="text-end fw-bold">$0.00 CAD</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div> -->

    <!-- Documents Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-4" id="documents">Documents</h5>
            <div class="mb-4">
                <a href="https://innovations.roserocket.com/#/app/documents/trips/2347efb7-7623-48a5-a2a6-4aa92a1244a4/rate-confirmation" class="btn btn-outline-primary px-4">
                    <i class="fas fa-file-invoice me-2"></i>
                    Rate confirmation
                </a>
            </div>
            {{-- <div class="border-top pt-4" id="resources">
                <h6 class="fw-bold mb-3">Other Documents</h6>
                <p class="text-muted mb-3">No documents uploaded at the moment.</p>
                <button class="btn btn-primary px-4">
                    <i class="fas fa-upload me-2"></i>Upload
                </button>
            </div> --}}
        </div>
    </div>
</div>

<style>
    /* Modern styling */
    .nav-tabs-modern {
        border-bottom: 1px solid #dee2e6;
    }
    
    .nav-tabs-modern .nav-link {
        color: #6c757d;
        border: none;
        padding: 0.75rem 1rem;
        margin-right: 1rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .nav-tabs-modern .nav-link.active {
        color: #0d6efd;
        border-bottom: 2px solid #0d6efd;
        background: none;
    }
    
    .card {
        border: none;
        transition: all 0.2s ease;
    }
    
    .hover-lift {
        transition: all 0.2s ease;
    }
    
    .hover-lift:hover {
        transform: translateY(-2px);
    }
    
    .form-switch .form-check-input {
        cursor: pointer;
        height: 1.25rem;
        width: 2.5rem;
    }
    
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    .btn-outline-primary {
        border-color: #dee2e6;
    }
    
    .btn-outline-primary:hover {
        background-color: #f8f9fa;
        border-color: #0d6efd;
        color:black;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .nav-tabs-modern .nav-link {
            padding: 0.5rem 0.75rem;
            margin-right: 0.5rem;
            font-size: 0.875rem;
        }
    }
</style>




<!-- modals -->
@include('dashboard.manifest.edit.add-carrier')
@include('dashboard.manifest.edit.add-task')
@include('dashboard.manifest.edit.add-stop')
@include('dashboard.manifest.edit.add-equipment')
@include('dashboard.manifest.edit.add-driver-modal')

@endsection