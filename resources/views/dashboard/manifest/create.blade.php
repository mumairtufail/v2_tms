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
        <div class="d-flex align-items-center">
            <!-- <span class="me-3 text-muted">Last saved: Just now</span>
            <button class="btn btn-outline-secondary btn-sm"> -->
            <!-- <i data-feather="clock" class="me-1 ml-3"></i> History -->
            </button>
        </div>
    </div>

    <!-- Dispatcher Info -->
    <div class="card shadow-sm mb-4">
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
        </div>
    </div>

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
            <a class="nav-link d-flex align-items-center" id="stops-tab" data-bs-toggle="tab" href="#stops-content" role="tab">
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
        <div class="col-md-4">
            <button class="btn btn-outline-primary w-100 py-3 position-relative hover-lift" data-action="add-driver">
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
    <div class="card shadow mb-4">
    <div class="card-body">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="card-title fw-bold mb-0">Stops</h4>
            <button class="btn btn-outline-primary d-flex align-items-center">
                <i class="bi bi-list me-2"></i>
                Reorder stops
            </button>
        </div>

        <!-- Controls Section -->
        <!-- <div class="bg-light rounded-3 p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="form-check form-switch d-flex align-items-center">
                        <input class="form-check-input me-2" type="checkbox" id="showTravel">
                        <label class="form-check-label" for="showTravel">Show Travel</label>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="form-check form-switch d-flex align-items-center">
                        <input class="form-check-input me-2" type="checkbox" id="autoGroup">
                        <label class="form-check-label" for="autoGroup">Auto Group</label>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Table Section -->
        <div class="table-responsive mb-4">
            <table class="table table-hover border">
                <thead class="table-light">
                    <tr>
                        <th class="fw-semibold">Company</th>
                        <th class="fw-semibold">City</th>
                        <th class="fw-semibold">State</th>
                        <th class="fw-semibold">Country</th>
                        <th class="fw-semibold">Address 1</th>
                        <th class="fw-semibold">Address 2</th>
                        <th class="fw-semibold">Postal</th>
                    </tr>
                </thead>
                <tbody>
                    @if($stops && $stops->count())
                        @foreach($stops as $stop)
                        <tr>
                            <td>{{ $stop->company }}</td>
                            <td>{{ $stop->city }}</td>
                            <td>{{ $stop->state }}</td>
                            <td>{{ $stop->country }}</td>
                            <td>{{ $stop->address1 }}</td>
                            <td>{{ $stop->address2 }}</td>
                            <td>{{ $stop->postal }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <p class="mb-0">No stops found. Add a stop to get started.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-center gap-3" id="stops">
            <button class="btn btn-outline-secondary px-4 d-flex align-items-center" onclick="openTaskModal()">
                <i class="bi bi-check-square me-2"></i>
                Add Task
            </button>
            <button class="btn btn-primary px-4 d-flex align-items-center" data-action="add-stop">
                <i class="bi bi-geo-alt me-2"></i>
                Add Stop
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
        document.getElementById('totalCost').textContent = formatCurrency(total) ;
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
});
</script>

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
            <div class="border-top pt-4" id="resources">
                <h6 class="fw-bold mb-3">Other Documents</h6>
                <p class="text-muted mb-3">No documents uploaded at the moment.</p>
                <button class="btn btn-primary px-4">
                    <i class="fas fa-upload me-2"></i>Upload
                </button>
            </div>
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
@include('dashboard.manifest.add-driver-modal')
@include('dashboard.manifest.add-carrier')
@include('dashboard.manifest.add-task')
@include('dashboard.manifest.add-stop')
@include('dashboard.manifest.add-equipment')

@endsection